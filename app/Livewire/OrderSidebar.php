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
        if ($this->orderId === $orderId) {
            $this->refreshOrder();
        }
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
        if (!$sid) {
            $draft = Order::create(); // client_id NULL
            session(['draft_order_id' => $draft->id]);
            $this->orderId = (int) $draft->id;
            return;
        }
        if ($this->orderId !== $sid) {
            $this->orderId = $sid;
        }
    }

    private function startNewDraft(): void
    {
        $draft = Order::create();
        session(['draft_order_id' => $draft->id]);

        $this->orderId = (int) $draft->id;
        $this->items = [];
        $this->total = 0.0;
        $this->customerName = ''; // 👈 limpiar cliente

        $this->dispatch('draft-changed', id: $this->orderId);
        $this->skipRender = false;
    }

    private function guardDraft(): void
    {
        $order = Order::find($this->orderId);
        if (!$order || $order->status !== Order::STATUS_DRAFT) {
            $this->ensureDraftExists();
        }
    }

    // 👇 NUEVO: cuando se edita el nombre, sincronizamos cliente en la orden
    public function updatedCustomerName($value): void
    {
        $name = trim((string) $value);

        $order = Order::find($this->orderId);
        if (!$order || $order->status !== Order::STATUS_DRAFT) return;

        if ($name === '') {
            // Si se borró el nombre, desvinculamos el cliente
            $order->client_id = null;
            $order->save();
            return;
        }

        // Buscamos o creamos un cliente con ese nombre (email/teléfono pueden ir después)
        $client = Client::firstOrCreate(['name' => $name]);
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

        $stock = app(StockService::class);

        $draftId = (int) session('draft_order_id');
        if ($draftId !== (int) $this->orderId) {
            $this->dispatch('notify', type:'error', message:'Pedido no pertenece a tu sesión.');
            $this->finishing = false;
            return;
        }

        try {
            $finishedId = null;
            $affectedProductIds = [];

            DB::transaction(function () use ($stock, &$finishedId, &$affectedProductIds) {
                $order = Order::with(['items.product'])
                    ->lockForUpdate()
                    ->findOrFail($this->orderId);

                // 👇 Asegurar cliente si se escribió nombre y aún no está asociado
                $name = trim((string) $this->customerName);
                if ($name !== '' && !$order->client_id) {
                    $client = Client::firstOrCreate(['name' => $name]);
                    $order->client()->associate($client);
                }

                if ($order->items->isEmpty()) {
                    throw new DomainException('El pedido está vacío.');
                }

                foreach ($order->items as $item) {
                    if ($item->product->stock < $item->quantity) {
                        throw new DomainException("Stock insuficiente: {$item->product->name}");
                    }
                }

                foreach ($order->items as $item) {
                    $stock->adjust($item->product, -$item->quantity, 'order', $order);
                    $affectedProductIds[] = $item->product->id;
                }

                $order->recalcTotal();
                $order->status = Order::STATUS_COMPLETED;
                $order->save();

                $finishedId = (int) $order->id;
            });

            session()->forget('draft_order_id');
            $this->startNewDraft();
            $this->js('$wire.$refresh()');

            foreach ($affectedProductIds as $productId) {
                $this->dispatch('stock-updated', productId: $productId);
            }

            $url = route('orders.show', ['order' => $finishedId]);
            $this->dispatch('notify',
                type:'success',
                message:"Pedido #$finishedId creado correctamente. <a href=\"{$url}\" class=\"underline\">Ver</a>"
            );

            $this->dispatch('order-confirmed', orderId: $finishedId);
            $this->dispatch('order:confirmed', orderId: $finishedId);

        } catch (\Throwable $e) {
            $msg = $e instanceof DomainException ? $e->getMessage() : 'No se pudo finalizar el pedido.';
            $this->dispatch('notify', type:'error', message:$msg);
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
            if ($order->status === Order::STATUS_DRAFT) {
                $order->status = Order::STATUS_CANCELED;
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
