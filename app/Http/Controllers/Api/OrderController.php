<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\Product;
use App\Models\Client;
use App\Models\Service;
use App\Models\Setting;
use App\Enums\OrderStatus;
use App\Enums\PaymentStatus;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\Rule;
use DomainException;
use App\Services\OrderTicketPdfService;

class OrderController extends Controller
{
    /**
     * Contexto para la pantalla "Crear pedido" (productos + draft + servicios opcionales).
     */
    public function createContext(Request $request)
    {
        $auth = $request->user();

        // Reutilizar el último borrador del usuario o crear uno nuevo
        $draft = Order::query()
            ->where('user_id', $auth->id)
            ->where('status', OrderStatus::DRAFT->value)
            ->latest('id')
            ->first();

        if (!$draft) {
            $company = $auth->rootCompany();
            $companyId = $company ? $company->id : $auth->id;
            $branchId = $auth->isAdmin() || $auth->isCompany()
                ? $auth->id
                : ($auth->parent_id ?: $auth->id);

            $draft = Order::create([
                'user_id' => $auth->id,
                'company_id' => $companyId,
                'branch_id' => $branchId,
                'status' => OrderStatus::DRAFT->value,
                'payment_status' => PaymentStatus::PENDING->value,
                'discount' => 0,
                'tax_amount' => 0,
                'total' => 0,
            ]);
        }

        // Productos (misma lógica de la vista web)
        $productsQuery = $auth->isMaster()
            ? Product::query()->withoutGlobalScope('byUser')
            : Product::availableFor($auth);

        $products = $productsQuery
            ->active()
            ->when($request->filled('q'), function ($q) use ($request) {
                $term = trim((string) $request->input('q'));
                $lc = mb_strtolower($term, 'UTF-8');
                $q->where(function($w) use ($lc) {
                    $w->whereRaw('LOWER(name) LIKE ?', ["%{$lc}%"])
                      ->orWhereRaw('LOWER(sku) LIKE ?', ["%{$lc}%"]);
                });
            })
            ->orderBy('name')
            ->paginate(min((int) $request->input('per_page', 24), 100));

        // Servicios (opcional)
        $services = Service::availableFor($auth)
            ->active()
            ->orderBy('name')
            ->paginate(min((int) $request->input('services_per_page', 24), 100), ['*'], 'services_page');

        $snapshot = app(\App\Services\OrderService::class)->snapshot($draft->id);

        return response()->json([
            'success' => true,
            'data' => [
                'order' => [
                    'id' => (int) $draft->id,
                    'status' => $draft->status,
                    'payment_status' => $draft->payment_status,
                    'items' => $snapshot['items'] ?? [],
                    'total' => $snapshot['total'] ?? 0.0,
                ],
                'products' => collect($products->items())->map(fn($p) => $this->formatProductCard($p))->values(),
                'services' => collect($services->items())->map(fn($s) => $this->formatServiceCard($s))->values(),
            ],
            'meta' => [
                'products' => [
                    'current_page' => $products->currentPage(),
                    'last_page' => $products->lastPage(),
                    'per_page' => $products->perPage(),
                    'total' => $products->total(),
                ],
                'services' => [
                    'current_page' => $services->currentPage(),
                    'last_page' => $services->lastPage(),
                    'per_page' => $services->perPage(),
                    'total' => $services->total(),
                ],
            ],
        ], 200);
    }

    /**
     * Lista de pedidos con paginación
     */
    public function index(Request $request)
    {
        $auth = $request->user();

        $query = Order::availableFor($auth)
            ->with(['client:id,name,phone', 'user:id,name', 'items.product:id,name,price'])
            ->when($request->filled('status'), function ($q) use ($request) {
                $q->where('status', $request->status);
            })
            ->when($request->filled('payment_status'), function ($q) use ($request) {
                $q->where('payment_status', $request->payment_status);
            })
            ->when($request->filled('client_id'), function ($q) use ($request) {
                $q->where('client_id', $request->client_id);
            })
            ->when($request->filled('from_date'), function ($q) use ($request) {
                $q->whereDate('created_at', '>=', $request->from_date);
            })
            ->when($request->filled('to_date'), function ($q) use ($request) {
                $q->whereDate('created_at', '<=', $request->to_date);
            })
            ->when($request->filled('is_scheduled'), function ($q) use ($request) {
                $q->where('is_scheduled', filter_var($request->is_scheduled, FILTER_VALIDATE_BOOLEAN));
            });

        $perPage = min((int) $request->input('per_page', 20), 100);
        $orders = $query->orderBy('created_at', 'desc')->paginate($perPage);

        return response()->json([
            'success' => true,
            'data' => $orders->items(),
            'meta' => [
                'current_page' => $orders->currentPage(),
                'last_page' => $orders->lastPage(),
                'per_page' => $orders->perPage(),
                'total' => $orders->total(),
            ],
        ], 200);
    }

