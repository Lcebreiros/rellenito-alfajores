<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\Product;
use App\Models\Client;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class ReportController extends Controller
{
    /**
     * Resumen de ventas
     */
    public function salesSummary(Request $request)
    {
        $auth = $request->user();

        $validated = $request->validate([
            'from_date' => 'nullable|date',
            'to_date' => 'nullable|date|after_or_equal:from_date',
            'period' => 'nullable|in:today,week,month,year',
            'branch_id' => 'nullable|exists:branches,id',
        ]);

        $query = Order::availableFor($auth)
            ->where('status', 'completed');

        // Aplicar filtro de fechas
        $this->applyDateFilter($query, $validated);

        // Filtrar por sucursal si se especifica
        if (isset($validated['branch_id'])) {
            $query->where('branch_id', $validated['branch_id']);
        }

        $totalSales = $query->sum('total');
        $totalOrders = $query->count();
        $averageTicket = $totalOrders > 0 ? $totalSales / $totalOrders : 0;

        // Ventas por día (últimos 30 días o período especificado)
        $salesByDay = (clone $query)
            ->selectRaw('DATE(created_at) as date, SUM(total) as total, COUNT(*) as count')
            ->groupBy('date')
            ->orderBy('date')
            ->get();

        // Ventas por método de pago
        $salesByPaymentMethod = (clone $query)
            ->select('payment_status', DB::raw('SUM(total) as total, COUNT(*) as count'))
            ->groupBy('payment_status')
            ->get();

        return response()->json([
            'success' => true,
            'data' => [
                'summary' => [
                    'total_sales' => round((float) $totalSales, 2),
                    'total_orders' => (int) $totalOrders,
                    'average_ticket' => round((float) $averageTicket, 2),
                ],
                'sales_by_day' => $salesByDay->map(function ($item) {
                    return [
                        'date' => $item->date,
                        'total' => round((float) $item->total, 2),
                        'count' => (int) $item->count,
                    ];
                }),
                'sales_by_payment_status' => $salesByPaymentMethod->map(function ($item) {
                    return [
                        'payment_status' => $item->payment_status,
                        'total' => round((float) $item->total, 2),
                        'count' => (int) $item->count,
                    ];
                }),
            ],
        ]);
    }

    /**
     * Top productos más vendidos
     */
    public function topProducts(Request $request)
    {
        $auth = $request->user();

        $validated = $request->validate([
            'from_date' => 'nullable|date',
            'to_date' => 'nullable|date|after_or_equal:from_date',
            'period' => 'nullable|in:today,week,month,year',
            'limit' => 'nullable|integer|min:1|max:50',
            'branch_id' => 'nullable|exists:branches,id',
        ]);

        $limit = $validated['limit'] ?? 10;

        $query = DB::table('order_items')
            ->join('orders', 'order_items.order_id', '=', 'orders.id')
            ->join('products', 'order_items.product_id', '=', 'products.id')
            ->where('orders.status', 'completed')
            ->whereNotNull('order_items.product_id');

        // Aplicar filtros de empresa/jerarquía
        if ($auth->isMaster()) {
            // Ver todos
        } elseif ($auth->isCompany()) {
            $query->where('orders.company_id', $auth->id);
        } elseif ($auth->isAdmin()) {
            $query->where('orders.branch_id', $auth->id);
        } else {
            $query->where('orders.user_id', $auth->id);
        }

        // Aplicar filtro de fechas
        if (isset($validated['from_date'])) {
            $query->where('orders.created_at', '>=', $validated['from_date']);
        }
        if (isset($validated['to_date'])) {
            $query->where('orders.created_at', '<=', $validated['to_date'] . ' 23:59:59');
        }
        if (isset($validated['period'])) {
            $this->applyPeriodFilter($query, $validated['period'], 'orders.created_at');
        }

        // Filtrar por sucursal
        if (isset($validated['branch_id'])) {
            $query->where('orders.branch_id', $validated['branch_id']);
        }

        $topProducts = $query
            ->select(
                'products.id',
                'products.name',
                'products.sku',
                'products.price',
                'products.image',
                DB::raw('SUM(order_items.quantity) as total_quantity'),
                DB::raw('SUM(order_items.subtotal) as total_sales'),
                DB::raw('COUNT(DISTINCT orders.id) as order_count')
            )
            ->groupBy('products.id', 'products.name', 'products.sku', 'products.price', 'products.image')
            ->orderByDesc('total_sales')
            ->limit($limit)
            ->get();

        return response()->json([
            'success' => true,
            'data' => $topProducts->map(function ($item) {
                return [
                    'id' => $item->id,
                    'name' => $item->name,
                    'sku' => $item->sku,
                    'price' => round((float) $item->price, 2),
                    'image' => $item->image,
                    'total_quantity' => round((float) $item->total_quantity, 2),
                    'total_sales' => round((float) $item->total_sales, 2),
                    'order_count' => (int) $item->order_count,
                ];
            }),
        ]);
    }

    /**
     * Reporte de clientes
     */
    public function clientsReport(Request $request)
    {
        $auth = $request->user();

        $validated = $request->validate([
            'from_date' => 'nullable|date',
            'to_date' => 'nullable|date|after_or_equal:from_date',
            'limit' => 'nullable|integer|min:1|max:50',
        ]);

        $limit = $validated['limit'] ?? 10;

        $query = DB::table('clients')
            ->leftJoin('orders', function ($join) use ($validated) {
                $join->on('clients.id', '=', 'orders.client_id')
                    ->where('orders.status', 'completed');

                if (isset($validated['from_date'])) {
                    $join->where('orders.created_at', '>=', $validated['from_date']);
                }
                if (isset($validated['to_date'])) {
                    $join->where('orders.created_at', '<=', $validated['to_date'] . ' 23:59:59');
                }
            });

        // Filtros de jerarquía
        if ($auth->isMaster()) {
            // Ver todos
        } elseif ($auth->isCompany()) {
            $query->where('clients.user_id', $auth->id);
        } else {
            $query->where('clients.user_id', $auth->id);
        }

        $topClients = $query
            ->select(
                'clients.id',
                'clients.name',
                'clients.email',
                'clients.phone',
                DB::raw('COUNT(DISTINCT orders.id) as order_count'),
                DB::raw('SUM(orders.total) as total_spent'),
                DB::raw('MAX(orders.created_at) as last_purchase')
            )
            ->groupBy('clients.id', 'clients.name', 'clients.email', 'clients.phone')
            ->having('order_count', '>', 0)
            ->orderByDesc('total_spent')
            ->limit($limit)
            ->get();

        return response()->json([
            'success' => true,
            'data' => $topClients->map(function ($item) {
                return [
                    'id' => $item->id,
                    'name' => $item->name,
                    'email' => $item->email,
                    'phone' => $item->phone,
                    'order_count' => (int) $item->order_count,
                    'total_spent' => round((float) $item->total_spent, 2),
                    'last_purchase' => $item->last_purchase,
                ];
            }),
        ]);
    }

    /**
     * Reporte de stock
     */
    public function stockReport(Request $request)
    {
        $auth = $request->user();

        $validated = $request->validate([
            'type' => 'nullable|in:low,out,all',
            'branch_id' => 'nullable|exists:branches,id',
        ]);

        $type = $validated['type'] ?? 'all';

        $query = Product::availableFor($auth)
            ->select('id', 'name', 'sku', 'stock', 'min_stock', 'price', 'is_active')
            ->where('is_active', true);

        // Filtrar por sucursal si se especifica
        if (isset($validated['branch_id'])) {
            $query->where('branch_id', $validated['branch_id']);
        }

        // Aplicar filtros de stock
        if ($type === 'low') {
            $query->whereColumn('stock', '<=', 'min_stock')
                  ->where('stock', '>', 0);
        } elseif ($type === 'out') {
            $query->where('stock', '<=', 0);
        }

        $products = $query->orderBy('stock')->get();

        $summary = [
            'total_products' => Product::availableFor($auth)->count(),
            'low_stock_count' => Product::availableFor($auth)
                ->whereColumn('stock', '<=', 'min_stock')
                ->where('stock', '>', 0)
                ->count(),
            'out_of_stock_count' => Product::availableFor($auth)
                ->where('stock', '<=', 0)
                ->count(),
            'total_stock_value' => Product::availableFor($auth)
                ->selectRaw('SUM(stock * price) as value')
                ->value('value'),
        ];

        return response()->json([
            'success' => true,
            'data' => [
                'summary' => [
                    'total_products' => (int) $summary['total_products'],
                    'low_stock_count' => (int) $summary['low_stock_count'],
                    'out_of_stock_count' => (int) $summary['out_of_stock_count'],
                    'total_stock_value' => round((float) $summary['total_stock_value'], 2),
                ],
                'products' => $products->map(function ($product) {
                    return [
                        'id' => $product->id,
                        'name' => $product->name,
                        'sku' => $product->sku,
                        'stock' => (float) $product->stock,
                        'min_stock' => (float) $product->min_stock,
                        'price' => round((float) $product->price, 2),
                        'stock_value' => round((float) ($product->stock * $product->price), 2),
                        'status' => $product->stock <= 0 ? 'out' : ($product->stock <= $product->min_stock ? 'low' : 'ok'),
                    ];
                }),
            ],
        ]);
    }

    /**
     * Datos para exportación (usado por Flutter para generar PDF/Excel)
     */
    public function exportData(Request $request)
    {
        $auth = $request->user();

        $validated = $request->validate([
            'type' => 'required|in:sales,products,clients,stock',
            'from_date' => 'nullable|date',
            'to_date' => 'nullable|date|after_or_equal:from_date',
            'period' => 'nullable|in:today,week,month,year',
            'branch_id' => 'nullable|exists:branches,id',
        ]);

        $type = $validated['type'];

        switch ($type) {
            case 'sales':
                return $this->exportSales($auth, $validated);
            case 'products':
                return $this->exportProducts($auth, $validated);
            case 'clients':
                return $this->exportClients($auth, $validated);
            case 'stock':
                return $this->exportStock($auth, $validated);
        }
    }

    // Métodos privados auxiliares

    private function applyDateFilter($query, array $validated)
    {
        if (isset($validated['from_date'])) {
            $query->where('created_at', '>=', $validated['from_date']);
        }

        if (isset($validated['to_date'])) {
            $query->where('created_at', '<=', $validated['to_date'] . ' 23:59:59');
        }

        if (isset($validated['period'])) {
            $this->applyPeriodFilter($query, $validated['period']);
        }
    }

    private function applyPeriodFilter($query, string $period, string $column = 'created_at')
    {
        switch ($period) {
            case 'today':
                $query->whereDate($column, Carbon::today());
                break;
            case 'week':
                $query->whereBetween($column, [Carbon::now()->startOfWeek(), Carbon::now()->endOfWeek()]);
                break;
            case 'month':
                $query->whereMonth($column, Carbon::now()->month)
                      ->whereYear($column, Carbon::now()->year);
                break;
            case 'year':
                $query->whereYear($column, Carbon::now()->year);
                break;
        }
    }

    private function exportSales($auth, $validated)
    {
        $query = Order::availableFor($auth)
            ->where('status', 'completed')
            ->with(['client:id,name', 'items.product:id,name,price']);

        $this->applyDateFilter($query, $validated);

        if (isset($validated['branch_id'])) {
            $query->where('branch_id', $validated['branch_id']);
        }

        $orders = $query->orderBy('created_at', 'desc')->get();

        return response()->json([
            'success' => true,
            'data' => $orders->map(function ($order) {
                return [
                    'order_number' => $order->order_number ?? $order->id,
                    'date' => $order->created_at?->format('Y-m-d H:i:s'),
                    'client' => $order->customer_name ?? $order->client?->name ?? 'Sin cliente',
                    'total' => round((float) $order->total, 2),
                    'payment_status' => $order->payment_status,
                    'items_count' => $order->items->count(),
                ];
            }),
        ]);
    }

    private function exportProducts($auth, $validated)
    {
        $query = Product::availableFor($auth)->where('is_active', true);

        if (isset($validated['branch_id'])) {
            $query->where('branch_id', $validated['branch_id']);
        }

        $products = $query->orderBy('name')->get();

        return response()->json([
            'success' => true,
            'data' => $products->map(function ($product) {
                return [
                    'name' => $product->name,
                    'sku' => $product->sku,
                    'price' => round((float) $product->price, 2),
                    'stock' => (float) $product->stock,
                    'min_stock' => (float) $product->min_stock,
                    'stock_value' => round((float) ($product->stock * $product->price), 2),
                ];
            }),
        ]);
    }

    private function exportClients($auth, $validated)
    {
        $clients = Client::availableFor($auth)->orderBy('name')->get();

        return response()->json([
            'success' => true,
            'data' => $clients->map(function ($client) {
                return [
                    'name' => $client->name,
                    'email' => $client->email ?? '',
                    'phone' => $client->phone ?? '',
                    'address' => $client->address ?? '',
                ];
            }),
        ]);
    }

    private function exportStock($auth, $validated)
    {
        return $this->stockReport($validated);
    }
}
