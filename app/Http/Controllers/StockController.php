<?php

namespace App\Http\Controllers;

use App\Models\Product;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\StreamedResponse;

class StockController extends Controller
{
    public function index(Request $request)
    {
        $q        = trim((string) $request->input('q', ''));
        $status   = (string) $request->input('status', ''); // in, low, out
        $orderBy  = (string) $request->input('order_by', 'name'); // name|stock|value
        $dir      = (string) $request->input('dir', 'asc');       // asc|desc

        $products = Product::query()
            ->when($q !== '', function ($query) use ($q) {
                $query->where(function ($w) use ($q) {
                    $w->where('name', 'like', "%{$q}%")
                      ->orWhere('sku', 'like', "%{$q}%")
                      ->orWhere('barcode', 'like', "%{$q}%");
                });
            })
            ->when($status !== '', function ($query) use ($status) {
                // Suponiendo columnas: stock (int), reorder_level (int|null)
                // Ajustá 'reorder_level' si tu modelo usa otro nombre.
                $query->when($status === 'out', fn($q) => $q->where('stock', '<=', 0))
                      ->when($status === 'low', fn($q) => $q->where('stock', '>', 0)->whereColumn('stock', '<=', 'reorder_level'))
                      ->when($status === 'in',  fn($q) => $q->where('stock', '>', 0)->where(function($w){
                          $w->whereNull('reorder_level')->orWhereColumn('stock', '>', 'reorder_level');
                      }));
            });

        // Orden
        $products = match ($orderBy) {
            'stock' => $products->orderBy('stock', $dir),
            'value' => $products->orderByRaw('(COALESCE(price,0) * COALESCE(stock,0)) ' . ($dir === 'desc' ? 'desc' : 'asc')),
            default => $products->orderBy('name', $dir),
        };

        $products = $products->paginate(24)->withQueryString();

        // Totales para el resumen
        $totals = (clone $products->getCollection())
            ->reduce(function ($acc, $p) {
                $price = (float) ($p->price ?? 0);
                $stock = (int) ($p->stock ?? 0);
                $acc['items'] += 1;
                $acc['units'] += $stock;
                $acc['value'] += $price * $stock;
                return $acc;
            }, ['items' => 0, 'units' => 0, 'value' => 0.0]);

        return view('stock.index', compact('products', 'totals', 'q', 'status', 'orderBy', 'dir'));
    }

    public function exportCsv(Request $request): StreamedResponse
    {
        $q      = trim((string) $request->input('q', ''));
        $status = (string) $request->input('status', '');

        $query = Product::query()
            ->when($q !== '', function ($query) use ($q) {
                $query->where(function ($w) use ($q) {
                    $w->where('name', 'like', "%{$q}%")
                      ->orWhere('sku', 'like', "%{$q}%")
                      ->orWhere('barcode', 'like', "%{$q}%");
                });
            })
            ->when($status !== '', function ($query) use ($status) {
                $query->when($status === 'out', fn($q) => $q->where('stock', '<=', 0))
                      ->when($status === 'low', fn($q) => $q->where('stock', '>', 0)->whereColumn('stock', '<=', 'reorder_level'))
                      ->when($status === 'in',  fn($q) => $q->where('stock', '>', 0)->where(function($w){
                          $w->whereNull('reorder_level')->orWhereColumn('stock', '>', 'reorder_level');
                      }));
            })
            ->orderBy('name');

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
                    $price  = (float) ($p->price ?? 0);
                    $value  = $price * $stock;

                    fputcsv($out, [
                        $name, $sku, $stock, $min,
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
}
