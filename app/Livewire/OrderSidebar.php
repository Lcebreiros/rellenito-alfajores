<?php

namespace App\Livewire;

use Livewire\Attributes\On;
use Livewire\Component;
use App\Enums\OrderStatus;
use App\Services\OrderService;
use App\Services\StockService;
use App\Services\MercadoPagoService;
use App\Models\Order;
use App\Models\Client;
use App\Models\PaymentMethod;
use App\Models\MercadoPagoCredential;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use DomainException;
use RuntimeException;

class OrderSidebar extends Component
{
    public int $orderId;
    public array $items = [];
    public float $total = 0.0;
    public bool $finishing = false;

    // 👇 NUEVO: campo editable para el nombre del cliente
    public ?string $customerName = '';

    // 👇 NUEVO: métodos de pago seleccionados (IDs) - recibidos desde PaymentMethodSelector
    public array $selectedPaymentMethods = [];

    public bool $isScheduled = false; // legacy flag (UI moved to ScheduleOrder)
    public string $scheduledFor = '';
    public string $orderNotes = '';

    // MP Point: ID del payment intent activo (null = sin pago MP pendiente)
    public ?string $mpIntentId = null;

    public function mount(?int $orderId = null): void
    {
        $this->orderId = $orderId ?? (int) session('draft_order_id');
        $this->ensureDraftExists();
        $this->refreshOrder();
        $this->loadCustomerFromOrder(); // 👈 cargar nombre si ya tiene cliente
        // valor por defecto para agendar (mañana a esta hora)
        $this->scheduledFor = now()->addDay()->format('Y-m-d\TH:i');
    }

    #[On('order-updated')]
    public function onOrderUpdated(): void
    {
        $this->refreshOrder();
    }

    #[On('draft-changed')]
    public function onDraftChanged(int $id): void
    {
        $this->orderId = $id;
        $this->refreshOrder();
        $this->loadCustomerFromOrder();
    }

    #[On('item-added-to-order')]
    public function onItemAddedToOrder(int $orderId): void
    {
        // Si el evento llega para otro borrador, cambiar el foco a ese borrador y sincronizar sesión
        if ($this->orderId !== $orderId) {
            session(['draft_order_id' => $orderId]);
            $this->orderId = $orderId;
        }
        $this->refreshOrder();
    }

    #[On('paymentMethodsUpdated')]
    public function onPaymentMethodsUpdated(array $selectedIds): void
    {
        $this->selectedPaymentMethods = $selectedIds;
    }

    public function refreshOrder(): void
    {
        $orders = app(OrderService::class);
        $snap = $orders->snapshot($this->orderId);
        $this->items = $snap['items'] ?? [];
        $this->total = $snap['total'] ?? 0.0;
    }

    private function ensureDraftExists(): void
{
    $sid = (int) session('draft_order_id');

    if ($sid && $order = Order::find($sid)) {
        $this->orderId = $order->id;
        return;
    }

    $user = auth()->user();
    if (!$user) {
        throw new \DomainException('No hay usuario autenticado para crear la orden.');
    }

    // Determinar branch_id
    if ($user->isAdmin() || $user->isCompany()) {
        $branchId = $user->id;
    } elseif ($user->parent_id) {
        $branchId = $user->parent_id;
    } else {
        $branchId = $user->id;
    }

    // Determinar company_id
    $companyId = Order::findRootCompanyId($user) ?? $user->id;

    $draft = Order::create([
        'user_id' => $user->id,
        'branch_id' => $branchId,
        'company_id' => $companyId,
        'status' => \App\Enums\OrderStatus::DRAFT->value,
        'payment_status' => \App\Enums\PaymentStatus::PENDING->value,
        'payment_method' => \App\Enums\PaymentMethod::CASH->value,
        'discount' => 0,
        'tax_amount' => 0,
    ]);

    session(['draft_order_id' => $draft->id]);
    $this->orderId = $draft->id;
    $this->items = [];
    $this->total = 0.0;
    $this->customerName = '';
}

