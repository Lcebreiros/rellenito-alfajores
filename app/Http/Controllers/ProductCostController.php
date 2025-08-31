<?php
// app/Http/Controllers/ProductCostController.php
namespace App\Http\Controllers;

use App\Models\Product;
use App\Models\ProductRecipe;
use App\Models\Supply;
use App\Models\CostAnalysis; // ⬅️ nuevo
use App\Services\CostService;
use Illuminate\Http\Request;

class ProductCostController extends Controller
{
    public function edit(Product $product) {
        $product->load('recipeItems.supply');
        $supplies = Supply::orderBy('name')->get();
        return view('products.edit_recipe', compact('product','supplies'));
    }

    public function addRecipeItem(Request $r, Product $product) {
        $data = $r->validate([
            'supply_id' => ['required','exists:supplies,id'],
            'qty'       => ['required','numeric','gt:0'],
            'unit'      => ['required','string','max:10'],
            'waste_pct' => ['nullable','numeric','min:0','max:100'],
        ]);

        ProductRecipe::updateOrCreate(
            ['product_id'=>$product->id, 'supply_id'=>$data['supply_id']],
            ['qty'=>$data['qty'],'unit'=>$data['unit'],'waste_pct'=>$data['waste_pct'] ?? 0]
        );

        return back()->with('ok','Ingrediente agregado/actualizado.');
    }

    public function cost(Product $product) {
        $product->load('recipeItems.supply');
        $cost = CostService::productCost($product);

        $price = (float)$product->price;
        $profit = max(0, $price - $cost['unit_cost']);
        $profitPct = $price > 0 ? ($profit/$price)*100 : 0;

        // ⬇️ traer últimos análisis guardados de este producto (útil para mostrarlos en la vista)
        $saved = CostAnalysis::where('product_id', $product->id)
            ->latest()->limit(12)->get();

        return view('products.cost', compact('product','cost','price','profit','profitPct','saved'));
    }

    /** =========================
     *  NUEVO: Guardar análisis
     *  ========================= */


public function storeAnalysis(Request $r, Product $product)
{
    $data = $r->validate([
        'source'      => ['required','in:simple,recipe'],
        'yield_units' => ['required','integer','min:1'],
        'unit_total'  => ['required','numeric','min:0'],
        'batch_total' => ['required','numeric','min:0'],
        'lines'       => ['required','array','min:1'],
        'lines.*.id'            => ['nullable'],
        'lines.*.name'          => ['required','string'],
        'lines.*.base_unit'     => ['required','string','in:g,ml,u'],
        'lines.*.per_unit_qty'  => ['required','numeric','min:0'],
        'lines.*.per_unit_cost' => ['required','numeric','min:0'],
        'lines.*.perc'          => ['required','numeric','min:0'],
    ]);

    $data['product_id'] = $product->id;

    try {
        $costing = CostAnalysis::create($data);
        
        // Formatear la respuesta para que sea consistente con el frontend
        $formattedCosting = [
            'id' => $costing->id,
            'source' => $costing->source,
            'yield_units' => (int) $costing->yield_units,
            'unit_total' => (float) $costing->unit_total,
            'batch_total' => (float) $costing->batch_total,
            'lines' => $costing->lines,
            'created_at' => $costing->created_at->toISOString(),
        ];

        return response()->json([
            'ok' => true, 
            'message' => 'Análisis guardado correctamente',
            'costing' => $formattedCosting
        ], 201);
        
    } catch (\Exception $e) {
        return response()->json([
            'ok' => false,
            'message' => 'Error al guardar el análisis: ' . $e->getMessage()
        ], 500);
    }
}

public function analyses(Product $product)
{
    try {
        $analyses = CostAnalysis::where('product_id', $product->id)
            ->latest()
            ->limit(24)
            ->get()
            ->map(function ($analysis) {
                return [
                    'id' => $analysis->id,
                    'source' => $analysis->source,
                    'yield_units' => (int) $analysis->yield_units,
                    'unit_total' => (float) $analysis->unit_total,
                    'batch_total' => (float) $analysis->batch_total,
                    'lines' => $analysis->lines,
                    'created_at' => $analysis->created_at->toISOString(),
                ];
            });

        return response()->json($analyses);
        
    } catch (\Exception $e) {
        return response()->json([
            'error' => 'Error al obtener análisis: ' . $e->getMessage()
        ], 500);
    }
}
}
