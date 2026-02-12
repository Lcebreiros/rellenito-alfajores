<?php

namespace App\Jobs;

use App\Models\User;
use App\Services\Insights\InsightService;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\Log;

/**
 * Job para generar insights de negocio de forma asíncrona
 *
 * Puede ejecutarse:
 * - Manualmente: dispatch(new GenerateBusinessInsights($user))
 * - Via cron: Schedule::job(new GenerateBusinessInsights($user))->daily()
 */
class GenerateBusinessInsights implements ShouldQueue
{
    use Queueable;

    /**
     * The number of times the job may be attempted.
     */
    public int $tries = 3;

    /**
     * The number of seconds to wait before retrying the job.
     */
    public int $backoff = 10;

    /**
     * The maximum number of unhandled exceptions to allow before failing.
     */
    public int $maxExceptions = 3;

    /**
     * Create a new job instance.
     */
    public function __construct(
        public User $user,
        public ?string $organizationId = null,
        public bool $clearExisting = false
    ) {
        //
    }

    /**
     * Execute the job.
     */
    public function handle(InsightService $insightService): void
    {
        try {
            // Refrescar usuario para obtener datos actualizados
            $this->user->refresh();

            Log::info('Starting insight generation', [
                'user_id' => $this->user->id,
                'organization_id' => $this->organizationId,
                'clear_existing' => $this->clearExisting,
            ]);

            // Generar insights
            $insights = $insightService->generateInsights(
                $this->user,
                $this->organizationId,
                $this->clearExisting
            );

            Log::info('Insight generation completed', [
                'user_id' => $this->user->id,
                'insights_count' => $insights->count(),
            ]);

        } catch (\Exception $e) {
            Log::error('Insight generation job failed', [
                'user_id' => $this->user->id,
                'organization_id' => $this->organizationId,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            // Re-lanzar excepción para que el job se marque como fallido
            throw $e;
        }
    }

    /**
     * Handle a job failure.
     */
    public function failed(\Throwable $exception): void
    {
        Log::error('Insight generation job permanently failed', [
            'user_id' => $this->user->id,
            'organization_id' => $this->organizationId,
            'error' => $exception->getMessage(),
        ]);

        // Aquí podrías notificar al administrador o al usuario
        // $this->user->notify(new InsightGenerationFailed($exception));
    }
}
