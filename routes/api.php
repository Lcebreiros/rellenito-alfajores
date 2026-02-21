<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\ProductController;
use App\Http\Controllers\Api\OrderController;
use App\Http\Controllers\Api\ClientController;
use App\Http\Controllers\Api\PaymentMethodController;
use App\Http\Controllers\Api\StockController;
use App\Http\Controllers\Api\ServiceController as ApiServiceController;
use App\Http\Controllers\Api\SupplyController as ApiSupplyController;
use App\Http\Controllers\Api\SupplierController as ApiSupplierController;
use App\Http\Controllers\Api\SupplierExpenseController as ApiSupplierExpenseController;
use App\Http\Controllers\Api\ServiceExpenseController as ApiServiceExpenseController;
use App\Http\Controllers\Api\ProductionExpenseController as ApiProductionExpenseController;
use App\Http\Controllers\Api\ExpenseSummaryController;
use App\Http\Controllers\Api\DashboardWidgetController;
use App\Http\Controllers\Api\SettingsController;
use App\Http\Controllers\Api\FinanceController;
use App\Http\Controllers\Api\NotificationController;
use App\Http\Controllers\Api\SupportController as ApiSupportController;
use App\Http\Controllers\Api\BranchController;
use App\Http\Controllers\Api\ReportController;
use App\Http\Controllers\Api\ParkingTicketController;
use App\Http\Controllers\Api\EmployeeController;
use App\Http\Controllers\Api\InvoiceController;
use App\Http\Controllers\Api\ServiceCategoryController;
use App\Http\Controllers\Api\CalendarController;
use App\Http\Controllers\Api\InsightController;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Rutas API para consumo desde aplicación Android
|
*/

// Rutas públicas (sin autenticación) - Con rate limiting estricto
Route::prefix('v1')->middleware(['throttle:api-auth', 'api.log'])->group(function () {
    // Autenticación - 5 intentos por minuto por IP
    Route::post('/auth/login', [AuthController::class, 'login']);
    Route::post('/auth/register', [AuthController::class, 'register']);
});

