<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Models\StockAdjustment;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\StreamedResponse;

class StockController extends Controller
{
    /**
     * Resuelve el contexto de visualización basado en el usuario y parámetros
     */
    private function resolveViewingContext(Request $request): array
    {
        $branchId = $request->input('branch_id');
        $user = auth()->user();

        // Si pasaron branch_id explícito, respetarlo (pero validar permisos)
        if (!empty($branchId)) {
            $branchId = (int)$branchId;
            
            // Validar que el usuario tenga permisos para ver esta sucursal
            if (!$this->userCanViewBranch($user, $branchId)) {
                $branchId = null; // Fallback a vista consolidada
            }
            
            return ['branchId' => $branchId, 'isCompanyView' => false];
        }

        // Si es admin de sucursal -> mostrar su branch por defecto
        if ($user && method_exists($user, 'isAdmin') && $user->isAdmin()) {
            return ['branchId' => $user->id, 'isCompanyView' => false];
        }

        // Si es company -> vista consolidada por empresa
        if ($user && method_exists($user, 'isCompany') && $user->isCompany()) {
            return ['branchId' => null, 'isCompanyView' => true];
        }

        // Master user -> vista global consolidada
        if ($user && method_exists($user, 'isMaster') && $user->isMaster()) {
            return ['branchId' => null, 'isCompanyView' => false, 'isMasterView' => true];
        }

        // Fallback: null (mostrar consolidado)
        return ['branchId' => null, 'isCompanyView' => false];
    }

    /**
     * Verifica si el usuario puede ver una sucursal específica
     */
    private function userCanViewBranch(User $user, int $branchId): bool
{
    if (!$user) return false;

    if ($user->isMaster()) return true;

    if ($user->isAdmin() && $user->id === $branchId) return true;

    if ($user->isCompany()) {
        return $user->branches()->where('id', $branchId)->exists();
    }

    return false;
}

    public function index(Request $request)
    {
        [$baseQuery, $meta] = $this->buildProductsQuery($request);

        // Determinar contexto de visualización
        $ctx = $this->resolveViewingContext($request);
        $branchId = $ctx['branchId'];
        $isCompanyView = $ctx['isCompanyView'] ?? false;
        $isMasterView = $ctx['isMasterView'] ?? false;

        $orderBy = (string) $request->input('order_by', 'name');
        $dir = strtolower((string) $request->input('dir', 'asc')) === 'desc' ? 'desc' : 'asc';

        // Construir query con stock por ubicaciones
        $productsQuery = $this->buildProductsQueryWithStock($baseQuery, $branchId);

        // Aplicar ordenamiento
        $productsQuery = $this->applyOrdering($productsQuery, $orderBy, $dir, $branchId);

        $products = $productsQuery->paginate(24)->withQueryString();

        // Calcular totales de la página actual
        $totals = $this->calculateTotals($products->getCollection(), $branchId);

        // Obtener información de sucursales según el contexto
        $branchesData = $this->getBranchesData($ctx, $request);

        return view('stock.index', array_merge([
            'products' => $products,
            'totals' => $totals,
            'q' => $meta['q'],
            'status' => $meta['status'],
            'orderBy' => $orderBy,
            'dir' => $dir,
            'branchId' => $branchId,
            'isCompanyView' => $isCompanyView,
            'isMasterView' => $isMasterView,
        ], $branchesData));
    }

    /**
     * Construye la query de productos con información de stock por ubicaciones
     */
    private function buildProductsQueryWithStock($baseQuery, ?int $branchId)
    {
        return $baseQuery->select('products.*')
            ->selectRaw('(SELECT COALESCE(SUM(pl.stock), 0) FROM product_locations pl WHERE pl.product_id = products.id) as total_stock')
            ->selectRaw('(SELECT COALESCE(SUM(pl.stock), 0) FROM product_locations pl WHERE pl.product_id = products.id AND pl.branch_id = ?) as stock_in_branch', [$branchId]);
    }

    /**
     * Aplica ordenamiento a la query
     */
    private function applyOrdering($query, string $orderBy, string $dir, ?int $branchId)
    {
        return match ($orderBy) {
            'stock' => $query->orderByRaw(($branchId ? 'stock_in_branch' : 'total_stock') . ' ' . $dir),
            'value' => $query->orderByRaw('(COALESCE(price, 0) * COALESCE(' . ($branchId ? 'stock_in_branch' : 'total_stock') . ', 0)) ' . $dir),
            default => $query->orderBy('name', $dir),
        };
    }

