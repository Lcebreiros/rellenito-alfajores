<?php

namespace App\Livewire\Dashboard;

use App\Models\SupplierExpense;
use App\Models\ServiceExpense;
use App\Models\ThirdPartyService;
use App\Models\ProductionExpense;
use App\Models\Supply;
use Livewire\Component;
use Illuminate\Support\Facades\Auth;

class ExpensesWidget extends Component
{
    public function render()
    {
        $user = Auth::user();

        // Cálculo de multiplicador por frecuencia (SQL)
        $freqCase = "CASE frequency WHEN 'unica' THEN 1 WHEN 'diaria' THEN 365 WHEN 'semanal' THEN 52 WHEN 'mensual' THEN 12 WHEN 'anual' THEN 1 ELSE 1 END";

        // Totales por tipo usando agregaciones en DB (respeta el scope por usuario)
        $totalSupplier = (float) SupplierExpense::query()
            ->where('is_active', true)
            ->selectRaw('COALESCE(SUM(cost * quantity * ('.$freqCase.')),0) as s')
            ->value('s');

        $totalService = (float) ServiceExpense::query()
            ->where('is_active', true)
            ->sum('cost');

        $totalThirdParty = (float) ThirdPartyService::query()
            ->where('is_active', true)
            ->selectRaw('COALESCE(SUM(cost * ('.$freqCase.')),0) as s')
            ->value('s');

        $totalProduction = (float) ProductionExpense::query()
            ->where('is_active', true)
            ->selectRaw('COALESCE(SUM(cost_per_unit * quantity),0) as s')
            ->value('s');

        $totalSupplies = (float) Supply::query()
            ->selectRaw('COALESCE(SUM(stock_base_qty * avg_cost_per_base),0) as s')
            ->value('s');

        $total = $totalSupplier + $totalService + $totalThirdParty + $totalProduction + $totalSupplies;

        // Conteo de ítems activos
        $totalItems = SupplierExpense::where('is_active', true)->count()
                    + ServiceExpense::where('is_active', true)->count()
                    + ThirdPartyService::where('is_active', true)->count()
                    + ProductionExpense::where('is_active', true)->count();

        // Distribución para mini gráfico
        $distribution = [
            ['name' => 'Proveedores', 'value' => $totalSupplier, 'color' => 'bg-blue-500', 'percent' => $total > 0 ? ($totalSupplier / $total) * 100 : 0],
            ['name' => 'Servicios',  'value' => $totalService,  'color' => 'bg-green-500',  'percent' => $total > 0 ? ($totalService  / $total) * 100 : 0],
            ['name' => 'Terceros',   'value' => $totalThirdParty,'color' => 'bg-purple-500', 'percent' => $total > 0 ? ($totalThirdParty / $total) * 100 : 0],
            ['name' => 'Producción', 'value' => $totalProduction,'color' => 'bg-orange-500', 'percent' => $total > 0 ? ($totalProduction / $total) * 100 : 0],
            ['name' => 'Insumos',    'value' => $totalSupplies, 'color' => 'bg-amber-500',  'percent' => $total > 0 ? ($totalSupplies / $total) * 100 : 0],
        ];

        return view('livewire.dashboard.expenses-widget', [
            'total'        => $total,
            'distribution' => $distribution,
            'totalItems'   => $totalItems,
        ]);
    }
}
