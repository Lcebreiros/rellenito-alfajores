<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

// Controllers
use App\Http\Controllers\ProductController;
use App\Http\Controllers\OrderController;
use App\Http\Controllers\StockController;
use App\Http\Controllers\SupplyController;
use App\Http\Controllers\ProductCostController;
use App\Http\Controllers\CalculatorController;

// Livewire (route binding)
use App\Livewire\Orders\Ticket as OrderTicket;
use App\Livewire\Dashboard;

//
// PÚBLICO: logos desde storage (evita symlink en hosting compartido)
//
Route::get('/branding/receipt-logo', function () {
    $disk = Storage::disk('public');
    $path = 'branding/receipt-logo.png';
    abort_unless($disk->exists($path), 404);

    $full = $disk->path($path);
    return response()->file($full, [
        'Content-Type'  => $disk->mimeType($path) ?? 'image/png',
        'Cache-Control' => 'public, max-age=604800',
    ]);
})->name('branding.receipt-logo');

Route::get('/branding/app-logo', function () {
    $disk = Storage::disk('public');
    $path = 'branding/app-logo.png';
    abort_unless($disk->exists($path), 404);

    $full = $disk->path($path);
    return response()->file($full, [
        'Content-Type'  => $disk->mimeType($path) ?? 'image/png',
        'Cache-Control' => 'public, max-age=604800',
    ]);
})->name('branding.app-logo');

//
// Raíz => login (Jetstream)
//
Route::redirect('/', '/login');

//
// ÁREA PRIVADA
//
Route::middleware([
    'auth:sanctum',
    config('jetstream.auth_session'),
    'verified',
])->group(function () {

    // ============ DASHBOARD (Livewire) ============
    Route::get('/dashboard', function () {
        return view('dashboard');
    })->name('dashboard');
    
    // API endpoint para guardar posiciones de widgets (AJAX)
    Route::post('/dashboard/update-positions', [Dashboard::class, 'updatePositions'])
        ->name('dashboard.update-positions');

    // ============ PRODUCTOS ============
    Route::resource('products', ProductController::class)->except('show');
    Route::patch('products/{product}/stock', [ProductController::class, 'updateStock'])
        ->name('products.stock.update');
    Route::patch('products/{product}/stock', [ProductController::class, 'updateStock'])
     ->name('products.update-stock');

    // ============ PEDIDOS ============
Route::middleware(['auth'])->group(function () {
    // 1) Descarga (colócala antes de orders/{order})
    Route::get('orders/download-report', [OrderController::class, 'downloadReport'])
        ->name('orders.download-report');
        // Eliminar múltiples pedidos
Route::post('orders/bulk-delete', [OrderController::class, 'bulkDelete'])
    ->name('orders.bulk-delete');


    // 2) Resto
    Route::get('orders/create', [OrderController::class, 'create'])->name('orders.create');
    Route::post('orders/{order}/items', [OrderController::class, 'addItem'])->name('orders.items.store');
    Route::delete('orders/{order}/items/{item}', [OrderController::class, 'removeItem'])->name('orders.items.destroy');
    Route::post('orders/{order}/finalize', [OrderController::class, 'finalize'])->name('orders.finalize');
    Route::post('orders/{order}/cancel', [OrderController::class, 'cancel'])->name('orders.cancel');
    Route::get('orders', [OrderController::class, 'index'])->name('orders.index');
    Route::get('orders/{order}', [OrderController::class, 'show'])->whereNumber('order')->name('orders.show');

    Route::get('orders/{order}/edit', [OrderController::class, 'edit'])
     ->whereNumber('order')
     ->name('orders.edit');
     Route::post('/orders/{order}/cancel', [OrderController::class, 'cancelManualHttp'])
     ->name('orders.cancel');

// Actualizar
Route::match(['put','patch'],'orders/{order}', [OrderController::class, 'update'])
     ->whereNumber('order')
     ->name('orders.update');

// Eliminar
Route::delete('orders/{order}', [OrderController::class, 'destroy'])
     ->whereNumber('order')
     ->name('orders.destroy');
});

    // ============ STOCK ============
    Route::get('/stock', [StockController::class, 'index'])->name('stock.index');
    Route::get('/stock/export/csv', [StockController::class, 'exportCsv'])->name('stock.export.csv');

    // ============ CALCULADORA DE COSTOS ============
    Route::get('/calculator', [CalculatorController::class, 'show'])->name('calculator.show');
    Route::get('/costing/calculator', fn(Request $r) => redirect()->route('calculator.show', $r->query()))
        ->name('costing.calculator');

    // ============ INSUMOS ============
    Route::post('/supplies/quick-store', [SupplyController::class, 'quickStore'])->name('supplies.quick-store');
    Route::post('/supplies', [SupplyController::class, 'store'])->name('supplies.store');
    Route::put('/supplies/{supply}', [SupplyController::class, 'update'])->name('supplies.update');
    Route::delete('/supplies/{supply}', [SupplyController::class, 'destroy'])->name('supplies.destroy');
    Route::post('/supplies/{supply}/purchase', [SupplyController::class, 'storePurchase'])->name('supplies.purchase.store');

    // Redirecciones de insumos hacia calculadora
    Route::get('/supplies', fn(Request $r) => redirect()->route('calculator.show', $r->query()))->name('supplies.index');
    Route::get('/supplies/create', fn(Request $r) => redirect()->route('calculator.show', $r->query()))->name('supplies.create');

    // ============ ANÁLISIS DE COSTOS ============
    Route::prefix('products/{product}')->group(function () {
        Route::post('/costings', [ProductCostController::class, 'storeAnalysis'])->name('products.costings.store');
        Route::get('/costings', [ProductCostController::class, 'analyses'])->name('products.costings.index');
        Route::post('/recipe', [ProductCostController::class, 'addRecipeItem'])->name('products.recipe.add');
    });

    // ============ SETTINGS ============
    Route::get('/settings', fn () => view('settings'))->name('settings');

    // ============ TICKET (Livewire) ============
    Route::get('/orders/{order}/ticket', OrderTicket::class)
        ->whereNumber('order')
        ->name('orders.ticket');
});