<?php

namespace App\Livewire;

use Livewire\Attributes\On;
use Livewire\Component;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\File;   // 👈 importa File
use Illuminate\Support\Str;            // 👈 importa Str
use App\Models\DashboardLayout;

class Dashboard extends Component
{
    /** @var array<int, array{id:string, key:string}> */
    public array $layout = [];

    /** @var array<string, array{component:string,label:string,size?:string}> */
    public array $available = [];

    public bool $editMode = false;

    private function loadAvailable(): array
    {
        $dir = app_path('Livewire/Dashboard');

        if (!is_dir($dir)) return [];

        $files = collect(File::files($dir))
            ->filter(fn($f) => $f->getFilenameWithoutExtension() !== 'Dashboard') // evita el manager si está ahí
            ->values();

        // Si tienes subcarpeta Widgets, inclúyela:
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

    public function mount(): void
    {
        // 👇 usa autodescubrimiento (y si no encuentra nada, cae a config)
        $this->available = $this->loadAvailable();
        if (empty($this->available)) {
            $this->available = config('dashboard.widgets', []);
        }

        $user  = Auth::user();
        $saved = DashboardLayout::firstOrCreate(['user_id' => $user->id]);
        $this->layout = $saved->layout ?: [];

        // Layout inicial por defecto (usa keys detectadas)
        if (empty($this->layout) && !empty($this->available)) {
            $keys = array_keys($this->available);
            foreach (array_slice($keys, 0, 3) as $k) {
                $this->layout[] = ['id' => uniqid('w_'), 'key' => $k];
            }
            $this->persist();
        }
    }

    public function toggleEdit(): void
    {
        $this->editMode = ! $this->editMode;
    }

    public function addWidget(string $key): void
    {
        if (! isset($this->available[$key])) return;
        $this->layout[] = ['id' => uniqid('w_'), 'key' => $key];
        $this->persist();
    }

    public function removeWidget(string $id): void
    {
        $this->layout = array_values(array_filter($this->layout, fn($w) => $w['id'] !== $id));
        $this->persist();
    }

    #[On('dashboard-reorder')]
    public function reorder(array $orderedIds): void
    {
        $map = collect($this->layout)->keyBy('id');
        $this->layout = collect($orderedIds)
            ->map(fn($id) => $map->get($id))
            ->filter()
            ->values()
            ->all();
        $this->persist();
    }

    protected function persist(): void
    {
        DashboardLayout::updateOrCreate(
            ['user_id' => Auth::id()],
            ['layout'  => $this->layout]
        );
    }

    public function render()
    {
        return view('livewire.dashboard');
    }
}
