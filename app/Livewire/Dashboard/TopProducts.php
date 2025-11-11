<?php

namespace App\Livewire\Dashboard;

use Livewire\Component;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use App\Models\Order;

class TopProducts extends Component
{
    public int $limit = 5;
    public int $days  = 30;

    public function mount(int $limit = 5, int $days = 30): void
    {
        $this->limit = max(3, min(10, $limit));
        $this->days  = max(1, min(90, $days));
    }

    public function render()
    {
        $from = now()->subDays($this->days);

        $user = Auth::user();
        $ordersSub = Order::query()
            ->availableFor($user)
            ->where('status', 'completed')
            ->where('created_at', '>=', $from)
            ->select('id');

        $rows = DB::table('order_items as oi')
            ->leftJoin('products as p', 'p.id', '=', 'oi.product_id')
            ->whereIn('oi.order_id', $ordersSub)
            ->groupBy('oi.product_id', 'p.name')
            ->orderByDesc(DB::raw('SUM(oi.quantity)'))
            ->limit($this->limit)
            ->selectRaw('p.name as name, COALESCE(SUM(oi.quantity),0) as qty')
            ->get();

        return view('livewire.dashboard.top-products', [
            'rows' => $rows,
            'days' => $this->days,
        ]);
    }
}
