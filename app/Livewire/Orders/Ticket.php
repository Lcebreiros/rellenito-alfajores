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

        // 1) Logo del comprobante PER-USUARIO (no compartido)
        $user = Auth::user();
        $userPath = $user?->receipt_logo_path; // e.g. branding/{user_id}/receipt-logo-{user_id}.png
        if ($userPath && Storage::disk('public')->exists($userPath)) {
            $v = Storage::disk('public')->lastModified($userPath);
            $this->logoUrl = route('user.receipt-logo', ['v' => $v]); // streamea solo el del usuario autenticado
        }
        // 2) Fallback: Gestior.png de raíz (no usar logo de otro usuario)
        else {
            $this->logoUrl = route('branding.default-receipt');
        }
        // 3) Foto de perfil (opcional) — desactivado para evitar confusiones con logos de otros usuarios
        // (Mantener simple: solo per-user o fallback global)

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
