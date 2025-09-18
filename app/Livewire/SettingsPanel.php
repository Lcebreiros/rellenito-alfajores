<?php

// app/Livewire/SettingsPanel.php
namespace App\Livewire;

use Livewire\Component;
use Livewire\WithFileUploads;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use App\Models\Setting; // <-- usamos Setting como store de claves
use Illuminate\Validation\Rule;

class SettingsPanel extends Component
{
    use WithFileUploads;

    public $theme = 'light';
    public $site_title = 'Mi App';
    public ?string $timezone = null;

    public array $timezones = [];

    // Logo del comprobante (per-user)
    public $receipt_logo;            // archivo temporal
    public $receipt_logo_url = null; // URL actual para preview

    public function mount()
    {
        // LEER desde Setting (con fallback)
        $this->theme      = Setting::get('theme', 'light');
        $this->site_title = Setting::get('site_title', 'Mi App');

        $this->refreshReceiptLogoUrl();

              $this->timezones = \DateTimeZone::listIdentifiers(\DateTimeZone::ALL);

        $user = auth()->user();
        $this->timezone = $user?->timezone ?: config('app.timezone', 'UTC');
    }

        public function saveTimezone(): void
    {
        $this->validate([
            'timezone' => ['required', 'string', Rule::in($this->timezones)],
        ], [
            'timezone.in' => 'Seleccioná una zona horaria válida.',
        ]);

        $user = auth()->user();
        $user->timezone = $this->timezone;
        $user->save();

        session()->flash('ok', 'Zona horaria actualizada.');
        $this->dispatch('timezone-updated'); // por si querés escuchar en front
    }

    /**
     * Cuando cambia el theme en el input (x-model / entangle),
     * persistimos en Setting y avisamos al navegador para aplicar sin recargar.
     */
    public function updatedTheme($value)
    {
        Setting::set('theme', $value); // <-- persistir clave
        $this->dispatch('theme-updated', theme: $value); // <-- browser event (Livewire v3)
    }

    public function setTheme($value)
    {
        $this->theme = $value;
        $this->updatedTheme($value);
    }

    /**
     * Guardar otros campos (site_title) en Setting.
     */
    public function save()
    {
        Setting::set('site_title', $this->site_title);

        session()->flash('ok', 'Configuraciones guardadas.');
    }

    // === RECEIPT LOGO (per-user) ===
    public function saveReceiptLogo()
    {
        $this->validate([
            'receipt_logo' => 'required|mimes:png,jpg,jpeg,webp|max:2048',
        ]);

        $user = Auth::user();
        if (!$user) return;

        $dir = "branding/{$user->id}";
        Storage::disk('public')->makeDirectory($dir);

        // guardamos con nombre fijo para el ticket
        $path = $this->receipt_logo->storeAs($dir, 'receipt-logo.png', 'public');

        // persistimos en el usuario (campo users.receipt_logo_path)
        $user->forceFill(['receipt_logo_path' => $path])->save();

        $this->reset('receipt_logo');
        $this->refreshReceiptLogoUrl();

        session()->flash('ok', 'Logo del comprobante actualizado.');
    }

    public function removeReceiptLogo()
    {
        $user = Auth::user();
        if (!$user) return;

        if ($user->receipt_logo_path && Storage::disk('public')->exists($user->receipt_logo_path)) {
            Storage::disk('public')->delete($user->receipt_logo_path);
        }

        $user->forceFill(['receipt_logo_path' => null])->save();

        $this->refreshReceiptLogoUrl();

        session()->flash('ok', 'Logo del comprobante eliminado.');
    }

    protected function refreshReceiptLogoUrl(): void
    {
        $user = Auth::user();
        if (!$user) {
            $this->receipt_logo_url = null;
            return;
        }

        if ($user->receipt_logo_path && Storage::disk('public')->exists($user->receipt_logo_path)) {
            $url = Storage::disk('public')->url($user->receipt_logo_path);
            $v   = Storage::disk('public')->lastModified($user->receipt_logo_path) ?: time();
            $this->receipt_logo_url = "{$url}?v={$v}";
            return;
        }

        // Fallback opcional:
        // $this->receipt_logo_url = asset('images/logo.png');
        $this->receipt_logo_url = null;
    }

    public function render()
    {
        return view('livewire.settings-panel');
    }
}