    private function startNewDraft(): void
    {
        $draft = Order::create();
        session(['draft_order_id' => $draft->id]);

        $this->orderId = (int) $draft->id;
        $this->items = [];
        $this->total = 0.0;
        $this->customerName = ''; // 👈 limpiar cliente
        $this->selectedPaymentMethods = []; // 👈 limpiar métodos de pago

        $this->dispatch('draft-changed', id: $this->orderId);
        $this->skipRender = false;
    }

    private function guardDraft(): void
    {
        $order = Order::find($this->orderId);
        if (!$order || $order->status !== OrderStatus::DRAFT) {
            $this->ensureDraftExists();
        }
    }

    // 👇 NUEVO: cuando se edita el nombre, sincronizamos cliente en la orden
    public function updatedCustomerName($value): void
    {
        $name = trim((string) $value);

        $order = Order::find($this->orderId);
        if (!$order || $order->status !== OrderStatus::DRAFT) return;

        if ($name === '') {
            // Si se borró el nombre, desvinculamos el cliente
            $order->client_id = null;
            $order->save();
            return;
        }

        // Buscar o crear cliente dentro del tenant (company root)
        $user = auth()->user();
        $companyId = $user->isCompany() ? $user->id : Order::findRootCompanyId($user);
        $client = Client::firstOrCreate([
            'user_id' => $companyId,
            'name'    => $name,
        ]);
        $order->client()->associate($client);
        $order->save();
    }

    // 👇 NUEVO: cargar el nombre del cliente actual del draft
    private function loadCustomerFromOrder(): void
    {
        $order = Order::with('client')->find($this->orderId);
        $this->customerName = $order?->client?->name ?? '';
    }

    public function add(int $itemId): void
    {
        $orders = app(OrderService::class);
        $this->guardDraft();
        $orders->mutateItem($this->orderId, $itemId, +1);
        $this->refreshOrder();
        $this->dispatch('order-updated');
    }

    public function sub(int $itemId): void
    {
        $orders = app(OrderService::class);
        $this->guardDraft();
        $orders->mutateItem($this->orderId, $itemId, -1);
        $this->refreshOrder();
        $this->dispatch('order-updated');
    }

    public function remove(int $itemId): void
    {
        $orders = app(OrderService::class);
        $this->guardDraft();

        try {
            $orders->removeItem($this->orderId, $itemId);
        } catch (\Throwable $e) {
            if (!($e instanceof DomainException)) {
                report($e);
            }
            $msg = $e instanceof DomainException ? $e->getMessage() : 'No se pudo eliminar el producto.';
            $this->dispatch('notify', type: 'error', message: $msg);
            return;
        }

        $this->refreshOrder();
        $this->dispatch('order-updated');
        $this->dispatch('notify', type: 'info', message: 'Producto eliminado del pedido.');
    }

    public function updateQty(int $itemId, $value): void
    {
        $orders = app(OrderService::class);
        $this->guardDraft();

        $qty = (int) filter_var($value, FILTER_SANITIZE_NUMBER_INT);
        if ($qty < 0) $qty = 0;

        try {
            $orders->setItemQuantity($this->orderId, $itemId, $qty);
        } catch (\Throwable $e) {
            if (!($e instanceof DomainException)) {
                report($e);
            }
            $msg = $e instanceof DomainException ? $e->getMessage() : 'No se pudo actualizar la cantidad.';
            $this->dispatch('notify', type: 'error', message: $msg);
            return;
        }

        $this->refreshOrder();
        $this->dispatch('order-updated');

        if ($qty <= 0) {
            $this->dispatch('notify', type: 'info', message: 'Producto eliminado del pedido.');
        }
    }

    /**
     * Fuerza el reset del estado de finalización (usado cuando finishing queda colgado).
     */
    public function resetFinalizing(): void
    {
        $this->finishing  = false;
        $this->mpIntentId = null;
    }

