<?php

namespace App\Livewire\Orders;

use Livewire\Component;
use App\Models\Order;
use App\Models\Setting;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;

class Ticket extends Component
{
    public Order $order;
    public ?string $logoUrl = null;
    public string $appName;

    private const APP_LOGO_PATH = 'branding/app-logo.png';

    public function mount(Order $order)
    {
        $this->order = $order->loadMissing(['items.product']);

        // 1) Logo del comprobante configurado en SettingsPanel
        $receiptPath = Setting::get('receipt_logo_path', 'branding/receipt-logo.png');
        if ($receiptPath && Storage::disk('public')->exists($receiptPath)) {
            $v = Storage::disk('public')->lastModified($receiptPath);
            $this->logoUrl = route('branding.receipt-logo', ['v' => $v]); // ✅ ruta que streamea
        }
        // 2) App logo como fallback
        elseif (Storage::disk('public')->exists(self::APP_LOGO_PATH)) {
            $v = Storage::disk('public')->lastModified(self::APP_LOGO_PATH);
            $this->logoUrl = route('branding.app-logo', ['v' => $v]); // ✅ ruta que streamea
        }
        // 3) Foto de perfil persistida (si quisieras streamearla también, podemos crear otra ruta)
        else {
            $user = Auth::user();
            $path = $user?->profile_photo_path; // e.g. profile-photos/xxxx.jpg
            if ($path && Storage::disk('public')->exists($path)) {
                // Si querés máxima robustez, podés crear una ruta que streamee $path del usuario.
                $this->logoUrl = Storage::disk('public')->url($path) . '?v=' . Storage::disk('public')->lastModified($path);
            } else {
                // Último recurso (puede ser ui-avatars, por eso no lo consideramos "persistente")
                $this->logoUrl = $user?->profile_photo_url;
            }
        }

        $this->appName = Setting::get('site_title', config('app.name', 'Rellenito'));
    }

    public function getSubtotalProperty(): float
    {
        return round((float)$this->order->items->sum(function ($i) {
            return !is_null($i->subtotal)
                ? (float)$i->subtotal
                : (float)$i->quantity * (float)$i->unit_price;
        }), 2);
    }

    public function getTaxProperty(): float
    {
        $taxRate = (float)($this->order->tax_rate ?? 0);
        return round($this->subtotal * ($taxRate / 100), 2);
    }

    public function getDiscountProperty(): float
    {
        return round((float)($this->order->discount ?? 0), 2);
    }

    public function getTotalProperty(): float
    {
        return !is_null($this->order->total)
            ? (float)$this->order->total
            : round($this->subtotal + $this->tax - $this->discount, 2);
    }

public function render()
{
    $view = view('livewire.orders.ticket');

    // Si viene embed=1 => usamos el layout "en blanco"
    if (request()->boolean('embed')) {
        return $view->layout('components.blank'); // ✅ evita el error
    }

    // Normal: con tu layout de aplicación
    return $view->layout('layouts.app');
}


}
