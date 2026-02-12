<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\DashboardWidget;
use App\Support\Dashboard\WidgetRegistry;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class DashboardWidgetController extends Controller
{
    /**
     * Devuelve los widgets del usuario (layout) y el catálogo disponible.
     */
    public function index(Request $request): JsonResponse
    {
        $userId = $request->user()->id;

        $widgets = DashboardWidget::query()
            ->where('user_id', $userId)
            ->where('is_visible', true)
            ->orderBy('y')
            ->orderBy('x')
            ->orderBy('position')
            ->get(['id', 'widget_type', 'x', 'y', 'width', 'height', 'position', 'is_visible', 'settings'])
            ->map(function (DashboardWidget $widget) {
                return [
                    'id' => (int) $widget->id,
                    'widget_type' => $widget->widget_type,
                    'x' => (int) ($widget->x ?? 0),
                    'y' => (int) ($widget->y ?? 0),
                    'w' => (int) ($widget->width ?: 6),
                    'h' => (int) ($widget->height ?: 3),
                    'position' => (int) ($widget->position ?? 0),
                    'is_visible' => (bool) $widget->is_visible,
                    'settings' => $widget->settings ?? [],
                ];
            })
            ->values();

        return response()->json([
            'success' => true,
            'data' => [
                'widgets' => $widgets,
                'available_widgets' => $this->formatAvailableWidgets(),
            ],
        ]);
    }

    /**
     * Catálogo de widgets sin layout (para autocompletar).
     */
    public function available(): JsonResponse
    {
        return response()->json([
            'success' => true,
            'data' => $this->formatAvailableWidgets(),
        ]);
    }

    /**
     * Normaliza el catálogo para consumo API.
     */
    private function formatAvailableWidgets(): array
    {
        return WidgetRegistry::getAvailableWidgets()
            ->map(function (array $widget) {
                $config = $widget['config'] ?? [];

                return [
                    'id' => $widget['id'] ?? null,
                    'name' => $widget['name'] ?? null,
                    'description' => $widget['description'] ?? null,
                    'category' => $widget['category'] ?? 'General',
                    'alias' => $widget['alias'] ?? null,
                    'config' => [
                        'min_size' => $config['min_size'] ?? $config['min'] ?? 1,
                        'default_size' => $config['default_size'] ?? $config['def'] ?? 6,
                        'max_size' => $config['max_size'] ?? $config['max'] ?? 12,
                        'default_rows' => $config['default_rows'] ?? $config['rows'] ?? 1,
                        'max_rows' => $config['max_rows'] ?? $config['rows'] ?? 4,
                    ],
                ];
            })
            ->values()
            ->all();
    }
}
