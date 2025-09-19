<?php

namespace App\Livewire\Profile;

use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Livewire\Component;
use Livewire\WithFileUploads;

class AppLogoForm extends Component
{
    use WithFileUploads;

    public $logo; // archivo temporal (UploadedFile)
    public $currentLogoUrl;

    protected $rules = [
        'logo' => 'required|mimes:png,jpg,jpeg,webp|max:2048',
    ];

    public function mount()
    {
        $this->refreshLogo();
    }

    public function save()
    {
        $this->validate();

        $user = Auth::user();
        if (!$user) {
            $this->dispatch('notify', ['type' => 'error', 'message' => 'Usuario no autenticado.']);
            return;
        }

        $dir = "branding/{$user->id}";
        Storage::disk('public')->makeDirectory($dir);

        $filename = "app-logo-{$user->id}.png";
        $path = $this->logo->storeAs($dir, $filename, 'public');

        try {
            Storage::disk('public')->setVisibility($path, 'public');
        } catch (\Throwable $e) {
            // noop
        }

        $this->refreshLogo();
        $this->reset('logo');

        $this->dispatch('notify', ['type' => 'success', 'message' => 'Logo actualizado correctamente.']);
    }

    public function remove()
    {
        $user = Auth::user();
        if (!$user) {
            $this->dispatch('notify', ['type' => 'error', 'message' => 'Usuario no autenticado.']);
            return;
        }

        $file = "branding/{$user->id}/app-logo-{$user->id}.png";
        if (Storage::disk('public')->exists($file)) {
            Storage::disk('public')->delete($file);
        }

        $this->refreshLogo();

        $this->dispatch('notify', ['type' => 'success', 'message' => 'Logo eliminado.']);
    }

    protected function refreshLogo()
    {
        $user = Auth::user();
        if (!$user) {
            $this->currentLogoUrl = null;
            return;
        }

        $file = "branding/{$user->id}/app-logo-{$user->id}.png";
        if (Storage::disk('public')->exists($file)) {
            $url = Storage::disk('public')->url($file);
            $v   = Storage::disk('public')->lastModified($file) ?: time();
            $this->currentLogoUrl = "{$url}?v={$v}";
        } else {
            $this->currentLogoUrl = null;
        }
    }

    public function render()
    {
        return view('livewire.profile.app-logo-form');
    }
}
