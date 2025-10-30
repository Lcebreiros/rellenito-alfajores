<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Models\Branch;
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
        $branchUsesCompanyInventory = false;
        $companyUserId = null;

        // Si pasaron branch_id explícito, respetarlo (pero validar permisos)
        if (!empty($branchId)) {
            $inputBranchId = (int)$branchId;
            
            // 1) Intentar tratarlo como user.id (admin de sucursal)
            $branchUser = \App\Models\User::find($inputBranchId);
            $branch     = $branchUser?->branch();

            // 2) Fallback: si no existe ese usuario, tratarlo como Branch.id y mapear al user
            if (!$branchUser) {
                $branchModel = \App\Models\Branch::find($inputBranchId);
                if ($branchModel) {
                    $branchUser = $branchModel->user;
                    $branch     = $branchModel;
                }
            }

            // Validar permisos usando el user.id si existe; si no, validar con el Branch.id
            $checkId = (int) ($branchUser?->id ?? $inputBranchId);
            if (!$this->userCanViewBranch($user, $checkId)) {
                // Fallback a consolidado
                return ['branchId' => null, 'isCompanyView' => $user?->isCompany() ?? false, 'branchUsesCompanyInventory' => false, 'companyUserId' => (int) $user?->id];
            }

            $normalizedBranchUserId = (int) ($branchUser?->id ?? 0);
            $branchUsesCompanyInventory = (bool)($branch?->use_company_inventory ?? false);
            $companyUserId = (int) ($branch?->company_id ?? $user?->id ?? 0);

            return [
                'branchId' => $normalizedBranchUserId > 0 ? $normalizedBranchUserId : $inputBranchId,
                'isCompanyView' => false,
                'branchUsesCompanyInventory' => $branchUsesCompanyInventory,
                'companyUserId' => $companyUserId,
            ];
        }

        // Si es admin de sucursal -> mostrar su branch por defecto
        if ($user && method_exists($user, 'isAdmin') && $user->isAdmin()) {
            $branch = $user->branch();
            $branchUsesCompanyInventory = (bool)($branch?->use_company_inventory ?? false);
            $companyUserId = (int) ($branch?->company_id ?? $user->rootCompany()?->id ?? 0);
            return [
                'branchId' => $user->id,
                'isCompanyView' => false,
                'branchUsesCompanyInventory' => $branchUsesCompanyInventory,
                'companyUserId' => $companyUserId,
            ];
        }

        // Si es company -> vista consolidada por empresa
        if ($user && method_exists($user, 'isCompany') && $user->isCompany()) {
            $companyUserId = (int) $user->id;
            return ['branchId' => null, 'isCompanyView' => true, 'branchUsesCompanyInventory' => false, 'companyUserId' => $companyUserId];
        }

        // Master user -> vista global consolidada
        if ($user && method_exists($user, 'isMaster') && $user->isMaster()) {
            return ['branchId' => null, 'isCompanyView' => false, 'isMasterView' => true];
        }

        // Fallback: null (mostrar consolidado)
        $companyUserId = (int) ($user?->rootCompany()?->id ?? 0);
        return ['branchId' => null, 'isCompanyView' => false, 'branchUsesCompanyInventory' => false, 'companyUserId' => $companyUserId];
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
        $branchUsesCompanyInventory = $ctx['branchUsesCompanyInventory'] ?? false;
        $companyUserId = (int) ($ctx['companyUserId'] ?? 0);

        $orderBy = (string) $request->input('order_by', 'name');
        $dir = strtolower((string) $request->input('dir', 'asc')) === 'desc' ? 'desc' : 'asc';

        // Construir query con stock por ubicaciones
        $productsQuery = $this->buildProductsQueryWithStock($baseQuery, $branchId, $branchUsesCompanyInventory, $companyUserId);

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
            'branchUsesCompanyInventory' => $branchUsesCompanyInventory,
        ], $branchesData));
    }

    /**
     * Construye la query de productos con información de stock por ubicaciones
     */
    private function buildProductsQueryWithStock($baseQuery, ?int $branchId, bool $branchUsesCompanyInventory = false, int $companyUserId = 0)
{
    $query = $baseQuery->select('products.*');

        if ($branchId) {
        // === VISTA DE SUCURSAL ESPECÍFICA ===
        
        if ($branchUsesCompanyInventory) {
            // Sucursal con inventario compartido: consolidado en total, local en stock_in_branch/display
            $query->selectRaw(
                'CASE WHEN EXISTS(SELECT 1 FROM product_locations pl WHERE pl.product_id = products.id)
                      THEN (SELECT COALESCE(SUM(pl1.stock),0) FROM product_locations pl1 WHERE pl1.product_id = products.id)
                      ELSE products.stock
                 END as total_stock'
            );
            
            $query->selectRaw(
                '(SELECT COALESCE(SUM(pl.stock), 0) 
                  FROM product_locations pl 
                  WHERE pl.product_id = products.id 
                  AND pl.branch_id = ?) as stock_in_branch',
                [$branchId]
            );
            
            $query->selectRaw(
                '(SELECT COALESCE(SUM(pl.stock), 0) 
                  FROM product_locations pl 
                  WHERE pl.product_id = products.id 
                  AND pl.branch_id = ?) as display_stock',
                [$branchId]
            );
            
        } else {
            // Sucursal con inventario propio
            // total_stock: si el dueño del producto es esta sucursal (o un usuario hijo), usar products.stock; si no, consolidado por ubicaciones
            $query->selectRaw(
                'CASE 
                    WHEN EXISTS(SELECT 1 FROM users u WHERE u.id = products.user_id AND (u.id = ? OR u.parent_id = ?))
                        THEN products.stock
                    WHEN EXISTS(SELECT 1 FROM product_locations pl WHERE pl.product_id = products.id)
                        THEN (SELECT COALESCE(SUM(pl1.stock),0) FROM product_locations pl1 WHERE pl1.product_id = products.id)
                    ELSE 0
                END as total_stock',
                [$branchId, $branchId]
            );
            
            // stock_in_branch: si hay ubicaciones en esta sucursal (o legadas), sumarlas; si no y el dueño es esta sucursal/hijo, usar products.stock
            $query->selectRaw(
                'CASE 
                    WHEN EXISTS(
                        SELECT 1 FROM product_locations pl 
                        WHERE pl.product_id = products.id 
                          AND pl.branch_id = ?
                    )
                        THEN (
                            SELECT COALESCE(SUM(pl2.stock),0) FROM product_locations pl2 
                            WHERE pl2.product_id = products.id AND pl2.branch_id = ?
                        )
                    WHEN EXISTS(SELECT 1 FROM users u WHERE u.id = products.user_id AND (u.id = ? OR u.parent_id = ?))
                        THEN products.stock
                    ELSE 0
                END as stock_in_branch',
                [$branchId, $branchId, $branchId, $branchId]
            );
            
            // display_stock: igual a stock_in_branch en vista sucursal
            $query->selectRaw(
                'CASE 
                    WHEN EXISTS(
                        SELECT 1 FROM product_locations pl 
                        WHERE pl.product_id = products.id 
                          AND pl.branch_id = ?
                    )
                        THEN (
                            SELECT COALESCE(SUM(pl2.stock),0) FROM product_locations pl2 
                            WHERE pl2.product_id = products.id AND pl2.branch_id = ?
                        )
                    WHEN EXISTS(SELECT 1 FROM users u WHERE u.id = products.user_id AND (u.id = ? OR u.parent_id = ?))
                        THEN products.stock
                    ELSE 0
                END as display_stock',
                [$branchId, $branchId, $branchId, $branchId]
            );
        }
        
    } else {
        // === VISTA CONSOLIDADA (EMPRESA O MASTER) ===
        $query->selectRaw(
            'CASE 
                WHEN EXISTS(SELECT 1 FROM product_locations pl WHERE pl.product_id = products.id)
                    THEN (SELECT COALESCE(SUM(pl1.stock),0) FROM product_locations pl1 WHERE pl1.product_id = products.id)
                ELSE products.stock
            END as total_stock'
        );
        
        $query->selectRaw('NULL as stock_in_branch');
        
        $query->selectRaw(
            'CASE 
                WHEN EXISTS(SELECT 1 FROM product_locations pl WHERE pl.product_id = products.id)
                    THEN (SELECT COALESCE(SUM(pl1.stock),0) FROM product_locations pl1 WHERE pl1.product_id = products.id)
                ELSE products.stock
            END as display_stock'
        );
    }

    return $query;
}

    /**
     * Aplica ordenamiento a la query
     */
    private function applyOrdering($query, string $orderBy, string $dir, ?int $branchId)
    {
        return match ($orderBy) {
            'stock' => $query->orderByRaw('COALESCE(display_stock, 0) ' . $dir),
            'value' => $query->orderByRaw('(COALESCE(price, 0) * COALESCE(display_stock, 0)) ' . $dir),
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
            $stock = (float) ($p->display_stock ?? ($branchId ? ($p->stock_in_branch ?? 0) : ($p->total_stock ?? 0)));
            
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
        // Vista de empresa: obtener sucursales desde Branch (no desde users)
        $branches = \App\Models\Branch::query()
            ->where('company_id', $user->id)
            ->with('user:id,name')
            ->get(['id', 'name', 'company_id']);

        // Usar el ID del usuario que representa la sucursal (admin) para branch_id
        $branchList = $branches->map(fn($b) => [
            'id' => (int) ($b->user?->id ?? 0),
            'name' => $b->name,
        ])->filter(fn($row) => $row['id'] > 0)->values()->toArray();

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
            ->map(fn($u) => [
                'id' => $u->id, // user id de la sucursal
                'name' => $u->name,
                'company_name' => $u->parent?->name ?? 'Sin empresa'
            ])->toArray();
        }

        if ($user->isCompany()) {
        // Company: intentar por Branch y, si no hay user asociado, caer a hijos admin (users)
        $byBranch = \App\Models\Branch::query()
            ->where('company_id', $user->id)
            ->with('user:id,name')
            ->get(['id', 'name'])
            ->map(function($b) use ($user){
                $uid = (int) ($b->user?->id ?? 0);
                return $uid > 0 ? [
                    'id' => $uid,
                    'name' => $b->name,
                    'company_name' => $user->name,
                ] : null;
            })
            ->filter()
            ->values()
            ->toArray();

        if (!empty($byBranch)) return $byBranch;

        // Fallback: hijos admin del usuario empresa (con nombre de Branch si existe representable)
        return User::where('parent_id', $user->id)
            ->where('hierarchy_level', User::HIERARCHY_ADMIN)
            ->where('representable_type', Branch::class)
            ->with('representable:id,name')
            ->get(['id','name','parent_id','representable_id','representable_type'])
            ->map(function($u) use ($user){
                $display = $u->representable?->name ?? $u->name;
                return [
                    'id' => (int) $u->id,
                    'name' => $display,
                    'company_name' => $user->name,
                ];
            })
            ->toArray();
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
     * Detalle de stock por producto: creador, total empresa y por sucursal
     */
    public function show(Request $request, \App\Models\Product $product)
    {
        $product->load(['user.parent','company']);

        // Ubicaciones existentes para el producto (stock por sucursal)
        $locations = \App\Models\ProductLocation::query()
            ->where('product_id', $product->id)
            ->with(['branch' => function($q){ $q->select('id','name','parent_id'); }])
            ->get();

        // Sucursales de la empresa (para mostrar también ceros)
        $companyUser = $product->company ?: $product->user?->rootCompany();
        $branches = $companyUser
            ? \App\Models\Branch::where('company_id', $companyUser->id)->with('user:id,name')->get(['id','name','company_id'])
            : collect();

        // Mapear stock por sucursal (key = user.id de sucursal)
        $byBranch = $locations->groupBy('branch_id')->map(fn($rows) => (float) $rows->sum('stock'));

        // Fallback: si no hay ubicaciones, y el dueño es una sucursal o un usuario hijo de esa sucursal,
        // asignar el stock de products.stock a la sucursal creadora para reflejarlo en el detalle.
        if ($locations->isEmpty()) {
            $owner = $product->user; // User creador
            $branchUserId = null;
            if ($owner) {
                if (method_exists($owner, 'isAdmin') && $owner->isAdmin()) {
                    $branchUserId = (int) $owner->id; // el owner ya es la sucursal
                } elseif (!empty($owner->parent_id)) {
                    // si es empleado de sucursal
                    $parent = $owner->parent; // User sucursal
                    if ($parent && method_exists($parent, 'isAdmin') && $parent->isAdmin()) {
                        $branchUserId = (int) $parent->id;
                    }
                }
            }
            if ($branchUserId) {
                $byBranch[$branchUserId] = (float) ($product->stock ?? 0);
            }
        }

        // Total de empresa: si hay locations, suma locations; si no hay, usar products.stock
        $totalCompanyStock = $locations->isNotEmpty()
            ? (float) $locations->sum('stock')
            : (float) ($product->stock ?? 0);

        // Armar lista final de sucursales con stock (incluyendo 0)
        $branchRows = $branches->map(function($b) use ($byBranch){
            $uid = (int) ($b->user?->id ?? 0);
            $stock = $uid ? (float) ($byBranch[$uid] ?? 0.0) : 0.0;
            return [
                'name' => $b->name,
                'user_id' => $uid,
                'stock' => $stock,
            ];
        })->filter(fn($row) => $row['user_id'] > 0)->values();

        // Si la sucursal creadora no aparece en la lista (por falta de user vinculado), agregarla manualmente
        $owner = $product->user;
        $creatorBranchUserId = null;
        $creatorBranchName = null;
        if ($owner) {
            if (method_exists($owner, 'isAdmin') && $owner->isAdmin()) {
                $creatorBranchUserId = (int) $owner->id;
                $creatorBranchName = $owner->representable?->name ?? $owner->name;
            } elseif (!empty($owner->parent_id)) {
                $parent = $owner->parent;
                if ($parent && method_exists($parent, 'isAdmin') && $parent->isAdmin()) {
                    $creatorBranchUserId = (int) $parent->id;
                    $creatorBranchName = $parent->representable?->name ?? $parent->name;
                }
            }
        }
        if ($creatorBranchUserId && $branchRows->where('user_id', $creatorBranchUserId)->count() === 0) {
            $branchRows->push([
                'name' => $creatorBranchName ?: 'Sucursal',
                'user_id' => $creatorBranchUserId,
                'stock' => (float) ($byBranch[$creatorBranchUserId] ?? ($locations->isEmpty() ? ($product->stock ?? 0) : 0)),
            ]);
        }

        // Información de creador
        $owner = $product->user;
        $creator = [
            'user_id' => $owner?->id,
            'user_name' => $owner?->name,
            'type' => ($owner && method_exists($owner,'isCompany') && $owner->isCompany()) ? 'company' : (( $owner && method_exists($owner,'isAdmin') && $owner->isAdmin()) ? 'branch' : 'user'),
            'company_name' => $product->company?->name,
            'branch_name' => ($owner && $owner->representable_type === \App\Models\Branch::class) ? optional($owner->representable)->name : null,
        ];

        return view('stock.show', [
            'product' => $product,
            'totalCompanyStock' => $totalCompanyStock,
            'branchRows' => $branchRows,
            'locations' => $locations,
            'creator' => $creator,
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

        // Cargar producto y sucursal (usuario que representa la sucursal) para mostrar en la vista
        $query = $query->with(['product', 'branch.representable']);

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

        $user = $request->user() ?? auth()->user();

        // Quitar scope byUser para poder ver movimientos de sucursales (empresa) o propios (sucursal)
        $adjustments = StockAdjustment::withoutGlobalScope('byUser')
            ->where('stock_adjustments.quantity_change', '!=', 0);

        // Filtrar por jerarquía
        if ($user) {
            if (method_exists($user, 'isMaster') && $user->isMaster()) {
                // sin filtro
            } elseif (method_exists($user, 'isCompany') && $user->isCompany()) {
                $branchIds = $user->children()
                    ->where('hierarchy_level', \App\Models\User::HIERARCHY_ADMIN)
                    ->pluck('id')
                    ->toArray();
                $branchIds[] = $user->id; // incluir central
                $adjustments->whereIn('stock_adjustments.branch_id', $branchIds);
            } elseif (method_exists($user, 'isAdmin') && $user->isAdmin()) {
                $adjustments->where('stock_adjustments.branch_id', $user->id);
            } elseif (!empty($user->parent_id)) {
                $adjustments->where('stock_adjustments.branch_id', $user->parent_id);
            } else {
                // Sin contexto: no mostrar nada
                $adjustments->whereRaw('1 = 0');
            }
        }

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

    /**
     * Actualizar configuración de notificaciones de stock
     */
    public function updateNotifications(Request $request)
    {
        $validated = $request->validate([
            'notify_low_stock' => 'required|boolean',
            'low_stock_threshold' => 'required|integer|min:1|max:1000',
            'notify_out_of_stock' => 'required|boolean',
        ]);

        $user = auth()->user();

        if (!$user) {
            return response()->json([
                'success' => false,
                'message' => 'Usuario no autenticado'
            ], 401);
        }

        $user->update([
            'notify_low_stock' => $validated['notify_low_stock'],
            'low_stock_threshold' => $validated['low_stock_threshold'],
            'notify_out_of_stock' => $validated['notify_out_of_stock'],
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Configuración de notificaciones guardada correctamente'
        ]);
    }
}
