<?php

namespace App\Http\Controllers;

use App\Services\StockService;
use App\Models\Order;
use App\Models\Product;
use App\Models\User; // AGREGADO: Import del modelo User
use App\Enums\OrderStatus;
use Illuminate\Http\Request;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;
use DomainException; // AGREGADO: Import de DomainException
use Illuminate\Support\Str;

class OrderController extends Controller
{
    /**
     * Historial de pedidos con filtros y paginación.
     */
public function index(Request $request)
{
    $user = $request->user();

    [$query] = $this->buildOrdersQuery($request);

    // Usar scope centralizado que respeta jerarquía real (master/company/admin/user)
    $query->availableFor($user);

    // Filtro por usuario (solo master)
    if (method_exists($user, 'isMaster') && $user->isMaster() && $request->filled('user_id')) {
        $query->where('user_id', (int) $request->input('user_id'));
    }

    $sort = (string) $request->input('sort', 'newest');

    $orders = $query
        ->when($sort === 'oldest', fn ($q) => $q->orderBy('created_at'))
        ->when($sort === 'total_desc', fn ($q) => $q->orderByDesc('total'))
        ->when($sort === 'total_asc', fn ($q) => $q->orderBy('total'))
        ->when($sort === 'newest' || !in_array($sort, ['oldest','total_desc','total_asc'], true), fn ($q) => $q->orderByDesc('created_at'))
        ->with(['client','branch','user:id,name']) // cargar relaciones
        ->paginate(20)
        ->withQueryString();

    $isCompany = method_exists($user, 'isCompany') ? $user->isCompany() : false;
    $isMaster = method_exists($user, 'isMaster') ? $user->isMaster() : false;
    return view('orders.index', compact('orders', 'user', 'isCompany', 'isMaster'));
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

    private function statusValue($status): string
    {
        // Normaliza enum App\Enums\OrderStatus a su representación string
        if ($status instanceof \BackedEnum) {
            return (string) $status->value;
        }
        if ($status instanceof \UnitEnum) {
            return (string) $status->name;
        }
        return is_scalar($status) ? (string) $status : (string) ($status ?? '');
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
                                  ucfirst($this->statusValue($o->status)),
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
                                      . '<td class="center">'.e(ucfirst($this->statusValue($o->status))).'</td>'
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

    /**
     * Importar pedidos desde CSV (fecha, producto, cantidad)
     */
    public function importCsv(Request $request): RedirectResponse
    {
        $actor = $request->user();
        // Resolver owner (branch o empresa)
        if (method_exists($actor, 'isCompany') && $actor->isCompany()) {
            $owner = $actor; // empresa sin sucursal
        } elseif (method_exists($actor, 'isAdmin') && $actor->isAdmin()) {
            $owner = $actor;
        } else {
            $owner = $actor->parent ?: $actor;
        }

        $companyId = Order::findRootCompanyId($actor) ?? $actor->id;
        $path = null;
        if ($request->hasFile('csv')) {
            $request->validate(['csv' => 'file|mimes:csv,txt']);
            $path = $request->file('csv')->getRealPath();
        } elseif ($request->filled('stored_path')) {
            $stored = $request->string('stored_path');
            $full = storage_path('app/' . ltrim($stored, '/'));
            if (is_file($full)) { $path = $full; }
        }
        if (!$path) {
            return back()->withErrors(['csv' => 'No se recibió archivo ni ruta válida'])->withInput();
        }

        if (!is_readable($path)) {
            return back()->withErrors(['csv' => 'No se pudo leer el archivo'])->withInput();
        }

        $fh = fopen($path, 'r');
        if (!$fh) {
            return back()->withErrors(['csv' => 'No se pudo abrir el archivo'])->withInput();
        }

        $ok = 0; $fail = 0; $errors = [];
        $line = 0;

        $expected = ['dulce de leche','trufa','frutos','coco','coñac','ganache','marroc','pistacho'];
        $delim = ','; $headersNorm = [];
        $colIndex = [];

        // Leer primer línea no vacía para detectar encabezado
        while (($raw = fgets($fh)) !== false) {
            $line++;
            if (trim($raw) === '') continue;
            $delim = Str::contains($raw, ';') ? ';' : ',';
            $cols = str_getcsv($raw, $delim);
            if (!isset($cols[0])) { continue; }
            $cols[0] = preg_replace('/^\xEF\xBB\xBF/', '', $cols[0]);
            $headersNorm = array_map(fn($v)=>mb_strtolower(trim((string)$v), 'UTF-8'), $cols);
            // Si primer columna dice 'fecha', tomamos como encabezado
            if (!empty($headersNorm) && Str::contains($headersNorm[0], 'fecha')) {
                // mapear columnas esperadas
                foreach ($expected as $name) {
                    $idx = array_search($name, $headersNorm, true);
                    if ($idx !== false) { $colIndex[$name] = $idx; }
                }
                break; // siguiente líneas son datos
            } else {
                // No hay encabezado; asumir orden fijo: fecha + expected
                $colIndex = collect($expected)->mapWithKeys(fn($n, $i)=>[$n => $i+1])->all();
                // procesar esta línea como primera de datos
                rewind($fh);
                $line = 0; // reset contador
                break;
            }
        }

        // Cache de productos encontrados por nombre normalizado
        $productCache = [];

        while (($raw = fgets($fh)) !== false) {
            $line++;
            $trim = trim($raw);
            if ($trim === '') continue;
            $cols = str_getcsv($raw, $delim);
            if (count($cols) === 0) continue;

            $dateStr = trim((string)($cols[0] ?? ''));
            if (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $dateStr)) {
                $fail++; $errors[] = "Línea {$line}: fecha inválida (YYYY-MM-DD)"; continue;
            }

            $items = [];
            $rowErrors = [];

            foreach ($expected as $name) {
                $idx = $colIndex[$name] ?? null;
                if ($idx === null || !isset($cols[$idx])) continue;
                $qty = (int) trim((string)$cols[$idx]);
                if ($qty <= 0) continue;

                $norm = mb_strtolower($name, 'UTF-8');
                if (!isset($productCache[$norm])) {
                    // Buscar producto por nombre o SKU en el ámbito
                    $isCompanyOwner = method_exists($owner, 'isCompany') && $owner->isCompany();
                    $base = Product::query();
                    if ($isCompanyOwner) { $base->where('company_id', $owner->id); }
                    else { $base->where('branch_id', $owner->id); }

                    $found = (clone $base)->whereRaw('LOWER(name) = ?', [$norm])->first();
                    if (!$found) {
                        $found = (clone $base)->whereRaw('LOWER(sku) = ?', [$norm])->first();
                    }
                    if (!$found && !$isCompanyOwner) {
                        $rootCompanyId = Order::findRootCompanyId($owner) ?? $companyId;
                        if ($rootCompanyId) {
                            $companyScope = Product::where('company_id', $rootCompanyId);
                            $found = (clone $companyScope)->whereRaw('LOWER(name) = ?', [$norm])->first();
                            if (!$found) {
                                $found = (clone $companyScope)->whereRaw('LOWER(sku) = ?', [$norm])->first();
                            }
                        }
                    }
                    $productCache[$norm] = $found ?: false;
                }

                if (!$productCache[$norm]) {
                    $rowErrors[] = "producto '{$name}' no encontrado";
                    continue;
                }

                /** @var Product $prod */
                $prod = $productCache[$norm];
                $items[] = [
                    'product' => $prod,
                    'qty' => $qty,
                ];
            }

            if (empty($items)) {
                $fail++;
                $errors[] = "Línea {$line}: sin items válidos";
                continue;
            }

            try {
                DB::transaction(function () use ($actor, $owner, $companyId, $items, $dateStr) {
                    $order = Order::create([
                        'user_id' => $actor->id,
                        'branch_id' => $owner->id,
                        'company_id' => $companyId,
                        'status' => OrderStatus::DRAFT,
                        'sold_at' => $dateStr . ' 12:00:00',
                    ]);

                    foreach ($items as $it) {
                        $prod = $it['product'];
                        $qty  = $it['qty'];
                        $order->items()->create([
                            'user_id' => $actor->id,
                            'product_id' => $prod->id,
                            'quantity' => $qty,
                            'unit_price' => $prod->price,
                            'subtotal' => bcmul((string)$prod->price, (string)$qty, 2),
                        ]);
                    }

                    // Marcar como completado SIN afectar stock
                    $order->sold_at = \Carbon\Carbon::parse($dateStr);
                    $order->status = OrderStatus::COMPLETED;
                    $order->recalcTotal(true);
                    $order->save();
                }, 3);
                $ok++;
                if (!empty($rowErrors)) {
                    $errors[] = "Línea {$line}: " . implode('; ', $rowErrors);
                }
            } catch (\Throwable $e) {
                Log::error('CSV import order error', ['line' => $line, 'err' => $e->getMessage()]);
                $fail++; $errors[] = "Línea {$line}: " . $e->getMessage();
            }
        }

        fclose($fh);

        $msg = "Importación: {$ok} ok, {$fail} con error";
        if ($fail > 0) {
            return back()->with('error', $msg)->with('import_errors', $errors);
        }
        return back()->with('ok', $msg);
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

        // CORREGIDO: Usar enums en lugar de constantes
        $validStatuses = [
            OrderStatus::DRAFT->value,
            OrderStatus::COMPLETED->value,
            OrderStatus::CANCELED->value,
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

            // Búsqueda libre
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

            // CORREGIDO: Estado usando enum value
            ->when($status === '' , fn ($q2) => $q2->where('status', '!=', OrderStatus::DRAFT->value))
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

        // Usar enum striktamente en comparación (modelo castea a enum)
        if (!$order || $order->status !== OrderStatus::DRAFT) {
            $order = Order::create(); // STATUS_DRAFT por defecto
            $request->session()->put('draft_order_id', $order->id);
        }

        $auth = $request->user() ?? auth()->user();
        // Unificar lógica de disponibilidad con scope centralizado
        $productsQuery = (method_exists($auth,'isMaster') && $auth->isMaster())
            ? Product::query()
            : Product::availableFor($auth);

        $products = $productsQuery
            ->when(method_exists(Product::class, 'scopeActive'), fn ($q) => $q->active(), fn ($q) => $q)
            ->orderBy('name')
            ->paginate(24)
            ->withQueryString();

        $services = \App\Models\Service::availableFor($auth)
            ->when(method_exists(\App\Models\Service::class, 'scopeActive'), fn ($q) => $q->active(), fn ($q) => $q)
            ->orderBy('name')
            ->paginate(24, ['*'], 'services_page')
            ->withQueryString();

        $order->load(['items.product','items.service','client']);
        return view('orders.create', compact('order', 'products', 'services'));
    }

    public function show(Order $order)
    {
        $order->load(['items.product','items.service','client']);
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

        $order->load('client', 'items.product', 'items.service');

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

        DB::transaction(function() use (&$order, $data) {
            // Bloquear orden e items para evitar condiciones de carrera
            $order = Order::with('items.product')->lockForUpdate()->findOrFail($order->id);

            // Actualizamos datos del cliente/dirección
            $order->update([
                'customer_name'    => $data['name'],
                'customer_email'   => $data['email'],
                'customer_phone'   => $data['phone'],
                'shipping_address' => $data['address'],
            ]);

            // Inventario: si la orden está COMPLETED y se editan cantidades, ajustar diferencias al stock
            $newItemsArr = json_decode($data['items_json'], true) ?: [];

            // Mapas producto_id => cantidad
            $oldMap = [];
            foreach ($order->items as $it) {
                if ($it->product_id) {
                    $oldMap[(int)$it->product_id] = ($oldMap[(int)$it->product_id] ?? 0) + (int)$it->quantity;
                }
            }
            $newMap = [];
            foreach ($newItemsArr as $i) {
                $pid = isset($i['id']) ? (int) $i['id'] : null;
                if ($pid) {
                    $newMap[$pid] = ($newMap[$pid] ?? 0) + (int) ($i['quantity'] ?? 0);
                }
            }

            if ($order->status === \App\Enums\OrderStatus::COMPLETED) {
                $stock = app(\App\Services\StockService::class);
                // Para cada producto afectado, calcular delta y ajustar stock
                $allPids = array_unique(array_merge(array_keys($oldMap), array_keys($newMap)));
                foreach ($allPids as $pid) {
                    $oldQty = (int) ($oldMap[$pid] ?? 0);
                    $newQty = (int) ($newMap[$pid] ?? 0);
                    $delta  = $newQty - $oldQty;
                    if ($delta === 0) continue;

                    // Ajuste: si delta < 0 (reducción), devolver stock (+); si delta > 0, descontar más (-)
                    $product = \App\Models\Product::withoutGlobalScope('byUser')->find($pid);
                    if ($product) {
                        $stock->adjust($product, -$delta, 'order_edit', $order);
                    }
                }
            }

            // Reemplazar items por la nueva lista
            $order->items()->delete();
            foreach ($newItemsArr as $i) {
                $qty = (int) ($i['quantity'] ?? 0);
                $price = (float) ($i['unit_price'] ?? 0);
                $order->items()->create([
                    'product_id' => $i['id'] ?? null,
                    'name'       => $i['name'] ?? null,
                    'quantity'   => $qty,
                    'unit_price' => $price,
                    'subtotal'   => $qty * $price,
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

    public function cancelManual(int $orderId): void
    {
        DB::transaction(function () use ($orderId) {
            $order = Order::with('items.product')->lockForUpdate()->findOrFail($orderId);

            // Comparar con enum directamente
            if ($order->status !== OrderStatus::COMPLETED) {
                throw new DomainException('Solo se pueden cancelar pedidos completados.');
            }

            $stock = app(StockService::class);

            foreach ($order->items as $item) {
                if ($item->product) {
                    // Devolver el stock
                    $stock->adjust($item->product, $item->quantity, 'manual-cancel', $order);
                }
            }

            // Asignar enum directamente
            $order->status = OrderStatus::CANCELED;
            $order->save();
        });

        $this->dispatch('notify', type:'success', message:"Pedido #$orderId cancelado y stock devuelto.");
    }

    public function cancelManualHttp(int $orderId): RedirectResponse
    {
        try {
            DB::transaction(function () use ($orderId) {
                $order = \App\Models\Order::with('items.product')->lockForUpdate()->findOrFail($orderId);

                // Comparar con enum directamente
                if ($order->status !== OrderStatus::COMPLETED) {
                    throw new DomainException('Solo se pueden cancelar pedidos completados.');
                }

                $stock = app(StockService::class);

                foreach ($order->items as $item) {
                    if ($item->product) {
                        // Devolver el stock
                        $stock->adjust($item->product, $item->quantity, 'manual-cancel', $order);
                    }
                }

                // Asignar enum directamente
                $order->status = OrderStatus::CANCELED;
                $order->save();
            });

            return redirect()->route('orders.index')
                ->with('ok', "Pedido #$orderId cancelado y stock devuelto.");

        } catch (DomainException $e) {
            return redirect()->route('orders.index')
                ->with('error', $e->getMessage());
        } catch (\Throwable $e) {
            \Log::error('Error cancelando pedido', ['msg' => $e->getMessage(), 'order_id' => $orderId]);
            return redirect()->route('orders.index')
                ->with('error', "Ocurrió un error al cancelar el pedido #$orderId.");
        }
    }

    /**
     * Finaliza un pedido (marca COMPLETED) y descuenta stock de productos.
     */
    public function finalize(Request $request, Order $order): RedirectResponse
    {
        try {
            if ($order->status !== OrderStatus::DRAFT) {
                throw new DomainException('El pedido no está en estado borrador.');
            }

            $order->markAsCompleted(now());

            return redirect()->route('orders.show', $order)
                ->with('ok', "Pedido #{$order->id} finalizado correctamente.");
        } catch (DomainException $e) {
            return back()->with('error', $e->getMessage());
        } catch (\Throwable $e) {
            \Log::error('Error finalizando pedido', ['order_id' => $order->id, 'msg' => $e->getMessage()]);
            return back()->with('error', 'No se pudo finalizar el pedido.');
        }
    }

    /**
     * Cancela un pedido COMPLETED y devuelve stock de productos.
     */
    public function cancel(Request $request, Order $order): RedirectResponse
    {
        try {
            DB::transaction(function () use ($order) {
                $order = Order::with('items.product')->lockForUpdate()->findOrFail($order->id);

                if ($order->status !== OrderStatus::COMPLETED) {
                    throw new DomainException('Solo se pueden cancelar pedidos completados.');
                }

                /** @var StockService $stock */
                $stock = app(StockService::class);
                foreach ($order->items as $item) {
                    if ($item->product) {
                        $stock->adjust($item->product, (int)$item->quantity, 'manual-cancel', $order);
                    }
                }

                $order->status = OrderStatus::CANCELED;
                $order->save();
            });

            return redirect()->route('orders.index')
                ->with('ok', "Pedido #{$order->id} cancelado y stock devuelto.");

        } catch (DomainException $e) {
            return back()->with('error', $e->getMessage());
        } catch (\Throwable $e) {
            \Log::error('Error cancelando pedido', ['order_id' => $order->id, 'msg' => $e->getMessage()]);
            return back()->with('error', 'No se pudo cancelar el pedido.');
        }
    }
}
