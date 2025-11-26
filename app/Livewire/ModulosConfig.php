<?php

namespace App\Livewire;

use Livewire\Component;
use App\Models\User;

class ModulosConfig extends Component
{
    public $modulosActivos = [];
    public $availableModules = [];

    public function mount()
    {
        $this->availableModules = User::availableModules();
        $this->modulosActivos = auth()->user()->getActiveModules();
    }

    public function toggleModulo($modulo)
    {
        if (in_array($modulo, $this->modulosActivos)) {
            $this->modulosActivos = array_values(array_diff($this->modulosActivos, [$modulo]));
        } else {
            $this->modulosActivos[] = $modulo;
        }
    }

    public function guardar()
    {
        auth()->user()->setActiveModules($this->modulosActivos);

        session()->flash('modulos-saved', 'Módulos actualizados correctamente');

        // Recargar la página para que el sidebar se actualice
        return redirect()->route('settings');
    }

    public function render()
    {
        return view('livewire.modulos-config');
    }
}
