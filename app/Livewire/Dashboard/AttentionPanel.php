<?php

namespace App\Livewire\Dashboard;

use Livewire\Component;
use Illuminate\Support\Facades\Auth;
use App\Models\Order;
use App\Models\Product;
use App\Models\ThirdPartyService;

class AttentionPanel extends Component
{
    public function render()
    {
        $user  = Auth::user();
        $items = [];

        // 1. Órdenes activas (no borrador, no completadas, no canceladas, no agendadas)
        try {
            $pending = Order::availableFor($user)
                ->whereNotIn('status', ['draft', 'completed', 'canceled', 'scheduled'])
                ->count();

            if ($pending > 0) {
                $items[] = [
                    'level' => 'warning',
                    'icon'  => 'orders',
                    'label' => $pending === 1
                        ? '1 orden activa sin finalizar'
                        : "{$pending} órdenes activas sin finalizar",
                    'route' => route('orders.index'),
                ];
            }
        } catch (\Throwable) {}

        // 2. Pedidos agendados para hoy
        try {
            $scheduledToday = Order::availableFor($user)
                ->scheduled()
                ->whereDate('scheduled_for', today())
                ->count();

            if ($scheduledToday > 0) {
                $items[] = [
                    'level' => 'info',
                    'icon'  => 'calendar',
                    'label' => $scheduledToday === 1
                        ? '1 pedido agendado para hoy'
                        : "{$scheduledToday} pedidos agendados para hoy",
                    'route' => route('orders.index'),
                ];
            }
        } catch (\Throwable) {}

        // 3. Productos con stock bajo
        try {
            $lowStock = Product::availableFor($user)->lowStock()->count();

            if ($lowStock > 0) {
                $items[] = [
                    'level' => 'warning',
                    'icon'  => 'stock',
                    'label' => $lowStock === 1
                        ? '1 producto con stock bajo'
                        : "{$lowStock} productos con stock bajo",
                    'route' => route('stock.index'),
                ];
            }
        } catch (\Throwable) {}

        // 4. Pagos de servicios vencidos o que vencen en 7 días
        try {
            $overdue = ThirdPartyService::query()
                ->where('is_active', true)
                ->whereNotNull('next_payment_date')
                ->where('next_payment_date', '<', now()->startOfDay())
                ->count();

            $upcoming = ThirdPartyService::query()
                ->where('is_active', true)
                ->whereNotNull('next_payment_date')
                ->whereBetween('next_payment_date', [now()->startOfDay(), now()->addDays(7)->endOfDay()])
                ->count();

            if ($overdue > 0) {
                $items[] = [
                    'level' => 'danger',
                    'icon'  => 'payment',
                    'label' => $overdue === 1
                        ? '1 pago de servicio vencido'
                        : "{$overdue} pagos de servicios vencidos",
                    'route' => route('expenses.third-party'),
                ];
            } elseif ($upcoming > 0) {
                $items[] = [
                    'level' => 'info',
                    'icon'  => 'payment',
                    'label' => $upcoming === 1
                        ? '1 pago de servicio vence en los próximos 7 días'
                        : "{$upcoming} pagos de servicios vencen en los próximos 7 días",
                    'route' => route('expenses.third-party'),
                ];
            }
        } catch (\Throwable) {}

        // 5. Reservas de hoy (módulo alquileres)
        if ($user->hasModule('alquileres')) {
            try {
                $companyId = $user->isCompany() ? $user->id : $user->parent_id;
                $todayBookings = \App\Models\Booking::where('company_id', $companyId)
                    ->whereIn('status', ['pending', 'confirmed'])
                    ->whereDate('starts_at', today())
                    ->count();

                if ($todayBookings > 0) {
                    $items[] = [
                        'level' => 'info',
                        'icon'  => 'booking',
                        'label' => $todayBookings === 1
                            ? '1 reserva activa hoy'
                            : "{$todayBookings} reservas activas hoy",
                        'route' => route('rentals.calendar'),
                    ];
                }
            } catch (\Throwable) {}
        }

        return view('livewire.dashboard.attention-panel', [
            'items' => $items,
        ]);
    }
}
