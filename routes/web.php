<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Http\Request;

use App\Http\Controllers\ProductController;
use App\Http\Controllers\OrderController;
use App\Http\Controllers\StockController;
use App\Http\Controllers\SupplyController;
use App\Http\Controllers\ProductCostController;
use App\Http\Controllers\CalculatorController;

use App\Models\Product;
use App\Models\Supply;
use App\Services\CostService;

// Raíz => login (Jetstream)
Route::redirect('/', '/login');

Route::middleware([
    'auth:sanctum',
    config('jetstream.auth_session'),
    'verified',
])->group(function () {

    // Dashboard
    Route::view('/dashboard', 'dashboard')->name('dashboard');

    // Productos
    Route::resource('products', ProductController::class)->except('show');
    Route::patch('products/{product}/stock', [ProductController::class, 'updateStock'])
        ->name('products.stock.update');

    // Pedidos
    Route::get('orders/create', [OrderController::class, 'create'])->name('orders.create');
    Route::post('orders/{order}/items', [OrderController::class, 'addItem'])->name('orders.items.store');
    Route::delete('orders/{order}/items/{item}', [OrderController::class, 'removeItem'])->name('orders.items.destroy');
    Route::post('orders/{order}/finalize', [OrderController::class, 'finalize'])->name('orders.finalize');
    Route::post('orders/{order}/cancel', [OrderController::class, 'cancel'])->name('orders.cancel');
    Route::get('orders', [OrderController::class, 'index'])->name('orders.index');
    Route::get('orders/{order}', [OrderController::class, 'show'])
        ->whereNumber('order')->name('orders.show');
    Route::get('/orders/download-report', [OrderController::class, 'downloadReport'])
        ->name('orders.download-report');

    // Stock
    Route::get('/stock', [StockController::class, 'index'])->name('stock.index');
    Route::get('/stock/export/csv', [StockController::class, 'exportCsv'])->name('stock.export.csv');

    /**
     * ===== CALCULADORA DE COSTOS (VISTA ÚNICA) =====
     */

    // ÚNICA ruta GET de la calculadora
    Route::get('/calculator', [CalculatorController::class, 'show'])
        ->name('calculator.show');

    // Alias para compatibilidad con links viejos
    Route::get('/costing/calculator', fn(Request $r) => redirect()->route('calculator.show', $r->query()))
        ->name('costing.calculator');

    /**
     * ===== RUTAS DE INSUMOS =====
     */
    
    // CRUD de insumos - CORREGIDO (sin duplicaciones)
    Route::post('/supplies/quick-store', [SupplyController::class, 'quickStore'])
        ->name('supplies.quick-store');
    Route::post('/supplies', [SupplyController::class, 'store'])
        ->name('supplies.store');
    Route::put('/supplies/{supply}', [SupplyController::class, 'update'])
        ->name('supplies.update');
    Route::delete('/supplies/{supply}', [SupplyController::class, 'destroy'])
        ->name('supplies.destroy');
    Route::post('/supplies/{supply}/purchase', [SupplyController::class, 'storePurchase'])
        ->name('supplies.purchase.store');

    // Redirecciones para rutas de insumos (hacia calculadora)
    Route::get('/supplies', function (Request $r) {
        return redirect()->route('calculator.show', $r->query());
    })->name('supplies.index');

    Route::get('/supplies/create', function (Request $r) {
        return redirect()->route('calculator.show', $r->query());
    })->name('supplies.create');

    /**
     * ===== RUTAS DE ANÁLISIS DE COSTOS =====
     */
    
    Route::prefix('products/{product}')->group(function () {
        // Guardar análisis de costos
        Route::post('/costings', [ProductCostController::class, 'storeAnalysis'])
            ->name('products.costings.store');
        
        // Listar análisis de costos (JSON)
        Route::get('/costings', [ProductCostController::class, 'analyses'])
            ->name('products.costings.index');
        
        // Agregar ingrediente a receta
        Route::post('/recipe', [ProductCostController::class, 'addRecipeItem'])
            ->name('products.recipe.add');
    });

});