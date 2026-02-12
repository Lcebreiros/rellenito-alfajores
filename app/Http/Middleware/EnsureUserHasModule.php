<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use Illuminate\Support\Facades\Auth;

class EnsureUserHasModule
{
    /**
     * Handle an incoming request.
     *
     * Verifica que el usuario tenga el módulo especificado activo.
     * Uso: Route::middleware('module:parking')
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     * @param  string  $module - Nombre del módulo requerido
     */
    public function handle(Request $request, Closure $next, string $module): Response
    {
        $user = Auth::user();

        // Si no hay usuario autenticado, dejar que otros middlewares manejen la autenticación
        if (!$user) {
            return $next($request);
        }

        // IMPORTANTE: El usuario master (nivel -1) tiene acceso ABSOLUTO a todo
        // No se verifica módulos para el master
        if ($user->isMaster()) {
            return $next($request);
        }

        // Obtener la empresa principal (si es empleado, usar la empresa padre)
        $company = $user->isCompany() ? $user : $user->parent;

        // Verificar si tiene el módulo activo
        if (!$company || !$company->hasModule($module)) {
            // Si es una petición AJAX/API, devolver JSON
            if ($request->expectsJson()) {
                return response()->json([
                    'message' => "Esta funcionalidad requiere el módulo '{$module}' activo.",
                    'required_module' => $module,
                ], 403);
            }

            // Si es web, redirigir con mensaje
            return redirect()->route('dashboard')
                ->with('error', "No tienes acceso a esta funcionalidad. Módulo '{$module}' no activo.");
        }

        return $next($request);
    }
}