    public function finalize(): void
    {
        // Si hay un payment intent MP activo, no re-disparar
        if ($this->mpIntentId) return;
        if ($this->finishing) return;
        $this->finishing = true;

        Log::info('OrderSidebar::finalize iniciado', [
            'order_id'               => $this->orderId,
            'selected_payment_methods' => $this->selectedPaymentMethods,
            'total'                  => $this->total,
        ]);

        // Alinear el draft de la sesión con el componente
        $draftId = (int) session('draft_order_id');
        if (!$draftId) {
            $this->ensureDraftExists();
            $draftId = (int) session('draft_order_id');
        }
        if ($draftId !== (int) $this->orderId) {
            $this->orderId = $draftId;
            $this->refreshOrder();
        }

        // Si hay MP entre los métodos seleccionados, iniciar flujo async de Point
        $mpMethod = !empty($this->selectedPaymentMethods)
            ? PaymentMethod::whereIn('id', $this->selectedPaymentMethods)
                ->where('gateway_provider', 'mercadopago')
                ->first()
            : null;

        Log::info('OrderSidebar::finalize detección MP', [
            'mp_detected'       => (bool) $mpMethod,
            'mp_method_id'      => $mpMethod?->id,
            'selected_methods'  => $this->selectedPaymentMethods,
        ]);

        if ($mpMethod) {
            try {
                $this->initiateMpPayment();
            } catch (\Throwable $e) {
                Log::error('OrderSidebar::initiateMpPayment excepción no controlada', [
                    'order_id' => $this->orderId,
                    'error'    => $e->getMessage(),
                    'trace'    => $e->getTraceAsString(),
                ]);
                $this->finishing  = false;
                $this->mpIntentId = null;
                $this->dispatch('notify', type: 'error', message: 'Error inesperado al iniciar el pago con Mercado Pago. Revisá los logs.');
            }
            return;
        }

        // ── Flujo estándar (sin gateway MP) ───────────────────────────────────
        try {
            $finishedId        = null;
            $finishedTotal     = 0.0;
            $affectedProductIds = [];

            DB::transaction(function () use (&$finishedId, &$finishedTotal, &$affectedProductIds) {
                $order = Order::with(['items.product', 'items.service'])
                    ->lockForUpdate()
                    ->findOrFail($this->orderId);

                if ($order->status !== OrderStatus::DRAFT) {
                    throw new DomainException('El pedido ya fue procesado.');
                }

                $this->associateClientToOrder($order);

                if ($order->items->isEmpty()) {
                    throw new DomainException('El pedido está vacío.');
                }

                foreach ($order->items as $item) {
                    if ($item->product && $item->product->stock < $item->quantity) {
                        throw new DomainException("Stock insuficiente: {$item->product->name}");
                    }
                }

                $this->syncPaymentMethods($order);

                if ((bool) ($order->is_scheduled ?? false)) {
                    $dt = $order->scheduled_for;
                    if (!$dt) throw new DomainException('Seleccioná la fecha de agendado.');
                    if ($dt->lessThanOrEqualTo(now())) throw new DomainException('La fecha/hora debe ser futura para agendar.');
                    $order->status = \App\Enums\OrderStatus::SCHEDULED->value;
                    $order->recalcTotal(true);
                    $order->save();
                } else {
                    $order->markAsCompleted(now());
                }

                foreach ($order->items as $item) {
                    if ($item->product) $affectedProductIds[] = $item->product->id;
                }

                $finishedId    = (int) $order->id;
                $finishedTotal = (float) $order->total;
            });

            $this->dispatchOrderFinalized($finishedId, $finishedTotal, $affectedProductIds);

        } catch (\Throwable $e) {
            $msg = $e instanceof DomainException ? $e->getMessage() : 'No se pudo finalizar el pedido.';
            $this->dispatch('notify', type: 'error', message: $msg);
        } finally {
            $this->finishing = false;
        }
    }

