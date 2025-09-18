<?php

namespace App\Livewire\Calculator;

use Livewire\Component;
use Livewire\WithPagination;
use App\Models\Supply;
use App\Models\SupplyPurchase;

class SuppliesManager extends Component
{
    use WithPagination;

    // -------- Alta rápida (compra) --------
    public string $name = '';
    public float  $qty = 0;
    public string $unit = 'g';
    public float  $total_cost = 0;

    // -------- Edición unificada (Nombre + precio total de última compra) --------
    public ?int   $editingId = null;           // supply id en edición
    public string $e_name = '';                // nuevo nombre
    public ?int   $editingPurchaseId = null;   // id de la última compra (si existe)
    public float  $ep_total_cost = 0;          // precio total editable

    // -------- Filtro --------
    public string $search = '';

    protected $rules = [
        'name'       => 'required|string|min:2',
        'qty'        => 'required|numeric|min:0',
        'unit'       => 'required|string|in:g,kg,ml,l,cm3,u',
        'total_cost' => 'required|numeric|min:0',
    ];

    // -------- Helpers de conversión --------
    protected function toBaseUnit(string $unit): string
    {
        return match ($unit) {
            'kg' => 'g',
            'l', 'cm3' => 'ml',
            default => $unit, // g, ml, u
        };
    }

    protected function factorToBase(string $unit): float
    {
        return match ($unit) {
            'kg' => 1000, // kg -> g
            'l'  => 1000, // l -> ml
            'cm3'=> 1,    // cm3 -> ml
            'g','ml','u' => 1,
            default => 1,
        };
    }

    // -------- Listeners UI --------
    public function updatedSearch() { $this->resetPage(); }

    // -------- Alta rápida: crea compra y recalcula --------
    public function quickStore()
    {
        $this->validate();

        $baseUnit = $this->toBaseUnit($this->unit);
        $factor   = $this->factorToBase($this->unit);

        // 1) Insumo por nombre + base_unit
        $supply = Supply::firstOrCreate(
            ['name' => $this->name, 'base_unit' => $baseUnit],
            ['stock_base_qty' => 0, 'avg_cost_per_base' => 0]
        );

        // 2) Registrar compra
        SupplyPurchase::create([
            'supply_id'    => $supply->id,
            'qty'          => (float)$this->qty,
            'unit'         => $this->unit,
            'unit_to_base' => $factor,
            'total_cost'   => (float)$this->total_cost,
        ]);

        // 3) Recalcular stock y promedio desde compras
        $supply->recomputeFromPurchases();

        // Limpiar form
        $this->reset(['name','qty','unit','total_cost']);
        $this->dispatch('supply-created', id: $supply->id);
        session()->flash('ok', 'Compra registrada y stock actualizado.');
        $this->resetPage();
    }

    // -------- Edición unificada --------
    public function startEditBoth(int $supplyId): void
    {
        // Traer insumo con su última compra (si existe)
        $s = Supply::with(['purchases' => fn($q) => $q->latest()->limit(1)])->findOrFail($supplyId);

        $this->editingId = $s->id;
        $this->e_name    = $s->name;

        $last = $s->purchases->first();
        if ($last) {
            $this->editingPurchaseId = $last->id;
            $this->ep_total_cost     = (float)$last->total_cost;
        } else {
            $this->editingPurchaseId = null;
            $this->ep_total_cost     = 0;
        }
    }

    public function cancelEditBoth(): void
    {
        $this->editingId = null;
        $this->editingPurchaseId = null;
        $this->reset(['e_name','ep_total_cost']);
    }

    public function saveBoth(): void
    {
        if (!$this->editingId) return;

        // Validar nombre
        $this->validate([
            'e_name' => 'required|string|min:2',
        ]);

        $s = Supply::findOrFail($this->editingId);
        $s->update(['name' => $this->e_name]);

        // Si hay última compra editable, validar y actualizar solo el precio total
        if ($this->editingPurchaseId) {
            $this->validate([
                'ep_total_cost' => 'required|numeric|min:0',
            ]);

            $p = SupplyPurchase::with('supply')->findOrFail($this->editingPurchaseId);
            $p->total_cost = (float)$this->ep_total_cost;
            $p->save();

            // Recalcular el insumo desde TODAS las compras
            $p->supply->recomputeFromPurchases();
            $this->dispatch('purchase-updated', id: $p->id);
        }

        $this->dispatch('supply-updated', id: $s->id);
        $this->cancelEditBoth();
        session()->flash('ok', 'Insumo actualizado.');
    }

    // -------- Borrado INSUMO --------
    public function delete(int $id)
    {
        $s = Supply::findOrFail($id);
        $s->delete();
        $this->dispatch('supply-deleted', id: $id);
        session()->flash('ok', 'Insumo eliminado.');
        $this->resetPage();
    }

    // -------- Computado --------
    public function getCountProperty()
    {
        return Supply::query()
            ->when($this->search, fn($q)=>$q->where('name','like',"%{$this->search}%"))
            ->count();
    }

    // -------- Render --------
    public function render()
    {
        $supplies = Supply::query()
            ->when($this->search, fn($q)=>$q->where('name','like',"%{$this->search}%"))
            ->with(['purchases' => fn($q) => $q->latest()->limit(1)]) // última compra por insumo
            ->orderBy('name')
            ->paginate(10);

        return view('livewire.calculator.supplies-manager', compact('supplies'));
    }
}
