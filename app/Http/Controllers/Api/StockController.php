<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Product;
use App\Models\StockAdjustment;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class StockController extends Controller
{
    /**
     * Consultar stock de productos
     */
    public function index(Request $request)
    {
        $auth = $request->user();

        $query = Product::availableFor($auth)
            ->select('id', 'name', 'sku', 'stock', 'min_stock', 'unit', 'is_active')
            ->when($request->filled('low_stock'), function ($q) {
                $q->whereColumn('stock', '<=', 'min_stock');
            })
            ->when($request->filled('out_of_stock'), function ($q) {
                $q->where('stock', '<=', 0);
            })
            ->when($request->filled('q'), function ($q) use ($request) {
                $term = trim($request->q);
                $lc = mb_strtolower($term, 'UTF-8');
                $q->where(function($w) use ($lc) {
                    $w->whereRaw('LOWER(name) LIKE ?', ["%{$lc}%"])
                      ->orWhereRaw('LOWER(sku) LIKE ?', ["%{$lc}%"]);
                });
            });

        $perPage = min((int) $request->input('per_page', 50), 100);
        $products = $query->orderBy('name')->paginate($perPage);

        return response()->json([
            'success' => true,
            'data' => $products->items(),
            'meta' => [
                'current_page' => $products->currentPage(),
                'last_page' => $products->lastPage(),
                'per_page' => $products->perPage(),
                'total' => $products->total(),
            ],
        ], 200);
    }

    /**
     * Obtener historial de ajustes de stock
     */
    public function history(Request $request)
    {
        $auth = $request->user();

        $query = StockAdjustment::with(['product:id,name,sku', 'user:id,name'])
            ->whereHas('product', function ($q) use ($auth) {
                $q->whereIn('id', function ($subQuery) use ($auth) {
                    $subQuery->select('id')
                        ->from('products')
                        ->when($auth->isCompany(), function ($sq) use ($auth) {
                            $sq->where('company_id', $auth->id);
                        })
                        ->when($auth->isAdmin(), function ($sq) use ($auth) {
                            $company = $auth->rootCompany();
                            $sq->where('company_id', $company?->id);
                        })
                        ->when(!$auth->isMaster() && !$auth->isCompany() && !$auth->isAdmin(), function ($sq) use ($auth) {
                            $sq->where('user_id', $auth->id);
                        });
                });
            })
            ->when($request->filled('product_id'), function ($q) use ($request) {
                $q->where('product_id', $request->product_id);
            })
            ->when($request->filled('from_date'), function ($q) use ($request) {
                $q->whereDate('created_at', '>=', $request->from_date);
            })
            ->when($request->filled('to_date'), function ($q) use ($request) {
                $q->whereDate('created_at', '<=', $request->to_date);
            });

        $perPage = min((int) $request->input('per_page', 20), 100);
        $adjustments = $query->orderBy('created_at', 'desc')->paginate($perPage);

        return response()->json([
            'success' => true,
            'data' => $adjustments->items(),
            'meta' => [
                'current_page' => $adjustments->currentPage(),
                'last_page' => $adjustments->lastPage(),
                'per_page' => $adjustments->perPage(),
                'total' => $adjustments->total(),
            ],
        ], 200);
    }

    /**
     * Obtener productos con stock bajo
     */
    public function lowStock(Request $request)
    {
        $auth = $request->user();

        $products = Product::availableFor($auth)
            ->select('id', 'name', 'sku', 'stock', 'min_stock', 'unit')
            ->whereColumn('stock', '<=', 'min_stock')
            ->where('stock', '>', 0)
            ->orderBy('stock', 'asc')
            ->limit(50)
            ->get();

        return response()->json([
            'success' => true,
            'data' => $products,
            'count' => $products->count(),
        ], 200);
    }

    /**
     * Obtener productos sin stock
     */
    public function outOfStock(Request $request)
    {
        $auth = $request->user();

        $products = Product::availableFor($auth)
            ->select('id', 'name', 'sku', 'stock', 'min_stock', 'unit')
            ->where('stock', '<=', 0)
            ->orderBy('name')
            ->limit(50)
            ->get();

        return response()->json([
            'success' => true,
            'data' => $products,
            'count' => $products->count(),
        ], 200);
    }

    /**
     * Resumen de stock
     */
    public function summary(Request $request)
    {
        $auth = $request->user();

        $query = Product::availableFor($auth);

        $total = $query->count();
        $lowStock = (clone $query)->whereColumn('stock', '<=', 'min_stock')->where('stock', '>', 0)->count();
        $outOfStock = (clone $query)->where('stock', '<=', 0)->count();
        $inStock = $total - $lowStock - $outOfStock;

        return response()->json([
            'success' => true,
            'data' => [
                'total_products' => $total,
                'in_stock' => $inStock,
                'low_stock' => $lowStock,
                'out_of_stock' => $outOfStock,
            ],
        ], 200);
    }

    /**
     * Ver detalle de un ajuste de stock
     */
    public function showAdjustment(Request $request, StockAdjustment $adjustment)
    {
        $auth = $request->user();

        // Verificar acceso al producto del ajuste
        $product = $adjustment->product;

        if (!$product) {
            return response()->json([
                'success' => false,
                'message' => 'Producto no encontrado',
            ], 404);
        }

        if (!$this->canAccessProduct($auth, $product)) {
            return response()->json([
                'success' => false,
                'message' => 'No autorizado',
            ], 403);
        }

        $adjustment->load(['product:id,name,sku,unit', 'user:id,name']);

        return response()->json([
            'success' => true,
            'data' => $adjustment,
        ], 200);
    }

    /**
     * Crear ajuste de stock manual
     */
    public function createAdjustment(Request $request)
    {
        $auth = $request->user();

        $validated = $request->validate([
            'product_id' => 'required|integer|exists:products,id',
            'quantity_change' => 'required|numeric|not_in:0',
            'reason' => 'required|string|in:manual_adjustment,inventory_count,damage,loss,found,correction,return,other',
            'notes' => 'nullable|string|max:500',
        ]);

        // Verificar acceso al producto
        $product = Product::findOrFail($validated['product_id']);

        if (!$this->canManageProduct($auth, $product)) {
            return response()->json([
                'success' => false,
                'message' => 'No tienes permiso para ajustar el stock de este producto',
            ], 403);
        }

        return DB::transaction(function () use ($validated, $auth, $product) {
            // Obtener stock actual
            $currentStock = $product->stock ?? 0;
            $newStock = $currentStock + $validated['quantity_change'];

            // No permitir stock negativo (opcional, depende de la lógica de negocio)
            if ($newStock < 0) {
                throw ValidationException::withMessages([
                    'quantity_change' => 'El ajuste resultaría en stock negativo. Stock actual: ' . $currentStock,
                ]);
            }

            // Actualizar stock del producto
            $product->stock = $newStock;
            $product->save();

            // Crear registro de ajuste
            $adjustment = StockAdjustment::create([
                'user_id' => $auth->id,
                'product_id' => $product->id,
                'quantity_change' => $validated['quantity_change'],
                'new_stock' => $newStock,
                'reason' => $validated['reason'],
                'notes' => $validated['notes'] ?? null,
            ]);

            $adjustment->load(['product:id,name,sku,unit', 'user:id,name']);

            return response()->json([
                'success' => true,
                'message' => 'Ajuste de stock realizado exitosamente',
                'data' => $adjustment,
            ], 201);
        });
    }

    /**
     * Eliminar/revertir ajuste de stock
     * IMPORTANTE: Solo se permite revertir ajustes manuales recientes
     */
    public function deleteAdjustment(Request $request, StockAdjustment $adjustment)
    {
        $auth = $request->user();

        // Verificar acceso al producto del ajuste
        $product = $adjustment->product;

        if (!$product) {
            return response()->json([
                'success' => false,
                'message' => 'Producto no encontrado',
            ], 404);
        }

        if (!$this->canManageProduct($auth, $product)) {
            return response()->json([
                'success' => false,
                'message' => 'No tienes permiso para revertir este ajuste',
            ], 403);
        }

        // Verificar que sea un ajuste manual o de corrección
        $revertibleReasons = ['manual_adjustment', 'inventory_count', 'correction', 'other'];
        if (!in_array($adjustment->reason, $revertibleReasons)) {
            return response()->json([
                'success' => false,
                'message' => 'Solo se pueden revertir ajustes manuales, conteos de inventario o correcciones',
            ], 422);
        }

        // Verificar que no sea muy antiguo (opcional: máximo 7 días)
        if ($adjustment->created_at->addDays(7)->isPast()) {
            return response()->json([
                'success' => false,
                'message' => 'No se pueden revertir ajustes con más de 7 días de antigüedad',
            ], 422);
        }

        return DB::transaction(function () use ($adjustment, $product) {
            // Revertir el cambio de stock
            $currentStock = $product->stock ?? 0;
            $revertedStock = $currentStock - $adjustment->quantity_change;

            // Actualizar stock del producto
            $product->stock = $revertedStock;
            $product->save();

            // Crear registro de reversión
            $reversion = StockAdjustment::create([
                'user_id' => $adjustment->user_id,
                'product_id' => $product->id,
                'quantity_change' => -$adjustment->quantity_change,
                'new_stock' => $revertedStock,
                'reason' => 'correction',
                'notes' => 'Reversión de ajuste #' . $adjustment->id . ($adjustment->notes ? ' - ' . $adjustment->notes : ''),
                'reference_id' => $adjustment->id,
                'reference_type' => StockAdjustment::class,
            ]);

            // Eliminar el ajuste original
            $adjustment->delete();

            return response()->json([
                'success' => true,
                'message' => 'Ajuste revertido exitosamente',
                'data' => [
                    'product_id' => $product->id,
                    'new_stock' => $revertedStock,
                    'reversion' => $reversion,
                ],
            ], 200);
        });
    }

    /**
     * Verificar si el usuario tiene acceso a un producto
     */
    private function canAccessProduct($user, Product $product): bool
    {
        if ($user->isMaster()) {
            return true;
        }

        if ($user->isCompany()) {
            return $product->company_id === $user->id;
        }

        if ($user->isAdmin()) {
            $company = $user->rootCompany();
            return $product->company_id === $company?->id || $product->user_id === $user->id;
        }

        return $product->user_id === $user->id || $product->user_id === $user->parent_id;
    }

    /**
     * Verificar si el usuario puede gestionar un producto
     */
    private function canManageProduct($user, Product $product): bool
    {
        if ($user->isMaster()) {
            return true;
        }

        if ($user->isCompany()) {
            return $product->company_id === $user->id;
        }

        return $product->user_id === $user->id;
    }
}
