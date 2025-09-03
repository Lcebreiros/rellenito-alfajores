<?php

namespace App\Livewire\Calculator;

use Livewire\Attributes\On;
use Livewire\Component;
use App\Models\Costing;
use App\Models\Product;

class AnalysesPanel extends Component
{
    public ?string $filterProductId = '';
    public array $products = [];
    public array $saved = [];

    // Dashboard
    public int $totalAnalyses = 0;
    public float $totalProduction = 0.0; // suma de yield_units
    public float $totalSpend = 0.0;      // suma de batch_total

    public function mount()
    {
        $this->products = Product::orderBy('name')->get(['id','name'])->toArray();
        $this->loadSaved();
    }

    #[On('analysis-saved')]
    public function onAnalysisSaved()
    {
        $this->loadSaved();
        $this->dispatch('calculator-switch-tab', tab: 'analysis');
    }

    public function updatedFilterProductId()
    {
        $this->loadSaved();
    }

    public function loadSaved(): void
    {
        // Query base respetando filtro
        $base = Costing::query();
        if ($this->filterProductId) {
            $base->where('product_id', $this->filterProductId);
        }

        // Listado (limitado para tarjetas)
        $this->saved = (clone $base)
            ->latest('created_at')
            ->take(60)
            ->get()
            ->toArray();

        // Agregados para dashboard
        $stats = (clone $base)
            ->selectRaw('COUNT(*) as c, COALESCE(SUM(yield_units),0) as prod, COALESCE(SUM(batch_total),0) as spend')
            ->first();

        $this->totalAnalyses = (int)($stats->c ?? 0);
        $this->totalProduction = (float)($stats->prod ?? 0);
        $this->totalSpend = (float)($stats->spend ?? 0);
    }

    public function useSaved(int $id)
    {
        session()->flash('ok', 'Análisis seleccionado.');
        $this->dispatch('calculator-switch-tab', tab: 'product');
    }

    public function delete(int $id): void
    {
        $c = Costing::find($id);
        if (!$c) {
            session()->flash('ok', 'El análisis ya no existe.');
            return;
        }
        $c->delete();

        // (Opcional) avisar al shell para decrementar contador global
        $this->dispatch('costing-deleted', id: $id);

        $this->loadSaved();
        session()->flash('ok', 'Análisis eliminado.');
    }

    public function render()
    {
        return view('livewire.calculator.analyses-panel');
    }
}
