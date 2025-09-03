<?php

namespace App\Livewire\Dashboard;

use App\Models\Order;
use Livewire\Component;

class OrdersWidget extends Component
{
    public function render()
    {
        $total = Order::count();

        return view('livewire.dashboard.orders-widget', [
            'total' => $total,
        ]);
    }
}
