<?php

namespace App\Livewire\Profile;

use Illuminate\Support\Facades\Storage;
use Livewire\Component;
use Livewire\WithFileUploads;

class AppLogoForm extends Component
{
    use WithFileUploads;

    public $logo; // archivo temporal
    public $currentLogoUrl;

    protected $rules = [
        // Permitimos PNG/JPG/JPEG/WEBP (SVG opcional, ver nota abajo)
        'logo' => 'required|mimes:png,jpg,jpeg,webp|max:2048',
    ];

    public function mount()
    {
        $this->refreshLogo();
    }

    public function save()
    {
        $this->validate();

        // Carpeta donde guardaremos el logo
        $dir = 'branding';
        Storage::disk('public')->makeDirectory($dir);

        // Guardamos con nombre fijo para que el layout lo encuentre
        $path = $this->logo->storeAs($dir, 'app-logo.png', 'public');

        $this->dispatch('notify', body: 'Logo actualizado correctamente.');
        $this->reset('logo');
        $this->refreshLogo();
    }

    public function remove()
    {
        $file = 'branding/app-logo.png';
        if (Storage::disk('public')->exists($file)) {
            Storage::disk('public')->delete($file);
        }
        $this->dispatch('notify', body: 'Logo eliminado.');
        $this->refreshLogo();
    }

    protected function refreshLogo()
    {
        $file = 'branding/app-logo.png';
        $this->currentLogoUrl = Storage::disk('public')->exists($file)
            ? Storage::url($file) . '?v=' . time() // cache-bust simple
            : null;
    }

    public function render()
    {
        return view('livewire.profile.app-logo-form');
    }
}
