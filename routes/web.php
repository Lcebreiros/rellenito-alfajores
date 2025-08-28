<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\OrderController;
use App\Http\Controllers\StockController;

// Raíz => login (Jetstream)
Route::redirect('/', '/login');

// Rutas protegidas (dashboard + productos + pedidos)
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
    ->whereNumber('order')      // evita capturar "create"
    ->name('orders.show');
    Route::get('/orders/download-report', [OrderController::class, 'downloadReport'])
    ->name('orders.download-report');

    // Stock
    Route::get('/stock', [StockController::class, 'index'])->name('stock.index');
Route::get('/stock/export/csv', [StockController::class, 'exportCsv'])->name('stock.export.csv');
    
});
