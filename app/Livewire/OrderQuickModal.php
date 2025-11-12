<?php

namespace App\Livewire;

use Livewire\Component;
use Livewire\WithPagination;
use App\Models\Product;
use App\Models\Service;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Client;
use App\Models\PaymentMethod;
use App\Enums\OrderStatus;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Carbon;

class OrderQuickModal extends Component
{
    use WithPagination;

    // ---- Estado UI
    public bool $completeOnSave = true; // ✅ por defecto COMPLETED
    public bool $open = false;
    public string $search = '';
    public array $items = [];
    public string $currentTab = 'products'; // 'products' o 'services'

    // ---- Cliente
    public ?int $client_id = null;
    public string $clientSearch = '';
    public bool $showClientForm = false;
    public string $newClientName = '';
    public string $newClientEmail = '';
    public string $newClientPhone = '';

    // ---- Medios de pago
    public array $paymentMethods = []; // [{payment_method_id, amount, reference}]

    // ---- Fecha/hora elegida por el usuario para el pedido (para created_at)
    // Formato compatible con <input type="datetime-local"> -> "Y-m-d\TH:i"
    public string $orderDate = '';

    // ---- Agendamiento
    public bool $isScheduled = false;
    public string $scheduledFor = '';
    public string $orderNotes = '';

    public function mount(): void
    {
        $this->resetModal();
        // Valor por defecto = ahora (en formato datetime-local)
        $this->orderDate = now()->format('Y-m-d\TH:i');
        $this->scheduledFor = now()->addDay()->format('Y-m-d\TH:i');
    }

    public function toggleScheduled(): void
    {
        $this->isScheduled = !$this->isScheduled;
        if ($this->isScheduled) {
            $this->completeOnSave = false; // Si está agendado, no puede estar completado
        }
    }

    // Abrir / cerrar modal
    public function showModal(): void
    {
        $this->open = true;
        $this->dispatch('disableBodyScroll');
    }

    public function hideModal(): void
    {
        $this->open = false;
        $this->dispatch('enableBodyScroll');
        $this->resetModal();
        // recalibrar fecha por si el usuario vuelve a abrir
        $this->orderDate = now()->format('Y-m-d\TH:i');
    }

    private function resetModal(): void
    {
        $this->reset([
            'search',
            'items',
            'client_id',
            'clientSearch',
            'showClientForm',
            'newClientName',
            'newClientEmail',
            'newClientPhone',
            'isScheduled',
            'orderNotes',
            'currentTab',
            'paymentMethods',
        ]);
        $this->currentTab = 'products';
        $this->resetPage();
    }

    // --- Cambiar tab ---
    public function setTab($tab): void
    {
        $this->currentTab = $tab;
        $this->search = '';
        $this->resetPage();
    }

    // --- Lógica de productos / servicios / cliente ---

    public function addProduct($productId): void
    {
        // Evitar scope byUser para soportar inventario compartido por empresa
        $product = Product::withoutGlobalScope('byUser')->find($productId);
        if (!$product) return;

        // Buscar si ya existe este producto en el carrito
        foreach ($this->items as $key => $item) {
            if (isset($item['product_id']) && $item['product_id'] === $productId) {
                $this->items[$key]['quantity']++;
                return;
            }
        }

        // Agregar nuevo item
        $this->items[] = [
            'type' => 'product',
            'product_id' => $product->id,
            'service_id' => null,
            'name'       => $product->name,
            'price'      => (float) $product->price,
            'quantity'   => 1,
        ];
    }

    public function addService($serviceId): void
    {
        $service = Service::withoutGlobalScope('byUser')->find($serviceId);
        if (!$service) return;

        // Buscar si ya existe este servicio en el carrito
        foreach ($this->items as $key => $item) {
            if (isset($item['service_id']) && $item['service_id'] === $serviceId) {
                $this->items[$key]['quantity']++;
                return;
            }
        }

        // Agregar nuevo item
        $this->items[] = [
            'type' => 'service',
            'product_id' => null,
            'service_id' => $service->id,
            'name'       => $service->name,
            'price'      => (float) $service->price,
            'quantity'   => 1,
        ];
    }

    public function removeItem($index): void
    {
        unset($this->items[$index]);
        $this->items = array_values($this->items);
    }

    public function updateQuantity($index, $quantity): void
    {
        $quantity = (int) $quantity;
        if ($quantity <= 0) {
            $this->removeItem($index);
        } else {
            $this->items[$index]['quantity'] = $quantity;
        }
    }

    public function getTotalProperty(): float
    {
        return collect($this->items)->sum(fn ($item) => ((float) $item['price']) * ((int) $item['quantity']));
    }

