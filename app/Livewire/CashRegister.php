<?php

namespace App\Livewire;

use App\Models\CashMovement;
use App\Models\CashSession;
use App\Models\Costing;
use App\Models\OrderItem;
use Livewire\Component;

class CashRegister extends Component
{
    public ?CashSession $session = null;

    // Para abrir caja
    public string $openingAmount = '';

    // Para movimiento manual
    public string $movementType   = 'ingreso';
    public string $movementAmount = '';
    public string $movementDesc   = '';

    // Para cerrar caja
    public string $closingAmount = '';
    public string $closingNote   = '';

    // UI state
    public bool $compact          = false;
    public bool $showOpenForm     = false;
    public bool $showMovementForm = false;
    public bool $showCloseForm    = false;
    public bool $showCloseModal   = false;

    protected function rules(): array
    {
        return [
            'openingAmount'  => 'required|numeric|min:0',
            'movementType'   => 'required|in:ingreso,egreso',
            'movementAmount' => 'required|numeric|min:0.01',
            'movementDesc'   => 'required|string|max:255',
            'closingAmount'  => 'required|numeric|min:0',
        ];
    }

    public function mount(): void
    {
        $this->session = CashSession::activeFor(auth()->id());
        if ($this->session) {
            $this->closingAmount = (string) $this->session->currentBalance();
        }
    }

    /** Escucha el evento que emite OrderSidebar al finalizar una venta */
    #[\Livewire\Attributes\On('order-finalized')]
    public function onOrderFinalized(int $orderId, float $total): void
    {
        if (!$this->session?->isOpen()) return;

        CashMovement::create([
            'cash_session_id' => $this->session->id,
            'company_id'      => $this->session->company_id,
            'order_id'        => $orderId,
            'created_by'      => auth()->id(),
            'type'            => 'sale',
            'amount'          => $total,
            'description'     => __('cash.sale_movement', ['order' => $orderId]),
        ]);

        $this->session->refresh();
        $this->closingAmount = (string) $this->session->currentBalance();
    }

    public function openSession(): void
    {
        $this->validateOnly('openingAmount');

        if (CashSession::activeFor(auth()->id())) {
            $this->addError('openingAmount', __('cash.already_open'));
            return;
        }

        $user      = auth()->user();
        $companyId = $this->resolveCompanyId($user);

        $session = CashSession::create([
            'user_id'        => $user->id,
            'company_id'     => $companyId,
            'opening_amount' => (float) $this->openingAmount,
            'status'         => 'open',
            'opened_at'      => now(),
        ]);

        if ((float) $this->openingAmount > 0) {
            CashMovement::create([
                'cash_session_id' => $session->id,
                'company_id'      => $companyId,
                'created_by'      => $user->id,
                'type'            => 'apertura',
                'amount'          => (float) $this->openingAmount,
                'description'     => __('cash.opening_movement'),
            ]);
        }

        $this->session      = $session;
        $this->openingAmount = '';
        $this->showOpenForm = false;
        $this->closingAmount = (string) $session->currentBalance();
    }

    public function addMovement(): void
    {
        $this->validateOnly('movementType');
        $this->validateOnly('movementAmount');
        $this->validateOnly('movementDesc');

        if (!$this->session?->isOpen()) return;

        CashMovement::create([
            'cash_session_id' => $this->session->id,
            'company_id'      => $this->session->company_id,
            'created_by'      => auth()->id(),
            'type'            => $this->movementType,
            'amount'          => (float) $this->movementAmount,
            'description'     => $this->movementDesc,
        ]);

        $this->session->refresh();
        $this->movementAmount  = '';
        $this->movementDesc    = '';
        $this->showMovementForm = false;
        $this->closingAmount   = (string) $this->session->currentBalance();
    }

    public function openCloseModal(): void
    {
        if (!$this->session?->isOpen()) return;
        $this->closingAmount    = (string) $this->session->currentBalance();
        $this->showCloseModal   = true;
        $this->showCloseForm    = false;
        $this->showMovementForm = false;
    }

    public function closeSession(): void
    {
        $this->validateOnly('closingAmount');

        if (!$this->session?->isOpen()) return;

        $this->session->update([
            'status'         => 'closed',
            'closing_amount' => (float) $this->closingAmount,
            'closing_note'   => $this->closingNote ?: null,
            'closed_at'      => now(),
        ]);

        $this->session        = null;
        $this->showCloseForm  = false;
        $this->showCloseModal = false;
        $this->closingAmount  = '';
        $this->closingNote    = '';
    }

    public function render()
    {
        $movements = $this->session
            ? $this->session->movements()->with('creator:id,name')->latest()->take(10)->get()
            : collect();

        $user = auth()->user();

        $closeStats = ($this->showCloseModal && $this->session?->isOpen())
            ? $this->computeCloseStats()
            : null;

        return view('livewire.cash-register', [
            'movements'    => $movements,
            'balance'      => $this->session?->currentBalance() ?? 0,
            'operatorName' => $user->parent_id ? $user->name : null,
            'closeStats'   => $closeStats,
        ]);
    }

    private function computeCloseStats(): array
    {
        $session = $this->session;

        $allMovements = CashMovement::where('cash_session_id', $session->id)->get();
        $orderIds     = $allMovements->where('type', 'sale')->whereNotNull('order_id')->pluck('order_id');
        $totalSold    = (float) $allMovements->where('type', 'sale')->sum('amount');
        $ingresos     = (float) $allMovements->where('type', 'ingreso')->sum('amount');
        $egresos      = (float) $allMovements->where('type', 'egreso')->sum('amount');
        $expectedCash = (float) $session->opening_amount + $totalSold + $ingresos - $egresos;

        $totalCost   = 0.0;
        $hasCostData = false;

        if ($orderIds->isNotEmpty()) {
            $items = OrderItem::whereIn('order_id', $orderIds)
                ->with(['product:id,cost_price'])
                ->get();

            // Latest costing per product (same priority as FinanceController)
            $productIds     = $items->pluck('product_id')->filter()->unique()->values();
            $latestCostings = Costing::whereIn('product_id', $productIds)
                ->orderByDesc('created_at')
                ->get()
                ->unique('product_id')
                ->keyBy('product_id');

            foreach ($items as $item) {
                $costing  = $latestCostings->get($item->product_id);
                $unitCost = $costing
                    ? (float) $costing->unit_total
                    : (float) ($item->product?->cost_price ?? 0);

                if ($unitCost > 0) {
                    $hasCostData = true;
                    $totalCost  += $unitCost * (float) $item->quantity;
                }
            }
        }

        return [
            'salesCount'   => $orderIds->count(),
            'totalSold'    => $totalSold,
            'totalCost'    => $totalCost,
            'profit'       => $totalSold - $totalCost,
            'hasCostData'  => $hasCostData,
            'ingresos'     => $ingresos,
            'egresos'      => $egresos,
            'opening'      => (float) $session->opening_amount,
            'expectedCash' => $expectedCash,
        ];
    }

    private function resolveCompanyId(\App\Models\User $user): int
    {
        if ($user->isCompany()) return $user->id;
        if ($user->parent_id) {
            $parent = \App\Models\User::find($user->parent_id);
            if ($parent?->isCompany()) return $parent->id;
            if ($parent?->parent_id) {
                $grandparent = \App\Models\User::find($parent->parent_id);
                if ($grandparent?->isCompany()) return $grandparent->id;
            }
        }
        return $user->id;
    }
}
