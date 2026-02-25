<?php

namespace App\Services;

use App\Models\GeneratedReport;
use App\Models\Order;
use App\Models\Product;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\View;

class NexumReportService
{
    public function __construct(private User $user) {}

    /**
     * Genera el PDF completo y guarda el GeneratedReport.
     */
    public function generate(
        Carbon $periodStart,
        Carbon $periodEnd,
        string $frequencyType = 'manual',
        ?GeneratedReport $existingReport = null
    ): GeneratedReport {
        $report = $existingReport ?? GeneratedReport::create([
            'user_id'        => $this->user->id,
            'frequency_type' => $frequencyType,
            'period_start'   => $periodStart->toDateString(),
            'period_end'     => $periodEnd->toDateString(),
            'status'         => 'generating',
        ]);

        try {
            $report->update(['status' => 'generating']);

            $data = $this->collectData($periodStart, $periodEnd);

            $pdf  = $this->renderPdf($data);
            $path = $this->storePdf($pdf, $report);

            $report->update([
                'file_path' => $path,
                'file_size' => Storage::size($path),
                'status'    => 'ready',
            ]);

            return $report->fresh();
        } catch (\Throwable $e) {
            Log::error('NexumReportService error: ' . $e->getMessage(), [
                'user_id'   => $this->user->id,
                'report_id' => $report->id,
                'trace'     => $e->getTraceAsString(),
            ]);

            $report->update([
                'status'        => 'failed',
                'error_message' => $e->getMessage(),
            ]);

            throw $e;
        }
    }

    // ─────────────────────────────────────────────────────────────────────────

    private function collectData(Carbon $from, Carbon $to): array
    {
        $health        = (new HealthReportService($this->user))->generate();
        $sales         = $this->salesData($from, $to);
        $topProducts   = $this->topProductsData($from, $to);
        $productMargin = $this->productMarginData($from, $to);
        $topClients    = $this->topClientsData($from, $to);
        $expenses      = $this->expensesData();
        $inventoryRot  = $this->inventoryRotationData($from, $to);
        $insights      = $this->generateInsightsForReport();
        $lowStockItems = Product::where('user_id', $this->user->id)
            ->where(fn($q) => $q->where('stock', '<=', 0)->orWhereRaw('stock <= COALESCE(min_stock, 5)'))
            ->orderBy('stock')
            ->get();

        return compact('health', 'sales', 'topProducts', 'productMargin', 'topClients', 'expenses', 'inventoryRot', 'insights', 'lowStockItems', 'from', 'to');
    }

    private function salesData(Carbon $from, Carbon $to): array
    {
        $orders = Order::availableFor($this->user)
            ->completed()
            ->whereBetween('sold_at', [$from, $to])
            ->get();

        $total     = $orders->sum('total');
        $count     = $orders->count();
        $avgTicket = $count > 0 ? $total / $count : 0;

        // Período anterior de la misma duración
        $days     = max(1, (int) $from->diffInDays($to) + 1);
        $prevTo   = $from->copy()->subSecond();
        $prevFrom = $prevTo->copy()->subDays($days - 1)->startOfDay();

        $prevOrders    = Order::availableFor($this->user)
            ->completed()
            ->whereBetween('sold_at', [$prevFrom, $prevTo])
            ->get();

        $prevTotal     = $prevOrders->sum('total');
        $prevCount     = $prevOrders->count();
        $prevAvgTicket = $prevCount > 0 ? $prevTotal / $prevCount : 0;

        $revenueChangePct = $prevTotal > 0
            ? round((($total - $prevTotal) / $prevTotal) * 100, 1)
            : ($total > 0 ? 100 : 0);
        $countChangePct = $prevCount > 0
            ? round((($count - $prevCount) / $prevCount) * 100, 1)
            : ($count > 0 ? 100 : 0);
        $ticketChangePct = $prevAvgTicket > 0
            ? round((($avgTicket - $prevAvgTicket) / $prevAvgTicket) * 100, 1)
            : 0;

        $byPayment = $orders->groupBy('payment_method')
            ->map(fn($g) => [
                'method' => $g->first()->payment_method ?? 'Otro',
                'total'  => $g->sum('total'),
                'count'  => $g->count(),
            ])
            ->values()
            ->sortByDesc('total')
            ->values();

        return [
            'total_revenue'      => round($total, 2),
            'order_count'        => $count,
            'avg_ticket'         => round($avgTicket, 2),
            'prev_total_revenue' => round($prevTotal, 2),
            'prev_order_count'   => $prevCount,
            'prev_avg_ticket'    => round($prevAvgTicket, 2),
            'revenue_change_pct' => $revenueChangePct,
            'count_change_pct'   => $countChangePct,
            'ticket_change_pct'  => $ticketChangePct,
            'by_payment_method'  => $byPayment->toArray(),
        ];
    }

