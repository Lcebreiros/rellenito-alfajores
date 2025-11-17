<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\ProductController;
use App\Http\Controllers\Api\OrderController;
use App\Http\Controllers\Api\ClientController;
use App\Http\Controllers\Api\PaymentMethodController;
use App\Http\Controllers\Api\StockController;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Rutas API para consumo desde aplicación Android
|
*/

// Rutas públicas (sin autenticación) - Con rate limiting estricto
Route::prefix('v1')->middleware('throttle:api-auth')->group(function () {
    // Autenticación - 5 intentos por minuto por IP
    Route::post('/auth/login', [AuthController::class, 'login']);
    Route::post('/auth/register', [AuthController::class, 'register']);
});

// Rutas protegidas (requieren autenticación Sanctum)
Route::prefix('v1')->middleware(['auth:sanctum', 'throttle:api'])->group(function () {

    // ============ AUTENTICACIÓN ============
    Route::post('/auth/logout', [AuthController::class, 'logout']);
    Route::post('/auth/logout-all', [AuthController::class, 'logoutAll']);
    Route::get('/auth/me', [AuthController::class, 'me']);

    // ============ PRODUCTOS ============
    // Lectura (100/min)
    Route::middleware('throttle:api-read')->group(function () {
        Route::get('/products', [ProductController::class, 'index']);
        Route::get('/products/search', [ProductController::class, 'search']);
        Route::get('/products/{product}', [ProductController::class, 'show']);
    });

    // Escritura (30/min)
    Route::middleware('throttle:api-write')->group(function () {
        Route::post('/products', [ProductController::class, 'store']);
        Route::put('/products/{product}', [ProductController::class, 'update']);
        Route::delete('/products/{product}', [ProductController::class, 'destroy']);
        Route::patch('/products/{product}/stock', [ProductController::class, 'updateStock']);
    });

    // ============ PEDIDOS ============
    // Lectura (100/min)
    Route::middleware('throttle:api-read')->group(function () {
        Route::get('/orders', [OrderController::class, 'index']);
        Route::get('/orders/{order}', [OrderController::class, 'show']);
    });

    // Escritura (30/min)
    Route::middleware('throttle:api-write')->group(function () {
        Route::post('/orders', [OrderController::class, 'store']);
        Route::put('/orders/{order}', [OrderController::class, 'update']);
        Route::delete('/orders/{order}', [OrderController::class, 'destroy']);

        // Gestión de items del pedido
        Route::post('/orders/{order}/items', [OrderController::class, 'addItem']);
        Route::delete('/orders/{order}/items/{item}', [OrderController::class, 'removeItem']);

        // Acciones sobre pedidos
        Route::post('/orders/{order}/finalize', [OrderController::class, 'finalize']);
        Route::post('/orders/{order}/cancel', [OrderController::class, 'cancel']);
    });

    // ============ CLIENTES ============
    // Lectura (100/min)
    Route::middleware('throttle:api-read')->group(function () {
        Route::get('/clients', [ClientController::class, 'index']);
        Route::get('/clients/search', [ClientController::class, 'search']);
        Route::get('/clients/{client}', [ClientController::class, 'show']);
    });

    // Escritura (30/min)
    Route::middleware('throttle:api-write')->group(function () {
        Route::post('/clients', [ClientController::class, 'store']);
        Route::put('/clients/{client}', [ClientController::class, 'update']);
        Route::delete('/clients/{client}', [ClientController::class, 'destroy']);
    });

    // ============ MÉTODOS DE PAGO ============
    // Lectura (100/min)
    Route::middleware('throttle:api-read')->group(function () {
        Route::get('/payment-methods', [PaymentMethodController::class, 'index']);
        Route::get('/payment-methods/{paymentMethod}', [PaymentMethodController::class, 'show']);
    });

    // Escritura (30/min)
    Route::middleware('throttle:api-write')->group(function () {
        Route::post('/payment-methods', [PaymentMethodController::class, 'store']);
        Route::put('/payment-methods/{paymentMethod}', [PaymentMethodController::class, 'update']);
        Route::delete('/payment-methods/{paymentMethod}', [PaymentMethodController::class, 'destroy']);
        Route::post('/payment-methods/{paymentMethod}/toggle', [PaymentMethodController::class, 'toggleActive']);
    });

    // ============ STOCK ============
    // Lectura (100/min)
    Route::middleware('throttle:api-read')->group(function () {
        Route::get('/stock', [StockController::class, 'index']);
        Route::get('/stock/history', [StockController::class, 'history']);
        Route::get('/stock/low-stock', [StockController::class, 'lowStock']);
        Route::get('/stock/out-of-stock', [StockController::class, 'outOfStock']);
        Route::get('/stock/summary', [StockController::class, 'summary']);
    });
});

// Ruta legacy (mantener compatibilidad)
Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');
