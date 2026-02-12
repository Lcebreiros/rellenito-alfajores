<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class ApiRequestLogger
{
    public function handle(Request $request, Closure $next)
    {
        $started = microtime(true);
        $userId = $request->user()?->id;

        try {
            $response = $next($request);
        } catch (\Throwable $e) {
            $duration = round((microtime(true) - $started) * 1000, 1);
            Log::error('api.request.error', [
                'method' => $request->getMethod(),
                'path' => $request->path(),
                'user_id' => $userId,
                'ip' => $request->ip(),
                'ms' => $duration,
                'msg' => $e->getMessage(),
            ]);
            throw $e;
        }

        $duration = round((microtime(true) - $started) * 1000, 1);
        $status = $response->getStatusCode();
        $level = $status >= 500 ? 'error' : ($status >= 400 ? 'warning' : 'info');

        Log::{$level}('api.request', [
            'method' => $request->getMethod(),
            'path' => $request->path(),
            'status' => $status,
            'user_id' => $userId,
            'ip' => $request->ip(),
            'ms' => $duration,
        ]);

        return $response;
    }
}
