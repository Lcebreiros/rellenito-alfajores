<?php

namespace App\Livewire;

use Livewire\Component;
use Livewire\Attributes\On;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;

class DashboardActions extends Component
{
    public bool $editMode = false;
    public array $available = [];

    public function mount(): void
    {
        $this->available = $this->loadAvailable();
    }

    #[On('dashboard:editModeChanged')]
    public function syncEditMode(bool $editMode): void
    {
        $this->editMode = $editMode;
    }

    private function loadAvailable(): array
    {
        $dir = app_path('Livewire/Dashboard');

        if (!is_dir($dir)) return [];

        $files = collect(File::files($dir))
            ->filter(fn($f) => $f->getFilenameWithoutExtension() !== 'Dashboard')
            ->values();

        if (is_dir($dir.'/Widgets')) {
            $files = $files->merge(File::files($dir.'/Widgets'));
        }

        $out = [];
        foreach ($files as $f) {
            $class = $f->getFilenameWithoutExtension();
            $fqcn  = str_contains($f->getPath(), 'Widgets')
                ? "App\\Livewire\\Dashboard\\Widgets\\{$class}"
                : "App\\Livewire\\Dashboard\\{$class}";

            $key   = Str::kebab($class);
            $label = Str::headline($class);

            if (class_exists($fqcn)) {
                $out[$key] = [
                    'component' => $fqcn,
                    'label'     => $label,
                ];
            }
        }

        return $out;
    }

    public function toggleEdit(): void
    {
        $this->editMode = ! $this->editMode;
        $this->dispatch('dashboard:toggleEdit', editMode: $this->editMode);
    }

    public function addWidget(string $key): void
    {
        $this->dispatch('dashboard:addWidget', key: $key);
    }

    public function render()
    {
        return view('livewire.dashboard-actions');
    }
}
