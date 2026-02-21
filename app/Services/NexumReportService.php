<?php

namespace App\Services;

use App\Models\BusinessInsight;
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
        $health     = (new HealthReportService($this->user))->generate();
        $sales      = $this->salesData($from, $to);
        $topProducts = $this->topProductsData($from, $to);
        $topClients  = $this->topClientsData($from, $to);
        $expenses    = $this->expensesData();
        $insights    = BusinessInsight::forUser($this->user->id)->active()->orderByPriority()->get();
        $lowStockItems = Product::where('user_id', $this->user->id)
            ->where(fn($q) => $q->where('stock', '<=', 0)->orWhereRaw('stock <= COALESCE(min_stock, 5)'))
            ->orderBy('stock')
            ->get();

        return compact('health', 'sales', 'topProducts', 'topClients', 'expenses', 'insights', 'lowStockItems');
    }

    private function salesData(Carbon $from, Carbon $to): array
    {
        $orders = Order::availableFor($this->user)
            ->completed()
            ->whereBetween('sold_at', [$from, $to])
            ->get();

        $total      = $orders->sum('total');
        $count      = $orders->count();
        $avgTicket  = $count > 0 ? $total / $count : 0;

        // Agrupa ventas por día (para el reporte solo usamos resumen por método de pago)
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
            'total_revenue'     => round($total, 2),
            'order_count'       => $count,
            'avg_ticket'        => round($avgTicket, 2),
            'by_payment_method' => $byPayment->toArray(),
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
            'start' => Carbon::parse($data['sales'] ? now()->subMonth()->startOfMonth() : now()),
            'end'   => now(),
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
