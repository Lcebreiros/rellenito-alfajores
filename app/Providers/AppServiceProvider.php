<?php

namespace App\Providers;

use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Product;
use App\Observers\OrderItemObserver;
use App\Observers\OrderObserver;
use App\Observers\ProductObserver;
use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Foundation\Support\Providers\RouteServiceProvider;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\ServiceProvider;
use Inertia\Inertia;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        if (env('APP_ENV') === 'production') {
            URL::forceScheme('https');
        }

        Inertia::setRootView('layouts.app');

        // Registrar observers
        Product::observe(ProductObserver::class);
        OrderItem::observe(OrderItemObserver::class);
        Order::observe(OrderObserver::class);

        // Configurar rate limiters personalizados
        RateLimiter::for('api', function (Request $request) {
            return Limit::perMinute(60)->by($request->user()?->id ?: $request->ip());
        });

        RateLimiter::for('api-auth', function (Request $request) {
            // 5 intentos por minuto para login/registro
            return Limit::perMinute(5)->by($request->ip())->response(function (Request $request, array $headers) {
                return response()->json([
                    'message' => 'Demasiados intentos. Por favor, intente nuevamente en 1 minuto.',
                    'retry_after' => $headers['Retry-After'] ?? 60,
                ], 429);
            });
        });

        RateLimiter::for('api-write', function (Request $request) {
            // 30 operaciones de escritura por minuto
            return Limit::perMinute(30)->by($request->user()?->id ?: $request->ip());
        });

        RateLimiter::for('api-read', function (Request $request) {
            // 100 operaciones de lectura por minuto
            return Limit::perMinute(100)->by($request->user()?->id ?: $request->ip());
        });
    }
}
