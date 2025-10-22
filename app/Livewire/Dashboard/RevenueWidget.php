<?php

namespace App\Livewire\Dashboard;

use Livewire\Component;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use App\Models\Order;

class RevenueWidget extends Component
{
    public int $days = 30;

    public function mount(int $days = 30): void
    {
        $this->days = max(1, min(90, $days));
    }

    public function render()
    {
        $from = now()->startOfDay()->subDays($this->days - 1);

        $user = Auth::user();

        // Ordenes visibles para el usuario en el período
        $ordersQ = Order::query()
            ->availableFor($user)
            ->where('status', 'completed')
            ->where('created_at', '>=', $from);

        $orderIds = $ordersQ->clone()->pluck('id');

        // Ingresos: suma de total de esas órdenes
        $revenue = (float) $ordersQ->clone()->sum('total');

        // Costo estimado por ítem usando costo de calculadora si existe (costings.unit_total),
        // con fallback a products.cost_price
        $cost = (float) DB::table('order_items as oi')
            ->leftJoin('products as p', 'p.id', '=', 'oi.product_id')
            ->leftJoin(DB::raw('(
                SELECT c1.product_id, c1.unit_total
                FROM costings c1
                JOIN (
                    SELECT product_id, MAX(created_at) as max_created
                    FROM costings
                    GROUP BY product_id
                ) c2 ON c1.product_id = c2.product_id AND c1.created_at = c2.max_created
            ) as cx'), 'cx.product_id', '=', 'oi.product_id')
            ->whereIn('oi.order_id', $orderIds)
            ->selectRaw('COALESCE(SUM(oi.quantity * COALESCE(cx.unit_total, COALESCE(p.cost_price,0))),0) as c')
            ->value('c');

        // Series por día para gráfico (ingresos y costos)
        $revByDay = DB::table('orders')
            ->whereIn('id', $orderIds)
            ->selectRaw('DATE(created_at) as d, SUM(total) as s')
            ->groupBy('d')
            ->pluck('s', 'd');

        $costByDay = DB::table('order_items as oi')
            ->join('orders as o', 'o.id', '=', 'oi.order_id')
            ->leftJoin('products as p', 'p.id', '=', 'oi.product_id')
            ->leftJoin(DB::raw('(
                SELECT c1.product_id, c1.unit_total
                FROM costings c1
                JOIN (
                    SELECT product_id, MAX(created_at) as max_created
                    FROM costings
                    GROUP BY product_id
                ) c2 ON c1.product_id = c2.product_id AND c1.created_at = c2.max_created
            ) as cx'), 'cx.product_id', '=', 'oi.product_id')
            ->whereIn('oi.order_id', $orderIds)
            ->selectRaw('DATE(o.created_at) as d, COALESCE(SUM(oi.quantity * COALESCE(cx.unit_total, COALESCE(p.cost_price,0))),0) as s')
            ->groupBy('d')
            ->pluck('s', 'd');

        // Normalizar eje X (últimos N días)
        $labels = [];
        $series = ['revenue'=>[], 'cost'=>[]];
        $maxVal = 0.0;
        for ($i = 0; $i < $this->days; $i++) {
            $d = $from->copy()->addDays($i)->format('Y-m-d');
            $labels[] = $d;
            $r = (float) ($revByDay[$d] ?? 0);
            $c = (float) ($costByDay[$d] ?? 0);
            $series['revenue'][] = $r;
            $series['cost'][]    = $c;
            $maxVal = max($maxVal, $r, $c);
        }

        $profit = $revenue - $cost;

        return view('livewire.dashboard.revenue-widget', [
            'revenue' => $revenue,
            'cost'    => $cost,
            'profit'  => $profit,
            'from'    => $from,
            'days'    => $this->days,
            'labels'  => $labels,
            'series'  => $series,
            'maxVal'  => max(1, $maxVal),
        ]);
    }
}
