<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Product;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule as ValidationRule;

class ProductController extends Controller
{
    /**
     * Lista de productos con paginación
     */
    public function index(Request $request)
    {
        $auth = $request->user();

        // Determinar query base según jerarquía
        if ($auth->isMaster()) {
            $base = Product::query();
        } elseif ($auth->isCompany()) {
            $base = Product::query()
                ->withoutGlobalScope('byUser')
                ->where('company_id', $auth->id);
        } elseif ($auth->isAdmin()) {
            $branch = $auth->representable;
            $company = $auth->rootCompany();

            if ($branch && $branch->use_company_inventory) {
                $base = Product::query()
                    ->withoutGlobalScope('byUser')
                    ->where('company_id', $company->id);
            } else {
                $base = Product::query()
                    ->withoutGlobalScope('byUser')
                    ->where(function ($q) use ($auth, $company) {
                        $q->where(function($sq) use ($auth) {
                            $sq->where('user_id', $auth->id)
                               ->where('created_by_type', 'branch');
                        })
                        ->orWhere(function($sq) use ($auth, $company) {
                            $sq->where('company_id', $company->id)
                               ->where('created_by_type', 'company')
                               ->whereExists(function ($sub) use ($auth) {
                                   $sub->selectRaw('1')
                                       ->from('product_locations as pl')
                                       ->whereColumn('pl.product_id', 'products.id')
                                       ->where('pl.branch_id', $auth->id)
                                       ->where('pl.stock', '>', 0);
                               });
                        });
                    });
            }
        } else {
            $base = Product::availableFor($auth);
        }

        $query = $base->with(['user:id,name', 'company:id,name'])
            ->when($request->filled('q'), function ($q) use ($request) {
                $term = trim((string)$request->input('q'));
                $lc = mb_strtolower($term, 'UTF-8');
                $q->where(function($w) use ($lc) {
                    $w->whereRaw('LOWER(name) LIKE ?', ["%{$lc}%"])
                      ->orWhereRaw('LOWER(sku) LIKE ?', ["%{$lc}%"])
                      ->orWhereRaw('LOWER(barcode) LIKE ?', ["%{$lc}%"]);
                });
            })
            ->when($request->filled('category'), function ($q) use ($request) {
                $q->where('category', $request->category);
            })
            ->when($request->filled('is_active'), function ($q) use ($request) {
                $q->where('is_active', filter_var($request->is_active, FILTER_VALIDATE_BOOLEAN));
            });

        $perPage = min((int) $request->input('per_page', 20), 100);
        $products = $query->orderBy('name')->paginate($perPage);

        $data = collect($products->items())->map(fn($p) => $this->formatProduct($p, $request))->values();

        return response()->json([
            'success' => true,
            'data' => $data,
            'meta' => [
                'current_page' => $products->currentPage(),
                'last_page' => $products->lastPage(),
                'per_page' => $products->perPage(),
                'total' => $products->total(),
            ],
        ], 200);
    }

    /**
     * Buscar productos por nombre, SKU o código de barras (para agregar a pedidos)
     */
    public function search(Request $request)
    {
        $request->validate([
            'q' => 'required|string|min:1',
        ]);

        $auth = $request->user();
        $term = trim($request->q);
        $lc = mb_strtolower($term, 'UTF-8');

        $products = Product::availableFor($auth)
            ->active()
            ->where(function($w) use ($lc) {
                $w->whereRaw('LOWER(name) LIKE ?', ["%{$lc}%"])
                  ->orWhereRaw('LOWER(sku) LIKE ?', ["%{$lc}%"])
                  ->orWhereRaw('LOWER(barcode) LIKE ?', ["%{$lc}%"]);
            })
            ->select('id', 'name', 'sku', 'barcode', 'price', 'stock', 'min_stock', 'image', 'is_active')
            ->limit(20)
            ->get();

        return response()->json([
            'success' => true,
            'data' => $products->map(fn($p) => $this->formatProduct($p, $request))->values(),
        ], 200);
    }

    /**
     * Mostrar un producto específico
     */
    public function show(Request $request, Product $product)
    {
        $auth = $request->user();

        // Verificar que el usuario tenga acceso al producto
        if (!$this->canAccessProduct($auth, $product)) {
            return response()->json([
                'success' => false,
                'message' => 'No tienes acceso a este producto',
            ], 403);
        }

        $product->load([
            'user:id,name',
            'company:id,name',
            'recipeItems.supply',
            'productLocations.branch:id,name',
            'adjustments' => function ($query) {
                $query->with('user:id,name')
                    ->orderBy('created_at', 'desc')
                    ->limit(20);
            }
        ]);

        return response()->json([
            'success' => true,
            'data' => $this->formatProduct($product, $request),
        ], 200);
    }

