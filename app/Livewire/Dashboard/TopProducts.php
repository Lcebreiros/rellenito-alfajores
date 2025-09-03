<?php

namespace App\Livewire\Dashboard;

use Livewire\Component;
use App\Models\OrderItem;

class TopProducts extends Component
{
    public $rows;

    public function mount(): void
    {
        // Últimos 30 días, top 6 por cantidad
        $this->rows = OrderItem::query()
            ->where('created_at', '>=', now()->subDays(30))
            // (opcional) solo órdenes completadas:
            // ->whereHas('order', fn($q) => $q->whereIn('status', ['paid','completed','closed','finalized']))
            ->selectRaw('product_id, SUM(quantity) as qty')
            ->groupBy('product_id')
            ->orderByDesc('qty')
            ->with(['product:id,name'])
            ->limit(6)
            ->get()
            ->map(fn ($row) => [
                'id'   => $row->product_id,
                'name' => $row->product?->name ?? '—',
                'qty'  => (float) $row->qty,
            ]);
    }

    public function render()
    {
        return view('livewire.dashboard.top-products');
    }
}
