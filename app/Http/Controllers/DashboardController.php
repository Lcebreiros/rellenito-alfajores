<?php

namespace App\Http\Controllers;

use Inertia\Inertia;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\DB;
use App\Models\DashboardWidget;
use ReflectionClass;

class DashboardController extends Controller
{
    /**
     * GET /dashboard (Inertia)
     */
    public function index()
    {
        $userId = Auth::id();

        // Widgets del usuario -> formato que consume React (w/h en lugar de width/height)
        $widgets = DashboardWidget::query()
            ->where('user_id', $userId)
            ->where('is_visible', true)
            ->orderBy('y')
            ->orderBy('x')
            ->orderBy('position')
            ->get(['id', 'widget_type', 'x', 'y', 'width', 'height'])
            ->map(function (DashboardWidget $w) {
                return [
                    'id'          => (int) $w->id,
                    'widget_type' => $w->widget_type,       // slug p.e. 'orders-total'
                    'x'           => (int) ($w->x ?? 0),
                    'y'           => (int) ($w->y ?? 0),
                    'w'           => (int) ($w->width  ?: 6),
                    'h'           => (int) ($w->height ?: 3),
                ];
            })
            ->values();

        // Descubre componentes Livewire disponibles bajo App\Livewire\Dashboard
        $available = $this->discoverDashboardWidgets();

        return Inertia::render('Dashboard', [
            'widgets'          => $widgets,
            'availableWidgets' => $available,
        ]);
    }

    /**
     * POST /dashboard/layout
     * Persiste x,y,w,h del layout (drag/resize) para el usuario actual.
     */
    public function saveLayout(Request $request)
    {
        $userId = Auth::id();

        $data = $request->validate([
            'layout'        => ['required', 'array'],
            'layout.*.id'   => ['required', 'integer'],
            'layout.*.x'    => ['required', 'integer', 'min:0'],
            'layout.*.y'    => ['required', 'integer', 'min:0'],
            'layout.*.w'    => ['required', 'integer', 'min:1', 'max:12'],
            'layout.*.h'    => ['required', 'integer', 'min:1', 'max:12'],
        ]);

        $rows = collect($data['layout'])
            ->map(fn ($i) => [
                'id' => (int) $i['id'],
                'x'  => (int) $i['x'],
                'y'  => (int) $i['y'],
                'w'  => (int) $i['w'],
                'h'  => (int) $i['h'],
            ]);

        // Solo actualizar widgets que sean del usuario
        $ids = $rows->pluck('id')->all();

        DB::transaction(function () use ($rows, $ids, $userId) {
            $owned = DashboardWidget::query()
                ->where('user_id', $userId)
                ->whereIn('id', $ids)
                ->pluck('id')
                ->all();

            $ownedSet = array_flip($owned);

            foreach ($rows as $r) {
                if (!isset($ownedSet[$r['id']])) {
                    continue;
                }

                DashboardWidget::where('user_id', $userId)
                    ->where('id', $r['id'])
                    ->update([
                        'x'      => $r['x'],
                        'y'      => $r['y'],
                        'width'  => $r['w'],
                        'height' => $r['h'],
                    ]);
            }
        });

        // Para Inertia, una 204 funciona perfecto (no hace redirect)
        return response()->noContent();
    }

    /**
     * Escanea App\Livewire\Dashboard\* y devuelve:
     *  [slug => ['alias'=>'dashboard.slug','class'=>FQCN,'name'=>Readable]]
     */
    private function discoverDashboardWidgets(): array
    {
        $basePath = app_path('Livewire/Dashboard');
        $baseNs   = 'App\\Livewire\\Dashboard';

        if (! File::isDirectory($basePath)) {
            return [];
        }

        $map = [];
        $rii = new \RecursiveIteratorIterator(
            new \RecursiveDirectoryIterator($basePath, \FilesystemIterator::SKIP_DOTS)
        );

        foreach ($rii as $file) {
            /** @var \SplFileInfo $file */
            if ($file->getExtension() !== 'php') {
                continue;
            }

            // FQCN desde el path
            $relPath = Str::after($file->getPathname(), app_path() . DIRECTORY_SEPARATOR);
            $class   = 'App\\' . str_replace(
                ['/', '\\\\'],
                ['\\', '\\'],
                Str::replaceLast('.php', '', $relPath)
            );

            // Bajo App\Livewire\Dashboard
            if (! Str::startsWith($class, $baseNs.'\\')) {
                continue;
            }

            if (! class_exists($class)) {
                require_once $file->getPathname();
                if (! class_exists($class)) {
                    continue;
                }
            }

            try {
                $ref = new ReflectionClass($class);
                if ($ref->isAbstract()) continue;
                if (! $ref->isSubclassOf(\Livewire\Component::class)) continue;
            } catch (\Throwable $e) {
                continue;
            }

            $short = $ref->getShortName();      // p.e. OrdersTotal
            $slug  = Str::kebab($short);        // orders-total
            $map[$slug] = [
                'alias' => 'dashboard.' . $slug, // dashboard.orders-total
                'class' => $class,
                'name'  => Str::headline($slug), // Orders Total
            ];
        }

        ksort($map);
        return $map;
    }
}