    /**
     * Mostrar un pedido específico
     */
    public function show(Request $request, Order $order)
    {
        $auth = $request->user();

        if (!$this->canAccessOrder($auth, $order)) {
            return response()->json([
                'success' => false,
                'message' => 'No tienes acceso a este pedido',
            ], 403);
        }

        $order->load([
            'client:id,name,email,phone,address',
            'user:id,name',
            'items.product:id,name,price,image',
            'paymentMethods'
        ]);

        return response()->json([
            'success' => true,
            'data' => $order,
        ], 200);
    }

    /**
     * Datos del comprobante (para app móvil)
     */
    public function ticket(Request $request, Order $order)
    {
        $auth = $request->user();
        $started = microtime(true);

        try {
            if (!$this->canAccessOrder($auth, $order)) {
                return response()->json([
                    'success' => false,
                    'message' => 'No tienes acceso a este pedido',
                ], 403);
            }

            $bundle = $this->buildTicketPayload($request, $auth, $order);

            $durationMs = round((microtime(true) - $started) * 1000, 1);
            Log::info('api.order.ticket.ok', [
                'order_id' => $order->id,
                'user_id' => $auth?->id,
                'ms' => $durationMs,
                'format' => 'json',
            ]);

            return response()->json([
                'success' => true,
                'data' => $bundle['payload'],
            ], 200);
        } catch (\Throwable $e) {
            $durationMs = round((microtime(true) - $started) * 1000, 1);
            Log::error('api.order.ticket.error', [
                'order_id' => $order->id,
                'user_id' => $auth?->id,
                'ms' => $durationMs,
                'msg' => $e->getMessage(),
            ]);
            throw $e;
        }
    }