// Rutas protegidas (requieren autenticación Sanctum)
Route::prefix('v1')->middleware(['auth:sanctum', 'throttle:api', 'integrator', 'api.log'])->group(function () {

    // ============ AUTENTICACIÓN ============
    Route::post('/auth/logout', [AuthController::class, 'logout']);
    Route::post('/auth/logout-all', [AuthController::class, 'logoutAll']);
    Route::get('/auth/me', [AuthController::class, 'me']);

    // ============ SETTINGS ============
    Route::middleware('throttle:api-read')->group(function () {
        Route::get('/settings', [SettingsController::class, 'show']);
    });

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

        // Gestión de recetas (insumos en productos)
        Route::get('/products/{product}/recipe', [ProductController::class, 'getRecipe']);
        Route::post('/products/{product}/recipe', [ProductController::class, 'addRecipeItem']);
        Route::put('/products/{product}/recipe/{recipe}', [ProductController::class, 'updateRecipeItem']);
        Route::delete('/products/{product}/recipe/{recipe}', [ProductController::class, 'removeRecipeItem']);
    });

    // ============ PARKING - IMPRESIÓN DE TICKETS ============
    Route::middleware('throttle:api-read')->group(function () {
        Route::get('/parking-stays/{parkingStay}/ticket', [ParkingTicketController::class, 'show']);
    });
    Route::middleware('throttle:api-write')->group(function () {
        Route::post('/parking-stays/{parkingStay}/print', [ParkingTicketController::class, 'print']);
    });

    // ============ PEDIDOS ============
    // Lectura (100/min)
    Route::middleware('throttle:api-read')->group(function () {
        Route::get('/orders', [OrderController::class, 'index']);
        Route::get('/orders/create/context', [OrderController::class, 'createContext']);
        Route::get('/orders/{order}', [OrderController::class, 'show']);
        Route::get('/orders/{order}/ticket', [OrderController::class, 'ticket']);
        Route::get('/comprobantes/{order}', [OrderController::class, 'ticketPdf']);
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
        Route::get('/stock-adjustments/{adjustment}', [StockController::class, 'showAdjustment']);
    });

    // Escritura (30/min) - Ajustes de stock
    Route::middleware('throttle:api-write')->group(function () {
        Route::post('/stock-adjustments', [StockController::class, 'createAdjustment']);
        Route::delete('/stock-adjustments/{adjustment}', [StockController::class, 'deleteAdjustment']);
    });

    // ============ SERVICIOS ============
    Route::middleware('throttle:api-read')->group(function () {
        Route::get('/services', [ApiServiceController::class, 'index']);
        Route::get('/services/search', [ApiServiceController::class, 'search']);
        Route::get('/services/{service}', [ApiServiceController::class, 'show']);
    });

    Route::middleware('throttle:api-write')->group(function () {
        Route::post('/services', [ApiServiceController::class, 'store']);
        Route::put('/services/{service}', [ApiServiceController::class, 'update']);
        Route::delete('/services/{service}', [ApiServiceController::class, 'destroy']);

        // Gestión de insumos en servicios
        Route::get('/services/{service}/supplies', [ApiServiceController::class, 'getSupplies']);
        Route::post('/services/{service}/supplies', [ApiServiceController::class, 'addSupply']);
        Route::put('/services/{service}/supplies/{serviceSupply}', [ApiServiceController::class, 'updateSupply']);
        Route::delete('/services/{service}/supplies/{serviceSupply}', [ApiServiceController::class, 'removeSupply']);
    });

    // Gestión de variantes de servicios
    Route::middleware('throttle:api-read')->group(function () {
        Route::get('/services/{service}/variants', [ApiServiceController::class, 'getVariants']);
    });

    Route::middleware('throttle:api-write')->group(function () {
        Route::post('/services/{service}/variants', [ApiServiceController::class, 'createVariant']);
        Route::put('/services/{service}/variants/{variant}', [ApiServiceController::class, 'updateVariant']);
        Route::delete('/services/{service}/variants/{variant}', [ApiServiceController::class, 'deleteVariant']);
        Route::post('/services/{service}/variants/{variant}/toggle', [ApiServiceController::class, 'toggleVariant']);
    });

    // ============ CATEGORÍAS DE SERVICIOS ============
    Route::middleware('throttle:api-read')->group(function () {
        Route::get('/service-categories', [ServiceCategoryController::class, 'index']);
        Route::get('/service-categories/{category}', [ServiceCategoryController::class, 'show']);
    });

    Route::middleware('throttle:api-write')->group(function () {
        Route::post('/service-categories', [ServiceCategoryController::class, 'store']);
        Route::put('/service-categories/{category}', [ServiceCategoryController::class, 'update']);
        Route::delete('/service-categories/{category}', [ServiceCategoryController::class, 'destroy']);
    });

    // ============ INSUMOS ============
    Route::middleware(['throttle:api-read', 'internal.only'])->group(function () {
        Route::get('/supplies', [ApiSupplyController::class, 'index']);
        Route::get('/supplies/{supply}', [ApiSupplyController::class, 'show']);
    });

    Route::middleware(['throttle:api-write', 'internal.only'])->group(function () {
        Route::post('/supplies', [ApiSupplyController::class, 'store']);
        Route::put('/supplies/{supply}', [ApiSupplyController::class, 'update']);
        Route::delete('/supplies/{supply}', [ApiSupplyController::class, 'destroy']);
        Route::post('/supplies/{supply}/purchase', [ApiSupplyController::class, 'purchase']);
    });

    // ============ PROVEEDORES ============
    Route::middleware(['throttle:api-read', 'internal.only'])->group(function () {
        Route::get('/suppliers', [ApiSupplierController::class, 'index']);
        Route::get('/suppliers/{supplier}', [ApiSupplierController::class, 'show']);
    });
    Route::middleware(['throttle:api-write', 'internal.only'])->group(function () {
        Route::post('/suppliers', [ApiSupplierController::class, 'store']);
        Route::put('/suppliers/{supplier}', [ApiSupplierController::class, 'update']);
        Route::delete('/suppliers/{supplier}', [ApiSupplierController::class, 'destroy']);
    });

    // ============ GASTOS ============
    Route::middleware(['throttle:api-read', 'internal.only'])->group(function () {
        Route::get('/expenses/suppliers', [ApiSupplierExpenseController::class, 'index']);
        Route::get('/expenses/services', [ApiServiceExpenseController::class, 'index']);
        Route::get('/expenses/production', [ApiProductionExpenseController::class, 'index']);
        Route::get('/expenses/summary', [ExpenseSummaryController::class, 'summary']);
    });

    Route::middleware(['throttle:api-write', 'internal.only'])->group(function () {
        Route::post('/expenses/suppliers', [ApiSupplierExpenseController::class, 'store']);
        Route::put('/expenses/suppliers/{expense}', [ApiSupplierExpenseController::class, 'update']);
        Route::delete('/expenses/suppliers/{expense}', [ApiSupplierExpenseController::class, 'destroy']);

        Route::post('/expenses/services', [ApiServiceExpenseController::class, 'store']);
        Route::put('/expenses/services/{expense}', [ApiServiceExpenseController::class, 'update']);
        Route::delete('/expenses/services/{expense}', [ApiServiceExpenseController::class, 'destroy']);

        Route::post('/expenses/production', [ApiProductionExpenseController::class, 'store']);
        Route::put('/expenses/production/{expense}', [ApiProductionExpenseController::class, 'update']);
        Route::delete('/expenses/production/{expense}', [ApiProductionExpenseController::class, 'destroy']);
    });

    // ============ DASHBOARD / WIDGETS ============
    Route::middleware('throttle:api-read')->group(function () {
        Route::get('/widgets', [DashboardWidgetController::class, 'index']);
        Route::get('/widgets/available', [DashboardWidgetController::class, 'available']);
        Route::get('/finance/summary', [FinanceController::class, 'summary']);
    });

    // ============ SUCURSALES (BRANCHES) ============
    // Lectura (100/min)
    Route::middleware('throttle:api-read')->group(function () {
        Route::get('/branches', [BranchController::class, 'index']);
        Route::get('/branches/{branch}', [BranchController::class, 'show']);
    });

    // Escritura (30/min)
    Route::middleware('throttle:api-write')->group(function () {
        Route::post('/branches', [BranchController::class, 'store']);
        Route::put('/branches/{branch}', [BranchController::class, 'update']);
        Route::delete('/branches/{branch}', [BranchController::class, 'destroy']);
        Route::post('/branches/{branch}/toggle', [BranchController::class, 'toggle']);
    });

    // ============ EMPLEADOS (EMPLOYEES) ============
    // Lectura (100/min)
    Route::middleware('throttle:api-read')->group(function () {
        Route::get('/employees', [EmployeeController::class, 'index']);
        Route::get('/employees/{employee}', [EmployeeController::class, 'show']);
        Route::get('/employees/{employee}/shifts', [EmployeeController::class, 'shifts']);
    });

    // Escritura (30/min)
    Route::middleware('throttle:api-write')->group(function () {
        Route::post('/employees', [EmployeeController::class, 'store']);
        Route::put('/employees/{employee}', [EmployeeController::class, 'update']);
        Route::delete('/employees/{employee}', [EmployeeController::class, 'destroy']);
        Route::post('/employees/{employee}/toggle', [EmployeeController::class, 'toggleActive']);
    });

    // ============ FACTURAS (INVOICES) ============
    // Lectura (100/min)
    Route::middleware('throttle:api-read')->group(function () {
        Route::get('/invoices', [InvoiceController::class, 'index']);
        Route::get('/invoices/{invoice}', [InvoiceController::class, 'show']);
    });

    // Escritura (30/min)
    Route::middleware('throttle:api-write')->group(function () {
        Route::post('/invoices', [InvoiceController::class, 'store']);
        Route::post('/invoices/{invoice}/send', [InvoiceController::class, 'send']);
    });

    // ============ REPORTES ============
    // Lectura (100/min)
    Route::middleware('throttle:api-read')->prefix('reports')->group(function () {
        Route::get('/sales-summary', [ReportController::class, 'salesSummary']);
        Route::get('/top-products', [ReportController::class, 'topProducts']);
        Route::get('/clients', [ReportController::class, 'clientsReport']);
        Route::get('/stock', [ReportController::class, 'stockReport']);
        Route::get('/export', [ReportController::class, 'exportData']);
    });

    // ============ NOTIFICACIONES ============
    Route::middleware('throttle:api-read')->group(function () {
        Route::get('/notifications', [NotificationController::class, 'index']);
        Route::get('/notifications/unread-count', [NotificationController::class, 'unreadCount']);
    });
    Route::middleware('throttle:api-write')->group(function () {
        Route::post('/notifications/{notification}/read', [NotificationController::class, 'markAsRead']);
        Route::post('/notifications/read-all', [NotificationController::class, 'markAllAsRead']);
    });

    // ============ SOPORTE / CHAT ============
    Route::middleware('throttle:api-read')->group(function () {
        Route::get('/support/tickets', [ApiSupportController::class, 'index']);
        Route::get('/support/tickets/{ticket}', [ApiSupportController::class, 'show']);
    });
    Route::middleware('throttle:api-write')->group(function () {
        Route::post('/support/tickets', [ApiSupportController::class, 'store']);
        Route::post('/support/tickets/{ticket}/reply', [ApiSupportController::class, 'reply']);
        Route::patch('/support/tickets/{ticket}/status', [ApiSupportController::class, 'updateStatus']);
    });

    // ============ CALENDARIO ============
    Route::middleware('throttle:api-read')->group(function () {
        Route::get('/calendar/summary', [CalendarController::class, 'summary']);
        Route::get('/calendar/month/{year}/{month}', [CalendarController::class, 'monthEvents']);
        Route::get('/calendar/range', [CalendarController::class, 'rangeEvents']);
        Route::get('/calendar/upcoming', [CalendarController::class, 'upcoming']);
        Route::get('/calendar/overdue', [CalendarController::class, 'overdue']);
        Route::get('/calendar/type/{type}', [CalendarController::class, 'byType']);
        Route::get('/calendar/events/{event}', [CalendarController::class, 'show']);
    });
    Route::middleware('throttle:api-write')->group(function () {
        Route::post('/calendar/events', [CalendarController::class, 'store']);
        Route::put('/calendar/events/{event}', [CalendarController::class, 'update']);
        Route::post('/calendar/events/{event}/complete', [CalendarController::class, 'complete']);
        Route::delete('/calendar/events/{event}', [CalendarController::class, 'destroy']);
    });

    // ============ BUSINESS INSIGHTS ============
    Route::middleware('throttle:api-read')->group(function () {
        Route::get('/insights', [InsightController::class, 'index']);
        Route::get('/insights/stats', [InsightController::class, 'stats']);
        Route::get('/insights/health-report', [InsightController::class, 'healthReport']);
    });
    Route::middleware('throttle:api-write')->group(function () {
        Route::post('/insights/generate', [InsightController::class, 'generate']);
        Route::patch('/insights/{id}/dismiss', [InsightController::class, 'dismiss']);
    });
});

