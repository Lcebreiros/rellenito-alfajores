<?php

namespace App\Console\Commands;

use App\Models\Order;
use Illuminate\Console\Command;
use App\Notifications\ScheduledOrderDueToday;

class NotifyScheduledOrdersToday extends Command
{
    protected $signature = 'orders:notify-today';

    protected $description = 'Crea notificaciones para pedidos agendados para hoy, solicitando confirmación o cancelación';

    public function handle()
    {
        $orders = Order::scheduledForToday()->with('user')->get();

        if ($orders->isEmpty()) {
            $this->info('No hay pedidos agendados para hoy.');
            return 0;
        }

        $count = 0;
        foreach ($orders as $order) {
            $user = $order->user;
            if (!$user) continue;

            // Evitar duplicados: busca notificación previa de hoy para este pedido
            $exists = $user->notifications()
                ->where('type', ScheduledOrderDueToday::class)
                ->whereDate('created_at', now()->toDateString())
                ->whereJsonContains('data->order_id', $order->id)
                ->exists();
            if ($exists) continue;

            $user->notify(new ScheduledOrderDueToday($order));
            $this->line("Notificada orden #{$order->id} a {$user->name}");
            $count++;
        }

        $this->info("Total notificaciones creadas: {$count}");
        return 0;
    }
}