    private function topProductsData(Carbon $from, Carbon $to): array
    {
        return DB::table('order_items')
            ->join('orders', 'orders.id', '=', 'order_items.order_id')
            ->join('products', 'products.id', '=', 'order_items.product_id')
            ->where('orders.user_id', $this->user->id)
            ->where('orders.status', 'completed')
            ->whereBetween('orders.sold_at', [$from, $to])
            ->groupBy('products.id', 'products.name')
            ->select(
                'products.name',
                DB::raw('SUM(order_items.quantity) as quantity'),
                DB::raw('SUM(order_items.subtotal) as revenue')
            )
            ->orderByDesc('revenue')
            ->limit(10)
            ->get()
            ->map(fn($r) => [
                'name'     => $r->name,
                'quantity' => $r->quantity,
                'revenue'  => round($r->revenue, 2),
            ])
            ->toArray();
    }

    private function topClientsData(Carbon $from, Carbon $to): array
    {
        return DB::table('orders')
            ->join('clients', 'clients.id', '=', 'orders.client_id')
            ->where('orders.user_id', $this->user->id)
            ->where('orders.status', 'completed')
            ->whereBetween('orders.sold_at', [$from, $to])
            ->whereNotNull('orders.client_id')
            ->groupBy('clients.id', 'clients.name')
            ->select(
                'clients.name',
                DB::raw('COUNT(orders.id) as orders'),
                DB::raw('SUM(orders.total) as total')
            )
            ->orderByDesc('total')
            ->limit(5)
            ->get()
            ->map(fn($r) => [
                'name'   => $r->name,
                'orders' => $r->orders,
                'total'  => round($r->total, 2),
            ])
            ->toArray();
    }

    private function productMarginData(Carbon $from, Carbon $to): array
    {
        $results = DB::table('order_items')
            ->join('orders',   'orders.id',   '=', 'order_items.order_id')
            ->join('products', 'products.id', '=', 'order_items.product_id')
            ->where('orders.user_id', $this->user->id)
            ->where('orders.status', 'completed')
            ->whereBetween('orders.sold_at', [$from, $to])
            ->where('products.cost_price', '>', 0)
            ->groupBy('products.id', 'products.name', 'products.cost_price')
            ->select(
                'products.name',
                'products.cost_price',
                DB::raw('SUM(order_items.quantity) as quantity'),
                DB::raw('SUM(order_items.subtotal) as revenue'),
                DB::raw('AVG(order_items.unit_price) as avg_unit_price')
            )
            ->orderByDesc('revenue')
            ->limit(10)
            ->get();

        return $results->map(function ($r) {
            $avgPrice  = (float) $r->avg_unit_price;
            $costPrice = (float) $r->cost_price;
            $marginPct = $avgPrice > 0
                ? round((($avgPrice - $costPrice) / $avgPrice) * 100, 1)
                : 0;
            return [
                'name'       => $r->name,
                'quantity'   => (int) $r->quantity,
                'revenue'    => round((float) $r->revenue, 2),
                'cost_price' => round($costPrice, 2),
                'avg_price'  => round($avgPrice, 2),
                'margin_pct' => $marginPct,
            ];
        })->toArray();
    }

