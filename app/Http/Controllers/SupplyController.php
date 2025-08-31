<?php

namespace App\Http\Controllers;

use App\Models\Supply;
use App\Models\SupplyPurchase;
use App\Services\UnitConverter;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class SupplyController extends Controller
{
    // Si alguien entra a /supplies (GET), lo mandamos a la calculadora
    public function index(Request $r) {
        return redirect()->route('calculator.show', $r->query());
    }

    // Igual para /supplies/create (GET)
    public function create(Request $r) {
        return redirect()->route('calculator.show', $r->query());
    }

    public function store(Request $r) {
        $data = $r->validate([
            'name'      => ['required','string','max:120'],
            'base_unit' => ['required', Rule::in(['g','ml','u'])],
        ]);

        Supply::create($data);

        // Volvemos SIEMPRE a la calculadora con el flash
        return redirect()->route('calculator.show', $r->query())
            ->with('ok','Insumo creado.');
    }

    public function storePurchase(Request $r, Supply $supply) {
        $data = $r->validate([
            'qty'        => ['required','numeric','gt:0'],
            'unit'       => ['required','string','max:10'],
            'total_cost' => ['required','numeric','gt:0'],
        ]);

        $factor  = UnitConverter::factorToBase($data['unit'], $supply->base_unit);
        $baseQty = $data['qty'] * $factor;

        SupplyPurchase::create([
            'supply_id'    => $supply->id,
            'qty'          => $data['qty'],
            'unit'         => $data['unit'],
            'unit_to_base' => $factor,
            'total_cost'   => $data['total_cost'],
        ]);

        // promedio ponderado
        $oldStock = (float) $supply->stock_base_qty;
        $oldAvg   = (float) $supply->avg_cost_per_base;
        $newStock = $oldStock + $baseQty;
        $newAvg   = $newStock > 0 ? (($oldStock * $oldAvg) + $data['total_cost']) / $newStock : 0;

        $supply->update([
            'stock_base_qty'    => $newStock,
            'avg_cost_per_base' => $newAvg,
        ]);

        // Volver a la calculadora (con la query que hubiera)
        return redirect()->route('calculator.show', $r->query())
            ->with('ok','Compra cargada y costos actualizados.');
    }

  
public function quickStore(Request $request)
{
    $data = $request->validate([
        'name' => ['required', 'string', 'max:255'],
        'qty' => ['required', 'numeric', 'gt:0'],
        'unit' => ['required', 'string', 'in:g,kg,ml,l,cm3,u'],
        'total_cost' => ['required', 'numeric', 'gt:0']
    ]);

    try {
        // Convertir a unidad base
        $factorToBase = $this->getConversionFactor($data['unit']);
        $baseUnit = $this->getBaseUnit($data['unit']);
        $baseQty = $data['qty'] * $factorToBase;
        $costPerBase = $data['total_cost'] / $baseQty;

        $supply = Supply::create([
            'name' => $data['name'],
            'base_unit' => $baseUnit,
            'stock_base_qty' => $baseQty,
            'avg_cost_per_base' => $costPerBase
        ]);

        return redirect()->back()->with('ok', 'Insumo registrado correctamente: ' . $supply->name);
        
    } catch (\Exception $e) {
        return redirect()->back()->withErrors(['error' => 'Error al crear insumo: ' . $e->getMessage()]);
    }
}

public function update(Request $request, Supply $supply)
{
    $data = $request->validate([
        'name' => ['required', 'string', 'max:255'],
        'avg_cost_per_base' => ['required', 'numeric', 'min:0'],
        'stock_base_qty' => ['required', 'numeric', 'min:0']
    ]);

    try {
        $supply->update($data);

        return response()->json([
            'ok' => true,
            'message' => 'Insumo actualizado correctamente',
            'supply' => [
                'id' => $supply->id,
                'name' => $supply->name,
                'base_unit' => $supply->base_unit,
                'stock_base_qty' => (float) $supply->stock_base_qty,
                'avg_cost_per_base' => (float) $supply->avg_cost_per_base,
            ]
        ]);
        
    } catch (\Exception $e) {
        return response()->json([
            'ok' => false,
            'message' => 'Error al actualizar insumo: ' . $e->getMessage()
        ], 500);
    }
}

public function destroy(Supply $supply)
{
    try {
        $name = $supply->name;
        $supply->delete();

        return response()->json([
            'ok' => true,
            'message' => "Insumo '{$name}' eliminado correctamente"
        ]);
        
    } catch (\Exception $e) {
        return response()->json([
            'ok' => false,
            'message' => 'Error al eliminar insumo: ' . $e->getMessage()
        ], 500);
    }
}

private function getConversionFactor($unit)
{
    $factors = [
        'g' => 1, 'kg' => 1000,
        'ml' => 1, 'l' => 1000, 'cm3' => 1,
        'u' => 1
    ];
    
    return $factors[$unit] ?? 1;
}

private function getBaseUnit($unit)
{
    $baseUnits = [
        'g' => 'g', 'kg' => 'g',
        'ml' => 'ml', 'l' => 'ml', 'cm3' => 'ml',
        'u' => 'u'
    ];
    
    return $baseUnits[$unit] ?? $unit;
}

}
