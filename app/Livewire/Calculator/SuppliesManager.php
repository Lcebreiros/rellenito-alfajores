<?php

namespace App\Livewire\Calculator;

use Livewire\Attributes\On;
use Livewire\Component;
use Livewire\WithPagination;
use App\Models\Supply; // ajusta al nombre real de tu modelo

class SuppliesManager extends Component
{
    use WithPagination;

    // Alta rápida
    public string $name = '';
    public float $qty = 0;
    public string $unit = 'g';
    public float $total_cost = 0;

    // Edición inline (por fila)
    public ?int $editingId = null;
    public string $e_name = '';
    public float $e_stock = 0;
    public float $e_price = 0;

    public string $search = '';

    protected $rules = [
        'name'       => 'required|string|min:2',
        'qty'        => 'required|numeric|min:0',
        'unit'       => 'required|string|in:g,kg,ml,l,cm3,u',
        'total_cost' => 'required|numeric|min:0',
    ];

    public function updatedSearch() { $this->resetPage(); }

    public function quickStore()
{
    $this->validate();

    // Base del supply según la unidad elegida del pack
    $baseUnit = match ($this->unit) {
        'kg' => 'g',
        'l', 'cm3' => 'ml',
        default => $this->unit, // g, ml, u
    };

    // Factor hacia la unidad base del supply
    $factor = match ($this->unit) {
        'kg' => 1000, // kg -> g
        'l'  => 1000, // l -> ml
        'cm3'=> 1,    // cm3 -> ml
        'g','ml','u' => 1,
        default => 1,
    };

    // 1) buscar/crear insumo por nombre + base_unit
    $supply = Supply::firstOrCreate(
        ['name' => $this->name, 'base_unit' => $baseUnit],
        ['stock_base_qty' => 0, 'avg_cost_per_base' => 0]
    );

    // 2) registrar la compra con tu schema (qty, unit, unit_to_base, total_cost)
    \App\Models\SupplyPurchase::create([
        'supply_id'    => $supply->id,
        'qty'          => (float)$this->qty,         // ej: 10
        'unit'         => $this->unit,               // ej: 'kg'
        'unit_to_base' => $factor,                   // ej: 1000 (kg -> g)
        'total_cost'   => (float)$this->total_cost,  // ej: 35000
    ]);

    // 3) recalcular stock y promedio
    $supply->recomputeFromPurchases();

    // limpiar form
    $this->reset(['name','qty','unit','total_cost']);
    $this->dispatch('supply-created', id: $supply->id);
    session()->flash('ok', 'Compra registrada y stock actualizado.');
    $this->resetPage();
}


    public function startEdit(int $id)
    {
        $s = Supply::findOrFail($id);
        $this->editingId = $id;
        $this->e_name  = $s->name;
        $this->e_stock = (float)($s->stock_base_qty ?? 0);
        $this->e_price = (float)($s->avg_cost_per_base ?? 0);
    }

    public function cancelEdit()
    {
        $this->editingId = null;
        $this->reset(['e_name','e_stock','e_price']);
    }

    public function saveEdit()
    {
        if (!$this->editingId) return;
        $s = Supply::findOrFail($this->editingId);

        $s->update([
            'name'              => $this->e_name,
            'stock_base_qty'    => (float)$this->e_stock,
            'avg_cost_per_base' => (float)$this->e_price,
        ]);

        $this->dispatch('supply-updated', id: $s->id);
        $this->cancelEdit();
        session()->flash('ok', 'Insumo actualizado.');
    }

    public function delete(int $id)
    {
        $s = Supply::findOrFail($id);
        $s->delete();
        $this->dispatch('supply-deleted', id: $id);
        session()->flash('ok', 'Insumo eliminado.');
        $this->resetPage();
    }

    public function getCountProperty()
    {
        return Supply::query()
            ->when($this->search, fn($q)=>$q->where('name','like',"%{$this->search}%"))
            ->count();
    }

    public function render()
    {
        $supplies = Supply::query()
            ->when($this->search, fn($q)=>$q->where('name','like',"%{$this->search}%"))
            ->orderBy('name')
            ->paginate(10);

        return view('livewire.calculator.supplies-manager', compact('supplies'));
    }
}