    /**
     * Calcula totales para la colección de productos
     */
    private function calculateTotals($products, ?int $branchId): array
    {
        return $products->reduce(function ($acc, $p) use ($branchId) {
            $price = (float) ($p->price ?? 0);
            $stock = (float) ($branchId ? ($p->stock_in_branch ?? 0) : ($p->total_stock ?? 0));
            
            $acc['items'] += 1;
            $acc['units'] += $stock;
            $acc['value'] += $price * $stock;
            
            return $acc;
        }, ['items' => 0, 'units' => 0, 'value' => 0.0]);
    }

    /**
     * Obtiene datos de sucursales según el contexto
     */
    private function getBranchesData(array $ctx, Request $request): array
{
    $user = auth()->user();
    $branchList = null;
    $branchStocks = null;
    $companyTotal = null;
    $availableBranches = null;

    if ($ctx['isCompanyView'] ?? false) {
        // Vista de empresa: mostrar todas sus sucursales activas
        $branches = $user->branches()->get(['id', 'name']);

        $branchList = $branches->map(fn($b) => [
            'id' => $b->id,
            'name' => $b->name,
        ])->toArray();

        $branchIds = $branches->pluck('id')->toArray();

        if (!empty($branchIds)) {
            $branchStocks = $this->getBranchStocks($branchIds);
            $companyTotal = array_sum($branchStocks);
        } else {
            $branchStocks = [];
            $companyTotal = 0;
        }
    }

    $availableBranches = $this->getAvailableBranches($user);

    return [
        'branchList' => $branchList,
        'branchStocks' => $branchStocks,
        'companyTotal' => $companyTotal,
        'availableBranches' => $availableBranches,
    ];
}


    /**
     * Obtiene stock por sucursal
     */
    private function getBranchStocks(array $branchIds): array
    {
        $rows = DB::table('product_locations')
            ->select('branch_id', DB::raw('COALESCE(SUM(stock), 0) as stock'))
            ->whereIn('branch_id', $branchIds)
            ->groupBy('branch_id')
            ->get();

        return $rows->pluck('stock', 'branch_id')->toArray();
    }

    /**
     * Obtiene sucursales disponibles para el usuario según sus permisos
     */
    private function getAvailableBranches(User $user): array
{
    if ($user->isMaster()) {
        // Master ve todas las sucursales activas
        return User::whereNotNull('parent_id')
            ->where('hierarchy_level', User::HIERARCHY_ADMIN)
            ->where('representable_type', Branch::class)
            ->get(['id', 'name', 'parent_id'])
            ->map(fn($b) => [
                'id' => $b->id,
                'name' => $b->name,
                'company_name' => $b->parent?->name ?? 'Sin empresa'
            ])->toArray();
    }

    if ($user->isCompany()) {
        // Company ve solo sus sucursales activas
        return $user->branches()->get(['id', 'name'])->map(fn($b) => [
            'id' => $b->id,
            'name' => $b->name,
            'company_name' => $user->name
        ])->toArray();
    }

    if ($user->isAdmin()) {
        // Admin ve solo su propia sucursal
        return [[
            'id' => $user->id,
            'name' => $user->name,
            'company_name' => $user->parent?->name ?? 'Sin empresa'
        ]];
    }

    return [];
}


    /**
     * Export CSV con información de sucursal
     */
    public function exportCsv(Request $request): StreamedResponse
    {
        [$query] = $this->buildProductsQuery($request);
        $ctx = $this->resolveViewingContext($request);
        
        $query = $this->buildProductsQueryWithStock($query, $ctx['branchId']);
        $query->orderBy('name');

        $filename = 'reporte_stock_' . 
            ($ctx['branchId'] ? 'sucursal_' . $ctx['branchId'] . '_' : 'consolidado_') . 
            now()->format('Ymd_His') . '.csv';

        return response()->streamDownload(function () use ($query, $ctx) {
            $out = fopen('php://output', 'w');

            // Cabecera con información de sucursal
            $headers = ['Producto', 'SKU', 'Stock Total', 'Precio', 'Valorización'];
            if ($ctx['branchId']) {
                $headers = ['Producto', 'SKU', 'Stock Sucursal', 'Stock Total', 'Precio', 'Valorización'];
            }
            fputcsv($out, $headers);

            $query->chunk(500, function ($rows) use ($out, $ctx) {
                foreach ($rows as $p) {
                    $name = (string) $p->name;
                    $sku = (string) ($p->sku ?? '');
                    $totalStock = (int) ($p->total_stock ?? 0);
                    $branchStock = (int) ($p->stock_in_branch ?? 0);
                    $price = (float) ($p->price ?? 0);
                    
                    if ($ctx['branchId']) {
                        $value = $price * $branchStock;
                        fputcsv($out, [
                            $name, $sku, $branchStock, $totalStock,
                            number_format($price, 2, '.', ''),
                            number_format($value, 2, '.', '')
                        ]);
                    } else {
                        $value = $price * $totalStock;
                        fputcsv($out, [
                            $name, $sku, $totalStock,
                            number_format($price, 2, '.', ''),
                            number_format($value, 2, '.', '')
                        ]);
                    }
                }
            });

            fclose($out);
        }, $filename, [
            'Content-Type' => 'text/csv; charset=UTF-8',
        ]);
    }

