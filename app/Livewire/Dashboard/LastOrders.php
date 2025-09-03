<?php

namespace App\Livewire\Dashboard;

use Livewire\Component;
use App\Models\Order;

class LastOrders extends Component
{
    public $orders;

    public function mount()
    {
        $this->orders = Order::query()
            ->latest()
            ->take(6)
            ->get(['id','status','total','created_at']);
    }

    public function render()
    {
        return view('livewire.dashboard.last-orders');
    }
}
