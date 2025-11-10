<?php

namespace App\Livewire;

use App\Models\Service;
use App\Models\ServiceSupply;
use App\Models\Supply;
use Livewire\Component;

class ServiceSuppliesManager extends Component
{
    public Service $service;
    public $serviceSupplies = [];
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

    public function mount(Service $service)
    {
        $this->service = $service;
        $this->loadServiceSupplies();
        $this->loadSupplies();
    }

    public function loadServiceSupplies()
    {
        $this->serviceSupplies = ServiceSupply::where('service_id', $this->service->id)
            ->with('supply')
            ->get()
            ->map(function($ss) {
                $arr = $ss->toArray();
                // Agregar el accessor formatted_stock al array
                if (isset($arr['supply']) && $ss->supply) {
                    $arr['supply']['formatted_stock'] = $ss->supply->formatted_stock;
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
        $exists = ServiceSupply::where('service_id', $this->service->id)
            ->where('supply_id', $this->supply_id)
            ->exists();

        if ($exists) {
            $this->addError('supply_id', 'Este insumo ya está asignado a este servicio.');
            return;
        }

        ServiceSupply::create([
            'service_id' => $this->service->id,
            'supply_id' => $this->supply_id,
            'qty' => $this->qty,
            'unit' => $this->unit,
            'waste_pct' => $this->waste_pct ?? 0,
        ]);

        $this->reset(['supply_id', 'qty', 'unit', 'waste_pct']);
        $this->unit = 'g';
        $this->loadServiceSupplies();

        session()->flash('success', 'Insumo agregado exitosamente');
    }

    public function removeSupply($serviceSupplyId)
    {
        ServiceSupply::find($serviceSupplyId)?->delete();
        $this->loadServiceSupplies();

        session()->flash('success', 'Insumo eliminado');
    }

    public function render()
    {
        return view('livewire.service-supplies-manager');
    }
}