    /**
     * Valida la orden y envía el payment intent al terminal Point de MP.
     * La orden permanece en DRAFT hasta que MP confirme el pago.
     */
    private function initiateMpPayment(): void
    {
        $order = Order::with('items.product')->find($this->orderId);

        if (!$order || $order->status !== OrderStatus::DRAFT) {
            $this->dispatch('notify', type: 'error', message: 'El pedido no está disponible.');
            $this->finishing = false;
            return;
        }
        if ($order->items->isEmpty()) {
            $this->dispatch('notify', type: 'error', message: 'El pedido está vacío.');
            $this->finishing = false;
            return;
        }
        foreach ($order->items as $item) {
            if ($item->product && $item->product->stock < $item->quantity) {
                $this->dispatch('notify', type: 'error', message: "Stock insuficiente: {$item->product->name}");
                $this->finishing = false;
                return;
            }
        }

        $company    = auth()->user()->rootCompany() ?? auth()->user();
        $credential = MercadoPagoCredential::where('user_id', $company->id)->first();

        Log::info('OrderSidebar::initiateMpPayment credencial', [
            'company_id'        => $company->id,
            'has_credential'    => (bool) $credential,
            'has_device'        => (bool) $credential?->selected_device_id,
            'device_id'         => $credential?->selected_device_id,
            'amount'            => $this->total,
        ]);

        if (!$credential) {
            $this->dispatch('notify', type: 'error', message: __('mp.not_connected'));
            $this->finishing = false;
            return;
        }
        if (!$credential->selected_device_id) {
            $this->dispatch('notify', type: 'error', message: __('mp.no_device_selected'));
            $this->finishing = false;
            return;
        }

try {
    $intent = app(MercadoPagoService::class)->createPaymentIntent(
        $credential,
        $credential->selected_device_id,
        [
            'amount' => (float) $this->total, // Asegurate que sea float/double
            'additional_info' => [
                'external_reference' => (string) $this->orderId,
                'print_on_terminal'  => true, // Opcional: para que imprima el ticket
            ],
            // Si el error persiste con 'description', quitalo o movelo a additional_info
        ],
    );

            Log::info('OrderSidebar::initiateMpPayment respuesta MP', [
                'intent' => $intent,
            ]);

            if (empty($intent['id'])) {
                throw new RuntimeException('Mercado Pago no devolvió un ID de cobro. Respuesta: ' . json_encode($intent));
            }

            $this->mpIntentId = $intent['id'];
            // $this->finishing queda en true mientras esperamos → impide doble-click

        } catch (\Throwable $e) {
            Log::error('OrderSidebar::initiateMpPayment falló', [
                'order_id' => $this->orderId,
                'device_id' => $credential->selected_device_id,
                'error'    => $e->getMessage(),
            ]);
            $this->dispatch('notify', type: 'error', message: 'Error al enviar el cobro al terminal: ' . $e->getMessage());
            $this->finishing = false;
        }
    }

    /**
     * Llamado por Alpine.js cuando MP reporta estado FINISHED.
     * Completa la orden en base de datos.
     *
     * @param  array{payment_id?: int|null, state?: string} $mpData
     */
    public function completeMpOrder(array $mpData = []): void
    {
        try {
            $finishedId        = null;
            $finishedTotal     = 0.0;
            $affectedProductIds = [];
            $intentId          = $this->mpIntentId;

            DB::transaction(function () use (&$finishedId, &$finishedTotal, &$affectedProductIds, $mpData, $intentId) {
                $order = Order::with(['items.product', 'items.service'])
                    ->lockForUpdate()
                    ->findOrFail($this->orderId);

                if ($order->status !== OrderStatus::DRAFT) {
                    throw new DomainException('El pedido ya fue procesado.');
                }

                $this->associateClientToOrder($order);

                if ($order->items->isEmpty()) {
                    throw new DomainException('El pedido está vacío.');
                }

                // Sincronizar métodos de pago y guardar respuesta del gateway en el pivot de MP
                if (!empty($this->selectedPaymentMethods)) {
                    $totalAmount    = $order->total;
                    $methodCount    = count($this->selectedPaymentMethods);
                    $amountPerMethod = $totalAmount / $methodCount;
                    $pivotData      = [];

                    foreach ($this->selectedPaymentMethods as $pmId) {
                        $pm    = PaymentMethod::find($pmId);
                        $pivot = ['amount' => $amountPerMethod, 'created_at' => now(), 'updated_at' => now()];

                        if ($pm && $pm->gateway_provider === 'mercadopago') {
                            $pivot['gateway_response'] = json_encode(array_merge(
                                $mpData,
                                ['payment_intent_id' => $intentId],
                            ));
                        }
                        $pivotData[$pmId] = $pivot;
                    }
                    $order->paymentMethods()->sync($pivotData);
                }

                $order->markAsCompleted(now());

                foreach ($order->items as $item) {
                    if ($item->product) $affectedProductIds[] = $item->product->id;
                }

                $finishedId    = (int) $order->id;
                $finishedTotal = (float) $order->total;
            });

            $this->mpIntentId = null;
            $this->dispatchOrderFinalized($finishedId, $finishedTotal, $affectedProductIds);

        } catch (\Throwable $e) {
            $msg = $e instanceof DomainException ? $e->getMessage() : 'No se pudo finalizar el pedido.';
            $this->dispatch('notify', type: 'error', message: $msg);
            // Dejar mpIntentId para que el usuario pueda reintentar o cancelar
        } finally {
            $this->finishing = false;
        }
    }

