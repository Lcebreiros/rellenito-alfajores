<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\BusinessInsight;
use App\Services\Insights\InsightService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\Rule;

class InsightController extends Controller
{
    protected InsightService $insightService;

    public function __construct(InsightService $insightService)
    {
        $this->insightService = $insightService;
    }

    /**
     * Obtener insights activos
     *
     * GET /api/insights
     *
     * Query params:
     * - type: filtrar por tipo
     * - priority: filtrar por prioridad
     * - limit: limitar resultados (default: 10)
     */
    public function index(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'type' => ['nullable', Rule::in([
                BusinessInsight::TYPE_STOCK_ALERT,
                BusinessInsight::TYPE_REVENUE_OPPORTUNITY,
                BusinessInsight::TYPE_COST_WARNING,
                BusinessInsight::TYPE_TREND,
                BusinessInsight::TYPE_CLIENT_RETENTION,
                BusinessInsight::TYPE_PREDICTION,
                BusinessInsight::TYPE_REMINDER,
            ])],
            'priority' => ['nullable', Rule::in([
                BusinessInsight::PRIORITY_CRITICAL,
                BusinessInsight::PRIORITY_HIGH,
                BusinessInsight::PRIORITY_MEDIUM,
                BusinessInsight::PRIORITY_LOW,
            ])],
            'limit' => ['nullable', 'integer', 'min:1', 'max:50'],
        ]);

        try {
            $insights = $this->insightService->getInsights(
                $request->user(),
                $validated
            );

            return response()->json([
                'success' => true,
                'data' => $insights->map(function ($insight) {
                    return [
                        'id' => $insight->id,
                        'type' => $insight->type,
                        'priority' => $insight->priority,
                        'title' => $insight->title,
                        'description' => $insight->description,
                        'metadata' => $insight->metadata,
                        'action_label' => $insight->action_label,
                        'action_route' => $insight->action_route,
                        'is_dismissed' => $insight->is_dismissed,
                        'created_at' => $insight->created_at->toISOString(),
                        'expires_at' => $insight->expires_at?->toISOString(),
                        'priority_color' => $insight->getPriorityColor(),
                        'type_icon' => $insight->getTypeIcon(),
                    ];
                }),
            ]);
        } catch (\Exception $e) {
            Log::error('Error getting insights: ' . $e->getMessage(), [
                'user_id' => $request->user()->id,
                'exception' => $e,
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Error al obtener insights',
            ], 500);
        }
    }

    /**
     * Generar nuevos insights
     *
     * POST /api/insights/generate
     *
     * Body:
     * - clear_existing: eliminar insights previos (default: false)
     */
    public function generate(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'clear_existing' => ['nullable', 'boolean'],
        ]);

        try {
            $insights = $this->insightService->generateInsights(
                $request->user(),
                null,
                $validated['clear_existing'] ?? false
            );

            return response()->json([
                'success' => true,
                'message' => 'Insights generados exitosamente',
                'data' => [
                    'count' => $insights->count(),
                    'insights' => $insights->map(function ($insight) {
                        return [
                            'id' => $insight->id,
                            'type' => $insight->type,
                            'priority' => $insight->priority,
                            'title' => $insight->title,
                            'description' => $insight->description,
                            'metadata' => $insight->metadata,
                            'action_label' => $insight->action_label,
                            'action_route' => $insight->action_route,
                            'created_at' => $insight->created_at->toISOString(),
                            'expires_at' => $insight->expires_at?->toISOString(),
                            'priority_color' => $insight->getPriorityColor(),
                            'type_icon' => $insight->getTypeIcon(),
                        ];
                    }),
                ],
            ], 201);
        } catch (\Exception $e) {
            Log::error('Error generating insights: ' . $e->getMessage(), [
                'user_id' => $request->user()->id,
                'exception' => $e,
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Error al generar insights',
            ], 500);
        }
    }

    /**
     * Marcar insight como descartado
     *
     * PATCH /api/insights/{id}/dismiss
     */
    public function dismiss(Request $request, int $id): JsonResponse
    {
        try {
            $insight = $this->insightService->dismissInsight($id, $request->user());

            if (!$insight) {
                return response()->json([
                    'success' => false,
                    'message' => 'Insight no encontrado',
                ], 404);
            }

            return response()->json([
                'success' => true,
                'message' => 'Insight descartado exitosamente',
                'data' => [
                    'id' => $insight->id,
                    'is_dismissed' => $insight->is_dismissed,
                ],
            ]);
        } catch (\Exception $e) {
            Log::error('Error dismissing insight: ' . $e->getMessage(), [
                'user_id' => $request->user()->id,
                'insight_id' => $id,
                'exception' => $e,
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Error al descartar insight',
            ], 500);
        }
    }

    /**
     * Obtener estadísticas de insights
     *
     * GET /api/insights/stats
     */
    public function stats(Request $request): JsonResponse
    {
        try {
            $stats = $this->insightService->getStats($request->user());

            return response()->json([
                'success' => true,
                'data' => $stats,
            ]);
        } catch (\Exception $e) {
            Log::error('Error getting insight stats: ' . $e->getMessage(), [
                'user_id' => $request->user()->id,
                'exception' => $e,
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Error al obtener estadísticas',
            ], 500);
        }
    }
}
