<?php

namespace App\Livewire;

use Livewire\Component;
use Illuminate\Support\Facades\Auth;
use App\Models\Product;
use App\Models\PaymentMethod;
use App\Models\Order;
use App\Models\StockAdjustment;
use App\Models\SupplierExpense;

class WelcomeModal extends Component
{
    public bool $showWizard = false;

    protected $listeners = ['openOnboardingWizard' => 'open'];

    public function mount(): void
    {
        $user = Auth::user();
        $this->showWizard = $user && !$user->has_seen_welcome;

        if (request()->boolean('guide')) {
            $this->showWizard = true;
        }
    }

    public function getStepsProperty(): array
    {
        return [
            [
                'key'         => 'products',
                'title'       => 'Creá tus productos',
                'description' => 'Agregá tu catálogo con precios, unidades y costos.',
                'route'       => route('products.create'),
                'done'        => Product::exists(),
            ],
            [
                'key'         => 'payment_methods',
                'title'       => 'Configurá métodos de pago',
                'description' => 'Habilitá efectivo, tarjeta u otros medios.',
                'route'       => route('payment-methods.index'),
                'done'        => PaymentMethod::exists(),
            ],
            [
                'key'         => 'first_sale',
                'title'       => 'Realizá tu primera venta',
                'description' => 'Creá un pedido y registrá tu primera transacción.',
                'route'       => route('orders.create'),
                'done'        => Order::exists(),
            ],
            [
                'key'         => 'expenses',
                'title'       => 'Registrá un gasto',
                'description' => 'Llevá el control de tus gastos y proveedores.',
                'route'       => route('expenses.index'),
                'done'        => SupplierExpense::exists(),
            ],
            [
                'key'         => 'stock',
                'title'       => 'Revisá tu inventario',
                'description' => 'Controlá el stock, alertas y movimientos.',
                'route'       => route('stock.index'),
                'done'        => StockAdjustment::exists(),
            ],
        ];
    }

    public function dismiss(): void
    {
        $this->showWizard = false;
    }

    public function disableForever(): void
    {
        if ($user = Auth::user()) {
            $user->forceFill(['has_seen_welcome' => true])->save();
        }
        $this->showWizard = false;
    }

    public function open(): void
    {
        $this->showWizard = true;
    }

    public function render()
    {
        return view('livewire.welcome-modal', [
            'steps' => $this->steps,
        ]);
    }
}
