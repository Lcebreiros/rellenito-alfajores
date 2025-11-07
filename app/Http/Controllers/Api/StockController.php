<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Product;
use App\Models\StockAdjustment;
use Illuminate\Http\Request;

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
}
