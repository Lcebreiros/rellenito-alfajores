<?php

namespace App\Livewire\Dashboard;

use App\Models\Order;
use Livewire\Component;
use Illuminate\Support\Facades\Auth;

class OrdersWidget extends Component
{
    public function render()
    {
        $total = Order::availableFor(Auth::user())
            ->where('status', '!=', 'draft')
            ->count();

        return view('livewire.dashboard.orders-widget', [
            'total' => $total,
        ]);
    }
}
