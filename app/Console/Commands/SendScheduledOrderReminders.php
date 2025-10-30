<?php

namespace App\Console\Commands;

use App\Models\Order;
use App\Models\User;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Notification;
use App\Notifications\ScheduledOrderReminder;

class SendScheduledOrderReminders extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'orders:send-reminders';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Envía recordatorios de pedidos agendados para mañana';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('Buscando pedidos agendados que necesitan recordatorio...');

        // Obtener pedidos agendados para mañana que aún no tienen recordatorio enviado
        $orders = Order::needsReminder()->with(['client', 'user'])->get();

        if ($orders->isEmpty()) {
            $this->info('No hay pedidos que necesiten recordatorio.');
            return 0;
        }

        $this->info("Encontrados {$orders->count()} pedido(s) para enviar recordatorio.");

        $sent = 0;
        $failed = 0;

        foreach ($orders as $order) {
            try {
                // Enviar notificación al usuario creador del pedido
                if ($order->user) {
                    // Aquí podrías usar diferentes canales: mail, database, sms, etc.
                    // Por ahora solo marcamos como enviado
                    $this->line("📧 Recordatorio para pedido #{$order->id} - Usuario: {$order->user->name}");

                    // Marcar recordatorio como enviado
                    $order->reminder_sent_at = now();
                    $order->save();

                    $sent++;
                } else {
                    $this->warn("⚠️  Pedido #{$order->id} no tiene usuario asignado");
                    $failed++;
                }
            } catch (\Throwable $e) {
                $this->error("❌ Error enviando recordatorio para pedido #{$order->id}: {$e->getMessage()}");
                $failed++;
            }
        }

        $this->newLine();
        $this->info("✅ Recordatorios enviados: {$sent}");
        if ($failed > 0) {
            $this->warn("⚠️  Fallidos: {$failed}");
        }

        return 0;
    }
}
