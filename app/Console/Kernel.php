<?php

namespace App\Console;

use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;

class Kernel extends ConsoleKernel
{
    protected $commands = [
        \App\Console\Commands\BackfillProductLocations::class,
    ];

    protected function schedule(Schedule $schedule): void
    {
        // Enviar recordatorios de pedidos agendados todos los días a las 10:00 AM
        $schedule->command('orders:send-reminders')
            ->dailyAt('10:00')
            ->timezone('America/Argentina/Buenos_Aires');

        // Notificar pedidos agendados para hoy a las 08:00 AM
        $schedule->command('orders:notify-today')
            ->dailyAt('08:00')
            ->timezone('America/Argentina/Buenos_Aires');
    }

    protected function commands(): void
    {
        $this->load(__DIR__.'/Commands');
    }
}
