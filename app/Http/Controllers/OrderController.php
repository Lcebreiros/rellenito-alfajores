<?php

namespace App\Http\Controllers;

use App\Models\Order;
use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

class OrderController extends Controller
{
    /**
     * Historial de pedidos con filtros y paginación.
     */
    public function index(Request $request)
    {
        [$query] = $this->buildOrdersQuery($request);

        $sort = (string) $request->input('sort', 'newest');

        $orders = $query
            ->when($sort === 'oldest', fn ($q) => $q->orderBy('created_at'))
            ->when($sort === 'total_desc', fn ($q) => $q->orderByDesc('total'))
            ->when($sort === 'total_asc', fn ($q) => $q->orderBy('total'))
            ->when($sort === 'newest' || !in_array($sort, ['oldest','total_desc','total_asc'], true), fn ($q) => $q->orderByDesc('created_at'))
            ->paginate(20)
            ->withQueryString();

        return view('orders.index', compact('orders'));
    }

    /**
     * Exportación CSV/Excel con filtros actuales.
     * - CSV: streaming + BOM UTF-8 + separador ';'
     * - Excel: genera archivo temporal (HTML table) y lo descarga (sin streaming con echo)
     */
    public function downloadReport(Request $request)
    {
        @set_time_limit(0);
        @ini_set('output_buffering', 'off');
        @ini_set('zlib.output_compression', '0');

        [$baseQuery, $meta] = $this->buildOrdersQuery($request);

        $filenameBase = 'pedidos_' . now()->format('Ymd_His');
        if ($meta['period']) {
            $filenameBase .= '_' . $meta['period'];
        } elseif ($meta['from'] || $meta['to']) {
            $filenameBase .= '_' . ($meta['from']?->format('Y-m-d') ?? 'inicio') . '_a_' . ($meta['to']?->format('Y-m-d') ?? 'fin');
        }

        $format = strtolower((string) $request->input('format', 'csv'));
        if (!in_array($format, ['csv','excel'], true)) $format = 'csv';

        if ($format === 'csv') {
            return $this->streamCsv($baseQuery->clone(), $filenameBase . '.csv', $meta['has_note']);
        }

        return $this->excelToTempFileAndDownload($baseQuery->clone(), $filenameBase . '.xls', $meta['has_note']);
    }

    /* ---------------------------------------------------------------------
     |  Helpers de exportación
     * -------------------------------------------------------------------*/

    /** CSV en streaming con BOM UTF-8 y separador ';' (excel-friendly es-AR) */
    private function streamCsv($query, string $filename, bool $hasNote)
    {
        $headers = [
            'Content-Type'        => 'text/csv; charset=UTF-8',
            'Content-Disposition' => "attachment; filename=\"{$filename}\"",
            'Cache-Control'       => 'no-store, no-cache, must-revalidate',
        ];

        return response()->streamDownload(function () use ($query, $hasNote) {
            try {
                while (ob_get_level() > 0) { @ob_end_clean(); }

                $out = fopen('php://output', 'w');
                if (!$out) throw new \RuntimeException('No se pudo abrir php://output');

                // BOM UTF-8 (Excel Windows)
                fwrite($out, chr(0xEF) . chr(0xBB) . chr(0xBF));

                // Encabezados (dejamos "Notas" aunque no exista la columna)
                fputcsv($out, ['ID','Fecha','Cliente','Estado','Items','Total','Notas'], ';');

                $columns = $this->orderExportColumns($hasNote);

                $query->select($columns)
                      ->orderBy('id')
                      ->chunkById(1000, function ($orders) use ($out, $hasNote) {
                          foreach ($orders as $o) {
                              $note = $hasNote ? (string)($o->note ?? '') : '';
                              fputcsv($out, [
                                  (int) $o->id,
                                  $o->created_at ? $o->created_at->format('d/m/Y H:i') : '',
                                  optional($o->client)->name ?? 'Sin cliente',
                                  ucfirst((string) $o->status),
                                  (int) ($o->items_qty ?? 0),
                                  number_format((float) $o->total, 2, ',', '.'),
                                  $note,
                              ], ';');
                          }
                          fflush($out);
                      });

                fclose($out);
            } catch (\Throwable $e) {
                Log::error('CSV stream error', ['msg' => $e->getMessage()]);
                $fallback = fopen('php://output', 'w');
                if ($fallback) {
                    fputcsv($fallback, ['ERROR','Ocurrió un problema generando el CSV. Revise logs.'], ';');
                    fclose($fallback);
                }
            }
        }, $filename, $headers);
    }

