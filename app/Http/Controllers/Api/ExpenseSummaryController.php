<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\ProductionExpense;
use App\Models\ServiceExpense;
use App\Models\SupplierExpense;
use App\Models\ThirdPartyService;
use Illuminate\Http\Request;

class ExpenseSummaryController extends Controller
{
    /**
     * Resumen rápido de gastos por categoría y anualizados.
     */
    public function summary(Request $request)
    {
        $auth = $request->user();
        $companyId = $auth->rootCompany()?->id ?? $auth->id;

        $supplier = SupplierExpense::withoutGlobalScope('byUser')
            ->where('user_id', $companyId)
            ->get();
        $service = ServiceExpense::withoutGlobalScope('byUser')
            ->where('user_id', $companyId)
            ->get();
        $third = ThirdPartyService::query()
            ->where('user_id', $companyId)
            ->get();
        $production = ProductionExpense::withoutGlobalScope('byUser')
            ->where('user_id', $companyId)
            ->get();

        $totalSupplier = $supplier->sum(fn($e) => $e->annualized_cost ?? ($e->cost * $e->quantity));
        $totalService = $service->sum('cost');
        $totalThird = $third->sum(fn($e) => method_exists($e, 'getAnnualizedCostAttribute') ? $e->annualized_cost : ($e->cost ?? 0));
        $totalProduction = $production->sum(fn($e) => $e->total_cost ?? ($e->cost_per_unit * $e->quantity));

        return response()->json([
            'success' => true,
            'data' => [
                'supplier_expenses' => $totalSupplier,
                'service_expenses' => $totalService,
                'third_party_services' => $totalThird,
                'production_expenses' => $totalProduction,
                'grand_total' => $totalSupplier + $totalService + $totalThird + $totalProduction,
            ],
        ], 200);
    }
}
