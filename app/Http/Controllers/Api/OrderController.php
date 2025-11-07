<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\Product;
use App\Models\Client;
use App\Enums\OrderStatus;
use App\Enums\PaymentStatus;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class OrderController extends Controller
{
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
     * Crear un nuevo pedido
     */
    public function store(Request $request)
    {
        $auth = $request->user();

        $validated = $request->validate([
            'client_id' => 'nullable|exists:clients,id',
            'notes' => 'nullable|string',
            'discount' => 'nullable|numeric|min:0',
            'tax_amount' => 'nullable|numeric|min:0',
            'scheduled_for' => 'nullable|date',
            'is_scheduled' => 'boolean',
            'items' => 'required|array|min:1',
            'items.*.product_id' => 'required|exists:products,id',
            'items.*.quantity' => 'required|numeric|min:0.001',
            'items.*.price' => 'nullable|numeric|min:0',
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
                'status' => OrderStatus::DRAFT,
                'payment_status' => PaymentStatus::PENDING,
                'total' => 0,
            ]);

            // Agregar items
            $total = 0;
            foreach ($validated['items'] as $item) {
                $product = Product::find($item['product_id']);

                if (!$product) {
                    continue;
                }

                // Verificar que el usuario tenga acceso al producto
                if (!$this->canAccessProduct($auth, $product)) {
                    throw new \Exception("No tienes acceso al producto: {$product->name}");
                }

                $price = $item['price'] ?? $product->price;
                $quantity = $item['quantity'];
                $subtotal = $price * $quantity;

                $order->items()->create([
                    'product_id' => $product->id,
                    'user_id' => $auth->id,
                    'quantity' => $quantity,
                    'price' => $price,
                    'subtotal' => $subtotal,
                ]);

                $total += $subtotal;
            }

            // Actualizar total
            $finalTotal = $total - ($validated['discount'] ?? 0) + ($validated['tax_amount'] ?? 0);
            $order->update(['total' => max(0, $finalTotal)]);

            $order->load(['items.product:id,name,price', 'client:id,name']);

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
            'price' => $price,
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
            'payment_method_id' => 'nullable|exists:payment_methods,id',
        ]);

        return DB::transaction(function () use ($order, $validated) {
            // Descontar stock
            foreach ($order->items as $item) {
                $product = $item->product;
                if ($product) {
                    $newStock = max(0, $product->stock - $item->quantity);
                    $product->update(['stock' => $newStock]);
                }
            }

            // Actualizar pedido
            $order->update([
                'status' => OrderStatus::COMPLETED,
                'payment_status' => PaymentStatus::from($validated['payment_status']),
                'sold_at' => now(),
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

        $order->update(['status' => OrderStatus::CANCELLED]);

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
}
