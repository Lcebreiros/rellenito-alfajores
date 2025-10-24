<?php

namespace App\Http\Controllers;

use App\Models\Product;
use Illuminate\Http\Request;
use App\Services\StockService;
use Illuminate\Support\Facades\Storage;

class ProductController extends Controller
{
    public function index(Request $request)
    {
        $auth = $request->user() ?? auth()->user();

        // Master ve todo sin restricciones
        $base = (method_exists($auth,'isMaster') && $auth->isMaster())
            ? Product::query()
            : Product::availableFor($auth);

        $query = $base->with([
                'user:id,name,parent_id,representable_id,representable_type',
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

        if (method_exists($auth, 'isMaster') && $auth->isMaster() && $request->filled('user_id')) {
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
    // Stock total por sucursal
    $product->load(['user.parent','user.representable','company']);
    $locations = $product->productLocations()->with('branch')->get();

    // Stock total consolidado
    $totalStock = $locations->sum(fn($l) => $l->stock);

    return view('products.show', compact('product', 'locations', 'totalStock'));
}



    // Actualizar stock desde panel
    public function updateStock(Request $request, Product $product, StockService $stock)
    {
        $data = $request->validate(['stock'=>'required|integer|min:0']);
        $stock->setAbsolute($product, $data['stock'], 'admin set');
        return back()->with('ok','Stock actualizado');
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
