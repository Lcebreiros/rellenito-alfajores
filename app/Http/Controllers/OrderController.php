<?php

namespace App\Http\Controllers;

use App\Models\Order;
use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Http\Response;

class OrderController extends Controller
{
    /**
     * Listado (historial) de pedidos con filtros.
     */
    public function index(Request $request)
    {
        $q      = trim((string) $request->input('q', ''));
        $status = (string) $request->input('status', '');
        $from   = $request->date('from'); // devuelve Carbon|null
        $to     = $request->date('to');   // devuelve Carbon|null
        $period = (string) $request->input('period', ''); // nuevo filtro por período
        $sort   = (string) $request->input('sort', 'newest'); // ordenamiento

        // Estados válidos (desde el modelo)
        $validStatuses = [
            Order::STATUS_DRAFT,
            Order::STATUS_COMPLETED,
            Order::STATUS_CANCELED,
        ];

        $orders = Order::query()
            // Suma total de unidades del pedido (SUM de order_items.quantity)
            ->withSum('items as items_qty', 'quantity')

            // Filtro de búsqueda: por ID exacto o por nota (si existe la col 'note')
            ->when($q !== '', function ($query) use ($q) {
                $query->where(function ($w) use ($q) {
                    // si es número, permitimos buscar por ID exacto
                    if (ctype_digit($q)) {
                        $w->orWhere('id', (int) $q);
                    }
                    // si existe columna 'note' en tu schema
                    $w->orWhere('note', 'like', '%' . $q . '%');
                    // Si más adelante agregás columnas:
                    // $w->orWhere('guest_name', 'like', '%' . $q . '%');
                    // $w->orWhere('customer_name', 'like', '%' . $q . '%');
                });
            })

            // Filtro por estado (solo si es uno válido)
            ->when($status !== '' && in_array($status, $validStatuses, true), function ($query) use ($status) {
                $query->where('status', $status);
            })

            // Filtro por período predefinido
            ->when($period !== '', function ($query) use ($period) {
                switch ($period) {
                    case 'today':
                        $query->whereDate('created_at', today());
                        break;
                    case 'yesterday':
                        $query->whereDate('created_at', yesterday());
                        break;
                    case 'this_week':
                        $query->whereBetween('created_at', [
                            now()->startOfWeek(),
                            now()->endOfWeek()
                        ]);
                        break;
                    case 'last_week':
                        $query->whereBetween('created_at', [
                            now()->subWeek()->startOfWeek(),
                            now()->subWeek()->endOfWeek()
                        ]);
                        break;
                    case 'this_month':
                        $query->whereMonth('created_at', now()->month)
                              ->whereYear('created_at', now()->year);
                        break;
                    case 'last_month':
                        $lastMonth = now()->subMonth();
                        $query->whereMonth('created_at', $lastMonth->month)
                              ->whereYear('created_at', $lastMonth->year);
                        break;
                    case 'last_7_days':
                        $query->where('created_at', '>=', now()->subDays(7));
                        break;
                    case 'last_30_days':
                        $query->where('created_at', '>=', now()->subDays(30));
                        break;
                }
            })

            // Rango de fechas personalizado (tiene prioridad sobre período)
            ->when($from && !$period, fn ($q2) => $q2->where('created_at', '>=', Carbon::parse($from)->startOfDay()))
            ->when($to && !$period,   fn ($q2) => $q2->where('created_at', '<=', Carbon::parse($to)->endOfDay()))

            // Ordenamiento
            ->when($sort === 'oldest', fn ($q) => $q->orderBy('created_at'))
            ->when($sort === 'total_desc', fn ($q) => $q->orderByDesc('total'))
            ->when($sort === 'total_asc', fn ($q) => $q->orderBy('total'))
            ->when($sort === 'newest' || !in_array($sort, ['oldest', 'total_desc', 'total_asc']), fn ($q) => $q->orderByDesc('created_at'))

            ->paginate(20)
            ->withQueryString();

        return view('orders.index', compact('orders'));
    }