    /**
     * Excel robusto con archivo temporal (HTML table).
     */
    private function excelToTempFileAndDownload($query, string $downloadName, bool $hasNote)
    {
        $tmpDir = storage_path('app/tmp');
        if (!is_dir($tmpDir)) { @mkdir($tmpDir, 0775, true); }
        $tmpPath = tempnam($tmpDir, 'xls_');
        if ($tmpPath === false) {
            return $this->excelHtmlResponseNoStream($query, $downloadName, $hasNote);
        }

        try {
            $head = '<html><head><meta charset="utf-8"><style>
                        table{border-collapse:collapse;width:100%}
                        th,td{border:1px solid #ccc;padding:6px}
                        th{background:#f5f5f5}
                        .num{text-align:right}.center{text-align:center}
                     </style></head><body>';
            $head .= '<h3>Reporte de Pedidos - ' . now()->format('d/m/Y H:i') . '</h3>';
            $head .= '<table><thead><tr>
                        <th>ID</th><th>Fecha</th><th>Cliente</th><th>Estado</th>
                        <th>Items</th><th>Total</th><th>Notas</th>
                      </tr></thead><tbody>';
            file_put_contents($tmpPath, $head, LOCK_EX);

            $totalSum = 0; $count = 0;
            $columns = $this->orderExportColumns($hasNote);

            $query->select($columns)
                  ->orderBy('id')
                  ->chunkById(1000, function ($orders) use (&$totalSum, &$count, $tmpPath, $hasNote) {
                      $chunkHtml = '';
                      foreach ($orders as $o) {
                          $totalSum += (float) $o->total; $count++;
                          $note = $hasNote ? (string)($o->note ?? '') : '';
                          $chunkHtml .= '<tr>'
                                      . '<td class="center">'.(int)$o->id.'</td>'
                                      . '<td>'.($o->created_at ? $o->created_at->format('d/m/Y H:i') : '').'</td>'
                                      . '<td>'.e(optional($o->client)->name ?? 'Sin cliente').'</td>'
                                      . '<td class="center">'.e(ucfirst((string)$o->status)).'</td>'
                                      . '<td class="num">'.(int)($o->items_qty ?? 0).'</td>'
                                      . '<td class="num">$'.number_format((float)$o->total, 2, ',', '.').'</td>'
                                      . '<td>'.e($note).'</td>'
                                      . '</tr>';
                      }
                      file_put_contents($tmpPath, $chunkHtml, FILE_APPEND | LOCK_EX);
                  });

            $tail = '</tbody></table>'
                  . '<p><strong>Total de pedidos:</strong> '.(int)$count.'</p>'
                  . '<p><strong>Suma total:</strong> $'.number_format((float)$totalSum, 2, ',', '.').'</p>'
                  . '</body></html>';
            file_put_contents($tmpPath, $tail, FILE_APPEND | LOCK_EX);

            return response()->download($tmpPath, $downloadName, [
                'Content-Type'        => 'application/vnd.ms-excel; charset=UTF-8',
                'Cache-Control'       => 'no-store, no-cache, must-revalidate',
            ])->deleteFileAfterSend(true);

        } catch (\Throwable $e) {
            Log::error('XLS temp-file error', ['msg' => $e->getMessage()]);
            @unlink($tmpPath);
            return $this->excelHtmlResponseNoStream($query, $downloadName, $hasNote);
        }
    }

    /** Fallback: Excel HTML directo (no streaming). */
    private function excelHtmlResponseNoStream($query, string $downloadName, bool $hasNote)
    {
        $html = '<html><head><meta charset="utf-8"><style>
                    table{border-collapse:collapse;width:100%}
                    th,td{border:1px solid #ccc;padding:6px}
                    th{background:#f5f5f5}
                    .num{text-align:right}.center{text-align:center}
                 </style></head><body>';

        $html .= '<h3>Reporte de Pedidos - ' . now()->format('d/m/Y H:i') . '</h3>';
        $html .= '<table><thead><tr>
                    <th>ID</th><th>Fecha</th><th>Cliente</th><th>Estado</th><th>Items</th><th>Total</th><th>Notas</th>
                  </tr></thead><tbody>';

        $totalSum = 0; $count = 0;
        $columns = $this->orderExportColumns($hasNote);

        $query->select($columns)
              ->orderBy('id')
              ->chunkById(1000, function ($orders) use (&$html, &$totalSum, &$count, $hasNote) {
                  foreach ($orders as $o) {
                      $totalSum += (float) $o->total; $count++;
                      $note = $hasNote ? (string)($o->note ?? '') : '';
                      $html .= '<tr>'
                             . '<td class="center">'.(int)$o->id.'</td>'
                             . '<td>'.($o->created_at ? $o->created_at->format('d/m/Y H:i') : '').'</td>'
                             . '<td>'.e(optional($o->client)->name ?? 'Sin cliente').'</td>'
                             . '<td class="center">'.e(ucfirst((string)$o->status)).'</td>'
                             . '<td class="num">'.(int)($o->items_qty ?? 0).'</td>'
                             . '<td class="num">$'.number_format((float)$o->total, 2, ',', '.').'</td>'
                             . '<td>'.e($note).'</td>'
                             . '</tr>';
                  }
              });

        $html .= '</tbody></table>';
        $html .= '<p><strong>Total de pedidos:</strong> '.(int)$count.'</p>';
        $html .= '<p><strong>Suma total:</strong> $'.number_format((float)$totalSum, 2, ',', '.').'</p>';
        $html .= '</body></html>';

        return response($html, 200, [
            'Content-Type'        => 'application/vnd.ms-excel; charset=UTF-8',
            'Content-Disposition' => "attachment; filename=\"{$downloadName}\"",
            'Cache-Control'       => 'no-store, no-cache, must-revalidate',
        ]);
    }

    /** Columnas a seleccionar en exports (depende de si existe orders.note). */
    private function orderExportColumns(bool $hasNote): array
    {
        $cols = ['id','client_id','created_at','total','status'];
        if ($hasNote) $cols[] = 'note';
        return $cols;
    }

    /* ---------------------------------------------------------------------
     |  Construcción de query (reutilizable entre index y export)
     * -------------------------------------------------------------------*/

private function buildOrdersQuery(Request $request): array
{
    $q        = trim((string) $request->input('q', ''));
    $status   = (string) $request->input('status', '');
    $from     = $request->date('from');
    $to       = $request->date('to');
    $period   = (string) $request->input('period', '');
    $clientQ  = trim((string) $request->input('client', ''));
    $clientId = $request->input('client_id');

    $hasNote       = Schema::hasColumn('orders', 'note');
    $hasCustName   = Schema::hasColumn('orders', 'customer_name');
    $hasCustEmail  = Schema::hasColumn('orders', 'customer_email');
    $hasCustPhone  = Schema::hasColumn('orders', 'customer_phone');

    $validStatuses = [
        Order::STATUS_DRAFT,
        Order::STATUS_COMPLETED,
        Order::STATUS_CANCELED,
    ];

    [$dateFrom, $dateTo] = $this->resolveDateRange($period, $from, $to);

    $idGuess = null;
    if ($q !== '') {
        $digits = preg_replace('/\D+/', '', $q);
        if ($digits !== '' && ctype_digit($digits)) $idGuess = (int) $digits;
    }

    $query = Order::query()
        ->with(['client:id,name,email,phone'])
        ->withSum('items as items_qty', 'quantity')

        // 🔎 Búsqueda libre
        ->when($q !== '', function ($query) use ($q, $idGuess, $hasNote, $hasCustName, $hasCustEmail, $hasCustPhone) {
            $query->where(function ($w) use ($q, $idGuess, $hasNote, $hasCustName, $hasCustEmail, $hasCustPhone) {
                if (!is_null($idGuess)) { $w->orWhere('id', $idGuess); }
                if ($hasNote) { $w->orWhere('note', 'like', "%{$q}%"); }
                if ($hasCustName)  { $w->orWhere('customer_name',  'like', "%{$q}%"); }
                if ($hasCustEmail) { $w->orWhere('customer_email', 'like', "%{$q}%"); }
                if ($hasCustPhone) { $w->orWhere('customer_phone', 'like', "%{$q}%"); }
                $w->orWhereHas('client', fn ($cq) =>
                    $cq->where(fn ($cqw) =>
                        $cqw->where('name','like',"%{$q}%")
                            ->orWhere('email','like',"%{$q}%")
                            ->orWhere('phone','like',"%{$q}%")
                    )
                );
            });
        })

        // Cliente
        ->when($clientQ !== '', fn ($q2) => $q2->whereHas('client', fn ($c) => $c->where('name', 'like', "%{$clientQ}%")))
        ->when(!empty($clientId), fn ($q2) => $q2->where('client_id', (int) $clientId))

        // ✅ Estado: por defecto EXCLUIR borradores; si se pide un estado, respetarlo
        ->when($status === '' , fn ($q2) => $q2->where('status', '!=', Order::STATUS_DRAFT))
        ->when($status !== '' && in_array($status, $validStatuses, true), fn ($q2) => $q2->where('status', $status))

        // Fechas
        ->when($dateFrom, fn ($q2) => $q2->where('created_at', '>=', $dateFrom))
        ->when($dateTo,   fn ($q2) => $q2->where('created_at', '<=', $dateTo));

    return [$query, [
        'from'      => $dateFrom,
        'to'        => $dateTo,
        'period'    => $period,
        'has_note'  => $hasNote,
    ]];
}



    private function resolveDateRange(?string $period, ?Carbon $from, ?Carbon $to): array
    {
        if ($from || $to) {
            return [
                $from ? $from->copy()->startOfDay() : null,
                $to   ? $to->copy()->endOfDay()     : null,
            ];
        }

        $today = now();

        return match ($period) {
            'today'        => [$today->copy()->startOfDay(), $today->copy()->endOfDay()],
            'yesterday'    => [$today->copy()->subDay()->startOfDay(), $today->copy()->subDay()->endOfDay()],
            'this_week'    => [$today->copy()->startOfWeek(), $today->copy()->endOfWeek()],
            'last_week'    => [$today->copy()->subWeek()->startOfWeek(), $today->copy()->subWeek()->endOfWeek()],
            'this_month'   => [$today->copy()->startOfMonth(), $today->copy()->endOfMonth()],
            'last_month'   => [$today->copy()->subMonth()->startOfMonth(), $today->copy()->subMonth()->endOfMonth()],
            'last_7_days'  => [$today->copy()->subDays(6)->startOfDay(), $today->copy()->endOfDay()],
            'last_30_days' => [$today->copy()->subDays(29)->startOfDay(), $today->copy()->endOfDay()],
            default        => [null, null],
        };
    }

    /* ---------------------------------------------------------------------
     |  Resto (creación / show)
     * -------------------------------------------------------------------*/

    public function create(Request $request)
    {
        $orderId = $request->session()->get('draft_order_id');
        $order   = $orderId ? Order::with('client')->find($orderId) : null;

        if (!$order || $order->status !== Order::STATUS_DRAFT) {
            $order = Order::create(); // STATUS_DRAFT por defecto
            $request->session()->put('draft_order_id', $order->id);
        }

        $products = Product::query()
            ->when(method_exists(Product::class, 'scopeActive'), fn ($q) => $q->active(), fn ($q) => $q)
            ->orderBy('name')
            ->paginate(24)
            ->withQueryString();

        $order->load(['items.product','client']);

        return view('orders.create', compact('order', 'products'));
    }

    public function show(Order $order)
    {
        $order->load(['items.product','client']);
        return view('orders.show', compact('order'));
    }
public function edit(Order $order)
{
    // Solo el propietario puede editar
    if ($order->user_id !== auth()->id()) {
        abort(403);
    }

    $order->load('items.product');

    $products = Product::all(); // o filtrados según tu lógica

    $order->load('client', 'items.product');

    // Pasamos también la lista de clientes para el select
    $clients = \App\Models\Client::all();

    return view('orders.edit', compact('order', 'clients', 'products'));
}

public function update(Request $request, Order $order)
{
    // Solo el propietario puede actualizar
    if ($order->user_id !== auth()->id()) {
        abort(403);
    }

    // Validación
$data = $request->validate([
    'name'            => 'required|string|max:255',
    'email'           => 'nullable|email|max:255',
    'phone'           => 'nullable|string|max:50',
    'address'         => 'nullable|string|max:255',
    'items_json'      => 'required|string',
]);

DB::transaction(function() use ($order, $data) {
    // Actualizamos la orden
    $order->update([
        'customer_name'    => $data['name'],      // mapeo al campo de la tabla orders
        'customer_email'   => $data['email'],
        'customer_phone'   => $data['phone'],
        'shipping_address' => $data['address'],
    ]);

    // Decodificamos y reemplazamos los items
    $items = json_decode($data['items_json'], true);
    $order->items()->delete();

    foreach ($items as $i) {
        $order->items()->create([
            'product_id' => $i['id'],
            'name'       => $i['name'],
            'quantity'   => $i['quantity'],
            'unit_price' => $i['unit_price'],
            'subtotal'   => $i['quantity'] * $i['unit_price'],
            'user_id'    => auth()->id(),
        ]);
    }

    $order->recalcTotal();
    $order->save();
});


    return redirect()->route('orders.index')->with('ok', 'Pedido actualizado correctamente.');
}


    public function destroy(Order $order)
    {
        if ($order->user_id !== auth()->id()) {
            abort(403);
        }

        DB::transaction(function() use ($order) {
            // eliminar items primero si corresponde
            $order->items()->delete();
            $order->delete();
        });

        return redirect()->route('orders.index')->with('ok','Pedido eliminado correctamente.');
    }
    public function bulkDelete(Request $request)
{
    $request->validate([
        'ids' => 'required|array',
        'ids.*' => 'integer|exists:orders,id',
    ]);

    // Solo eliminar pedidos del usuario logueado (ajustar según tu regla)
    Order::whereIn('id', $request->ids)
         ->where('user_id', auth()->id())
         ->delete();

    return response()->json(['success' => true]);
}

}