// ============ BÚSQUEDA GLOBAL ============
Route::middleware(['auth:sanctum', 'throttle:api-read'])->group(function () {
    Route::get('/search', function (Request $request) {
        $query = $request->input('q', '');

        if (strlen($query) < 2) {
            return response()->json(['results' => []]);
        }

        $user = $request->user();
        $results = [];

        // Buscar productos
        $products = \App\Models\Product::query()
            ->where('user_id', $user->id)
            ->where(function($q) use ($query) {
                $q->where('name', 'LIKE', "%{$query}%")
                  ->orWhere('sku', 'LIKE', "%{$query}%");
            })
            ->limit(5)
            ->get();

        foreach ($products as $product) {
            $results[] = [
                'id' => 'product-' . $product->id,
                'type' => 'producto',
                'title' => $product->name,
                'subtitle' => 'SKU: ' . ($product->sku ?? 'N/A') . ' • Stock: ' . ($product->stock ?? 0),
                'url' => route('products.edit', $product),
            ];
        }

        // Buscar pedidos
        $orders = \App\Models\Order::query()
            ->where('user_id', $user->id)
            ->where('order_number', 'LIKE', "%{$query}%")
            ->limit(5)
            ->get();

        foreach ($orders as $order) {
            $results[] = [
                'id' => 'order-' . $order->id,
                'type' => 'pedido',
                'title' => 'Pedido #' . $order->order_number,
                'subtitle' => 'Total: $' . number_format($order->total, 2),
                'url' => route('orders.show', $order),
            ];
        }

        // Buscar clientes
        $clients = \App\Models\Client::query()
            ->where('user_id', $user->id)
            ->where(function($q) use ($query) {
                $q->where('name', 'LIKE', "%{$query}%")
                  ->orWhere('email', 'LIKE', "%{$query}%")
                  ->orWhere('phone', 'LIKE', "%{$query}%");
            })
            ->limit(5)
            ->get();

        foreach ($clients as $client) {
            $results[] = [
                'id' => 'client-' . $client->id,
                'type' => 'cliente',
                'title' => $client->name,
                'subtitle' => $client->email ?? $client->phone ?? 'Sin contacto',
                'url' => route('clients.show', $client),
            ];
        }

        return response()->json(['results' => $results]);
    });
});

// ============ HEALTH CHECK ============
Route::get('/health', function () {
    return response()->json([
        'status' => 'ok',
        'timestamp' => now()->toIso8601String(),
    ]);
});

// Ruta legacy (mantener compatibilidad)
Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');