    /**
     * Descargar reporte de pedidos en formato CSV
     */
    public function downloadReport(Request $request)
    {
        $q      = trim((string) $request->input('q', ''));
        $status = (string) $request->input('status', '');
        $from   = $request->date('from');
        $to     = $request->date('to');
        $period = (string) $request->input('period', '');
        $format = $request->input('format', 'csv'); // csv o excel

        // Estados válidos
        $validStatuses = [
            Order::STATUS_DRAFT,
            Order::STATUS_COMPLETED,
            Order::STATUS_CANCELED,
        ];

        // Misma query que en index pero sin paginación
        $orders = Order::query()
            ->withSum('items as items_qty', 'quantity')
            ->with(['items.product'])

            ->when($q !== '', function ($query) use ($q) {
                $query->where(function ($w) use ($q) {
                    if (ctype_digit($q)) {
                        $w->orWhere('id', (int) $q);
                    }
                    $w->orWhere('note', 'like', '%' . $q . '%');
                });
            })

            ->when($status !== '' && in_array($status, $validStatuses, true), function ($query) use ($status) {
                $query->where('status', $status);
            })

            ->when($period !== '', function ($query) use ($period) {
                switch ($period) {
                    case 'today':
                        $query->whereDate('created_at', today());
                        break;
                    case 'yesterday':
                        $query->whereDate('created_at', yesterday());
                        break;
                    case 'this_week':
                        $query->whereBetween('created_at', [
                            now()->startOfWeek(),
                            now()->endOfWeek()
                        ]);
                        break;
                    case 'last_week':
                        $query->whereBetween('created_at', [
                            now()->subWeek()->startOfWeek(),
                            now()->subWeek()->endOfWeek()
                        ]);
                        break;
                    case 'this_month':
                        $query->whereMonth('created_at', now()->month)
                              ->whereYear('created_at', now()->year);
                        break;
                    case 'last_month':
                        $lastMonth = now()->subMonth();
                        $query->whereMonth('created_at', $lastMonth->month)
                              ->whereYear('created_at', $lastMonth->year);
                        break;
                    case 'last_7_days':
                        $query->where('created_at', '>=', now()->subDays(7));
                        break;
                    case 'last_30_days':
                        $query->where('created_at', '>=', now()->subDays(30));
                        break;
                }
            })

            ->when($from && !$period, fn ($q2) => $q2->where('created_at', '>=', Carbon::parse($from)->startOfDay()))
            ->when($to && !$period,   fn ($q2) => $q2->where('created_at', '<=', Carbon::parse($to)->endOfDay()))

            ->orderByDesc('created_at')
            ->get();

        // Generar nombre del archivo
        $filename = 'pedidos_' . now()->format('Y-m-d_H-i-s');
        
        if ($period) {
            $filename .= '_' . $period;
        } elseif ($from || $to) {
            $filename .= '_' . ($from ? $from->format('Y-m-d') : 'inicio') . '_a_' . ($to ? $to->format('Y-m-d') : 'fin');
        }

        if ($format === 'excel') {
            return $this->downloadExcelReport($orders, $filename);
        }

        return $this->downloadCsvReport($orders, $filename);
    }

    /**
     * Generar reporte CSV
     */
    private function downloadCsvReport($orders, $filename)
    {
        $csv = [];
        
        // Encabezados
        $csv[] = [
            'ID',
            'Fecha',
            'Cliente',
            'Estado',
            'Items',
            'Total',
            'Notas'
        ];

        // Datos
        foreach ($orders as $order) {
            $csv[] = [
                $order->id,
                $order->created_at ? $order->created_at->format('d/m/Y H:i') : '',
                $order->guest_name ?? $order->customer_name ?? 'Sin cliente',
                ucfirst($order->status),
                $order->items_qty ?? 0,
                number_format($order->total, 2, ',', '.'),
                $order->note ?? ''
            ];
        }

        // Generar contenido CSV
        $output = '';
        foreach ($csv as $row) {
            $output .= implode(';', array_map(function($field) {
                return '"' . str_replace('"', '""', $field) . '"';
            }, $row)) . "\n";
        }

        return response($output)
            ->header('Content-Type', 'text/csv; charset=utf-8')
            ->header('Content-Disposition', 'attachment; filename="' . $filename . '.csv"')
            ->header('Pragma', 'no-cache')
            ->header('Cache-Control', 'must-revalidate, post-check=0, pre-check=0')
            ->header('Expires', '0');
    }

