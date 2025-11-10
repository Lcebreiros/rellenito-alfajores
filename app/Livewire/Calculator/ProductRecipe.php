<?php

namespace App\Livewire\Calculator;

use App\Models\Product;
use App\Models\Supply;
use App\Models\ProductRecipe as RecipeItem;
use App\Models\ProductionExpense;
use Illuminate\Support\Facades\DB;
use App\Models\Costing;
use Livewire\Component;

class ProductRecipe extends Component
{
    public array $products = [];
    public array $supplies = [];
    public ?int $productId = null;
    public string $productName = '';
    public int $yieldUnits = 1;

    public array $rows = [];
    public float $totalBatch = 0;
    public float $totalPerUnit = 0;

    public function mount(?int $productId = null)
    {
        $this->products = Product::query()->orderBy('name')->get(['id','name'])->toArray();
        $this->supplies = Supply::query()->orderBy('name')->get(['id','name','base_unit','avg_cost_per_base'])->toArray();

        if ($productId) {
            $this->productId = $productId;
            $this->productName = Product::find($productId)?->name ?? '';
        }

        $this->resetBuilderRows(); // inicia con una fila vacía
        $this->yieldUnits = max(1, $this->yieldUnits);
        $this->recalc();
    }

    /** Reinicia SOLO las filas/valores del builder */
    private function resetBuilderRows(): void
    {
        $this->rows = [[
            'key'        => uniqid('row_', true),
            'supply_id'  => null,
            'qty'        => 0,
            'unit'       => '',
            'base_unit'  => '',
            'cost_base'  => 0,
        ]];
        $this->totalBatch   = 0;
        $this->totalPerUnit = 0;
    }

    /** Reinicia TODO para empezar un análisis nuevo */
    private function resetAll(): void
    {
        $this->productId   = null;
        $this->productName = '';
        $this->yieldUnits  = 1;
        $this->resetBuilderRows();
        $this->recalc();
        // pequeño QoL: scrollear arriba para ver el flash
        $this->dispatch('scroll-top');
    }

    public function updatedProductId($val)
    {
        $this->productName = collect($this->products)->firstWhere('id', (int)$val)['name'] ?? '';
    }

    public function addRow(){ /* igual que tenías */ 
        $this->rows[] = [
            'key' => uniqid('row_', true),
            'supply_id' => null,
            'qty' => 0,
            'unit' => '',
            'base_unit' => '',
            'cost_base' => 0,
        ];
    }

    public function removeRow($index)
    {
        unset($this->rows[$index]);
        $this->rows = array_values($this->rows);
        if (count($this->rows) === 0) $this->addRow();
        $this->recalc();
    }

    public function onSupplyChange($index)
    {
        $row = $this->rows[$index] ?? null;
        if (!$row) return;

        $s = collect($this->supplies)->firstWhere('id', (int)$row['supply_id']);
        $row['base_unit'] = $s['base_unit'] ?? '';
        $row['cost_base'] = (float)($s['avg_cost_per_base'] ?? 0);
        $row['unit'] = $row['base_unit'] ?? '';
        $this->rows[$index] = $row;
        $this->recalc();
    }

    private function factorToBase(string $from, string $base): float
    {
        $from = strtolower($from); $base = strtolower($base);
        $mass = ['g'=>1,'kg'=>1000];
        $vol  = ['ml'=>1,'l'=>1000,'cm3'=>1];
        $unit = ['u'=>1];

        if ($base === 'g'  && isset($mass[$from])) return $mass[$from];
        if ($base === 'ml' && isset($vol[$from]))  return $vol[$from];
        if ($base === 'u'  && isset($unit[$from])) return $unit[$from];
        return NAN;
    }

    private function lineBaseQty(array $row): float
    {
        if (empty($row['supply_id']) || empty($row['base_unit'])) return 0;
        $factor = $this->factorToBase($row['unit'] ?: '', $row['base_unit']);
        if (!is_finite($factor)) return 0;
        return (float)($row['qty'] ?? 0) * $factor;
    }

    private function lineCost(array $row): float
    {
        $price = (float)($row['cost_base'] ?? 0);
        return $this->lineBaseQty($row) * $price;
    }

    public function recalc()
    {
        $batch = 0.0;
        foreach ($this->rows as $row) $batch += $this->lineCost($row);

        $this->totalBatch   = round($batch, 2);
        $this->yieldUnits   = max(1, (int)($this->yieldUnits ?: 1));
        $this->totalPerUnit = round($batch / $this->yieldUnits, 2);
    }

