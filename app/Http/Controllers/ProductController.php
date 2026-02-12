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

            // Usar FULLTEXT search para búsquedas más rápidas (requiere MySQL 5.6+)
            // Fallback a LIKE si el término es muy corto o contiene caracteres especiales
            if (strlen($term) >= 3 && preg_match('/^[\p{L}\p{N}\s]+$/u', $term)) {
                // FULLTEXT search (mucho más rápido con índice)
                $q->where(function($w) use ($term) {
                    $w->whereRaw('MATCH(name) AGAINST(? IN BOOLEAN MODE)', ["{$term}*"])
                      ->orWhere('sku', 'LIKE', "%{$term}%");
                });
            } else {
                // Fallback a LIKE para búsquedas cortas o especiales
                $lc = mb_strtolower($term, 'UTF-8');
                $q->where(function($w) use ($lc) {
                    $w->whereRaw('LOWER(name) LIKE ?', ["%{$lc}%"])
                      ->orWhereRaw('LOWER(sku) LIKE ?', ["%{$lc}%"]);
                });
            }
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

    // Verificar si es actualización de stock de producto existente
    $existingProductId = $request->input('_existing_product_id');

    if ($existingProductId) {
        // 🔄 Agregar stock a producto existente
        $product = Product::query()
            ->withoutGlobalScope('byUser')
            ->where('id', $existingProductId)
            ->firstOrFail();

        // Validar solo el stock
        $validated = $request->validate([
            'stock' => 'required|numeric|min:1',
        ]);

        $stockToAdd = (float) $validated['stock'];

        // Usar el método adjustStock del modelo
        try {
            $product->adjustStock(
                $stockToAdd,
                'Entrada por scanner - código de barras',
                auth()->user()
            );

            return redirect()->route('products.index')
                ->with('ok', "Stock agregado: +{$stockToAdd} unidades a {$product->name}");

        } catch (\DomainException $e) {
            return back()->with('error', 'Error al ajustar stock: ' . $e->getMessage());
        }
    }

    // ➕ Crear nuevo producto
    $data = $request->validate([
        'name' => 'required|string|max:100',
        'sku' => 'required|string|max:50|unique:products,sku,NULL,id,user_id,' . $userId,
        'barcode' => 'nullable|string|max:64|unique:products,barcode,NULL,id,user_id,' . $userId,
        'image' => 'nullable|image|max:5120',
        'external_image_url' => 'nullable|url|max:500',
        'price' => 'required|numeric|min:0',
        'stock' => 'nullable|numeric|min:0',
        'uses_stock' => 'boolean',
        'is_active' => 'boolean'
    ]);

    // Normalizar uses_stock desde checkbox
    $data['uses_stock'] = $request->boolean('uses_stock');

    // Si no usa stock, forzar stock a 0
    if (!$data['uses_stock']) {
        $data['stock'] = 0;
    } else {
        $data['stock'] = $data['stock'] ?? 0;
    }

    // Guardar imagen si se subió un archivo
    if ($request->hasFile('image')) {
        $data['image'] = $request->file('image')->store('products', 'public');
    }
    // O descargar imagen desde URL externa
    elseif ($request->filled('external_image_url')) {
        try {
            $imagePath = $this->downloadExternalImage($request->input('external_image_url'));
            if ($imagePath) {
                $data['image'] = $imagePath;
            }
        } catch (\Throwable $e) {
            \Log::warning('No se pudo descargar imagen externa', [
                'url' => $request->input('external_image_url'),
                'error' => $e->getMessage()
            ]);
            // Continuar sin imagen si falla la descarga
        }
    }

    // Remover external_image_url del array (no es columna de BD)
    unset($data['external_image_url']);

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
        'external_image_url' => 'nullable|url|max:500',
        'uses_stock' => 'boolean',
        'is_active' => 'boolean'
    ]);

    // Normalizar uses_stock desde checkbox
    $data['uses_stock'] = $request->boolean('uses_stock');

    // Si no usa stock, forzar stock a 0
    if (!$data['uses_stock']) {
        $product->stock = 0;
        $product->min_stock = 0;
    }

    // Guardar imagen si se subió un archivo
    if ($request->hasFile('image')) {
        // Borrar vieja (si existe)
        if ($product->image && Storage::disk('public')->exists($product->image)) {
            Storage::disk('public')->delete($product->image);
        }
        $data['image'] = $request->file('image')->store('products', 'public');
    }
    // O descargar imagen desde URL externa
    elseif ($request->filled('external_image_url')) {
        try {
            $imagePath = $this->downloadExternalImage($request->input('external_image_url'));
            if ($imagePath) {
                // Borrar imagen anterior
                if ($product->image && Storage::disk('public')->exists($product->image)) {
                    Storage::disk('public')->delete($product->image);
                }
                $data['image'] = $imagePath;
            }
        } catch (\Throwable $e) {
            \Log::warning('No se pudo descargar imagen externa', [
                'url' => $request->input('external_image_url'),
                'error' => $e->getMessage()
            ]);
            // Continuar sin cambiar la imagen si falla la descarga
        }
    }

    // Remover external_image_url del array (no es columna de BD)
    unset($data['external_image_url']);

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

    \Log::info('=== LOOKUP EXTERNO INICIADO ===', ['barcode' => $barcode]);

    $result = ['ok' => true, 'found' => false, 'product' => null];

    // 1) OpenFoodFacts
    try {
        $url = 'https://world.openfoodfacts.org/api/v2/product/' . urlencode($barcode) . '.json';
        \Log::info('Consultando OpenFoodFacts', ['url' => $url]);
        
        $resp = Http::timeout(15)
            ->withHeaders([
                'User-Agent' => 'Gestior-POS/1.0 (+https://gestior.com.ar)',
                'Accept' => 'application/json'
            ])
            ->get($url);
        
        if ($resp->successful()) {
            $json = $resp->json();
            
            if (isset($json['status']) && $json['status'] == 1 && isset($json['product'])) {
                $p = $json['product'];
                
                $name = $p['product_name'] ?? 
                        $p['product_name_es'] ?? 
                        $p['product_name_en'] ?? 
                        $p['generic_name'] ?? 
                        null;
                
                $brand = $p['brands'] ?? null;
                
                // 🖼️ OBTENER IMÁGENES
                $image = null;
                
                // Prioridad: imagen frontal > imagen del producto > cualquier imagen
                if (isset($p['image_front_url'])) {
                    $image = $p['image_front_url'];
                } elseif (isset($p['image_url'])) {
                    $image = $p['image_url'];
                } elseif (isset($p['image_front_small_url'])) {
                    $image = $p['image_front_small_url'];
                } elseif (isset($p['selected_images']['front']['display']['es'])) {
                    $image = $p['selected_images']['front']['display']['es'];
                } elseif (isset($p['selected_images']['front']['display']['en'])) {
                    $image = $p['selected_images']['front']['display']['en'];
                }
                
                \Log::info('Imagen encontrada:', ['image' => $image]);
                
                if ($name) {
                    $result['found'] = true;
                    $result['product'] = [
                        'name' => trim($name),
                        'brand' => $brand ? trim($brand) : null,
                        'source' => 'OpenFoodFacts',
                        'image_url' => $image, // 🖼️ Agregar imagen
                        'barcode' => $barcode
                    ];
                    
                    \Log::info('✅ Producto encontrado en OpenFoodFacts', $result['product']);
                    return response()->json($result);
                }
            }
        }
    } catch (\Throwable $e) {
        \Log::error('Error en OpenFoodFacts', ['message' => $e->getMessage()]);
    }

    // 2) UPCItemDB (también tiene imágenes)
    if (!$result['found']) {
        try {
            \Log::info('Consultando UPCItemDB...');
            
            $resp = Http::timeout(15)
                ->get('https://api.upcitemdb.com/prod/trial/lookup', [
                    'upc' => $barcode
                ]);
            
            if ($resp->successful()) {
                $json = $resp->json();
                $items = $json['items'] ?? [];
                
                if (!empty($items)) {
                    $item = $items[0];
                    $title = $item['title'] ?? null;
                    
                    if ($title) {
                        // 🖼️ UPCItemDB también tiene imágenes
                        $image = $item['images'][0] ?? null;
                        
                        $result['found'] = true;
                        $result['product'] = [
                            'name' => trim($title),
                            'brand' => isset($item['brand']) ? trim($item['brand']) : null,
                            'source' => 'UPCItemDB',
                            'image_url' => $image, // 🖼️ Imagen
                            'barcode' => $barcode
                        ];
                        
                        \Log::info('✅ Producto encontrado en UPCItemDB', $result['product']);
                    }
                }
            }
        } catch (\Throwable $e) {
            \Log::error('Error en UPCItemDB', ['message' => $e->getMessage()]);
        }
    }

    \Log::info('=== LOOKUP EXTERNO FINALIZADO ===', ['result' => $result]);
    
    return response()->json($result);
}