    /**
     * Crear un nuevo producto
     */
    public function store(Request $request)
    {
        $auth = $request->user();
        $company = $auth->rootCompany();
        $companyId = $company ? $company->id : $auth->id;

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'sku' => [
                'nullable',
                'string',
                'max:100',
                ValidationRule::unique('products', 'sku')->where('company_id', $companyId),
            ],
            'barcode' => 'nullable|string|max:100',
            'price' => 'required|numeric|min:0',
            'cost_price' => 'nullable|numeric|min:0',
            'stock' => 'nullable|numeric|min:0',
            'min_stock' => 'nullable|numeric|min:0',
            'description' => 'nullable|string',
            'category' => 'nullable|string|max:100',
            'unit' => 'nullable|string|max:50',
            'is_active' => 'boolean',
            'is_shared' => 'boolean',
            'uses_stock' => 'boolean',
            'image' => 'nullable|string', // Base64 o URL
        ]);

        // Si no usa stock, forzar stock y min_stock a 0
        if (isset($validated['uses_stock']) && !$validated['uses_stock']) {
            $validated['stock'] = 0;
            $validated['min_stock'] = 0;
        }

        // Determinar company_id y created_by_type
        $validated['user_id'] = $auth->id;
        $validated['company_id'] = $companyId;

        if ($auth->isCompany()) {
            $validated['created_by_type'] = 'company';
        } elseif ($auth->isAdmin()) {
            $validated['created_by_type'] = 'branch';
        } else {
            $validated['created_by_type'] = 'user';
        }

        // Manejar imagen si viene en base64
        if (isset($validated['image']) && str_starts_with($validated['image'], 'data:image')) {
            $validated['image'] = $this->saveBase64Image($validated['image']);
        }

        $product = Product::create($validated);

        return response()->json([
            'success' => true,
            'message' => 'Producto creado exitosamente',
            'data' => $this->formatProduct($product, $request),
        ], 201);
    }

    /**
     * Actualizar un producto existente
     */
    public function update(Request $request, Product $product)
    {
        $auth = $request->user();
        $company = $auth->rootCompany();
        $companyId = $company ? $company->id : $auth->id;

        // Verificar permisos
        if (!$this->canManageProduct($auth, $product)) {
            return response()->json([
                'success' => false,
                'message' => 'No tienes permiso para editar este producto',
            ], 403);
        }

        $validated = $request->validate([
            'name' => 'string|max:255',
            'sku' => [
                'nullable',
                'string',
                'max:100',
                ValidationRule::unique('products', 'sku')
                    ->where('company_id', $companyId)
                    ->ignore($product->id),
            ],
            'barcode' => 'nullable|string|max:100',
            'price' => 'numeric|min:0',
            'cost_price' => 'nullable|numeric|min:0',
            'stock' => 'nullable|numeric|min:0',
            'min_stock' => 'nullable|numeric|min:0',
            'description' => 'nullable|string',
            'category' => 'nullable|string|max:100',
            'unit' => 'nullable|string|max:50',
            'is_active' => 'boolean',
            'is_shared' => 'boolean',
            'uses_stock' => 'boolean',
            'image' => 'nullable|string',
        ]);

        // Si cambia a no usar stock, resetear stock y min_stock
        if (isset($validated['uses_stock']) && !$validated['uses_stock']) {
            $validated['stock'] = 0;
            $validated['min_stock'] = 0;
        }

        // Manejar imagen si viene en base64
        if (isset($validated['image']) && str_starts_with($validated['image'], 'data:image')) {
            // Eliminar imagen anterior si existe
            if ($product->image && Storage::disk('public')->exists($product->image)) {
                Storage::disk('public')->delete($product->image);
            }
            $validated['image'] = $this->saveBase64Image($validated['image']);
        }

        $product->update($validated);

        return response()->json([
            'success' => true,
            'message' => 'Producto actualizado exitosamente',
            'data' => $this->formatProduct($product, $request),
        ], 200);
    }

    /**
     * Eliminar un producto
     */
    public function destroy(Request $request, Product $product)
    {
        $auth = $request->user();

        if (!$this->canManageProduct($auth, $product)) {
            return response()->json([
                'success' => false,
                'message' => 'No tienes permiso para eliminar este producto',
            ], 403);
        }

        // Verificar si tiene pedidos asociados
        if ($product->orderItems()->exists()) {
            return response()->json([
                'success' => false,
                'message' => 'No se puede eliminar un producto con pedidos asociados',
            ], 422);
        }

        $product->delete();

        return response()->json([
            'success' => true,
            'message' => 'Producto eliminado exitosamente',
        ], 200);
    }

    /**
     * Actualizar stock de un producto
     */
    public function updateStock(Request $request, Product $product)
    {
        $auth = $request->user();

        if (!$this->canManageProduct($auth, $product)) {
            return response()->json([
                'success' => false,
                'message' => 'No tienes permiso para actualizar el stock',
            ], 403);
        }

        if (!$product->uses_stock) {
            return response()->json([
                'success' => false,
                'message' => 'Este producto no utiliza control de stock',
            ], 422);
        }

        $validated = $request->validate([
            'stock' => 'required|numeric|min:0',
            'reason' => 'nullable|string|max:255',
        ]);

        $oldStock = $product->stock;
        $product->update(['stock' => $validated['stock']]);

        // Crear ajuste de stock
        $product->adjustments()->create([
            'user_id' => $auth->id,
            'previous_stock' => $oldStock,
            'new_stock' => $validated['stock'],
            'adjustment' => $validated['stock'] - $oldStock,
            'reason' => $validated['reason'] ?? 'Ajuste manual desde API',
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Stock actualizado exitosamente',
            'data' => $this->formatProduct($product, $request),
        ], 200);
    }

    /**
     * Verificar si el usuario puede acceder al producto
     */
    private function canAccessProduct(User $user, Product $product): bool
    {
        if ($user->isMaster()) {
            return true;
        }

        if ($user->isCompany()) {
            return $product->company_id === $user->id;
        }

        if ($user->isAdmin()) {
            $company = $user->rootCompany();
            return $product->company_id === $company?->id;
        }

        return $product->user_id === $user->id || $product->user_id === $user->parent_id;
    }

    /**
     * Verificar si el usuario puede gestionar el producto
     */
    private function canManageProduct(User $user, Product $product): bool
    {
        if ($user->isMaster()) {
            return true;
        }

        if ($user->isCompany()) {
            return $product->company_id === $user->id;
        }

        return $product->user_id === $user->id;
    }

    /**
     * Guardar imagen en base64
     */
    private function saveBase64Image(string $base64): string
    {
        // Extraer el tipo de imagen y los datos
        preg_match('/^data:image\/(\w+);base64,/', $base64, $matches);
        $extension = $matches[1] ?? 'png';
        $data = substr($base64, strpos($base64, ',') + 1);
        $data = base64_decode($data);

        // Generar nombre único
        $filename = 'products/' . uniqid() . '.' . $extension;
        Storage::disk('public')->put($filename, $data);

        return $filename;
    }

    /**
     * Obtener receta del producto (insumos)
     */
    public function getRecipe(Request $request, Product $product)
    {
        $auth = $request->user();

        if (!$this->canAccessProduct($auth, $product)) {
            return response()->json([
                'success' => false,
                'message' => 'No tienes acceso a este producto',
            ], 403);
        }

        $recipe = $product->recipeItems()->with('supply')->get();

        return response()->json([
            'success' => true,
            'data' => $recipe,
        ], 200);
    }

    /**
     * Agregar o actualizar insumo en receta
     */
    public function addRecipeItem(Request $request, Product $product)
    {
        $auth = $request->user();

        if (!$this->canManageProduct($auth, $product)) {
            return response()->json([
                'success' => false,
                'message' => 'No tienes permiso para gestionar este producto',
            ], 403);
        }

        $validated = $request->validate([
            'supply_id' => 'required|integer|exists:supplies,id',
            'qty' => 'required|numeric|gt:0',
            'unit' => 'required|string|max:10',
            'waste_pct' => 'nullable|numeric|min:0|max:100',
        ]);

        $validated['waste_pct'] = $validated['waste_pct'] ?? 0;

        // Usar updateOrCreate para agregar o actualizar
        $recipe = $product->recipeItems()->updateOrCreate(
            ['supply_id' => $validated['supply_id']],
            $validated
        );

        $recipe->load('supply');

        return response()->json([
            'success' => true,
            'message' => 'Insumo agregado a la receta',
            'data' => $recipe,
        ], 200);
    }

    /**
     * Actualizar insumo en receta
     */
    public function updateRecipeItem(Request $request, Product $product, $recipeId)
    {
        $auth = $request->user();

        if (!$this->canManageProduct($auth, $product)) {
            return response()->json([
                'success' => false,
                'message' => 'No tienes permiso para gestionar este producto',
            ], 403);
        }

        $recipe = $product->recipeItems()->findOrFail($recipeId);

        $validated = $request->validate([
            'qty' => 'numeric|gt:0',
            'unit' => 'string|max:10',
            'waste_pct' => 'nullable|numeric|min:0|max:100',
        ]);

        $recipe->update($validated);
        $recipe->load('supply');

        return response()->json([
            'success' => true,
            'message' => 'Insumo actualizado',
            'data' => $recipe,
        ], 200);
    }

    /**
     * Eliminar insumo de receta
     */
    public function removeRecipeItem(Request $request, Product $product, $recipeId)
    {
        $auth = $request->user();

        if (!$this->canManageProduct($auth, $product)) {
            return response()->json([
                'success' => false,
                'message' => 'No tienes permiso para gestionar este producto',
            ], 403);
        }

        $recipe = $product->recipeItems()->findOrFail($recipeId);
        $recipe->delete();

        return response()->json([
            'success' => true,
            'message' => 'Insumo eliminado de la receta',
        ], 200);
    }

    /**
     * Formatea producto según el integrador (storefront oculta campos sensibles).
     */
    private function formatProduct(Product $product, Request $request)
    {
        $isStorefront = $request->attributes->get('integrator') === 'storefront';

        if (!$isStorefront) {
            return $product;
        }

        return [
            'id' => $product->id,
            'name' => $product->name,
            'sku' => $product->sku,
            'barcode' => $product->barcode,
            'price' => $product->price,
            'stock' => $product->stock,
            'min_stock' => $product->min_stock,
            'is_active' => $product->is_active,
            'is_low_stock' => $product->is_low_stock,
            'image' => $product->image,
        ];
    }
}