    private function inventoryRotationData(Carbon $from, Carbon $to): array
    {
        $days = max(1, (int) $from->diffInDays($to) + 1);

        $products = Product::where('user_id', $this->user->id)
            ->where('uses_stock', true)
            ->where('stock', '>', 0)
            ->get();

        if ($products->isEmpty()) {
            return ['has_data' => false, 'dead_stock_count' => 0, 'dead_stock_capital' => 0, 'avg_days_of_stock' => 0, 'items' => []];
        }

        $soldQtys = DB::table('order_items')
            ->join('orders', 'orders.id', '=', 'order_items.order_id')
            ->where('orders.user_id', $this->user->id)
            ->where('orders.status', 'completed')
            ->whereBetween('orders.sold_at', [$from, $to])
            ->groupBy('order_items.product_id')
            ->selectRaw('order_items.product_id, SUM(order_items.quantity) as total_qty')
            ->pluck('total_qty', 'product_id');

        $items            = [];
        $deadStockCount   = 0;
        $deadStockCapital = 0;
        $daysOfStockList  = [];

        foreach ($products as $product) {
            $unitsSold     = (float) ($soldQtys[$product->id] ?? 0);
            $dailyVelocity = $unitsSold / $days;
            $daysOfStock   = $dailyVelocity > 0 ? (int) ceil($product->stock / $dailyVelocity) : null;
            $capital       = $product->stock * ($product->cost_price > 0 ? $product->cost_price : $product->price);

            if ($unitsSold == 0) {
                $deadStockCount++;
                $deadStockCapital += $capital;
            } elseif ($daysOfStock !== null) {
                $daysOfStockList[] = $daysOfStock;
            }

            $items[] = [
                'name'          => $product->name,
                'stock'         => $product->stock,
                'units_sold'    => (int) $unitsSold,
                'days_of_stock' => $daysOfStock,
                'capital'       => round($capital, 2),
                'is_dead'       => $unitsSold == 0,
            ];
        }

        $avgDaysOfStock = count($daysOfStockList) > 0
            ? (int) round(array_sum($daysOfStockList) / count($daysOfStockList))
            : 0;

        // Ordenar: primero inmovilizados (is_dead), luego por días desc
        usort($items, function ($a, $b) {
            if ($a['is_dead'] !== $b['is_dead']) return $b['is_dead'] <=> $a['is_dead'];
            return ($b['days_of_stock'] ?? 0) <=> ($a['days_of_stock'] ?? 0);
        });

        return [
            'has_data'           => true,
            'dead_stock_count'   => $deadStockCount,
            'dead_stock_capital' => round($deadStockCapital, 2),
            'avg_days_of_stock'  => $avgDaysOfStock,
            'items'              => array_slice($items, 0, 8),
        ];
    }

    /**
     * Corre los generators en memoria sin guardar en BD.
     * Retorna arrays con priority_color incluido, ordenados por prioridad.
     */
    private function generateInsightsForReport(): array
    {
        $generators = [
            \App\Services\Insights\Generators\LowStockInsightGenerator::class,
            \App\Services\Insights\Generators\RevenueOpportunityGenerator::class,
            \App\Services\Insights\Generators\CostWarningGenerator::class,
            \App\Services\Insights\Generators\ClientRetentionGenerator::class,
        ];

        $priorityOrder = ['critical' => 0, 'high' => 1, 'medium' => 2, 'low' => 3, 'info' => 4];
        $all = collect();

        foreach ($generators as $class) {
            try {
                $generator = new $class($this->user, null);
                $all = $all->merge($generator->generate());
            } catch (\Throwable) {
                // continuar con el siguiente
            }
        }

        return $all
            ->map(fn($insight) => array_merge($insight, [
                'priority_color' => $this->insightPriorityColor($insight['priority'] ?? 'info'),
            ]))
            ->sortBy(fn($i) => $priorityOrder[$i['priority'] ?? 'info'] ?? 5)
            ->values()
            ->take(8)
            ->toArray();
    }

    private function insightPriorityColor(string $priority): string
    {
        return match($priority) {
            'critical' => '#EF4444',
            'high'     => '#F59E0B',
            'medium'   => '#3B82F6',
            'low'      => '#10B981',
            default    => '#6b7280',
        };
    }

    private function expensesData(): array
    {
        $uid = $this->user->id;

        $supplier   = (float) DB::table('supplier_expenses')->where('user_id', $uid)->where('is_active', true)->sum('cost');
        $services   = (float) DB::table('third_party_services')->where('user_id', $uid)->where('is_active', true)->sum('cost');
        $production = (float) DB::table('production_expenses')->where('user_id', $uid)->where('is_active', true)->sum('cost_per_unit');
        $service_exp = (float) DB::table('service_expenses')->where('user_id', $uid)->where('is_active', true)->sum('cost');

        return [
            ['label' => 'Gastos de proveedores',          'amount' => round($supplier, 2)],
            ['label' => 'Servicios de terceros',           'amount' => round($services, 2)],
            ['label' => 'Gastos de producción',            'amount' => round($production, 2)],
            ['label' => 'Gastos de servicios propios',     'amount' => round($service_exp, 2)],
        ];
    }

    private function renderPdf(array $data): string
    {
        $data['user']   = $this->user;
        $data['period'] = [
            'start' => $data['from'],
            'end'   => $data['to'],
        ];
        $data['config'] = ['frequency_label' => 'Período seleccionado'];

        $html = View::make('pdf.nexum-report', $data)->render();

        $dompdf = new \Dompdf\Dompdf();
        $dompdf->setPaper('A4', 'portrait');
        $dompdf->loadHtml($html);
        $dompdf->render();

        return $dompdf->output();
    }

    private function storePdf(string $content, GeneratedReport $report): string
    {
        $dir      = 'nexum-reports/' . $this->user->id;
        $filename = 'reporte-' . $report->id . '-' . now()->format('Y-m-d') . '.pdf';
        $path     = $dir . '/' . $filename;

        Storage::put($path, $content);

        return $path;
    }
}
