<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Models\StockAdjustment;
use Illuminate\Support\Facades\DB;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\StreamedResponse;

class StockController extends Controller
{
    public function index(Request $request)
    {
        [$query, $meta] = $this->buildProductsQuery($request);

        // Orden
        $orderBy = (string) $request->input('order_by', 'name'); // name|stock|value
        $dir     = strtolower((string) $request->input('dir', 'asc')) === 'desc' ? 'desc' : 'asc';

        $query = match ($orderBy) {
            'stock' => $query->orderBy('stock', $dir),
            'value' => $query->orderByRaw('(COALESCE(price,0) * COALESCE(stock,0)) ' . $dir), // <-- si tu columna no es price, cámbiala aquí también
            default => $query->orderBy('name', $dir),
        };

        $products = $query->paginate(24)->withQueryString();

        // Totales (de la página actual; si querés de todo el dataset, hacé una query aparte sin paginate)
        $totals = $products->getCollection()
            ->reduce(function ($acc, $p) {
                $price = (float) ($p->price ?? 0);  // <-- si tu columna no es price, cámbiala aquí también
                $stock = (int) ($p->stock ?? 0);
                $acc['items'] += 1;
                $acc['units'] += $stock;
                $acc['value'] += $price * $stock;
                return $acc;
            }, ['items' => 0, 'units' => 0, 'value' => 0.0]);

        return view('stock.index', [
            'products' => $products,
            'totals'   => $totals,
            'q'        => $meta['q'],
            'status'   => $meta['status'],
            'orderBy'  => $orderBy,
            'dir'      => $dir,
        ]);
    }

    public function exportCsv(Request $request): StreamedResponse
    {
        [$query] = $this->buildProductsQuery($request);
        $query->orderBy('name');

        $filename = 'reporte_stock_' . now()->format('Ymd_His') . '.csv';

        return response()->streamDownload(function () use ($query) {
            $out = fopen('php://output', 'w');

            // Cabecera
            fputcsv($out, ['Producto', 'SKU', 'Stock', 'Min.', 'Precio', 'Valorización']);

            $query->chunk(500, function ($rows) use ($out) {
                foreach ($rows as $p) {
                    $name   = (string) $p->name;
                    $sku    = (string) ($p->sku ?? '');
                    $stock  = (int) ($p->stock ?? 0);
                    $min    = (int) ($p->reorder_level ?? 0);
                    $price  = (float) ($p->price ?? 0); // <-- si tu columna no es price, cámbiala aquí también
                    $value  = $price * $stock;

                    fputcsv($out, [
                        $name,
                        $sku,
                        $stock,
                        $min,
                        number_format($price, 2, '.', ''),
                        number_format($value, 2, '.', ''),
                    ]);
                }
            });

            fclose($out);
        }, $filename, [
            'Content-Type' => 'text/csv; charset=UTF-8',
        ]);
    }

