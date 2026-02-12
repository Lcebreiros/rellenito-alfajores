<?php

namespace App\Services\Insights;

use App\Models\BusinessInsight;
use App\Models\User;
use App\Services\Insights\Generators\LowStockInsightGenerator;
use App\Services\Insights\Generators\RevenueOpportunityGenerator;
use App\Services\Insights\Generators\CostWarningGenerator;
use App\Services\Insights\Generators\ClientRetentionGenerator;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

/**
 * Servicio principal de Business Insights
 *
 * Orquesta la generación, almacenamiento y recuperación de insights
 */
class InsightService
{
    /**
     * Generadores disponibles
     */
    protected array $generators = [
        LowStockInsightGenerator::class,
        RevenueOpportunityGenerator::class,
        CostWarningGenerator::class,
        ClientRetentionGenerator::class,
    ];

    /**
     * Genera nuevos insights para un usuario
     *
     * @param User $user
     * @param string|null $organizationId
     * @param bool $clearExisting Si debe eliminar insights existentes antes de generar nuevos
     * @return Collection<BusinessInsight>
     */
    public function generateInsights(
        User $user,
        ?string $organizationId = null,
        bool $clearExisting = false
    ): Collection {
        try {
            // Eliminar insights antiguos si se requiere
            if ($clearExisting) {
                $this->clearOldInsights($user, $organizationId);
            }

            $allInsights = collect();

            // Ejecutar todos los generadores
            foreach ($this->generators as $generatorClass) {
                try {
                    $generator = new $generatorClass($user, $organizationId);
                    $insights = $generator->generate();

                    // Guardar insights en la base de datos
                    foreach ($insights as $insightData) {
                        $insight = BusinessInsight::create([
                            'user_id' => $user->id,
                            'organization_id' => $organizationId,
                            ...$insightData,
                        ]);

                        $allInsights->push($insight);
                    }

                    Log::info("Generated {$insights->count()} insights using {$generatorClass}", [
                        'user_id' => $user->id,
                    ]);
                } catch (\Exception $e) {
                    Log::error("Error generating insights with {$generatorClass}: " . $e->getMessage(), [
                        'user_id' => $user->id,
                        'exception' => $e,
                    ]);
                    // Continuar con el siguiente generador
                    continue;
                }
            }

            return $allInsights;
        } catch (\Exception $e) {
            Log::error('Error in generateInsights: ' . $e->getMessage(), [
                'user_id' => $user->id,
                'exception' => $e,
            ]);
            throw $e;
        }
    }

    /**
     * Obtiene insights activos para un usuario
     *
     * @param User $user
     * @param array $filters Filtros opcionales (type, priority, limit)
     * @return Collection<BusinessInsight>
     */
    public function getInsights(User $user, array $filters = []): Collection
    {
        $query = BusinessInsight::query()
            ->forUser($user->id)
            ->active()
            ->orderByPriority();

        // Filtro por tipo
        if (!empty($filters['type'])) {
            $query->ofType($filters['type']);
        }

        // Filtro por prioridad
        if (!empty($filters['priority'])) {
            $query->ofPriority($filters['priority']);
        }

        // Límite
        if (!empty($filters['limit'])) {
            $query->limit($filters['limit']);
        }

        return $query->get();
    }

    /**
     * Marca un insight como descartado
     *
     * @param int $insightId
     * @param User $user
     * @return BusinessInsight|null
     */
    public function dismissInsight(int $insightId, User $user): ?BusinessInsight
    {
        $insight = BusinessInsight::query()
            ->where('id', $insightId)
            ->where('user_id', $user->id)
            ->first();

        if (!$insight) {
            return null;
        }

        $insight->dismiss();
        return $insight;
    }

    /**
     * Obtiene estadísticas de insights para un usuario
     *
     * @param User $user
     * @return array
     */
    public function getStats(User $user): array
    {
        $activeInsights = BusinessInsight::query()
            ->forUser($user->id)
            ->active()
            ->get();

        $byType = $activeInsights->groupBy('type')
            ->map(fn($group) => $group->count())
            ->toArray();

        $byPriority = $activeInsights->groupBy('priority')
            ->map(fn($group) => $group->count())
            ->toArray();

        $byTypeAndPriority = $activeInsights->groupBy(function ($insight) {
            return "{$insight->type}|{$insight->priority}";
        })->map(fn($group) => [
            'type' => $group->first()->type,
            'priority' => $group->first()->priority,
            'count' => $group->count(),
        ])->values()->toArray();

        return [
            'total' => $activeInsights->count(),
            'by_type' => $byType,
            'by_priority' => $byPriority,
            'by_type_and_priority' => $byTypeAndPriority,
        ];
    }

    /**
     * Limpia insights antiguos descartados
     *
     * @param User $user
     * @param string|null $organizationId
     * @param int $daysOld
     * @return int Cantidad de insights eliminados
     */
    public function clearOldInsights(
        User $user,
        ?string $organizationId = null,
        int $daysOld = 30
    ): int {
        $query = BusinessInsight::query()
            ->where('user_id', $user->id)
            ->where(function ($q) use ($daysOld) {
                $q->where('is_dismissed', true)
                  ->orWhere('expires_at', '<', now()->subDays($daysOld));
            });

        if ($organizationId) {
            $query->where('organization_id', $organizationId);
        }

        return $query->delete();
    }

    /**
     * Expira insights antiguos no descartados
     *
     * @param User $user
     * @param int $daysOld
     * @return int Cantidad de insights expirados
     */
    public function expireOldInsights(User $user, int $daysOld = 7): int
    {
        return BusinessInsight::query()
            ->where('user_id', $user->id)
            ->whereNull('expires_at')
            ->where('created_at', '<', now()->subDays($daysOld))
            ->update(['expires_at' => now()]);
    }

    /**
     * Agrega un generador personalizado
     *
     * @param string $generatorClass
     * @return void
     */
    public function addGenerator(string $generatorClass): void
    {
        if (!in_array($generatorClass, $this->generators)) {
            $this->generators[] = $generatorClass;
        }
    }
}