    /**
     * Construye la query de productos con búsqueda mejorada
     */
    private function buildProductsQuery(Request $request): array
    {
        $q = trim((string) $request->input('q', ''));
        $status = (string) $request->input('status', '');
        $user = auth()->user();

        // Parser de precios (manteniendo tu lógica existente)
        $parseMoney = static function (string $raw): ?float {
            $s = trim($raw);
            $s = str_ireplace(['ARS', 'ARG', '$'], '', $s);
            $s = preg_replace('/[^\d.,]/u', '', $s);

            if ($s === '') return null;

            if (preg_match('/^\d{1,3}(\.\d{3})+,\d{1,2}$/', $s)) {
                $s = str_replace('.', '', $s);
                $s = str_replace(',', '.', $s);
            } else {
                if (substr_count($s, ',') === 1 && substr_count($s, '.') === 0) {
                    $s = str_replace(',', '.', $s);
                } else {
                    $s = str_replace(',', '', $s);
                }
            }

            return is_numeric($s) ? (float) $s : null;
        };

        $detectRange = static function (string $raw) use ($parseMoney): array {
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

        [$priceMin, $priceMax] = $detectRange($q);
        $priceExact = null;
        if ($priceMin === null && $priceMax === null) {
            $priceExact = $parseMoney($q);
        }

        // Query base con permisos del usuario
        $products = Product::query()->availableFor($user);

        // Aplicar filtros de búsqueda
        if ($q !== '') {
            $products->where(function ($query) use ($q, $priceExact, $priceMin, $priceMax) {
                $query->where(function ($w) use ($q, $priceExact, $priceMin, $priceMax) {
                    $w->where('name', 'like', "%{$q}%")
                      ->orWhere('sku', 'like', "%{$q}%");

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
            });
        }

        // Aplicar filtros de estado
        if ($status !== '') {
            $products->when($status === 'out', fn($q) => $q->having('total_stock', '<=', 0))
                    ->when($status === 'low', fn($q) => $q->having('total_stock', '>', 0)->havingRaw('total_stock <= min_stock'))
                    ->when($status === 'in', fn($q) => $q->having('total_stock', '>', 0)->havingRaw('total_stock > min_stock OR min_stock IS NULL'));
        }

        return [$products, [
            'q' => $q,
            'status' => $status,
            'price_exact' => $priceExact,
            'price_min' => $priceMin,
            'price_max' => $priceMax,
        ]];
    }

    // Mantener tus métodos history() y buildHistoryQuery() sin cambios
    public function history(Request $request)
    {
        [$query, $meta] = $this->buildHistoryQuery($request);

        $orderBy = (string) $request->input('order_by', 'created_at');
        $dir = strtolower((string) $request->input('dir', 'desc')) === 'asc' ? 'asc' : 'desc';

        $query = $query->with('product');

        if ($orderBy === 'product_name') {
            $query = $query
                ->leftJoin('products', 'products.id', '=', 'stock_adjustments.product_id')
                ->select('stock_adjustments.*')
                ->orderBy('products.name', $dir);
        } else {
            $map = [
                'quantity_change' => 'stock_adjustments.quantity_change',
                'created_at' => 'stock_adjustments.created_at',
            ];
            $col = $map[$orderBy] ?? 'stock_adjustments.created_at';
            $query = $query->orderBy($col, $dir);
        }

        $stockHistory = $query->paginate(20)->withQueryString();

        return view('stock.history', [
            'stockHistory' => $stockHistory,
            'q' => $meta['q'],
            'type' => $meta['type'],
            'orderBy' => $orderBy,
            'dir' => $dir,
        ]);
    }

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
                ->orWhere('reason', 'like', $term)
                ->orWhere('notes', 'like', $term);
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