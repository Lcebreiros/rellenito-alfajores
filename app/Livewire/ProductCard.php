<?php
declare(strict_types=1);

namespace App\Livewire;

use App\Models\Order;
use App\Models\Product;
use App\Services\OrderService;
use DomainException;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Livewire\Attributes\On;
use Livewire\Component;

class ProductCard extends Component
{
    public ?Product $product = null;
    public ?int $productId = null;

    public int $qty = 1;
    public int $currentStock = 0;
    public bool $isActive = false;
    public bool $isAdding = false;

    public string $displayMode = 'card';
    public string $buttonText = 'Agregar';
    public string $buttonClass = '';

    public function mount(
        mixed $product = null,
        ?int $productId = null,
        int $qty = 1,
        string $displayMode = 'card',
        string $buttonText = 'Agregar',
        string $buttonClass = ''
    ): void {
        $this->qty         = max(1, $qty);
        $this->displayMode = $displayMode;
        $this->buttonText  = $buttonText;
        $this->buttonClass = $buttonClass;

        if ($product instanceof Product) {
            $this->product   = $product;
            $this->productId = $product->id;
        } elseif ($productId !== null) {
            $this->productId = $productId;
        } elseif (is_int($product)) { // retrocompat
            $this->productId = $product;
        }

        $this->refreshProduct();
    }

    #[On('draft-changed')]   public function onDraftChanged(int $id): void   { $this->refreshProduct(); }
    #[On('order-updated')]   public function onOrderUpdated(): void          { $this->refreshProduct(); }
    #[On('order-finalized')] public function onOrderFinalized(int $id): void { $this->refreshProduct(); }
    #[On('stock-updated')]
    public function onStockUpdated(int $productId): void
    {
        if ($this->productId === $productId) $this->refreshProduct();
    }

    private function refreshProduct(): void
    {
        if (!$this->productId) {
            $this->product = null;
            $this->currentStock = 0;
            $this->isActive = false;
            $this->qty = 1;
            return;
        }

        if (!$this->product || $this->product->id !== $this->productId) {
            // Importante: evitar el scope global 'byUser' para soportar inventario compartido por empresa
            $this->product = Product::withoutGlobalScope('byUser')->find($this->productId);
        } else {
            // Refrescar también sin el scope para mantener consistencia
            $this->product = Product::withoutGlobalScope('byUser')->find($this->product->id);
        }

        if ($this->product) {
            $this->currentStock = (int) $this->product->stock;
            $this->isActive     = (bool) $this->product->is_active;
            $this->qty          = max(1, min($this->qty, max(0, $this->currentStock)));
        } else {
            $this->currentStock = 0;
            $this->isActive     = false;
            $this->qty          = 1;
        }
    }

    private function getCurrentDraftId(): int
    {
        // Usar servicio central para crear/obtener el draft y asegurar consistencia (branch/company, sesión).
        /** @var \Illuminate\Http\Request $req */
        $req = request();
        /** @var \App\Services\OrderService $orders */
        $orders = app(\App\Services\OrderService::class);
        $draft = $orders->currentDraft($req);
        return (int) $draft->id;
    }

    public function incrementQty(): void { if ($this->qty < $this->currentStock) $this->qty++; }
    public function decrementQty(): void { if ($this->qty > 1) $this->qty--; }
    public function updatedQty(): void   { $this->qty = max(1, min($this->qty, max(0, $this->currentStock))); }

    public function add(): void
    {
        if ($this->isAdding) return;
        $this->isAdding = true;

        try {
            // 🔎 Log al inicio (debug temporal)
            \Log::info('ProductCard@add fired', ['productId' => $this->productId]);

            if (!$this->productId) {
                $this->dispatch('notify', type:'error', message:'Producto inválido.');
                return;
            }

            $this->refreshProduct();

            if (!$this->product) {
                $this->dispatch('notify', type:'error', message:'Producto no encontrado.');
                return;
            }
            if (!$this->isActive) {
                $this->dispatch('notify', type:'error', message:'Producto inactivo.');
                return;
            }
            if ($this->currentStock < $this->qty) {
                $this->dispatch('notify', type:'error', message:'Stock insuficiente.');
                return;
            }

            $draftId = $this->getCurrentDraftId();
            $orders  = app(OrderService::class);
            $orders->addItem($draftId, $this->productId, $this->qty);

            $this->refreshProduct();

            // Notificar al OrderSidebar explícitamente para evitar problemas de alcance de eventos
            $this->dispatch('item-added-to-order', orderId:$draftId)->to(\App\Livewire\OrderSidebar::class);
            $this->dispatch('order-updated')->to(\App\Livewire\OrderSidebar::class);
            $this->dispatch('stock-updated', productId:$this->productId)->to(\App\Livewire\OrderSidebar::class);

            $msg = $this->qty === 1 ? 'Agregado al pedido' : "Agregados {$this->qty} al pedido";
            $this->dispatch('notify', type:'success', message:$msg);

            if ($this->displayMode === 'button') $this->qty = 1;

        } catch (ModelNotFoundException) {
            $this->dispatch('notify', type:'error', message:'Producto no encontrado.');
        } catch (DomainException $e) {
            $this->dispatch('notify', type:'error', message:$e->getMessage());
        } catch (\Throwable $e) {
            \Log::error('Error adding product to order', [
                'message'    => $e->getMessage(),
                'product_id' => $this->productId,
                'qty'        => $this->qty,
                'draft_id'   => session('draft_order_id'),
            ]);
            $this->dispatch('notify', type:'error', message:'Error al agregar producto.');
        } finally {
            $this->isAdding = false;
        }
    }

    public function addOne(): void
    {
        $prev = $this->qty;
        $this->qty = 1;
        $this->add();
        $this->qty = $prev;
    }

    public function render()
    {
        return view('livewire.product-card');
    }

    // dentro de la clase ProductCard
public int $clicks = 0; // 👈 contador de test

public function testClick(): void
{
    $this->clicks++;
    \Log::info('ProductCard@testClick fired', [
        'productId' => $this->productId,
        'clicks'    => $this->clicks,
    ]);
}

}
