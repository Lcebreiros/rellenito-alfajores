<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Order;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class FinanceController extends Controller
{
    /**
     * Resumen de ingresos, costos y ganancia (últimos N días).
     *
     * Params: days (int, opcional, 1-90, default 30)
     */
    public function summary(Request $request): JsonResponse
    {
        $user = $request->user();
        $days = max(1, min((int) $request->input('days', 30), 90));
        $from = now()->startOfDay()->subDays($days - 1);

        $ordersSub = Order::query()
            ->availableFor($user)
            ->where('status', 'completed')
            ->where('created_at', '>=', $from)
            ->select('id');

        $revenue = (float) Order::query()
            ->availableFor($user)
            ->where('status', 'completed')
            ->where('created_at', '>=', $from)
            ->sum('total');

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
            ->whereIn('oi.order_id', $ordersSub)
            ->selectRaw('COALESCE(SUM(oi.quantity * COALESCE(cx.unit_total, COALESCE(p.cost_price,0))),0) as c')
            ->value('c');

        $revByDay = DB::table('orders')
            ->whereIn('id', $ordersSub)
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
            ->whereIn('oi.order_id', $ordersSub)
            ->selectRaw('DATE(o.created_at) as d, COALESCE(SUM(oi.quantity * COALESCE(cx.unit_total, COALESCE(p.cost_price,0))),0) as s')
            ->groupBy('d')
            ->pluck('s', 'd');

        $labels = [];
        $series = ['revenue' => [], 'cost' => []];
        for ($i = 0; $i < $days; $i++) {
            $d = $from->copy()->addDays($i)->format('Y-m-d');
            $labels[] = $d;
            $series['revenue'][] = (float) ($revByDay[$d] ?? 0);
            $series['cost'][] = (float) ($costByDay[$d] ?? 0);
        }

        return response()->json([
            'success' => true,
            'data' => [
                'revenue' => $revenue,
                'cost' => $cost,
                'profit' => $revenue - $cost,
                'from' => $from->toDateString(),
                'to' => now()->toDateString(),
                'days' => $days,
                'labels' => $labels,
                'series' => $series,
            ],
        ]);
    }
}
