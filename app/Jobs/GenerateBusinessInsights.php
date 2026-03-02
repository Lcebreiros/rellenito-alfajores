<?php

namespace App\Jobs;

use App\Models\User;
use App\Services\Insights\InsightService;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;

/**
 * Job para generar insights de negocio de forma asíncrona.
 * Deposita el resultado en cache para que Nexum::checkGeneration() lo consuma.
 */
class GenerateBusinessInsights implements ShouldQueue
{
    use Queueable;

    /** Reintentos solo para errores transitorios (network, timeout). */
    public int $tries = 3;

    /** Segundos entre reintentos. */
    public int $backoff = 15;

    /** No reintentar si el job explota (RuntimeException ya cancela antes). */
    public int $maxExceptions = 3;

    public function __construct(
        public User $user,
        public ?string $organizationId = null,
        public bool $clearExisting = false
    ) {}

    public function handle(InsightService $insightService): void
    {
        $this->user->refresh();
        $userId = $this->resolveCompanyUserId();

        Log::info('Starting insight generation', [
            'user_id'       => $userId,
            'clear_existing'=> $this->clearExisting,
        ]);

        try {
            $insights = $insightService->generateInsights(
                $this->user,
                $this->organizationId,
                $this->clearExisting
            );

            $count = $insights->count();
            $msg   = $this->user->hasAiInsights()
                ? "Nexum AI generó {$count} diagnósticos."
                : "{$count} diagnósticos actualizados.";

            Cache::put("nexum_result_{$userId}", ['success' => true, 'message' => $msg], 300);
            Cache::forget("nexum_pending_{$userId}");

            Log::info('Insight generation completed', [
                'user_id'        => $userId,
                'insights_count' => $count,
            ]);

        } catch (\RuntimeException $e) {
            // Error con mensaje para el usuario (sin API key, sin créditos, etc.)
            // No tiene sentido reintentar — falla inmediatamente.
            Log::warning('Insight generation: non-retryable error', [
                'user_id' => $userId,
                'error'   => $e->getMessage(),
            ]);
            Cache::put("nexum_result_{$userId}", ['success' => false, 'message' => $e->getMessage()], 300);
            Cache::forget("nexum_pending_{$userId}");
            $this->fail($e);

        } catch (\Exception $e) {
            // Error transitorio — el job se reintentará automáticamente.
            // Solo limpiar el estado en el último intento.
            if ($this->attempts() >= $this->tries) {
                Cache::put("nexum_result_{$userId}", [
                    'success' => false,
                    'message' => 'Error al generar diagnósticos. Intentá nuevamente más tarde.',
                ], 300);
                Cache::forget("nexum_pending_{$userId}");
            }
            Log::error('Insight generation job failed (attempt ' . $this->attempts() . ')', [
                'user_id' => $userId,
                'error'   => $e->getMessage(),
            ]);
            throw $e;
        }
    }

    public function failed(\Throwable $exception): void
    {
        $userId = $this->resolveCompanyUserId();
        // Asegurarse de limpiar el pending aunque fallen los catch internos
        Cache::forget("nexum_pending_{$userId}");
        Log::error('Insight generation job permanently failed', [
            'user_id' => $userId,
            'error'   => $exception->getMessage(),
        ]);
    }

    private function resolveCompanyUserId(): int
    {
        return $this->user->isCompany()
            ? $this->user->id
            : ($this->user->rootCompany()?->id ?? $this->user->id);
    }
}
