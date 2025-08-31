<?php

namespace App\Livewire;

use Livewire\Attributes\On;
use Livewire\Component;
use App\Services\OrderService;
use App\Services\StockService;
use App\Models\Order;
use Illuminate\Support\Facades\DB;
use DomainException;

class OrderSidebar extends Component
{
    public int $orderId;
    public array $items = [];
    public float $total = 0.0;
    public bool $finishing = false;

    public function mount(?int $orderId = null): void
    {
        $this->orderId = $orderId ?? (int) session('draft_order_id');
        $this->ensureDraftExists();
        $this->refreshOrder();
    }

    #[On('order-updated')]
    public function onOrderUpdated(): void
    {
        $this->refreshOrder();
    }

    // 🔥 CORREGIDO: Solo recibir el payload, resolver OrderService internamente
    #[On('draft-changed')]
    public function onDraftChanged(int $id): void
    {
        $this->orderId = $id;
        $this->refreshOrder();
    }

    // 🔥 CORREGIDO: Solo recibir el payload, resolver OrderService internamente
    #[On('item-added-to-order')]
    public function onItemAddedToOrder(int $orderId): void
    {
        // Solo actualizar si es nuestro pedido actual
        if ($this->orderId === $orderId) {
            $this->refreshOrder();
        }
    }

    // 🔥 CORREGIDO: Resolver OrderService internamente
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
            $draft = Order::create();
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

        // 👇 CORREGIDO: Emitir evento global para que otros componentes se enteren
        $this->dispatch('draft-changed', id: $this->orderId);
        
        // 👇 NUEVO: Forzar re-render de este componente
        $this->skipRender = false;
    }

    private function guardDraft(): void
    {
        $order = Order::find($this->orderId);
        if (!$order || $order->status !== Order::STATUS_DRAFT) {
            $this->ensureDraftExists();
        }
    }

    // 🔥 CORREGIDO: Resolver OrderService internamente
    public function add(int $itemId): void
    {
        $orders = app(OrderService::class);
        $this->guardDraft();
        $orders->mutateItem($this->orderId, $itemId, +1);
        $this->refreshOrder();
        
        // 👇 CORREGIDO: Emitir evento global para que otros componentes se sincronicen
        $this->dispatch('order-updated');
    }

    // 🔥 CORREGIDO: Resolver OrderService internamente
    public function sub(int $itemId): void
    {
        $orders = app(OrderService::class);
        $this->guardDraft();
        $orders->mutateItem($this->orderId, $itemId, -1);
        $this->refreshOrder();
        
        // 👇 CORREGIDO: Emitir evento global
        $this->dispatch('order-updated');
    }

    // 🔥 CORREGIDO: Resolver OrderService internamente
    public function remove(int $itemId): void
    {
        $orders = app(OrderService::class);
        $this->guardDraft();
        $orders->removeItem($this->orderId, $itemId);
        $this->refreshOrder();
        
        // 👇 CORREGIDO: Emitir evento global
        $this->dispatch('order-updated');
    }

    // 🔥 CORREGIDO: Resolver StockService internamente
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

            // 👇 CORREGIDO: Limpiar sesión ANTES de crear nuevo draft
            session()->forget('draft_order_id');
            
            // 👇 CORREGIDO: Crear nuevo draft y forzar actualización
            $this->startNewDraft();
            
            // 👇 NUEVO: Forzar re-render completo
            $this->js('$wire.$refresh()');

            // 👇 NUEVO: Notificar que el stock cambió para productos específicos
            foreach ($affectedProductIds as $productId) {
                $this->dispatch('stock-updated', productId: $productId);
            }

            $url = route('orders.show', ['order' => $finishedId]);
            $this->dispatch('notify',
                type:'success',
                message:"Pedido #$finishedId creado correctamente. <a href=\"{$url}\" class=\"underline\">Ver</a>"
            );

            // 👇 CORREGIDO: Emitir evento global para historial
            $this->dispatch('order-finalized', id: $finishedId);

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
        
        // 👇 NUEVO: Forzar re-render completo
        $this->js('$wire.$refresh()');
        
        $this->dispatch('notify', type:'info', message:'Pedido cancelado');
    }

    public function render()
    {
        return view('livewire.order-sidebar');
    }
}