<?php

namespace App\Livewire;

use Livewire\Component;
use App\Models\Product;
use App\Services\OrderService;

class AddToOrderButton extends Component
{
    public Product $product;
    public int $qty = 1;

    public function mount(Product $product, int $qty = 1)
    {
        $this->product = $product;
        $this->qty     = max(1, $qty);
    }

    public function add(OrderService $orders)
    {
        // Validaciones rápidas
        if (!$this->product->is_active) {
            $this->dispatch('notify', type: 'error', message: 'Producto inactivo.');
            return;
        }
        if ($this->qty < 1) $this->qty = 1;

        // Añadir al pedido
        $orders->addItem($this->product, $this->qty);

        // Avisar a otros componentes (ej: la barra lateral del pedido)
        $this->dispatch('order-updated');

        // Feedback local
        $this->dispatch('notify', type: 'success', message: 'Agregado al pedido');
    }

    public function render()
    {
        return view('livewire.add-to-order-button');
    }
}
