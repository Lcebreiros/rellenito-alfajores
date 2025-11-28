<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class RequiresSubscription
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        // Si el usuario no está autenticado, dejar que el middleware 'auth' lo maneje
        if (!$user) {
            return $next($request);
        }

        // Master users (-1) siempre tienen acceso
        if ($user->hierarchy_level === -1) {
            return $next($request);
        }

        // Verificar si el usuario tiene un plan de suscripción válido
        $validPlans = ['basic', 'premium', 'enterprise'];

        if (!$user->subscription_level || !in_array($user->subscription_level, $validPlans)) {
            // Redirigir a la página de planes con mensaje
            return redirect()->route('plans')
                ->with('error', 'Necesitas un plan de suscripción activo para acceder al panel. Por favor, solicita acceso a un plan.');
        }

        return $next($request);
    }
}
