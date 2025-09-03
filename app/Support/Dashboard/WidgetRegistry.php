<?php

namespace App\Support\Dashboard;

use Illuminate\Support\Str;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Collection;
use Livewire\Component;
use ReflectionClass;
use ReflectionException;

class WidgetRegistry
{
    private static ?Collection $cachedWidgets = null;
    
    /**
     * Obtiene todos los widgets disponibles
     */
    public static function getAvailableWidgets(): Collection
    {
        if (self::$cachedWidgets !== null) {
            return self::$cachedWidgets;
        }

        return self::$cachedWidgets = self::discoverWidgets();
    }

    /**
     * Descubre automáticamente widgets en app/Livewire/Dashboard
     */
    private static function discoverWidgets(): Collection
    {
        $dashboardPath = app_path('Livewire/Dashboard');
        $defaultConfig = config('dashboard.defaults', [
            'min_size' => 2,
            'default_size' => 6,
            'max_size' => 12,
            'default_rows' => 1,
            'max_rows' => 4,
        ]);

        if (!File::isDirectory($dashboardPath)) {
            return collect();
        }

        $widgets = collect();
        
        foreach (File::files($dashboardPath) as $file) {
            if ($file->getExtension() !== 'php') {
                continue;
            }

            $className = $file->getFilenameWithoutExtension();
            
            // Evitar archivos que no deberían ser widgets
            if (self::shouldSkipFile($className)) {
                continue;
            }

            $widget = self::createWidgetDefinition($className, $defaultConfig);
            
            if ($widget) {
                $widgets->push($widget);
            }
        }

        return $widgets->sortBy('name');
    }

    /**
     * Determina si un archivo debe ser omitido
     */
    private static function shouldSkipFile(string $className): bool
    {
        $skipPatterns = [
            '/^(Dashboard|Grid|Base|Abstract)/i',
            '/Test$/i',
            '/Trait$/i',
            '/Interface$/i',
        ];

        foreach ($skipPatterns as $pattern) {
            if (preg_match($pattern, $className)) {
                return true;
            }
        }

        return false;
    }

    /**
     * Crea la definición de un widget
     */
    private static function createWidgetDefinition(string $className, array $defaultConfig): ?array
    {
        $fullyQualifiedClassName = "App\\Livewire\\Dashboard\\{$className}";
        
        try {
            if (!class_exists($fullyQualifiedClassName)) {
                return null;
            }

            $reflection = new ReflectionClass($fullyQualifiedClassName);
            
            if ($reflection->isAbstract() || !$reflection->isSubclassOf(Component::class)) {
                return null;
            }

            $id = Str::kebab($className);
            $widgetConfig = config("dashboard.widgets.{$id}", []);
            
            return [
                'id' => $id,
                'name' => $widgetConfig['name'] ?? Str::headline($className),
                'description' => $widgetConfig['description'] ?? "Widget personalizado {$className}",
                'category' => $widgetConfig['category'] ?? 'General',
                'alias' => "dashboard.{$id}",
                'class' => $fullyQualifiedClassName,
                'config' => array_merge($defaultConfig, $widgetConfig),
            ];
            
        } catch (ReflectionException $e) {
            report($e);
            return null;
        }
    }

    /**
     * Obtiene un widget específico por ID
     */
    public static function getWidget(string $id): ?array
    {
        return self::getAvailableWidgets()->firstWhere('id', $id);
    }

    /**
     * Valida si un widget existe y es válido
     */
    public static function isValidWidget(string $id): bool
    {
        return self::getWidget($id) !== null;
    }

    /**
     * Limpia el cache de widgets
     */
    public static function clearCache(): void
    {
        self::$cachedWidgets = null;
    }
}