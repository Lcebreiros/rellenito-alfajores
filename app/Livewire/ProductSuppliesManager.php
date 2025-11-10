<?php

namespace App\Livewire;

use App\Models\Product;
use App\Models\ProductRecipe;
use App\Models\Supply;
use Livewire\Component;

class ProductSuppliesManager extends Component
{
    public Product $product;
    public $recipes = [];
    public $supplies = [];

    // Formulario de nuevo insumo
    public $supply_id = '';
    public $qty = '';
    public $unit = 'g';
    public $waste_pct = 0;

    protected $rules = [
        'supply_id' => 'required|exists:supplies,id',
        'qty' => 'required|numeric|min:0.001',
        'unit' => 'required|in:g,kg,ml,l,cm3,u',
        'waste_pct' => 'nullable|numeric|min:0|max:100',
    ];

    public function mount(Product $product)
    {
        $this->product = $product;
        $this->loadRecipes();
        $this->loadSupplies();
    }

    public function loadRecipes()
    {
        $this->recipes = ProductRecipe::where('product_id', $this->product->id)
            ->with('supply')
            ->get()
            ->map(function($recipe) {
                $arr = $recipe->toArray();
                // Agregar el accessor formatted_stock al array
                if (isset($arr['supply']) && $recipe->supply) {
                    $arr['supply']['formatted_stock'] = $recipe->supply->formatted_stock;
                }
                return $arr;
            })
            ->toArray();
    }

    public function loadSupplies()
    {
        $user = auth()->user();
        $this->supplies = Supply::availableFor($user)
            ->orderBy('name')
            ->get()
            ->map(function($supply) {
                $arr = $supply->toArray();
                $arr['formatted_stock'] = $supply->formatted_stock;
                return $arr;
            })
            ->toArray();
    }

    public function addSupply()
    {
        $this->validate();

        // Verificar que no esté duplicado
        $exists = ProductRecipe::where('product_id', $this->product->id)
            ->where('supply_id', $this->supply_id)
            ->exists();

        if ($exists) {
            $this->addError('supply_id', 'Este insumo ya está asignado a este producto.');
            return;
        }

        ProductRecipe::create([
            'product_id' => $this->product->id,
            'supply_id' => $this->supply_id,
            'qty' => $this->qty,
            'unit' => $this->unit,
            'waste_pct' => $this->waste_pct ?? 0,
        ]);

        $this->reset(['supply_id', 'qty', 'unit', 'waste_pct']);
        $this->unit = 'g';
        $this->loadRecipes();

        session()->flash('success', 'Insumo agregado exitosamente');
    }

    public function removeSupply($recipeId)
    {
        ProductRecipe::find($recipeId)?->delete();
        $this->loadRecipes();

        session()->flash('success', 'Insumo eliminado');
    }

    public function render()
    {
        return view('livewire.product-supplies-manager');
    }
}