    public function saveAnalysis()
    {
        if (!$this->productId || !$this->productName) {
            $this->addError('productId', 'Selecciona un producto.');
            return;
        }

        $y = max(1, (int)$this->yieldUnits);

        $lines = [];
        foreach ($this->rows as $row) {
            if (!$row['supply_id'] || !$row['base_unit']) continue;

            $baseQty = $this->lineBaseQty($row);
            $perUnitCost = ($baseQty * (float)$row['cost_base']) / $y;

            $s = collect($this->supplies)->firstWhere('id', (int)$row['supply_id']);

            $lines[] = [
                'id'            => (int)$row['supply_id'],
                'name'          => $s['name'] ?? '—',
                'base_unit'     => $row['base_unit'],
                'per_unit_qty'  => round($baseQty / $y, 4),
                'per_unit_cost' => round($perUnitCost, 4),
            ];
        }

        if (empty($lines)) {
            $this->addError('rows', 'Agrega al menos un ingrediente a la receta.');
            return;
        }

        $unitTotal  = array_sum(array_column($lines, 'per_unit_cost'));
        $batchTotal = $unitTotal * $y;

        foreach ($lines as &$l) {
            $l['perc'] = $unitTotal > 0 ? round($l['per_unit_cost'] / $unitTotal, 6) : 0;
        }

        $costing = Costing::create([
            'source'       => 'recipe',
            'yield_units'  => $y,
            'unit_total'   => round($unitTotal, 2),
            'batch_total'  => round($batchTotal, 2),
            'lines'        => $lines,
            'product_id'   => $this->productId,
            'product_name' => $this->productName,
        ]);

        // Notificar, mostrar éxito y reiniciar
        $this->dispatch('analysis-saved', id: $costing->id);
        session()->flash('ok', '¡Análisis guardado correctamente!');
        $this->resetAll();
    }

    /**
     * Aplica la receta actual al producto seleccionado creando/actualizando items en product_recipes.
     * No descuenta stock aquí; solo configura la receta. Los pedidos posteriores sí descontarán insumos.
     */
    public function applyToProduct(): void
    {
        if (!$this->productId) {
            $this->addError('productId', 'Selecciona un producto.');
            return;
        }

        $y = max(1, (int)$this->yieldUnits);
        $rows = $this->rows ?? [];
        if (empty($rows)) {
            $this->addError('rows', 'Agrega al menos un ingrediente.');
            return;
        }

        // Construir valores por unidad y persistir
        // Calcula costo total por unidad según filas actuales
        $unitTotal = 0.0;
        foreach ($rows as $row) {
            if (empty($row['supply_id']) || empty($row['base_unit'])) continue;
            $baseQty = $this->lineBaseQty($row);
            $perUnitBase = $y > 0 ? ($baseQty / $y) : 0;
            $costBase = (float)($row['cost_base'] ?? 0);
            $unitTotal += $perUnitBase * $costBase;
        }

        DB::transaction(function () use ($rows, $y, $unitTotal) {
            $supplyIdsUsed = [];

            foreach ($rows as $row) {
                $supplyId  = (int)($row['supply_id'] ?? 0);
                $baseUnit  = (string)($row['base_unit'] ?? '');
                $unit      = (string)($row['unit'] ?: $baseUnit);
                if (!$supplyId || !$baseUnit) continue;

                // Cantidad por unidad en unidad base
                $baseQty = $this->lineBaseQty($row); // total de la receta en base
                $perUnitBase = $y > 0 ? ($baseQty / $y) : 0;
                if ($perUnitBase <= 0) continue;

                // Convertir a unidad elegida para guardar
                $factor = $this->factorToBase($unit, $baseUnit);
                if (!is_finite($factor) || $factor <= 0) {
                    // fallback: guardar en base
                    $unit = $baseUnit;
                    $factor = 1;
                }
                $perUnitInUnit = $perUnitBase / $factor;

                RecipeItem::updateOrCreate(
                    ['product_id' => $this->productId, 'supply_id' => $supplyId],
                    ['qty' => $perUnitInUnit, 'unit' => $unit, 'waste_pct' => 0]
                );

                $supplyIdsUsed[] = $supplyId;
            }

            // Eliminar recetas que no están en los renglones actuales (reemplazo total)
            if (!empty($supplyIdsUsed)) {
                RecipeItem::where('product_id', $this->productId)
                    ->whereNotIn('supply_id', $supplyIdsUsed)
                    ->delete();
            }
        });

        // Registrar/actualizar gasto de producción por unidad para este producto
        if ($unitTotal > 0) {
            ProductionExpense::updateOrCreate(
                [
                    'product_id'   => $this->productId,
                    'expense_name' => 'Costo de insumos (receta)',
                ],
                [
                    'description'    => 'Generado desde calculadora de costos',
                    'cost_per_unit'  => round($unitTotal, 4),
                    'quantity'       => 1,
                    'unit'           => 'u',
                    'is_active'      => true,
                ]
            );
        }

        session()->flash('ok', 'Receta aplicada al producto y gasto de producción actualizado. Los pedidos descontarán insumos según esta configuración.');
        $this->dispatch('analysis-saved');
    }

    public function render()
    {
        return view('livewire.calculator.product-recipe');
    }
}
