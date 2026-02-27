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

        // Verificar plan efectivo (empleados heredan el plan de su empresa raíz)
        $validPlans = ['basic', 'premium', 'enterprise'];
        $level = $user->effectiveSubscriptionLevel();

        if (!$level || !in_array($level, $validPlans)) {
            return redirect()->route('plans')
                ->with('error', 'Necesitas un plan de suscripción activo para acceder al panel. Por favor, solicita acceso a un plan.');
        }

        return $next($request);
    }
}
