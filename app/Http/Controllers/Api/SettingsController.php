<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Setting;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class SettingsController extends Controller
{
    /**
     * Devuelve los ajustes principales de la cuenta para la app móvil.
     */
    public function show(Request $request): JsonResponse
    {
        $user = $request->user();

        $theme = [
            'current' => Setting::get('theme', 'light'),
            'custom_color' => Setting::get('custom_color', '#6366f1'),
            'custom_theme_dark' => filter_var(Setting::get('custom_theme_dark', false), FILTER_VALIDATE_BOOLEAN),
            'available' => $this->availableThemes(),
        ];

        $googleConnected = (bool) ($user->google_access_token && $user->google_refresh_token);

        return response()->json([
            'success' => true,
            'data' => [
                'branding' => [
                    'site_title' => Setting::get('site_title', config('app.name', 'Gestior')),
                    'app_logo_url' => $user->app_logo_url,
                    'receipt_logo_url' => $user->receipt_logo_url,
                ],
                'theme' => $theme,
                'timezone' => $user->timezone ?? config('app.timezone', 'UTC'),
                'notifications' => [
                    'stock' => [
                        'notify_low_stock' => (bool) $user->notify_low_stock,
                        'low_stock_threshold' => (int) ($user->low_stock_threshold ?? 5),
                        'notify_out_of_stock' => (bool) $user->notify_out_of_stock,
                    ],
                ],
                'google_calendar' => [
                    'connected' => $googleConnected,
                    'email' => $user->google_email,
                    'sync_enabled' => (bool) ($user->google_calendar_sync_enabled ?? false),
                ],
                'modules' => $user->getActiveModules(),
            ],
        ]);
    }

    private function availableThemes(): array
    {
        return [
            [
                'id' => 'light',
                'name' => 'Claro',
                'description' => 'Fondo blanco con acentos suaves',
            ],
            [
                'id' => 'dark',
                'name' => 'Oscuro',
                'description' => 'Fondo oscuro profesional',
            ],
            [
                'id' => 'neon',
                'name' => 'Neón',
                'description' => 'Colores vibrantes fluorescentes',
                'badge' => 'BETA',
            ],
            [
                'id' => 'custom',
                'name' => 'Personalizado',
                'description' => 'Elige tu color favorito',
            ],
        ];
    }
}
