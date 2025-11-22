<?php

namespace App\Console\Commands;

use App\Models\Order;
use App\Enums\OrderStatus;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Notification;

class ProcessScheduledOrders extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'orders:process-scheduled';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Procesa pedidos agendados para hoy y los cambia a estado PENDING';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('Buscando pedidos agendados para hoy...');

        // Obtener pedidos agendados para hoy
        $orders = Order::scheduledForToday()->with(['user', 'client'])->get();

        if ($orders->isEmpty()) {
            $this->info('No hay pedidos agendados para hoy.');
            return 0;
        }

        $this->info("Encontrados {$orders->count()} pedido(s) agendados para hoy.");

        $processed = 0;
        $failed = 0;

        foreach ($orders as $order) {
            try {
                // Cambiar estado de SCHEDULED a PENDING
                $order->status = OrderStatus::PENDING->value;
                $order->save();

                $this->line("✅ Pedido #{$order->id} movido a PENDING - Cliente: " . ($order->client?->name ?? 'Sin cliente'));

                // Aquí podrías enviar una notificación al usuario
                // $order->user?->notify(new ScheduledOrderReady($order));

                $processed++;
            } catch (\Throwable $e) {
                $this->error("❌ Error procesando pedido #{$order->id}: {$e->getMessage()}");
                $failed++;
            }
        }

        $this->newLine();
        $this->info("✅ Pedidos procesados: {$processed}");
        if ($failed > 0) {
            $this->warn("⚠️  Fallidos: {$failed}");
        }

        return 0;
    }
}
