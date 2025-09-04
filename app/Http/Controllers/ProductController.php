<?php

namespace App\Http\Controllers;

use App\Models\Product;
use Illuminate\Http\Request;
use App\Services\StockService;

class ProductController extends Controller
{
    public function index()
    {
        $products = Product::orderBy('name')->paginate(20);
        return view('products.index', compact('products'));
    }

    public function create()
    {
        return view('products.create');
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'name'=>'required|string|max:100',
            'sku'=>'required|string|max:50|unique:products,sku',
            'price'=>'required|numeric|min:0',
            'stock'=>'required|integer|min:0',
            'is_active'=>'boolean'
        ]);
        Product::create($data);
        return redirect()->route('products.index')->with('ok','Producto creado');
    }

    public function edit(Product $product)
    {
        return view('products.edit', compact('product'));
    }

    public function update(Request $request, Product $product)
    {
        $data = $request->validate([
            'name'=>'required|string|max:100',
            'sku'=>"required|string|max:50|unique:products,sku,{$product->id}",
            'price'=>'required|numeric|min:0',
            'is_active'=>'boolean'
        ]);
        $product->update($data);
        return back()->with('ok','Producto actualizado');
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
