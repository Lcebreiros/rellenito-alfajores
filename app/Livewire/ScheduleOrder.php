<?php

namespace App\Livewire;

use Livewire\Component;
use App\Models\Order;
use App\Models\Client;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use DomainException;
use Carbon\Carbon;

class ScheduleOrder extends Component
{
    public ?int $orderId = null;
    public bool $enabled = false;
    public string $datetime = '';

    public function mount(?int $orderId = null): void
    {
        $this->orderId = $orderId ?? (int) session('draft_order_id');
        $this->ensureDraftExists();

        $order = Order::find($this->orderId);
        $this->enabled = (bool) ($order?->is_scheduled ?? false);
        $this->datetime = $order?->scheduled_for?->format('Y-m-d\TH:i') ?? now()->addDay()->format('Y-m-d\TH:i');
    }

    #[\Livewire\Attributes\On('draft-changed')]
    public function onDraftChanged(int $id): void
    {
        $this->orderId = $id;
    }

    #[\Livewire\Attributes\On('item-added-to-order')]
    public function onItemAddedToOrder(int $orderId): void
    {
        if ($this->orderId !== $orderId) {
            session(['draft_order_id' => $orderId]);
            $this->orderId = $orderId;
        }
    }

    

    private function ensureDraftExists(): void
    {
        $sid = (int) session('draft_order_id');
        if ($sid && Order::find($sid)) { $this->orderId = $sid; return; }

        $user = Auth::user();
        if (!$user) throw new DomainException('No hay usuario autenticado.');

        $branchId = $user->isAdmin() || $user->isCompany() ? $user->id : ($user->parent_id ?: $user->id);
        $companyId = Order::findRootCompanyId($user) ?? $user->id;

        $draft = Order::create([
            'user_id' => $user->id,
            'branch_id' => $branchId,
            'company_id' => $companyId,
            'status' => \App\Enums\OrderStatus::DRAFT->value,
            'payment_status' => \App\Enums\PaymentStatus::PENDING->value,
            'payment_method' => \App\Enums\PaymentMethod::CASH->value,
            'discount' => 0,
            'tax_amount' => 0,
        ]);

        session(['draft_order_id' => $draft->id]);
        $this->orderId = (int) $draft->id;
    }

    // No auto-agendar: el usuario confirma con botón
    public function updatedEnabled($value): void
    {
        $enabled = (bool) $value;
        try {
            DB::transaction(function () use ($enabled) {
                $order = Order::lockForUpdate()->findOrFail($this->orderId);
                if ($order->status !== \App\Enums\OrderStatus::DRAFT->value) return;
                $order->is_scheduled = $enabled;
                if (!$enabled) {
                    $order->scheduled_for = null;
                }
                $order->save();
            });
        } catch (\Throwable $e) {
            $this->dispatch('notify', type: 'error', message: 'No se pudo actualizar el agendado.');
        }
    }

    public function updatedDatetime($value): void
    {
        if (!$this->enabled) return;
        try {
            $dt = Carbon::createFromFormat('Y-m-d\TH:i', (string) $value);
        } catch (\Throwable $e) {
            $this->dispatch('notify', type: 'error', message: 'Fecha/hora inválida.');
            return;
        }
        if ($dt->lessThanOrEqualTo(now())) {
            $this->dispatch('notify', type: 'error', message: 'La fecha debe ser futura.');
            return;
        }

        try {
            DB::transaction(function () use ($dt) {
                $order = Order::lockForUpdate()->findOrFail($this->orderId);
                if ($order->status !== \App\Enums\OrderStatus::DRAFT->value) return;
                if (!$order->is_scheduled) return;
                $order->scheduled_for = $dt;
                $order->save();
            });
        } catch (\Throwable $e) {
            $this->dispatch('notify', type: 'error', message: 'No se pudo guardar la fecha.');
        }
    }

    public function schedule(): void
    {
        DB::transaction(function () {
            $order = Order::with(['items'])->lockForUpdate()->findOrFail($this->orderId);
            if ($order->status !== \App\Enums\OrderStatus::DRAFT->value) {
                throw new DomainException('El pedido ya fue procesado.');
            }
            if ($order->items->isEmpty()) {
                throw new DomainException('Agrega productos antes de agendar.');
            }

            try {
                $dt = Carbon::createFromFormat('Y-m-d\TH:i', $this->datetime);
            } catch (\Throwable $e) {
                throw new DomainException('Fecha/hora inválida.');
            }
            if (!$dt || $dt->lessThanOrEqualTo(now())) {
                throw new DomainException('La fecha/hora debe ser futura.');
            }

            $order->is_scheduled = true;
            $order->scheduled_for = $dt;
            $order->status = \App\Enums\OrderStatus::SCHEDULED->value;
            $order->recalcTotal(true);
            $order->save();

            $finishedId = (int) $order->id;

            // Notificar éxito con enlace al pedido
            $url = route('orders.show', ['order' => $finishedId]);
            $this->dispatch('notify', type: 'success', message: "Pedido #$finishedId agendado para " . $dt->format('d/m/Y H:i') . ". <a href=\"$url\" class=\"underline\">Ver</a>");

            // Resetear draft y anunciar nuevo borrador
            session()->forget('draft_order_id');
            $this->ensureDraftExists();
            $this->dispatch('draft-changed', id: $this->orderId);

            // Reset UI
            $this->enabled = false;
            $this->datetime = now()->addDay()->format('Y-m-d\TH:i');
        });
    }

    public function render()
    {
        return view('livewire.schedule-order');
    }
}
