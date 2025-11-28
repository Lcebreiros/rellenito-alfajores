<?php

// app/Livewire/SettingsPanel.php
namespace App\Livewire;

use Livewire\Component;
use Livewire\WithFileUploads;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use App\Models\Setting;
use Illuminate\Validation\Rule;

class SettingsPanel extends Component
{
    use WithFileUploads;

    public $theme = 'light';
    public $site_title = 'Mi App';
    public ?string $timezone = null;
    public $custom_color = '#6366f1'; // Indigo por defecto
    public bool $custom_theme_dark = false; // Modo oscuro para tema personalizado

    public array $timezones = [];
    public array $availableThemes = [];

    // Logo del comprobante (per-user)
    public $receipt_logo;            // archivo temporal (Livewire)
    public $receipt_logo_url = null; // URL actual para preview

    // Configuraciones de notificaciones de stock
    public bool $notify_low_stock = true;
    public int $low_stock_threshold = 5;
    public bool $notify_out_of_stock = true;

    public function mount()
    {
        $this->theme      = Setting::get('theme', 'light');
        $this->site_title = Setting::get('site_title', 'Mi App');

        $this->timezones = \DateTimeZone::listIdentifiers(\DateTimeZone::ALL);

        $user = auth()->user();
        $this->timezone = $user?->timezone ?: config('app.timezone', 'UTC');

        // Cargar configuraciones de notificaciones de stock
        $this->notify_low_stock = $user?->notify_low_stock ?? true;
        $this->low_stock_threshold = $user?->low_stock_threshold ?? 5;
        $this->notify_out_of_stock = $user?->notify_out_of_stock ?? true;

        // Cargar color personalizado
        $this->custom_color = Setting::get('custom_color', '#6366f1');
        // Convertir explícitamente a booleano
        $darkValue = Setting::get('custom_theme_dark', 'false');
        $this->custom_theme_dark = filter_var($darkValue, FILTER_VALIDATE_BOOLEAN);

        // Temas disponibles simplificados
        $this->availableThemes = [
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

        $this->refreshReceiptLogoUrl();
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
        $this->dispatch('timezone-updated');
    }

    public function updatedTheme($value)
    {
        Setting::set('theme', $value);
        $this->dispatch('theme-updated', theme: $value);
    }

    public function setTheme($value)
    {
        $this->theme = $value;
        $this->updatedTheme($value);
    }

    public function saveCustomColor()
    {
        $this->validate([
            'custom_color' => ['required', 'regex:/^#[A-Fa-f0-9]{6}$/'],
        ], [
            'custom_color.regex' => 'El color debe ser un código hexadecimal válido de 6 dígitos (ej: #6366f1)',
        ]);

        Setting::set('custom_color', $this->custom_color);
        $this->dispatch('custom-color-updated', color: $this->custom_color);
        session()->flash('ok', 'Color personalizado guardado.');
    }

    public function toggleCustomThemeMode()
    {
        $this->custom_theme_dark = !$this->custom_theme_dark;
        // Guardar como string para evitar problemas de conversión
        Setting::set('custom_theme_dark', $this->custom_theme_dark ? 'true' : 'false');

        // No hay que recargar el componente, solo confirmar el cambio
        $this->skipRender();
    }

    public function save()
    {
        Setting::set('site_title', $this->site_title);

        session()->flash('ok', 'Configuraciones guardadas.');
    }

    // === RECEIPT LOGO (per-user) ===
    public function saveReceiptLogo()
    {
        $this->validate([
            'receipt_logo' => 'required|image|mimes:png,jpg,jpeg,webp|max:2048',
        ]);

        $user = Auth::user();
        if (!$user) return;

        $dir = "branding/{$user->id}";

        // Nombre único por usuario (evita confusiones / colisiones)
        $filename = "receipt-logo-{$user->id}.png";

        // Crear directorio si hace falta (disk public)
        Storage::disk('public')->makeDirectory($dir);

        // Guardar con visibilidad pública
        $path = $this->receipt_logo->storeAs($dir, $filename, 'public');

        // Forzar visibilidad pública (en caso de driver que lo soporte)
        if (Storage::disk('public')->exists($path)) {
            try {
                Storage::disk('public')->setVisibility($path, 'public');
            } catch (\Throwable $e) {
                // algunos drivers no implementan setVisibility, no bloqueamos
            }
        }

        // Persistimos en el usuario
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
            $v   = Storage::disk('public')->lastModified($user->receipt_logo_path) ?: time();
            // Servir SIEMPRE vía ruta autenticada para evitar filtrado entre usuarios
            $this->receipt_logo_url = route('user.receipt-logo', ['v' => $v]);
            return;
        }

        $this->receipt_logo_url = null;
    }

    // === STOCK NOTIFICATIONS ===
    public function saveStockNotifications()
    {
        $this->validate([
            'notify_low_stock' => 'boolean',
            'low_stock_threshold' => 'required|integer|min:1|max:1000',
            'notify_out_of_stock' => 'boolean',
        ], [
            'low_stock_threshold.required' => 'El umbral de stock bajo es obligatorio.',
            'low_stock_threshold.min' => 'El umbral debe ser al menos 1.',
            'low_stock_threshold.max' => 'El umbral no puede exceder 1000.',
        ]);

        $user = Auth::user();
        if (!$user) return;

        $user->update([
            'notify_low_stock' => $this->notify_low_stock,
            'low_stock_threshold' => $this->low_stock_threshold,
            'notify_out_of_stock' => $this->notify_out_of_stock,
        ]);

        session()->flash('ok', 'Configuración de notificaciones de stock guardada.');
        $this->dispatch('stock-notifications-updated');
    }

    public function render()
    {
        return view('livewire.settings-panel');
    }
}