/**
 * Normaliza nombres de productos para el mercado argentino
 */
private function normalizarNombre(string $nombre): string
{
    // Diccionario de correcciones comunes
    $correcciones = [
        // Marcas argentinas mal escritas
        'manaus' => 'Manaos',
        'quilmez' => 'Quilmes',
        'arcor' => 'Arcor',
        
        // Sabores mal escritos
        'guatana' => 'Guaraná',
        'guarana' => 'Guaraná',
        'pomelo' => 'Pomelo',
        'limon' => 'Limón',
        'naranja' => 'Naranja',
        'manzana' => 'Manzana',
        
        // Errores comunes
        'coca cola' => 'Coca-Cola',
        'cocacola' => 'Coca-Cola',
        'sprite' => 'Sprite',
        'fanta' => 'Fanta',
    ];
    
    $nombreLower = mb_strtolower($nombre);
    
    // Aplicar correcciones
    foreach ($correcciones as $buscar => $reemplazar) {
        if (stripos($nombreLower, $buscar) !== false) {
            $nombre = str_ireplace($buscar, $reemplazar, $nombre);
        }
    }
    
    // Limpiar espacios y capitalizar correctamente
    $nombre = trim($nombre);
    
    // Capitalizar primera letra de cada palabra
    $nombre = mb_convert_case($nombre, MB_CASE_TITLE, 'UTF-8');
    
    // Remover duplicados (ej: "Manaus Manaus" -> "Manaus")
    $palabras = explode(' ', $nombre);
    $palabras = array_unique(array_map('mb_strtolower', $palabras));
    
    // Reconstruir manteniendo capitalización
    $palabrasFinales = [];
    foreach ($palabras as $palabra) {
        if (!empty($palabra)) {
            $palabrasFinales[] = mb_convert_case($palabra, MB_CASE_TITLE, 'UTF-8');
        }
    }
    
    return implode(' ', $palabrasFinales);
}
    public function destroy(Product $product)
    {
        try {
            // Eliminar imagen física si existe
            if ($product->image && Storage::disk('public')->exists($product->image)) {
                Storage::disk('public')->delete($product->image);
            }

            // Eliminar permanentemente (bypass soft delete)
            // Como order_items ahora tiene nullOnDelete, esto funcionará incluso con ventas
            $product->forceDelete();

            return redirect()->route('products.index')->with('ok','Producto eliminado permanentemente');
        } catch (\Throwable $e) {
            \Log::error('Error al eliminar producto', [
                'product_id' => $product->id,
                'error' => $e->getMessage()
            ]);
            return back()->with('error','No se pudo eliminar: ' . $e->getMessage());
        }
    }

    /**
     * Descarga una imagen desde una URL externa y la guarda en storage
     *
     * @param string $url URL de la imagen externa
     * @return string|null Path relativo de la imagen guardada o null si falla
     */
    private function downloadExternalImage(string $url): ?string
    {
        try {
            // Validar que sea una URL válida
            if (!filter_var($url, FILTER_VALIDATE_URL)) {
                \Log::warning('URL inválida para descarga de imagen', ['url' => $url]);
                return null;
            }

            // Descargar la imagen con timeout de 15 segundos
            $response = Http::timeout(15)
                ->withHeaders([
                    'User-Agent' => 'Gestior-POS/1.0 (+https://gestior.com.ar)',
                    'Accept' => 'image/*'
                ])
                ->get($url);

            if (!$response->successful()) {
                \Log::warning('Error al descargar imagen externa', [
                    'url' => $url,
                    'status' => $response->status()
                ]);
                return null;
            }

            // Obtener el contenido de la imagen
            $imageContent = $response->body();

            // Validar que sea una imagen (verificar primeros bytes - magic numbers)
            $finfo = new \finfo(FILEINFO_MIME_TYPE);
            $mimeType = $finfo->buffer($imageContent);

            // Solo permitir formatos de imagen comunes
            $allowedMimes = ['image/jpeg', 'image/png', 'image/gif', 'image/webp'];
            if (!in_array($mimeType, $allowedMimes)) {
                \Log::warning('Tipo de archivo no permitido', [
                    'url' => $url,
                    'mime' => $mimeType
                ]);
                return null;
            }

            // Determinar extensión según mime type
            $extensions = [
                'image/jpeg' => 'jpg',
                'image/png' => 'png',
                'image/gif' => 'gif',
                'image/webp' => 'webp'
            ];
            $extension = $extensions[$mimeType] ?? 'jpg';

            // Generar nombre único para la imagen
            $filename = 'products/' . uniqid('ext_', true) . '.' . $extension;

            // Guardar en storage/app/public/products
            Storage::disk('public')->put($filename, $imageContent);

            \Log::info('Imagen externa descargada exitosamente', [
                'url' => $url,
                'path' => $filename,
                'size' => strlen($imageContent)
            ]);

            return $filename;

        } catch (\Throwable $e) {
            \Log::error('Excepción al descargar imagen externa', [
                'url' => $url,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            return null;
        }
    }
}
