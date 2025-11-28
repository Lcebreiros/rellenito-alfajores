<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use App\Http\Middleware\SetUserTimezone;
use App\Http\Middleware\RequiresSubscription;
use Illuminate\Console\Scheduling\Schedule;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        api: __DIR__.'/../routes/api.php',
        commands: __DIR__.'/../routes/console.php',
        channels: __DIR__.'/../routes/channels.php',
        health: '/up',
    )
    ->withSchedule(function (Schedule $schedule): void {
        // Backup diario a las 2:00 AM
        $schedule->command('backup:run')->dailyAt('02:00');

        // Limpiar backups antiguos diariamente a las 3:00 AM
        $schedule->command('backup:clean')->dailyAt('03:00');

        // Monitorear estado de backups cada hora
        $schedule->command('backup:monitor')->hourly();
    })
    ->withMiddleware(function (Middleware $middleware): void {
        // Aplica el middleware en todas las requests
        $middleware->web(append: [
            SetUserTimezone::class,
        ]);

        // Registrar middleware con alias
        $middleware->alias([
            'requires.subscription' => RequiresSubscription::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        // Manejar error 419 (CSRF Token Mismatch / Sesión expirada)
        $exceptions->renderable(function (\Symfony\Component\HttpKernel\Exception\HttpException $e, $request) {
            if ($e->getStatusCode() === 419) {
                // Si es una petición AJAX o Livewire, devolver JSON
                if ($request->expectsJson() || $request->header('X-Livewire')) {
                    return response()->json([
                        'message' => 'Su sesión ha expirado. Por favor, recargue la página e inicie sesión nuevamente.',
                        'redirect' => route('login')
                    ], 419);
                }

                // Para peticiones normales, mostrar la vista personalizada
                return response()->view('errors.419', [], 419);
            }
        });
    })
    ->create();
