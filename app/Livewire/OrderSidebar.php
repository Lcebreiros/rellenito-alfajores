<?php

namespace App\Livewire;

use Livewire\Attributes\On;
use Livewire\Component;
use App\Services\OrderService;
use App\Services\StockService;
use App\Models\Order;
use App\Models\Client;
use Illuminate\Support\Facades\DB;
use DomainException;

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

    public function mount(?int $orderId = null): void
    {
        $this->orderId = $orderId ?? (int) session('draft_order_id');
        $this->ensureDraftExists();
        $this->refreshOrder();
        $this->loadCustomerFromOrder(); // 👈 cargar nombre si ya tiene cliente
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
        if (!$order || $order->status !== \App\Enums\OrderStatus::DRAFT) {
            $this->ensureDraftExists();
        }
    }

    // 👇 NUEVO: cuando se edita el nombre, sincronizamos cliente en la orden
    public function updatedCustomerName($value): void
    {
        $name = trim((string) $value);

        $order = Order::find($this->orderId);
        if (!$order || $order->status !== \App\Enums\OrderStatus::DRAFT) return;

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
        $orders->removeItem($this->orderId, $itemId);
        $this->refreshOrder();
        $this->dispatch('order-updated');
    }

public function finalize(): void
{
    if ($this->finishing) return;
    $this->finishing = true;

    // Alinear el draft de la sesión con el componente en lugar de bloquear
    $draftId = (int) session('draft_order_id');
    if (!$draftId) {
        $this->ensureDraftExists();
        $draftId = (int) session('draft_order_id');
    }
    if ($draftId !== (int) $this->orderId) {
        // sincronizar y seguir
        $this->orderId = $draftId;
        $this->refreshOrder();
    }

    $stockService = app(StockService::class);
    $ordersService = app(OrderService::class);

    try {
        $finishedId = null;
        $affectedProductIds = [];

        DB::transaction(function () use (&$finishedId, &$affectedProductIds, $stockService, $ordersService) {
            $order = Order::with(['items.product','items.service'])
                ->lockForUpdate()
                ->findOrFail($this->orderId);

            if ($order->status !== \App\Enums\OrderStatus::DRAFT) {
                throw new DomainException('El pedido ya fue procesado.');
            }

            // Asociar cliente si se ingresó nombre
            $name = trim((string) $this->customerName);
            if ($name !== '' && !$order->client_id) {
                $user = auth()->user();
                $companyId = $user->isCompany() ? $user->id : Order::findRootCompanyId($user);
                $client = Client::firstOrCreate([
                    'user_id' => $companyId,
                    'name'    => $name,
                ]);
                $order->client()->associate($client);
                $order->save();
            }

            if ($order->items->isEmpty()) {
                throw new DomainException('El pedido está vacío.');
            }

            // Verificar stock
            foreach ($order->items as $item) {
                if ($item->product && $item->product->stock < $item->quantity) {
                    throw new DomainException("Stock insuficiente: {$item->product->name}");
                }
            }

            // Ajustar stock (usar adjustStock para que dispare notificaciones)
            foreach ($order->items as $item) {
                if ($item->product) {
                    $item->product->adjustStock(
                        -$item->quantity,
                        'pedido_completado',
                        auth()->user(),
                        $order
                    );
                    $affectedProductIds[] = $item->product->id;
                }
            }

            // Asociar métodos de pago seleccionados
            if (!empty($this->selectedPaymentMethods)) {
                $pivotData = [];
                $totalAmount = $order->total;
                $methodCount = count($this->selectedPaymentMethods);

                // Si solo hay un método, asignar todo el monto
                // Si hay múltiples, dividir el monto equitativamente (puede ajustarse después)
                $amountPerMethod = $totalAmount / $methodCount;

                foreach ($this->selectedPaymentMethods as $pmId) {
                    $pivotData[$pmId] = [
                        'amount' => $amountPerMethod,
                        'created_at' => now(),
                        'updated_at' => now(),
                    ];
                }

                $order->paymentMethods()->sync($pivotData);
            }

            // Recalcular total y finalizar pedido
            $order->recalcTotal();
            $order->status = \App\Enums\OrderStatus::COMPLETED;
            $order->save();

            $finishedId = (int) $order->id;
        });

        // Limpiar sesión y reiniciar draft
        session()->forget('draft_order_id');
        $this->startNewDraft();
        $this->js('$wire.$refresh()');

        // Disparar eventos de stock actualizado
        foreach ($affectedProductIds as $productId) {
            $this->dispatch('stock-updated', productId: $productId);
        }

        // Notificar al selector de métodos de pago para limpiar la selección
        $this->dispatch('orderFinalized');

        // Notificación de éxito
        $url = route('orders.show', ['order' => $finishedId]);
        $this->dispatch('notify', type: 'success', message: "Pedido #$finishedId creado correctamente. <a href=\"$url\" class=\"underline\">Ver</a>");

        // Eventos Livewire
        $this->dispatch('order-confirmed', orderId: $finishedId);
        $this->dispatch('order:confirmed', orderId: $finishedId);

    } catch (\Throwable $e) {
        $msg = $e instanceof DomainException ? $e->getMessage() : 'No se pudo finalizar el pedido.';
        $this->dispatch('notify', type: 'error', message: $msg);
    } finally {
        $this->finishing = false;
    }
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
            if ($order->status === \App\Enums\OrderStatus::DRAFT) {
                $order->status = \App\Enums\OrderStatus::CANCELED;
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