    /**
     * Construye la query de productos con búsqueda por nombre, SKU, barcode y PRECIO (incluye rangos).
     */
    private function buildProductsQuery(Request $request): array
    {
        $q      = trim((string) $request->input('q', ''));
        $status = (string) $request->input('status', ''); // in, low, out

        // --- Helpers para parsear precio/rango ---
        $parseMoney = static function (string $raw): ?float {
            $s = trim($raw);

            // Quitar símbolos de moneda y espacios duros
            $s = str_ireplace(['ARS', 'ARG', '$'], '', $s);
            $s = preg_replace('/[^\d.,]/u', '', $s); // solo dígitos, coma y punto

            if ($s === '') return null;

            // Caso típico AR: 1.234,56  -> 1234.56
            if (preg_match('/^\d{1,3}(\.\d{3})+,\d{1,2}$/', $s)) {
                $s = str_replace('.', '', $s);
                $s = str_replace(',', '.', $s);
            } else {
                // Reemplazar coma por punto si aparece como decimal
                // (no perfecto, pero robusto para inputs comunes)
                // "123,45" -> "123.45"
                if (substr_count($s, ',') === 1 && substr_count($s, '.') === 0) {
                    $s = str_replace(',', '.', $s);
                } else {
                    // Quitar separadores de miles ambiguos
                    $s = str_replace(',', '', $s);
                }
            }

            return is_numeric($s) ? (float) $s : null;
        };

        $detectRange = static function (string $raw) use ($parseMoney): array {
            // Admite "100-150", "100..150", "100 – 150" (con guiones varios)
            if (preg_match('/^\s*(.+?)\s*(?:\-|–|—|\.{2,})\s*(.+?)\s*$/u', $raw, $m)) {
                $min = $parseMoney($m[1] ?? '');
                $max = $parseMoney($m[2] ?? '');
                if ($min !== null || $max !== null) {
                    if ($min !== null && $max !== null && $min > $max) {
                        [$min, $max] = [$max, $min];
                    }
                    return [$min, $max];
                }
            }
            return [null, null];
        };

        // Derivar intención de búsqueda de PRECIO (exacto o rango)
        [$priceMin, $priceMax] = $detectRange($q);
        $priceExact = null;
        if ($priceMin === null && $priceMax === null) {
            $priceExact = $parseMoney($q);
        }

// dentro de buildProductsQuery()

$products = Product::query()
    ->when($q !== '', function ($query) use ($q, $priceExact, $priceMin, $priceMax) {
        $query->where(function ($w) use ($q, $priceExact, $priceMin, $priceMax) {
            // Nombre / SKU
            $w->where('name', 'like', "%{$q}%")
              ->orWhere('sku', 'like', "%{$q}%");

            // PRECIO: exacto con tolerancia ±0.005 (dos decimales) o rango
            if ($priceExact !== null) {
                $t = 0.005;
                $w->orWhereBetween('price', [$priceExact - $t, $priceExact + $t]);
            } elseif ($priceMin !== null || $priceMax !== null) {
                $w->orWhere(function ($pw) use ($priceMin, $priceMax) {
                    if ($priceMin !== null) $pw->where('price', '>=', $priceMin);
                    if ($priceMax !== null) $pw->where('price', '<=', $priceMax);
                });
            }
        });
    })
    ->when($status !== '', function ($query) use ($status) {
        $query->when($status === 'out', fn($q) => $q->where('stock', '<=', 0))
              ->when($status === 'low', fn($q) => $q->where('stock', '>', 0)->whereColumn('stock', '<=', 'reorder_level'))
              ->when($status === 'in',  fn($q) => $q->where('stock', '>', 0)->where(function($w){
                  $w->whereNull('reorder_level')->orWhereColumn('stock', '>', 'reorder_level');
              }));
    });


        return [$products, [
            'q'      => $q,
            'status' => $status,
            'price_exact' => $priceExact,
            'price_min'   => $priceMin,
            'price_max'   => $priceMax,
        ]];
    }
public function history(Request $request)
{

    [$query, $meta] = $this->buildHistoryQuery($request);

    // Parámetros de orden
    $orderBy = (string) $request->input('order_by', 'created_at');
    $dir = strtolower((string) $request->input('dir', 'desc')) === 'asc' ? 'asc' : 'desc';

    // Si piden ordenar por producto, agregamos el join justo antes de paginar
    if ($orderBy === 'product_name') {
        $query = $query->leftJoin('products', 'products.id', '=', 'stock_adjustments.product_id')
                       ->select('stock_adjustments.*') // muy importante
                       ->orderBy('products.name', $dir);
    } else {
        $map = [
            'quantity_change' => 'stock_adjustments.quantity_change',
            'created_at' => 'stock_adjustments.created_at',
        ];
        $col = $map[$orderBy] ?? 'stock_adjustments.created_at';
        $query = $query->orderBy($col, $dir);
    }

    // DEBUG opcional (descomentar solo para depurar)
    // \Log::info('History SQL', ['sql' => $query->toSql(), 'bindings' => $query->getBindings()]);
    // dd($query->toSql(), $query->getBindings());

    $stockHistory = $query->paginate(20)->withQueryString();

    // Aseguramos eager load para la colección que paginamos (por si usamos join)
    $stockHistory->getCollection()->load('product');

    return view('stock.history', [
        'stockHistory' => $stockHistory,
        'q' => $meta['q'],
        'type' => $meta['type'],
        'orderBy' => $orderBy,
        'dir' => $dir,
    ]);
}

/**
 * Construye la query del historial con filtros (usa whereHas para busqueda por producto)
 */
private function buildHistoryQuery(Request $request): array
{
    $q = trim((string) $request->input('q', ''));
    $type = (string) $request->input('type', '');

    $adjustments = StockAdjustment::query()
        ->where('stock_adjustments.quantity_change', '!=', 0);

    if ($q !== '') {
        $term = "%{$q}%";
        $adjustments->where(function ($w) use ($term) {
            $w->whereHas('product', function ($pq) use ($term) {
                $pq->where('name', 'like', $term)
                   ->orWhere('sku', 'like', $term);
            })
            ->orWhere('reason', 'like', $term);
        });
    }

    if ($type !== '') {
        if ($type === 'increase') {
            $adjustments->where('quantity_change', '>', 0);
        } elseif ($type === 'decrease') {
            $adjustments->where('quantity_change', '<', 0);
        }
    }

    return [$adjustments, [
        'q' => $q,
        'type' => $type,
    ]];
}

}