    public function toggleClientForm(): void
    {
        $this->showClientForm = !$this->showClientForm;
        if ($this->showClientForm) {
            $this->client_id = null;
            $this->clientSearch = '';
        } else {
            $this->reset(['newClientName', 'newClientEmail', 'newClientPhone']);
        }
    }

    public function selectClient($clientId): void
    {
        $client = Client::forUser(auth()->user())->find($clientId);
        if ($client) {
            $this->client_id = $client->id;
            $this->clientSearch = $client->name;
        } else {
            $this->client_id = null;
            $this->clientSearch = '';
            $this->addError('client_id', 'Cliente no válido para tu cuenta.');
        }
    }

    public function getClientsProperty()
    {
        if (empty($this->clientSearch)) {
            return collect();
        }

        return Client::forUser(auth()->user())
            ->where(function ($q) {
                $q->where('name', 'like', '%' . $this->clientSearch . '%')
                  ->orWhere('email', 'like', '%' . $this->clientSearch . '%')
                  ->orWhere('phone', 'like', '%' . $this->clientSearch . '%');
            })
            ->limit(5)
            ->get();
    }

    // ---- Guardado de pedido con fecha personalizada a created_at
    public function save()
    {
        // Validación base
        $baseRules = [
            'items'     => 'required|array|min:1',
            'orderDate' => ['required', 'date_format:Y-m-d\TH:i'],
            'orderNotes' => 'nullable|string|max:1000',
        ];

        // Si está agendado, validar fecha futura
        if ($this->isScheduled) {
            $baseRules['scheduledFor'] = ['required', 'date_format:Y-m-d\TH:i', 'after:now'];
        }

        if ($this->showClientForm) {
            $rules = array_merge($baseRules, [
                'newClientName'  => 'required|string|max:255',
                'newClientEmail' => 'nullable|email|max:255',
                'newClientPhone' => 'nullable|string|max:50',
            ]);
        } else {
            $rules = array_merge($baseRules, [
                'client_id' => 'nullable|exists:clients,id',
            ]);
        }

        $this->validate($rules);

        // Parsear fecha elegida por el usuario
        // IMPORTANTE: Usar la timezone del usuario para interpretar correctamente la fecha
        $userTimezone = auth()->user()->timezone ?? config('app.timezone', 'UTC');

        try {
            $createdAt = Carbon::createFromFormat('Y-m-d\TH:i', $this->orderDate, $userTimezone);
        } catch (\Throwable $e) {
            $this->addError('orderDate', 'Fecha/hora inválida.');
            return;
        }

        try {
            DB::beginTransaction();

            // Crear cliente si corresponde
            $clientId = $this->client_id;
            if ($this->showClientForm && trim($this->newClientName) !== '') {
                $user = auth()->user();
                $companyId = $user->isCompany() ? $user->id : \App\Models\Order::findRootCompanyId($user);
                $client = Client::create([
                    'user_id' => $companyId,
                    'name'  => trim($this->newClientName),
                    'email' => $this->newClientEmail ? trim($this->newClientEmail) : null,
                    'phone' => $this->newClientPhone ? trim($this->newClientPhone) : null,
                ]);
                $clientId = $client->id;
            } elseif ($clientId) {
                // Validar pertenencia del cliente seleccionado
                $valid = Client::forUser(auth()->user())->where('id', $clientId)->exists();
                if (!$valid) {
                    throw new \RuntimeException('Cliente seleccionado no pertenece a tu cuenta.');
                }
            }

            //  Estado según toggle y agendamiento
            if ($this->isScheduled) {
                $status = OrderStatus::SCHEDULED;
                // Parsear con la timezone del usuario
                $scheduledDateTime = Carbon::createFromFormat('Y-m-d\TH:i', $this->scheduledFor, $userTimezone);
            } else {
                $status = $this->completeOnSave ? OrderStatus::COMPLETED : OrderStatus::DRAFT;
                $scheduledDateTime = null;
            }

            // Crear pedido con created_at/updated_at forzados
            $order = new Order([
                'client_id' => $clientId,
                'total'     => 0,
                'status'    => $status,
                'user_id'   => auth()->id(),
                'notes'     => $this->orderNotes ? trim($this->orderNotes) : null,
                'is_scheduled' => $this->isScheduled,
                'scheduled_for' => $scheduledDateTime,
            ]);
            $order->created_at = $createdAt;
            $order->updated_at = $createdAt;
            $order->save();

            // Detectar columnas reales en order_items
            $hasUnitPrice  = Schema::hasColumn('order_items', 'unit_price');
            $hasPrice      = Schema::hasColumn('order_items', 'price');
            $hasSubtotal   = Schema::hasColumn('order_items', 'subtotal');
            $hasTotalPrice = Schema::hasColumn('order_items', 'total_price');

            if (!$hasUnitPrice && !$hasPrice) {
                throw new \RuntimeException("order_items: falta columna 'price' o 'unit_price'.");
            }
            if (!$hasSubtotal && !$hasTotalPrice) {
                throw new \RuntimeException("order_items: falta columna 'subtotal' o 'total_price'.");
            }

            // Items + total
            $total = 0.0;
            foreach ($this->items as $item) {
                $qty   = (int) ($item['quantity'] ?? 0);
                $price = (float) ($item['price'] ?? 0.0);
                if ($qty <= 0) continue;

                $subtotal = $price * $qty;
                $payload = [
                    'order_id'   => $order->id,
                    'product_id' => $item['product_id'] ?? null,
                    'service_id' => $item['service_id'] ?? null,
                    'quantity'   => $qty,
                ];

                // mapear columnas según existan
                if ($hasUnitPrice)  { $payload['unit_price']  = $price; }
                if ($hasPrice)      { $payload['price']       = $price; }
                if ($hasSubtotal)   { $payload['subtotal']    = $subtotal; }
                if ($hasTotalPrice) { $payload['total_price'] = $subtotal; }

                OrderItem::create($payload);
                $total += $subtotal;
            }

            // Actualizar total
            $order->total = $total;
            $order->save();

            // Guardar medios de pago
            if (!empty($this->paymentMethods)) {
                foreach ($this->paymentMethods as $pm) {
                    if (!empty($pm['payment_method_id']) && !empty($pm['amount']) && $pm['amount'] > 0) {
                        $order->paymentMethods()->attach($pm['payment_method_id'], [
                            'amount' => $pm['amount'],
                            'reference' => $pm['reference'] ?? null,
                        ]);
                    }
                }
            }

            // Si debe completarse, usar markAsCompleted para descontar stock e insumos
            if ($this->completeOnSave) {
                $order->markAsCompleted($createdAt); // Descuenta productos e insumos
            }

            // Mantener coherencia temporal
            $order->updated_at = $createdAt;
            $order->save();

            DB::commit();

            $message = $this->isScheduled
                ? "Pedido #{$order->id} agendado para " . $scheduledDateTime->format('d/m/Y H:i')
                : "Pedido #{$order->id} creado exitosamente";

            session()->flash('ok', $message);

            // Cierra y resetea modal
            $this->hideModal();
            $this->dispatch('orderCreated', orderId: $order->id);

            // Redirige al show del pedido
            return redirect()->route('orders.show', $order);

        } catch (\Throwable $e) {
            DB::rollBack();
            Log::error('Error creating order', [
                'error'          => $e->getMessage(),
                'items'          => $this->items,
                'client_id'      => $this->client_id,
                'showClientForm' => $this->showClientForm,
            ]);
            session()->flash('error', 'Error al crear el pedido. Revisa los datos o contacta al admin.');
        }
    }

