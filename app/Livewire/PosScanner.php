<?php

declare(strict_types=1);

namespace App\Livewire;

use App\Models\Product;
use App\Services\OrderService;
use Livewire\Attributes\On;
use Livewire\Component;

class PosScanner extends Component
{
    // idle | scanning | found | not_found | no_stock | error
    public string $status = 'idle';
    public string $productName = '';
    public string $lastCode = '';

    public function scan(string $code): void
    {
        $code = trim($code);
        if (strlen($code) < 2) return;

        $this->status      = 'scanning';
        $this->lastCode    = $code;
        $this->productName = '';

        $user = auth()->user();

        // Buscar por barcode o SKU
        $query = Product::withoutGlobalScope('byUser')
            ->where(fn($q) => $q->where('barcode', $code)->orWhere('sku', $code))
            ->active();

        if (!$user->isMaster()) {
            $query = Product::availableFor($user)
                ->where(fn($q) => $q->where('barcode', $code)->orWhere('sku', $code))
                ->active();
        }

        $product = $query->first();

        if (!$product) {
            $this->status = 'not_found';
            return;
        }

        if ($product->uses_stock && $product->stock < 1) {
            $this->status      = 'no_stock';
            $this->productName = $product->name;
            return;
        }

        try {
            /** @var OrderService $orders */
            $orders = app(OrderService::class);
            $draft  = $orders->currentDraft(request());
            $orders->addItem($draft->id, $product->id, 1);

            $this->status      = 'found';
            $this->productName = $product->name;

            $this->dispatch('order-updated')->to(OrderSidebar::class);
            $this->dispatch('item-added-to-order', orderId: $draft->id)->to(OrderSidebar::class);
            $this->dispatch('stock-updated', productId: $product->id);

        } catch (\Throwable $e) {
            $this->status = 'error';
            \Log::error('PosScanner error', ['code' => $code, 'error' => $e->getMessage()]);
        }
    }

    public function resetStatus(): void
    {
        $this->status      = 'idle';
        $this->productName = '';
        $this->lastCode    = '';
    }

    public function render()
    {
        return view('livewire.pos-scanner');
    }
}