    /**
     * Llamado por Alpine.js cuando el pago falla, es rechazado o el operador cancela.
     * Intenta cancelar el payment intent en MP y resetea el estado.
     */
    public function abortMpPayment(string $reason = ''): void
    {
        $intentId = $this->mpIntentId;
        $this->mpIntentId = null;
        $this->finishing  = false;

        if ($intentId) {
            try {
                $company    = auth()->user()->rootCompany() ?? auth()->user();
                $credential = MercadoPagoCredential::where('user_id', $company->id)->first();
                if ($credential?->selected_device_id) {
                    app(MercadoPagoService::class)->cancelPaymentIntent(
                        $credential,
                        $credential->selected_device_id,
                        $intentId,
                    );
                }
            } catch (\Throwable) {
                // Ignorar errores al cancelar (el intent pudo ya expirar)
            }
        }

        if ($reason) {
            $this->dispatch('notify', type: 'error', message: $reason);
        }
    }

    // ─── Helpers de finalize ──────────────────────────────────────────────────

    private function associateClientToOrder(Order $order): void
    {
        $name = trim((string) $this->customerName);
        if ($name === '' || $order->client_id) return;

        $user      = auth()->user();
        $companyId = $user->isCompany() ? $user->id : Order::findRootCompanyId($user);
        $client    = Client::firstOrCreate(['user_id' => $companyId, 'name' => $name]);
        $order->client()->associate($client);
        $order->save();
    }

    private function syncPaymentMethods(Order $order, array $gatewayOverrides = []): void
    {
        if (empty($this->selectedPaymentMethods)) return;

        $totalAmount     = $order->total;
        $methodCount     = count($this->selectedPaymentMethods);
        $amountPerMethod = $totalAmount / $methodCount;
        $pivotData       = [];

        foreach ($this->selectedPaymentMethods as $pmId) {
            $pivot = ['amount' => $amountPerMethod, 'created_at' => now(), 'updated_at' => now()];
            if (isset($gatewayOverrides[$pmId])) {
                $pivot['gateway_response'] = $gatewayOverrides[$pmId];
            }
            $pivotData[$pmId] = $pivot;
        }
        $order->paymentMethods()->sync($pivotData);
    }

    private function dispatchOrderFinalized(int $orderId, float $total, array $affectedProductIds): void
    {
        session()->forget('draft_order_id');
        $this->startNewDraft();
        $this->js('$wire.$refresh()');

        foreach ($affectedProductIds as $productId) {
            $this->dispatch('stock-updated', productId: $productId);
        }

        $this->dispatch('orderFinalized');
        $this->dispatch('order-finalized', orderId: $orderId, total: $total);

        $url = route('orders.show', ['order' => $orderId]);
        $this->dispatch('notify', type: 'success', message: "Pedido #$orderId creado correctamente. <a href=\"$url\" class=\"underline\">Ver</a>");
        $this->dispatch('order-confirmed', orderId: $orderId);
        $this->dispatch('order:confirmed', orderId: $orderId);
    }

    public function cancel(): void
    {
        $draftId = (int) session('draft_order_id');
        if ($draftId !== (int) $this->orderId) {
            $this->dispatch('notify', type:'error', message:'Pedido no pertenece a tu sesión.');
            return;
        }

        DB::transaction(function () {
            $order = Order::lockForUpdate()->findOrFail($this->orderId);
            if ($order->status === OrderStatus::DRAFT) {
                $order->status = \App\Enums\OrderStatus::CANCELED->value;
                $order->save();
            }
        });

        session()->forget('draft_order_id');
        $this->startNewDraft();
        $this->js('$wire.$refresh()');
        $this->dispatch('notify', type:'info', message:'Pedido cancelado');
    }

    public function render()
    {
        return view('livewire.order-sidebar');
    }
}