    public function render()
    {
        $user = auth()->user();

        // Cargar productos
        $productQuery = (method_exists($user,'isMaster') && $user->isMaster())
            ? Product::query()
            : Product::availableFor($user);

        $products = $productQuery
            ->when($this->search, function ($query) {
                $query->where(function ($q) {
                    $q->where('name', 'like', '%' . $this->search . '%')
                      ->orWhere('sku',  'like', '%' . $this->search . '%');
                });
            })
            ->when(method_exists(Product::class, 'scopeActive'), fn ($q) => $q->active())
            ->orderBy('name')
            ->paginate(12, ['*'], 'productsPage');

        // Cargar servicios
        $serviceQuery = (method_exists($user,'isMaster') && $user->isMaster())
            ? Service::query()
            : Service::availableFor($user);

        $services = $serviceQuery
            ->when($this->search, function ($query) {
                $query->where('name', 'like', '%' . $this->search . '%');
            })
            ->when(method_exists(Service::class, 'scopeActive'), fn ($q) => $q->active())
            ->orderBy('name')
            ->paginate(12, ['*'], 'servicesPage');

        // Cargar medios de pago disponibles
        $availablePaymentMethods = PaymentMethod::where('is_active', true)
            ->where(function ($q) use ($user) {
                $q->where('is_global', true)
                  ->orWhere('user_id', $user->id);
            })
            ->orderBy('sort_order')
            ->orderBy('name')
            ->get();

        return view('livewire.order-quick-modal', compact('products', 'services', 'availablePaymentMethods'));
    }
}