    /**
     * Generar reporte Excel (HTML que Excel puede abrir)
     */
    private function downloadExcelReport($orders, $filename)
    {
        $html = '
        <html>
        <head>
            <meta charset="utf-8">
            <style>
                table { border-collapse: collapse; width: 100%; }
                th, td { border: 1px solid #ccc; padding: 8px; text-align: left; }
                th { background-color: #f2f2f2; font-weight: bold; }
                .number { text-align: right; }
                .center { text-align: center; }
            </style>
        </head>
        <body>
            <h2>Reporte de Pedidos - ' . now()->format('d/m/Y H:i') . '</h2>
            <table>
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Fecha</th>
                        <th>Cliente</th>
                        <th>Estado</th>
                        <th>Items</th>
                        <th>Total</th>
                        <th>Notas</th>
                    </tr>
                </thead>
                <tbody>';

        foreach ($orders as $order) {
            $html .= '<tr>
                <td class="center">' . $order->id . '</td>
                <td>' . ($order->created_at ? $order->created_at->format('d/m/Y H:i') : '') . '</td>
                <td>' . htmlspecialchars($order->guest_name ?? $order->customer_name ?? 'Sin cliente') . '</td>
                <td class="center">' . ucfirst($order->status) . '</td>
                <td class="number">' . ($order->items_qty ?? 0) . '</td>
                <td class="number">$' . number_format($order->total, 2, ',', '.') . '</td>
                <td>' . htmlspecialchars($order->note ?? '') . '</td>
            </tr>';
        }

        $html .= '</tbody>
            </table>
            <p><strong>Total de pedidos:</strong> ' . $orders->count() . '</p>
            <p><strong>Suma total:</strong> $' . number_format($orders->sum('total'), 2, ',', '.') . '</p>
        </body>
        </html>';

        return response($html)
            ->header('Content-Type', 'application/vnd.ms-excel')
            ->header('Content-Disposition', 'attachment; filename="' . $filename . '.xls"')
            ->header('Pragma', 'no-cache')
            ->header('Cache-Control', 'must-revalidate, post-check=0, pre-check=0')
            ->header('Expires', '0');
    }

    /**
     * Pantalla de creación de pedidos (muestra productos + sidebar Livewire).
     * Crea o recupera el borrador de la sesión.
     */
    public function create(Request $request)
    {
        // Recuperar borrador de la sesión, o crear uno nuevo si no existe/ya no es draft
        $orderId = $request->session()->get('draft_order_id');
        $order   = $orderId ? Order::find($orderId) : null;

        if (!$order || $order->status !== Order::STATUS_DRAFT) {
            $order = Order::create(); // status DRAFT por defecto en el modelo
            $request->session()->put('draft_order_id', $order->id);
        }

        // Productos paginados para elegir (ajustá el scope 'active' según tu modelo)
        $products = Product::query()
            ->when(method_exists(Product::class, 'scopeActive'), fn ($q) => $q->active(), fn ($q) => $q) // fallback
            ->orderBy('name')
            ->paginate(24)
            ->withQueryString();

        // Traemos items por si la vista quiere mostrar el state inicial (no obligatorio)
        $order->load('items.product');

        return view('orders.create', compact('order', 'products'));
    }

    /**
     * Ver un pedido puntual.
     */
    public function show(Order $order)
    {
        $order->load(['items.product']);
        // Podés querer calcular/asegurar total actualizado:
        // $order->recalcTotal(); // si tu modelo tiene este método y lo preferís on-demand

        return view('orders.show', compact('order'));
    }
}