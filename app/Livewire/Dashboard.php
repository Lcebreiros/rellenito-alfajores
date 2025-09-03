<?php
// app/Livewire/Dashboard.php

namespace App\Livewire;

use Livewire\Component;
use App\Models\DashboardWidget;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;
use Livewire\Attributes\On;

class Dashboard extends Component
{
    /** @var array<int, array> */
    public $widgets = [];

    /** @var array<string, array{name:string,description:string,size:string,component:string}> */
    public $availableWidgets = [];

    public $editMode = false;

    public function mount(): void
    {
        $this->discoverWidgets();
        $this->loadUserWidgets();

        $this->dispatch('dashboard-edit-toggled', ['editMode' => $this->editMode]);
    }

    protected function discoverWidgets(): void
    {
        $widgetPath = app_path('Livewire/Dashboard');

        if (!File::exists($widgetPath)) {
            File::makeDirectory($widgetPath, 0755, true);
            return;
        }

        foreach (File::files($widgetPath) as $file) {
            $className = 'App\\Livewire\\Dashboard\\' . $file->getFilenameWithoutExtension();

            if (class_exists($className)) {
                $instance  = app($className);
                $widgetKey = Str::kebab($file->getFilenameWithoutExtension());

                $this->availableWidgets[$widgetKey] = [
                    'name'        => $instance->title       ?? Str::title(str_replace('-', ' ', $widgetKey)),
                    'description' => $instance->description ?? '',
                    'size'        => $instance->size        ?? 'medium',
                    'component'   => $className,
                ];
            }
        }
    }

    protected function loadUserWidgets(): void
    {
        $this->widgets = DashboardWidget::query()
            ->where('user_id', Auth::id())
            ->where('is_visible', true)
            ->orderBy('position')
            ->get()
            ->map(function (DashboardWidget $widget) {
                $config = $this->availableWidgets[$widget->widget_type] ?? null;
                if (!$config) return null;

                return [
                    'id'        => $widget->id,
                    'type'      => $widget->widget_type,
                    'name'      => $config['name'],
                    'size'      => $config['size'],
                    'position'  => $widget->position,
                    'component' => $config['component'],
                    'settings'  => $widget->settings ?? [],
                ];
            })
            ->filter()
            ->values()
            ->toArray();
    }

    /**
     * Eventos externos (cliente → servidor)
     * Usa Livewire.dispatch(...) o Livewire.dispatchTo('dashboard', ...)
     */

    #[On('dash.reorder')]
    public function updateWidgetPositions($widgetPositions): void
    {
        foreach ($widgetPositions as $index => $widgetId) {
            DashboardWidget::query()
                ->where('id', $widgetId)
                ->where('user_id', Auth::id())
                ->update(['position' => $index]);
        }

        $this->loadUserWidgets();
        $this->dispatch('positions-updated'); // browser event (opcional)
    }

    #[On('dash.addWidget')]
    public function addWidget(string $widgetType): void
    {
        if (!isset($this->availableWidgets[$widgetType])) {
            session()->flash('error', 'Widget no válido.');
            return;
        }

        $exists = DashboardWidget::query()
            ->where('user_id', Auth::id())
            ->where('widget_type', $widgetType)
            ->exists();

        if ($exists) {
            session()->flash('error', 'Este widget ya está añadido a tu dashboard.');
            return;
        }

        $maxPosition = DashboardWidget::where('user_id', Auth::id())->max('position') ?? -1;

        DashboardWidget::create([
            'user_id'     => Auth::id(),
            'widget_type' => $widgetType,
            'position'    => $maxPosition + 1,
            'is_visible'  => true,
            'settings'    => [],
        ]);

        $this->loadUserWidgets();
        session()->flash('success', 'Widget añadido correctamente.');
    }

    #[On('dash.removeWidget')]
    public function removeWidget(int $widgetId): void
    {
        DashboardWidget::query()
            ->where('id', $widgetId)
            ->where('user_id', Auth::id())
            ->delete();

        $this->loadUserWidgets();
        session()->flash('success', 'Widget eliminado correctamente.');
    }

    #[On('dash.toggleEdit')]
    public function toggleEditMode(): void
    {
        $this->editMode = !$this->editMode;
        $this->dispatch('dashboard-edit-toggled', editMode: $this->editMode);
    }

    public function getWidgetSizeClass($size): string
    {
        return match ($size) {
            'small'  => 'col-span-12 md:col-span-6 lg:col-span-4',
            'medium' => 'col-span-12 md:col-span-6',
            'large'  => 'col-span-12 lg:col-span-8',
            'wide'   => 'col-span-12',
            default  => 'col-span-12 md:col-span-6',
        };
    }

    public function getResponsiveWidgetClass($size): string
    {
        // Si vas con grid 3×3 estricto, mantenlo 1×1
        return 'col-span-1';
    }

    public function render()
    {
        return view('livewire.dashboard');
    }
}