    /**
     * PDF inline del comprobante (misma lógica que la vista web)
     */
    public function ticketPdf(Request $request, Order $order, OrderTicketPdfService $pdf)
    {
        $auth = $request->user();
        $started = microtime(true);
        $format = $request->query('format') ?: 'pdf';

        try {
            if (!$this->canAccessOrder($auth, $order)) {
                return response()->json([
                    'success' => false,
                    'message' => 'No tienes acceso a este pedido',
                ], 403);
            }

            $bundle = $this->buildTicketPayload($request, $auth, $order);

            // Útil para debugging en la app móvil: format=json
            if ($request->query('format') === 'json') {
                $durationMs = round((microtime(true) - $started) * 1000, 1);
                Log::info('api.order.ticket_pdf.ok', [
                    'order_id' => $order->id,
                    'user_id' => $auth?->id,
                    'ms' => $durationMs,
                    'format' => 'json',
                ]);

                return response()->json([
                    'success' => true,
                    'data' => $bundle['payload'],
                ], 200);
            }

            // Formato HTML embebible (mismo diseño que web, sin controles)
            if ($request->query('format') === 'html') {
                $html = view('pdf.order-ticket', [
                    'order' => $bundle['order'],
                    'logoUrl' => $bundle['logo_url'],
                    'appName' => $bundle['app_name'],
                    'subtotal' => $bundle['totals']['subtotal'],
                    'discount' => $bundle['totals']['discount'],
                    'tax' => $bundle['totals']['tax_amount'],
                    'total' => $bundle['totals']['total'],
                    'totals' => $bundle['totals'],
                    'qr' => $bundle['payload']['qr'] ?? null,
                    'paymentMethod' => $bundle['payload']['payment_method'] ?? null,
                ])->render();

                $durationMs = round((microtime(true) - $started) * 1000, 1);
                Log::info('api.order.ticket_pdf.ok', [
                    'order_id' => $order->id,
                    'user_id' => $auth?->id,
                    'ms' => $durationMs,
                    'format' => 'html',
                ]);

                return response($html, 200, [
                    'Content-Type' => 'text/html; charset=UTF-8',
                    'Cache-Control' => 'no-store, no-cache, must-revalidate',
                ]);
            }

            $filename = 'comprobante-' . ($bundle['payload']['order_number'] ?? $order->id) . '.pdf';

            $pdfContent = $pdf->render($bundle['order'], [
                'order' => $bundle['order'],
                'logoUrl' => $bundle['logo_base64'] ?? $bundle['logo_url'],
                'appName' => $bundle['app_name'],
                'subtotal' => $bundle['totals']['subtotal'],
                'discount' => $bundle['totals']['discount'],
                'tax' => $bundle['totals']['tax_amount'],
                'total' => $bundle['totals']['total'],
                'totals' => $bundle['totals'],
                'qr' => $bundle['payload']['qr'] ?? null,
                'paymentMethod' => $bundle['payload']['payment_method'] ?? null,
            ]);

            // Para la app móvil: opción de pedir el PDF en base64 (evita lidiar con blobs/binary).
            if ($request->query('format') === 'base64') {
                $base64 = base64_encode($pdfContent);

                $durationMs = round((microtime(true) - $started) * 1000, 1);
                Log::info('api.order.ticket_pdf.ok', [
                    'order_id' => $order->id,
                    'user_id' => $auth?->id,
                    'ms' => $durationMs,
                    'format' => 'base64',
                    'size' => strlen($pdfContent),
                ]);

                return response()->json([
                    'success' => true,
                    'data' => [
                        'pdf_base64' => $base64,
                        'filename' => $filename,
                        'size' => strlen($pdfContent),
                    ],
                ], 200);
            }

            // Descargar directo (attachment) para que el share nativo del móvil lo tome como archivo.
            $asDownload = $request->boolean('download');
            $disposition = $asDownload ? 'attachment' : 'inline';
            $contentLength = strlen($pdfContent);

            $durationMs = round((microtime(true) - $started) * 1000, 1);
            Log::info('api.order.ticket_pdf.ok', [
                'order_id' => $order->id,
                'user_id' => $auth?->id,
                'ms' => $durationMs,
                'format' => $format,
                'size' => $contentLength,
                'download' => $asDownload,
            ]);

            return response($pdfContent, 200, [
                'Content-Type' => 'application/pdf',
                'Content-Length' => $contentLength,
                'Content-Disposition' => "{$disposition}; filename=\"{$filename}\"",
                'Cache-Control' => $asDownload
                    ? 'private, max-age=900, must-revalidate'
                    : 'no-store, no-cache, must-revalidate',
            ]);
        } catch (\Throwable $e) {
            $durationMs = round((microtime(true) - $started) * 1000, 1);
            Log::error('api.order.ticket_pdf.error', [
                'order_id' => $order->id,
                'user_id' => $auth?->id,
                'ms' => $durationMs,
                'format' => $format,
                'msg' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            // Devolver error amigable en formato JSON
            $errorMessage = 'Error al generar el comprobante';
            $statusCode = 500;

            // Detectar tipo de error para respuesta más específica
            if ($e instanceof \DomainException) {
                $errorMessage = $e->getMessage();
                $statusCode = 422;
            } elseif ($e instanceof \InvalidArgumentException) {
                $errorMessage = 'Datos inválidos para generar el comprobante';
                $statusCode = 400;
            } elseif (str_contains($e->getMessage(), 'timeout')) {
                $errorMessage = 'La generación del PDF tardó demasiado. Intenta de nuevo.';
                $statusCode = 504;
            } elseif (str_contains($e->getMessage(), 'memory')) {
                $errorMessage = 'Error de memoria al generar el PDF. Contacta a soporte.';
                $statusCode = 507;
            }

            return response()->json([
                'success' => false,
                'message' => $errorMessage,
                'error' => config('app.debug') ? $e->getMessage() : null,
                'order_id' => $order->id,
            ], $statusCode);
        }
    }

    /**
     * Arma la data y el contexto del comprobante (compartido entre JSON y PDF).
     */
    private function buildTicketPayload(Request $request, $auth, Order $order): array
    {
        $order->load([
            'items.product:id,name,sku,price,image',
            'items.service:id,name,price',
            'client:id,name,email,phone',
        ]);

        $items = $order->items->map(function ($item) {
            $subtotal = $item->subtotal ?? ((float) $item->quantity * (float) $item->unit_price);

            return [
                'id' => (int) $item->id,
                'type' => $item->product_id ? 'product' : 'service',
                'product_id' => $item->product_id ? (int) $item->product_id : null,
                'service_id' => $item->service_id ? (int) $item->service_id : null,
                'name' => $item->product->name
                    ?? $item->service->name
                    ?? $item->product_name
                    ?? $item->service_name
                    ?? 'Item',
                'quantity' => (float) $item->quantity,
                'unit_price' => (float) $item->unit_price,
                'subtotal' => round((float) $subtotal, 2),
            ];
        });

        $subtotal = round((float) $items->sum('subtotal'), 2);
        $discount = round((float) ($order->discount ?? 0), 2);
        $taxAmount = round((float) ($order->tax_amount ?? 0), 2);
        $taxRate = $order->tax_rate ?? null;
        $total = !is_null($order->total)
            ? (float) $order->total
            : round($subtotal - $discount + $taxAmount, 2);

        $paymentMethod = $order->payment_method;
        if ($paymentMethod instanceof \UnitEnum) {
            $paymentMethod = method_exists($paymentMethod, 'value')
                ? $paymentMethod->value
                : $paymentMethod->name;
        }

        // Logo: primero el del usuario, sino el default Gestior.png
        $logoUrl = route('branding.default-receipt');
        $logoBase64 = null;

        $userLogoPath = $auth->receipt_logo_path ?? null;
        if ($userLogoPath && Storage::disk('public')->exists($userLogoPath)) {
            $v = Storage::disk('public')->lastModified($userLogoPath) ?: time();
            $logoUrl = Storage::disk('public')->url($userLogoPath) . '?v=' . $v;

            // Convertir a base64 para PDFs
            try {
                $imageData = Storage::disk('public')->get($userLogoPath);
                $mimeType = Storage::disk('public')->mimeType($userLogoPath) ?: 'image/png';
                $logoBase64 = 'data:' . $mimeType . ';base64,' . base64_encode($imageData);
            } catch (\Throwable $e) {
                Log::warning('Failed to convert user logo to base64', ['path' => $userLogoPath, 'error' => $e->getMessage()]);
            }
        }

        // Si no hay logo de usuario, usar el default
        if (!$logoBase64) {
            $defaultLogoPath = base_path('images/Gestior.png');
            if (file_exists($defaultLogoPath)) {
                try {
                    $imageData = file_get_contents($defaultLogoPath);
                    $mimeType = mime_content_type($defaultLogoPath) ?: 'image/png';
                    $logoBase64 = 'data:' . $mimeType . ';base64,' . base64_encode($imageData);
                } catch (\Throwable $e) {
                    Log::warning('Failed to convert default logo to base64', ['error' => $e->getMessage()]);
                }
            }
        }

        $appName = Setting::get('site_title', config('app.name', 'Rellenito'));
        $totals = [
            'subtotal' => $subtotal,
            'discount' => $discount,
            'tax_rate' => $taxRate !== null ? (float) $taxRate : null,
            'tax_amount' => $taxAmount,
            'total' => $total,
        ];

        $payload = [
            'id' => (int) $order->id,
            'order_number' => $order->order_number ?? (string) $order->id,
            'created_at' => optional($order->created_at)->toIso8601String(),
            'customer_name' => $order->customer_name
                ?? $order->guest_name
                ?? $order->client?->name,
            'payment_method' => $paymentMethod,
            'notes' => $order->notes ?? $order->note ?? null,
            'items' => $items->values(),
            'totals' => $totals,
            'branding' => [
                'logo_url' => $logoUrl,
                'app_name' => $appName,
            ],
            'qr' => $request->query('qr'),
            // URLs directas (vía API) para que la app muestre el comprobante sin sesión web
            'pdf_url' => url("/api/v1/comprobantes/{$order->id}"),
            'html_url' => url("/api/v1/comprobantes/{$order->id}?format=html"),
        ];

        return [
            'order' => $order,
            'payload' => $payload,
            'totals' => $totals,
            'logo_url' => $logoUrl,
            'logo_base64' => $logoBase64,
            'app_name' => $appName,
        ];
    }

    /**
     * Crear un nuevo pedido
     */
    public function store(Request $request)
    {
        $auth = $request->user();
        $companyId = Order::findRootCompanyId($auth) ?? $auth->id;

        $validated = $request->validate([
            'client_id' => [
                'nullable',
                Rule::exists('clients', 'id')->where('user_id', $companyId),
            ],
            'notes' => 'nullable|string',
            'discount' => 'nullable|numeric|min:0',
            'tax_amount' => 'nullable|numeric|min:0',
            'scheduled_for' => 'nullable|date',
            'is_scheduled' => 'boolean',
            'items' => 'required|array|min:1',
            'items.*.product_id' => [
                'nullable',
                'required_without:items.*.service_id',
                Rule::exists('products', 'id')->where('company_id', $companyId),
            ],
            'items.*.service_id' => [
                'nullable',
                'required_without:items.*.product_id',
                Rule::exists('services', 'id')->where('user_id', $companyId),
            ],
            'items.*.quantity' => 'required|numeric|min:0.001',
            'items.*.price' => 'nullable|numeric|min:0',
            'payment_methods' => 'nullable|array',
            'payment_methods.*.payment_method_id' => [
                'required',
                Rule::exists('payment_methods', 'id')->where(function ($query) use ($companyId) {
                    $query->where('user_id', $companyId)->orWhereNull('user_id');
                }),
            ],
            'payment_methods.*.amount' => 'required|numeric|min:0',
        ]);

        return DB::transaction(function () use ($auth, $validated) {
            // Determinar company_id
            $company = $auth->rootCompany();
            $companyId = $company ? $company->id : $auth->id;

            // Crear pedido
            $order = Order::create([
                'user_id' => $auth->id,
                'company_id' => $companyId,
                'branch_id' => $auth->isAdmin() ? $auth->id : null,
                'client_id' => $validated['client_id'] ?? null,
                'notes' => $validated['notes'] ?? null,
                'discount' => $validated['discount'] ?? 0,
                'tax_amount' => $validated['tax_amount'] ?? 0,
                'scheduled_for' => $validated['scheduled_for'] ?? null,
                'is_scheduled' => $validated['is_scheduled'] ?? false,
                'status' => OrderStatus::DRAFT->value,
                'payment_status' => PaymentStatus::PENDING->value,
                'total' => 0,
            ]);

            // Agregar items
            $total = 0;
            foreach ($validated['items'] as $item) {
                $price = null;
                $quantity = $item['quantity'];
                $itemData = [
                    'user_id' => $auth->id,
                    'quantity' => $quantity,
                ];

                // Manejar producto
                if (!empty($item['product_id'])) {
                    $product = Product::find($item['product_id']);

                    if (!$product) {
                        continue;
                    }

                    // Verificar que el usuario tenga acceso al producto
                    if (!$this->canAccessProduct($auth, $product)) {
                        throw new \Exception("No tienes acceso al producto: {$product->name}");
                    }

                    $price = $item['price'] ?? $product->price;
                    $itemData['product_id'] = $product->id;
                }
                // Manejar servicio
                elseif (!empty($item['service_id'])) {
                    $service = Service::find($item['service_id']);

                    if (!$service) {
                        continue;
                    }

                    // Verificar que el usuario tenga acceso al servicio
                    if ($service->user_id !== $companyId) {
                        throw new \Exception("No tienes acceso al servicio: {$service->name}");
                    }

                    $price = $item['price'] ?? $service->price;
                    $itemData['service_id'] = $service->id;
                } else {
                    continue;
                }

                $subtotal = $price * $quantity;
                $itemData['unit_price'] = $price;
                $itemData['subtotal'] = $subtotal;

                $order->items()->create($itemData);

                $total += $subtotal;
            }

            // Actualizar total
            $finalTotal = $total - ($validated['discount'] ?? 0) + ($validated['tax_amount'] ?? 0);
            $order->update(['total' => max(0, $finalTotal)]);

            // Adjuntar métodos de pago si se proporcionaron
            if (!empty($validated['payment_methods'])) {
                foreach ($validated['payment_methods'] as $paymentMethod) {
                    $order->paymentMethods()->attach(
                        $paymentMethod['payment_method_id'],
                        [
                            'amount' => $paymentMethod['amount'],
                        ]
                    );
                }
            }

            $order->load(['items.product:id,name,price', 'items.service:id,name,price', 'client:id,name']);

            return response()->json([
                'success' => true,
                'message' => 'Pedido creado exitosamente',
                'data' => $order,
            ], 201);
        });
    }

    /**
     * Actualizar un pedido (solo en estado draft)
     */
    public function update(Request $request, Order $order)
    {
        $auth = $request->user();

        if (!$this->canManageOrder($auth, $order)) {
            return response()->json([
                'success' => false,
                'message' => 'No tienes permiso para editar este pedido',
            ], 403);
        }

        // Solo se puede editar si está en draft
        if ($order->status !== OrderStatus::DRAFT) {
            return response()->json([
                'success' => false,
                'message' => 'Solo se pueden editar pedidos en estado borrador',
            ], 422);
        }

        $validated = $request->validate([
            'client_id' => 'nullable|exists:clients,id',
            'notes' => 'nullable|string',
            'discount' => 'nullable|numeric|min:0',
            'tax_amount' => 'nullable|numeric|min:0',
            'scheduled_for' => 'nullable|date',
            'is_scheduled' => 'boolean',
        ]);

        $order->update($validated);

        return response()->json([
            'success' => true,
            'message' => 'Pedido actualizado exitosamente',
            'data' => $order,
        ], 200);
    }

    /**
     * Agregar un item al pedido
     */
    public function addItem(Request $request, Order $order)
    {
        $auth = $request->user();

        if (!$this->canManageOrder($auth, $order)) {
            return response()->json([
                'success' => false,
                'message' => 'No tienes permiso para modificar este pedido',
            ], 403);
        }

        if ($order->status !== OrderStatus::DRAFT) {
            return response()->json([
                'success' => false,
                'message' => 'Solo se pueden agregar items a pedidos en borrador',
            ], 422);
        }

        $validated = $request->validate([
            'product_id' => 'required|exists:products,id',
            'quantity' => 'required|numeric|min:0.001',
            'price' => 'nullable|numeric|min:0',
        ]);

        $product = Product::find($validated['product_id']);

        if (!$this->canAccessProduct($auth, $product)) {
            return response()->json([
                'success' => false,
                'message' => 'No tienes acceso a este producto',
            ], 403);
        }

        $price = $validated['price'] ?? $product->price;
        $quantity = $validated['quantity'];
        $subtotal = $price * $quantity;

        $item = $order->items()->create([
            'product_id' => $product->id,
            'user_id' => $auth->id,
            'quantity' => $quantity,
            'unit_price' => $price,
            'subtotal' => $subtotal,
        ]);

        // Recalcular total
        $this->recalculateOrderTotal($order);

        return response()->json([
            'success' => true,
            'message' => 'Item agregado exitosamente',
            'data' => $item->load('product:id,name,price'),
        ], 201);
    }

    /**
     * Eliminar un item del pedido
     */
    public function removeItem(Request $request, Order $order, $itemId)
    {
        $auth = $request->user();

        if (!$this->canManageOrder($auth, $order)) {
            return response()->json([
                'success' => false,
                'message' => 'No tienes permiso para modificar este pedido',
            ], 403);
        }

        if ($order->status !== OrderStatus::DRAFT) {
            return response()->json([
                'success' => false,
                'message' => 'Solo se pueden eliminar items de pedidos en borrador',
            ], 422);
        }

        $item = $order->items()->find($itemId);

        if (!$item) {
            return response()->json([
                'success' => false,
                'message' => 'Item no encontrado',
            ], 404);
        }

        $item->delete();

        // Recalcular total
        $this->recalculateOrderTotal($order);

        return response()->json([
            'success' => true,
            'message' => 'Item eliminado exitosamente',
        ], 200);
    }

    /**
     * Finalizar pedido (confirmar y descontar stock)
     */
    public function finalize(Request $request, Order $order)
    {
        $auth = $request->user();

        $companyId = Order::findRootCompanyId($auth) ?? $auth->id;

        if (!$this->canManageOrder($auth, $order)) {
            return response()->json([
                'success' => false,
                'message' => 'No tienes permiso para finalizar este pedido',
            ], 403);
        }

        if ($order->status !== OrderStatus::DRAFT) {
            return response()->json([
                'success' => false,
                'message' => 'Este pedido ya fue procesado',
            ], 422);
        }

        $validated = $request->validate([
            'payment_status' => 'required|string|in:paid,pending,partial',
            'payment_method_id' => [
                'nullable',
                Rule::exists('payment_methods', 'id')->where('user_id', $companyId),
            ],
        ]);

        try {
            return DB::transaction(function () use ($order, $validated) {
                // Finalizar usando la misma lógica del panel (descuenta productos e insumos)
                $order->markAsCompleted(now());

                // Actualizar estado de pago
                $order->update([
                    'payment_status' => PaymentStatus::from($validated['payment_status']),
                ]);

                // Registrar método de pago si se proporciona
                if (!empty($validated['payment_method_id'])) {
                    $order->paymentMethods()->attach($validated['payment_method_id'], [
                        'amount' => $order->total,
                    ]);
                }

                return response()->json([
                    'success' => true,
                    'message' => 'Pedido finalizado exitosamente',
                    'data' => $order->fresh(['items.product', 'client']),
                ], 200);
            });
        } catch (DomainException $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 409);
        }
    }

    /**
     * Cancelar pedido
     */
    public function cancel(Request $request, Order $order)
    {
        $auth = $request->user();

        if (!$this->canManageOrder($auth, $order)) {
            return response()->json([
                'success' => false,
                'message' => 'No tienes permiso para cancelar este pedido',
            ], 403);
        }

        $order->update(['status' => OrderStatus::CANCELLED->value]);

        return response()->json([
            'success' => true,
            'message' => 'Pedido cancelado exitosamente',
            'data' => $order,
        ], 200);
    }

    /**
     * Eliminar pedido
     */
    public function destroy(Request $request, Order $order)
    {
        $auth = $request->user();

        if (!$this->canManageOrder($auth, $order)) {
            return response()->json([
                'success' => false,
                'message' => 'No tienes permiso para eliminar este pedido',
            ], 403);
        }

        $order->delete();

        return response()->json([
            'success' => true,
            'message' => 'Pedido eliminado exitosamente',
        ], 200);
    }

    /**
     * Verificar si el usuario puede acceder al pedido
     */
    private function canAccessOrder($user, Order $order): bool
    {
        if ($user->isMaster()) {
            return true;
        }

        if ($user->isCompany()) {
            return $order->company_id === $user->id;
        }

        if ($user->isAdmin()) {
            $company = $user->rootCompany();
            return $order->company_id === $company?->id;
        }

        return $order->user_id === $user->id;
    }

    /**
     * Verificar si el usuario puede gestionar el pedido
     */
    private function canManageOrder($user, Order $order): bool
    {
        if ($user->isMaster()) {
            return true;
        }

        if ($user->isCompany()) {
            return $order->company_id === $user->id;
        }

        return $order->user_id === $user->id;
    }

    /**
     * Verificar acceso a producto
     */
    private function canAccessProduct($user, Product $product): bool
    {
        if ($user->isMaster()) {
            return true;
        }

        if ($user->isCompany()) {
            return $product->company_id === $user->id;
        }

        if ($user->isAdmin()) {
            $company = $user->rootCompany();
            return $product->company_id === $company?->id;
        }

        return $product->user_id === $user->id || $product->user_id === $user->parent_id;
    }

    /**
     * Recalcular total del pedido
     */
    private function recalculateOrderTotal(Order $order): void
    {
        $subtotal = $order->items()->sum('subtotal');
        $total = $subtotal - $order->discount + $order->tax_amount;
        $order->update(['total' => max(0, $total)]);
    }

    /**
     * Formato compacto para "ProductCard" (API).
     */
    private function formatProductCard(Product $product): array
    {
        return [
            'id' => (int) $product->id,
            'name' => $product->name,
            'sku' => $product->sku,
            'price' => (float) $product->price,
            'stock' => (int) ($product->stock ?? 0),
            'min_stock' => (int) ($product->min_stock ?? 0),
            'is_active' => (bool) $product->is_active,
            'image_url' => $this->resolveImageUrl($product->image ?? $product->image_url ?? $product->photo_path ?? null),
        ];
    }

    /**
     * Formato compacto para servicios (UI similar a cards).
     */
    private function formatServiceCard(Service $service): array
    {
        return [
            'id' => (int) $service->id,
            'name' => $service->name,
            'price' => (float) $service->price,
            'is_active' => (bool) $service->is_active,
        ];
    }

    private function resolveImageUrl(?string $path): ?string
    {
        if (!$path) {
            return null;
        }

        if (str_starts_with($path, 'http')) {
            return $path;
        }

        $storage = \Illuminate\Support\Facades\Storage::disk('public');
        $normalized = ltrim(preg_replace('#^(public/|storage/)#', '', $path), '/');

        if ($storage->exists($normalized)) {
            return $storage->url($normalized);
        }

        return asset('storage/' . $normalized);
    }
}
