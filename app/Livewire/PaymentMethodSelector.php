<?php

namespace App\Livewire;

use App\Models\PaymentMethod;
use Livewire\Component;
use Livewire\Attributes\On;

class PaymentMethodSelector extends Component
{
    public array $selectedPaymentMethods = [];

    public function mount()
    {
        // Inicializar con métodos de pago vacíos
        $this->selectedPaymentMethods = [];
    }

    public function togglePaymentMethod(int $paymentMethodId): void
    {
        if (in_array($paymentMethodId, $this->selectedPaymentMethods)) {
            // Remover si ya está seleccionado
            $this->selectedPaymentMethods = array_values(
                array_filter($this->selectedPaymentMethods, fn($id) => $id !== $paymentMethodId)
            );
        } else {
            // Agregar si no está seleccionado
            $this->selectedPaymentMethods[] = $paymentMethodId;
        }

        // Emitir evento para que OrderSidebar sepa qué métodos están seleccionados
        $this->dispatch('paymentMethodsUpdated', selectedIds: $this->selectedPaymentMethods);
    }

    public function getSelectedPaymentMethodsProperty()
    {
        return $this->selectedPaymentMethods;
    }

    #[On('orderFinalized')]
    public function resetSelection(): void
    {
        $this->selectedPaymentMethods = [];
    }

    public function render()
    {
        // Solo mostrar métodos disponibles para el usuario:
        // - Globales activados en payment-methods.index (pivot user_payment_methods.is_active = true)
        // - + Métodos propios del usuario activos
        $paymentMethods = PaymentMethod::query()
            ->availableForUser(auth()->user())
            ->ordered()
            ->get();

        return view('livewire.payment-method-selector', [
            'paymentMethods' => $paymentMethods,
        ]);
    }
}
