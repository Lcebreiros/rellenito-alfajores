<?php

// app/Livewire/ProductCard.php
namespace App\Livewire;

use Livewire\Component;
use Livewire\Attributes\On;
use App\Models\Product;
use App\Models\Order;
use App\Services\OrderService;

class ProductCard extends Component
{
    public int $productId;
    public ?Product $product = null;
    public int $currentStock = 0;
    public bool $isActive = true;

    public function mount(int $productId)
    {
        $this->productId = $productId;
        $this->refreshProduct();
    }

    #[On('draft-changed')]
    public function onDraftChanged(int $id): void
    {
        // El sidebar creó un nuevo draft, refrescar producto por si cambió el stock
        $this->refreshProduct();
    }

    #[On('order-updated')]
    public function onOrderUpdated(): void
    {
        // Cuando se actualiza cualquier pedido, refrescar el stock
        $this->refreshProduct();
    }

    #[On('order-finalized')]
    public function onOrderFinalized(int $id): void
    {
        // Cuando se finaliza un pedido, el stock se reduce, actualizar
        $this->refreshProduct();
    }

    #[On('stock-updated')]
    public function onStockUpdated(int $productId): void
    {
        // Si se actualiza el stock de este producto específicamente
        if ($this->productId === $productId) {
            $this->refreshProduct();
        }
    }

    /**
     * Refresca los datos del producto desde la base de datos
     */
    private function refreshProduct(): void
    {
        $this->product = Product::find($this->productId);
        
        if ($this->product) {
            $this->currentStock = $this->product->stock;
            $this->isActive = $this->product->is_active;
        } else {
            $this->currentStock = 0;
            $this->isActive = false;
        }
    }

    /**
     * Obtiene el ID del pedido draft actual desde la sesión
     */
    private function getCurrentDraftId(): int
    {
        $draftId = (int) session('draft_order_id');
        
        if (!$draftId) {
            // Si no hay draft en sesión, crear uno nuevo
            $draft = Order::create();
            session(['draft_order_id' => $draft->id]);
            $draftId = $draft->id;
        }
        
        return $draftId;
    }

    public function add(OrderService $orders): void
    {
        // Verificar stock actualizado antes de agregar
        $this->refreshProduct();
        
        if (!$this->isActive || $this->currentStock <= 0) {
            $this->dispatch('notify', type: 'error', message: 'Sin stock o inactivo.');
            return;
        }

        // 👇 CORREGIDO: Obtener el draft ID actual dinámicamente
        $currentDraftId = $this->getCurrentDraftId();
        
        $orders->addItem($currentDraftId, $this->productId, 1);
        
        // 👇 Refrescar producto después de agregar (por si el stock cambió)
        $this->refreshProduct();
        
        // 👇 CORREGIDO: Notificar al sidebar con el ID correcto
        $this->dispatch('item-added-to-order', orderId: $currentDraftId);
        $this->dispatch('order-updated');
        $this->dispatch('notify', type: 'success', message: 'Agregado');
    }

    public function render()
    {
        return view('livewire.product-card');
    }
}