<?php

namespace App\Livewire\Dashboard;

use Livewire\Component;
use App\Models\Order;
use Illuminate\Support\Facades\Auth;

class RecentOrders extends Component
{
    public int $limit = 5;

    public function mount(int $limit = 5): void
    {
        $this->limit = max(3, min(10, $limit));
    }

    public function render()
    {
        $user = Auth::user();

        $orders = Order::query()
            ->availableFor($user)
            ->with(['client:id,name'])
            ->orderByDesc('created_at')
            ->limit($this->limit)
            ->get(['id','order_number','client_id','total','status','created_at']);

        return view('livewire.dashboard.recent-orders', [
            'orders' => $orders,
        ]);
    }
}
