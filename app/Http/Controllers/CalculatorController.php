<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Product;
use App\Models\Supply;
use App\Models\CostAnalysis;

class CalculatorController extends Controller
{
    public function show(Request $request)
    {
        $products = Product::orderBy('name')
            ->get(['id', 'name', 'sku', 'yield_units']);

        $allSupplies = Supply::orderBy('name')
            ->get(['id', 'name', 'base_unit', 'avg_cost_per_base']);

        // Lista paginada para la sección "Insumos"
        $supplies = Supply::orderBy('name')
            ->paginate(10)
            ->withQueryString();

        // Producto seleccionado
        $product = null;
        $savedAnalyses = [];

        if ($request->filled('product_id')) {
            $product = Product::find((int) $request->input('product_id'));

            if ($product) {
                $savedAnalyses = CostAnalysis::where('product_id', $product->id)
                    ->latest()
                    ->limit(12)
                    ->get()
                    ->map(function ($analysis) {
                        return [
                            'id'          => $analysis->id,
                            'source'      => $analysis->source,
                            'yield_units' => (int) $analysis->yield_units,
                            'unit_total'  => (float) $analysis->unit_total,
                            'batch_total' => (float) $analysis->batch_total,
                            'lines'       => $analysis->lines,
                            'created_at'  => $analysis->created_at->toISOString(),
                        ];
                    })
                    ->values()
                    ->all();
            }
        }

        return view('costing.calculator', compact(
            'products',
            'allSupplies',
            'product',
            'supplies',
            'savedAnalyses'
        ));
    }
}
