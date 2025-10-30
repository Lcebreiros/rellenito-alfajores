<?php

namespace App\Http\Controllers;

use App\Models\Product;
use Illuminate\Http\Request;
use App\Services\StockService;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Http;

class ProductController extends Controller
{
    public function index(Request $request)
{
    $auth = $request->user() ?? auth()->user();

    // Determinar query base según jerarquía
    if ($auth->isMaster()) {
        // Master ve todo
        $base = Product::query();
        
    } elseif ($auth->isCompany()) {
        // Company (nivel 0) ve: productos propios + de todas sus sucursales
        $base = Product::query()
            ->withoutGlobalScope('byUser')
            ->where('company_id', $auth->id);
        
    } elseif ($auth->isAdmin()) {
        // Admin de sucursal (nivel 1)
        $branch = $auth->representable;
        $company = $auth->rootCompany();
        
        if ($branch && $branch->use_company_inventory) {
            // Inventario compartido: ver catálogo completo de la empresa
            $base = Product::query()
                ->withoutGlobalScope('byUser')
                ->where('company_id', $company->id);
        } else {
            // Inventario propio: productos propios + recibidos de empresa
            $base = Product::query()
                ->withoutGlobalScope('byUser')
                ->where(function ($q) use ($auth, $company) {
                    // 1. Productos creados por esta sucursal
                    $q->where(function($sq) use ($auth) {
                        $sq->where('user_id', $auth->id)
                           ->where('created_by_type', 'branch');
                    })
                    // 2. Productos de empresa con stock en esta sucursal
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
        // Usuario regular (nivel 2)
        $base = Product::availableFor($auth);
    }

    $query = $base->with([
            'user:id,name,parent_id,representable_id,representable_type,hierarchy_level',
            'user.parent:id,name',
            'company:id,name'
        ])
        ->when($request->filled('q'), function ($q) use ($request) {
            $term = trim((string)$request->input('q'));
            $lc = mb_strtolower($term, 'UTF-8');
            $q->where(function($w) use ($lc) {
                $w->whereRaw('LOWER(name) LIKE ?', ["%{$lc}%"]) 
                  ->orWhereRaw('LOWER(sku) LIKE ?', ["%{$lc}%"]);
            });
        });

    if ($auth->isMaster() && $request->filled('user_id')) {
        $query->where('user_id', (int) $request->input('user_id'));
    }

    $products = $query->orderBy('name')->paginate(20)->withQueryString();

    return view('products.index', [
        'products' => $products,
        'authUser' => $auth,
    ]);
}

    public function create()
    {
        return view('products.create');
    }

    public function edit(Product $product)
    {
        return view('products.edit', compact('product'));
    }

    public function store(Request $request)
{
    $userId = (int) ($request->user()?->id ?? auth()->id());
    $data = $request->validate([
        'name' => 'required|string|max:100',
        'sku' => 'required|string|max:50|unique:products,sku,NULL,id,user_id,' . $userId,
        'barcode' => 'nullable|string|max:64|unique:products,barcode,NULL,id,user_id,' . $userId,
        'image' => 'nullable|image|max:5120',
        'price' => 'required|numeric|min:0',
        'stock' => 'required|integer|min:0',
        'is_active' => 'boolean'
    ]);

    // Guardar imagen si se subió
    if ($request->hasFile('image')) {
        $data['image'] = $request->file('image')->store('products', 'public');
    }

    Product::create($data);

    return redirect()->route('products.index')->with('ok', 'Producto creado');
}

public function update(Request $request, Product $product)
{
    $userId = (int) ($request->user()?->id ?? auth()->id());
    $data = $request->validate([
        'name' => 'required|string|max:100',
        'sku' => 'required|string|max:50|unique:products,sku,' . $product->id . ',id,user_id,' . $userId,
        'barcode' => 'nullable|string|max:64|unique:products,barcode,' . $product->id . ',id,user_id,' . $userId,
        'price' => 'required|numeric|min:0',
        'image' => 'nullable|image|max:5120',
        'is_active' => 'boolean'
    ]);

    if ($request->hasFile('image')) {
        // Borrar vieja (si existe)
        if ($product->image && Storage::disk('public')->exists($product->image)) {
            Storage::disk('public')->delete($product->image);
        }
        $data['image'] = $request->file('image')->store('products', 'public');
    }

    $product->update($data);

    return back()->with('ok', 'Producto actualizado');
}

    public function show(Product $product)
    {
        // Stock: replicar criterio de ProductCard: usar products.stock como stock principal visible
        $product->load(['user.parent','user.representable','company']);
        $locations = $product->productLocations()->with('branch')->get();
        $totalStock = (float) ($product->stock ?? 0);

        return view('products.show', compact('product', 'locations', 'totalStock'));
    }



    // Actualizar stock desde panel
    public function updateStock(Request $request, Product $product, StockService $stock)
    {
        $data = $request->validate(['stock'=>'required|integer|min:0']);
        $stock->setAbsolute($product, $data['stock'], 'admin set');
        return back()->with('ok','Stock actualizado');
    }

    // Lookup de producto por código de barras (AJAX)
    public function lookup(Request $request)
    {
        $barcode = trim((string) $request->query('barcode', ''));
        abort_unless($request->user(), 401);
        if ($barcode === '') {
            return response()->json(['ok' => false, 'error' => 'barcode_required'], 422);
        }

        $user = $request->user();
        $query = Product::query()->withoutGlobalScope('byUser');

        if ($user->isMaster()) {
            // sin filtro
        } elseif ($user->isCompany()) {
            $query->where('company_id', $user->id);
        } else {
            // usar scope de disponibilidad
            $query = Product::availableFor($user);
        }

        $product = $query->where('barcode', $barcode)->first();
        if (!$product) {
            return response()->json(['ok' => true, 'found' => false]);
        }

        return response()->json([
            'ok' => true,
            'found' => true,
            'product' => [
                'id' => $product->id,
                'name' => $product->name,
                'sku' => $product->sku,
                'barcode' => $product->barcode,
                'price' => (float) $product->price,
                'image_url' => $product->image ? \Storage::url($product->image) : null,
                'is_active' => (bool) $product->is_active,
            ],
        ]);
    }

    // Lookup externo: intenta obtener datos reales por EAN/UPC (OpenFoodFacts y otros)
    public function lookupExternal(Request $request)
{
    $barcode = trim((string) $request->query('barcode', ''));
    abort_unless($request->user(), 401);
    
    if ($barcode === '') {
        return response()->json(['ok' => false, 'error' => 'barcode_required'], 422);
    }

    \Log::info('Lookup externo:', ['barcode' => $barcode]);

    $result = ['ok' => true, 'found' => false, 'product' => null];

    // 1) OpenFoodFacts (mejor para alimentos y bebidas)
    try {
        $url = 'https://world.openfoodfacts.org/api/v2/product/' . urlencode($barcode) . '.json';
        \Log::info('Consultando OpenFoodFacts:', ['url' => $url]);
        
        $resp = Http::timeout(10)
            ->withHeaders([
                'User-Agent' => 'Gestior-POS/1.0 (+https://gestior.com.ar)'
            ])
            ->get($url);
        
        \Log::info('Respuesta OpenFoodFacts:', [
            'status' => $resp->status(),
            'successful' => $resp->successful()
        ]);
        
        if ($resp->successful()) {
            $json = $resp->json();
            
            // Log para debug
            \Log::info('JSON OpenFoodFacts:', [
                'status' => $json['status'] ?? 'no status',
                'has_product' => isset($json['product']),
                'product_keys' => isset($json['product']) ? array_keys($json['product']) : []
            ]);
            
            if (isset($json['status']) && $json['status'] == 1 && isset($json['product'])) {
                $p = $json['product'];
                
                // Intentar múltiples campos de nombre (orden de prioridad)
                $name = $p['product_name'] ?? 
                        $p['product_name_es'] ?? 
                        $p['product_name_en'] ?? 
                        $p['generic_name'] ?? 
                        $p['generic_name_es'] ?? 
                        null;
                
                $brand = $p['brands'] ?? null;
                
                \Log::info('Datos extraídos:', [
                    'name' => $name,
                    'brand' => $brand
                ]);
                
                if ($name) {
                    $result['found'] = true;
                    $result['product'] = [
                        'name' => trim($name),
                        'brand' => $brand ? trim($brand) : null,
                        'source' => 'OpenFoodFacts'
                    ];
                    
                    \Log::info('✅ Producto encontrado en OpenFoodFacts');
                    return response()->json($result);
                }
            }
        }
    } catch (\Throwable $e) {
        \Log::error('Error OpenFoodFacts:', [
            'message' => $e->getMessage(),
            'line' => $e->getLine()
        ]);
    }

    // 2) UPCItemDB - solo si no encontró en OpenFoodFacts
    if (!$result['found']) {
        try {
            \Log::info('Consultando UPCItemDB...');
            
            $resp = Http::timeout(10)
                ->get('https://api.upcitemdb.com/prod/trial/lookup', [
                    'upc' => $barcode
                ]);
            
            \Log::info('Respuesta UPCItemDB:', [
                'status' => $resp->status(),
                'successful' => $resp->successful()
            ]);
            
            if ($resp->successful()) {
                $json = $resp->json();
                $items = $json['items'] ?? [];
                
                \Log::info('Items UPCItemDB:', ['count' => count($items)]);
                
                if (is_array($items) && count($items) > 0) {
                    $item = $items[0];
                    $title = $item['title'] ?? null;
                    
                    if ($title) {
                        $result['found'] = true;
                        $result['product'] = [
                            'name' => trim($title),
                            'brand' => isset($item['brand']) ? trim($item['brand']) : null,
                            'source' => 'UPCItemDB'
                        ];
                        
                        \Log::info('✅ Producto encontrado en UPCItemDB');
                    }
                }
            }
        } catch (\Throwable $e) {
            \Log::error('Error UPCItemDB:', [
                'message' => $e->getMessage()
            ]);
        }
    }

    \Log::info('Resultado final:', $result);
    return response()->json($result);
}
    public function destroy(Product $product)
    {
        try {
            $product->delete(); // si el modelo usa SoftDeletes, hará borrado lógico
            return redirect()->route('products.index')->with('ok','Producto eliminado');
        } catch (\Throwable $e) {
            return back()->with('error','No se pudo eliminar. Puede estar referenciado por otros registros.');
        }
    }
}
