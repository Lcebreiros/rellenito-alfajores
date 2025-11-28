<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class EnsureUserIsNotSuspended
{
    public function handle(Request $request, Closure $next)
    {
        $user = $request->user();

        // Si no hay usuario, seguimos (esto middleware se suele aplicar a rutas auth)
        if (! $user) {
            return $next($request);
        }

        // Master users (-1) siempre tienen acceso sin verificación
        if ($user->hierarchy_level === -1) {
            return $next($request);
        }

        // Verificar si el usuario está suspendido (usando is_active)
        if (!$user->is_active) {
            // Si es petición AJAX/JSON devolvemos 403 con mensaje
            if ($request->expectsJson()) {
                return response()->json(['message' => 'Cuenta suspendida'], 403);
            }

            // Redirigir a login con mensaje de cuenta suspendida
            return redirect()->route('login')
                             ->with('error', 'Tu cuenta ha sido suspendida. Contacta con el administrador.');
        }

        return $next($request);
    }
}
