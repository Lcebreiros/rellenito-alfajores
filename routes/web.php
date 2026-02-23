<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

// Controllers
use App\Http\Controllers\ProductController;
use App\Http\Controllers\OrderController;
use App\Http\Controllers\ClientController;
use App\Http\Controllers\StockController;
use App\Http\Controllers\SupportController;
use App\Http\Controllers\SupplyController;
use App\Http\Controllers\ServiceController;
use App\Http\Controllers\ProductCostController;
use App\Http\Controllers\CalculatorController;
use App\Http\Controllers\ParkingSpaceController;
use App\Http\Controllers\ParkingStayController;
use App\Http\Controllers\ParkingRateController;
// Master Controllers
use App\Http\Controllers\Master\InvitationController;
use App\Http\Controllers\Master\UserController;
use App\Http\Controllers\PaymentMethodController;
use App\Http\Controllers\ExpenseController;
use App\Http\Controllers\GoogleCalendarController;
use App\Http\Controllers\InvoiceController;

// Company Controllers
use App\Http\Controllers\Company\BranchController;
use App\Http\Controllers\Company\EmployeeController; // <-- import correcto para company employees

// Si tenés un EmployeeController específico para el namespace Branch, importalo con alias:
use App\Http\Controllers\Branch\UserController as BranchUserController;


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

// Fallback de logo de comprobante por defecto desde carpeta raíz images
Route::get('/branding/default-receipt', function () {
    $full = base_path('images/Gestior.png');
    abort_unless(is_file($full), 404);

    return response()->file($full, [
        'Content-Type'  => 'image/png',
        'Cache-Control' => 'public, max-age=604800',
    ]);
})->name('branding.default-receipt');

//
// Raíz => login (Jetstream)
//
Route::redirect('/', '/login');

//
// RUTAS PÚBLICAS DE REGISTRO CON PLANES
//
use App\Http\Controllers\Auth\PlanRegisterController;

Route::get('/plans', function () {
    return view('plans');
})->name('plans');

Route::get('/register/{plan}', [PlanRegisterController::class, 'show'])
    ->name('register.with-plan')
    ->where('plan', 'basic|premium|enterprise');

Route::post('/register/store', [PlanRegisterController::class, 'store'])
    ->name('register.store');

Route::get('/register/success', [PlanRegisterController::class, 'success'])
    ->name('register.success');

Route::get('/register-wizard', [PlanRegisterController::class, 'showWizard'])
    ->name('register.wizard');

Route::post('/register-wizard/store', [PlanRegisterController::class, 'storeWizard'])
    ->name('register.wizard.store');

//
// ÁREA PRIVADA (Jetstream / Sanctum / verified)
//
Route::middleware([
    'auth:sanctum',
    config('jetstream.auth_session'),
    'verified',
])->group(function () {
    // Logo por nivel de suscripción (sirve imágenes desde base_path('images'))
    Route::get('/branding/plan-logo', function () {
        $user = auth()->user();
        abort_unless($user, 401);

        $map = [
            'basic'      => 'gestior-basic.png',
            'premium'    => 'gestior-premium.png',
            'enterprise' => 'gestior-enterprise.png', // intentaremos esta, luego variante con doble i
        ];

        $default = 'Gestior.png';

        $file = $default;
        $level = $user->subscription_level;
        if (is_string($level) && isset($map[$level])) {
            $candidate = $map[$level];

            // Manejar enterprise con posibles nombres
            if ($level === 'enterprise') {
                $path1 = base_path('images/gestior-enterprise.png');
                $path2 = base_path('images/gestiior-enterprise.png');
                if (is_file($path1)) {
                    $candidate = 'gestior-enterprise.png';
                } elseif (is_file($path2)) {
                    $candidate = 'gestiior-enterprise.png';
                }
            }

            if (is_file(base_path('images/' . $candidate))) {
                $file = $candidate;
            }
        }

        $full = base_path('images/' . $file);
        abort_unless(is_file($full), 404);

        return response()->file($full, [
            'Content-Type'  => 'image/png',
            'Cache-Control' => 'public, max-age=604800',
        ]);
    })->name('branding.plan-logo');
    // Logo de comprobante del USUARIO autenticado (privado por usuario)
    Route::get('/me/receipt-logo', function () {
        $user = auth()->user();
        abort_unless($user, 401);

        $path = $user->receipt_logo_path;
        abort_unless($path && Storage::disk('public')->exists($path), 404);

        $full = Storage::disk('public')->path($path);
        return response()->file($full, [
            'Content-Type'  => Storage::disk('public')->mimeType($path) ?? 'image/png',
            'Cache-Control' => 'private, max-age=604800', // cache para el propio usuario
        ]);
    })->name('user.receipt-logo');

    // ============ DASHBOARD (Livewire) ============
    Route::get('/dashboard', function () {
        return view('dashboard');
    })->name('dashboard');

    // API endpoint para guardar posiciones de widgets (AJAX)
    Route::post('/dashboard/update-positions', [Dashboard::class, 'updatePositions'])
        ->name('dashboard.update-positions');

// ============ PRODUCTOS ============
    // Rutas específicas PRIMERO (antes del resource)
    Route::get('products/lookup', [ProductController::class, 'lookup'])
        ->name('products.lookup');
    
    Route::get('products/lookup-external', [ProductController::class, 'lookupExternal'])
        ->name('products.lookup.external');
    
    Route::patch('products/{product}/stock', [ProductController::class, 'updateStock'])
        ->name('products.stock.update');
    
    // Resource DESPUÉS
    Route::resource('products', ProductController::class)->except('show');
    Route::get('products/{product}', [ProductController::class, 'show'])->name('products.show');

    // ============ PEDIDOS ============
    // Rutas de pedidos (no hace falta re-aplicar 'auth' porque ya estamos dentro del grupo)
    // 1) Descargas / bulk actions
    Route::get('orders/download-report', [OrderController::class, 'downloadReport'])
        ->name('orders.download-report');

    Route::post('orders/bulk-delete', [OrderController::class, 'bulkDelete'])
        ->name('orders.bulk-delete');
    Route::post('orders/import-csv', [OrderController::class, 'importCsv'])
        ->name('orders.import-csv');

    // 2) CRUD y acciones por pedido
    Route::get('orders/create', [OrderController::class, 'create'])->name('orders.create');

    Route::post('orders/{order}/items', [OrderController::class, 'addItem'])->name('orders.items.store');
    Route::delete('orders/{order}/items/{item}', [OrderController::class, 'removeItem'])->name('orders.items.destroy');

    // Finalizar / cancelar pedido (un único endpoint para cancelar vía POST)
    Route::post('orders/{order}/finalize', [OrderController::class, 'finalize'])->name('orders.finalize');
    Route::post('orders/{order}/cancel', [OrderController::class, 'cancel'])->name('orders.cancel');
    // Confirmar / cancelar pedidos agendados (acciones desde notificaciones)
    Route::post('orders/{order}/confirm-scheduled', [OrderController::class, 'confirmScheduled'])->name('orders.confirm-scheduled');
    Route::post('orders/{order}/cancel-scheduled', [OrderController::class, 'cancelScheduled'])->name('orders.cancel-scheduled');
    // Actualizar agendamiento desde la vista de pedido
    Route::post('orders/{order}/schedule', [OrderController::class, 'schedule'])->name('orders.schedule');

    // Index / show / edit / update / destroy
    Route::get('orders', [OrderController::class, 'index'])->name('orders.index');
    Route::get('orders/{order}', [OrderController::class, 'show'])->whereNumber('order')->name('orders.show');
    Route::get('orders/{order}/edit', [OrderController::class, 'edit'])->whereNumber('order')->name('orders.edit');
    Route::match(['put','patch'], 'orders/{order}', [OrderController::class, 'update'])->whereNumber('order')->name('orders.update');
    Route::delete('orders/{order}', [OrderController::class, 'destroy'])->whereNumber('order')->name('orders.destroy');

    // ============ STOCK ============
    Route::get('/stock', [StockController::class, 'index'])->name('stock.index');
    Route::get('/stock/export/csv', [StockController::class, 'exportCsv'])->name('stock.export.csv');

    // Configuración de notificaciones de stock
    Route::post('/stock/notifications/update', [StockController::class, 'updateNotifications'])->name('stock.notifications.update');

    // ============ NOTIFICACIONES ============
    Route::get('/notifications', function () {
        return view('notifications.index');
    })->name('notifications.index');

    // Marcar notificación como leída (UserNotification)
    Route::post('/notifications/{id}/mark-as-read', function($id) {
        $notification = \App\Models\UserNotification::where('id', $id)
            ->where('user_id', auth()->id())
            ->first();

        if ($notification) {
            $notification->update(['is_read' => true]);
        }

        return response()->json(['success' => true]);
    })->name('notifications.mark-as-read');

    // Historial de stock — una sola ruta canonical
    Route::get('/stock/history', [StockController::class, 'history'])->name('stock.history');
    // Detalle de stock por producto
    Route::get('/stock/{product}', [StockController::class, 'show'])->name('stock.show');

    // Si querés mantener el viejo path alias (opcional), redirige al canonical:
    Route::get('/stock-adjustments/history', fn() => redirect()->route('stock.history'));

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

    // ============ CLIENTES ============
    Route::resource('clients', ClientController::class);

    // ============ SERVICIOS ============
    Route::resource('services', ServiceController::class);

    // ============ ESTACIONAMIENTO ============
    // Solo accesible para empresas con módulo "parking" activo
    Route::middleware(['module:parking'])->group(function () {
        Route::resource('parking/spaces', ParkingSpaceController::class)->names('parking.spaces')->except(['show', 'create', 'edit']);
        Route::post('parking/space-categories', [ParkingSpaceController::class, 'storeCategory'])->name('parking.space-categories.store');
        Route::get('parking/board', [ParkingStayController::class, 'board'])->name('parking.board');
        Route::post('parking/shifts/start', [ParkingStayController::class, 'startShift'])->name('parking.shifts.start');
        Route::post('parking/shifts/close', [ParkingStayController::class, 'closeShift'])->name('parking.shifts.close');
        Route::get('parking/shifts/{shift}/download', [ParkingStayController::class, 'downloadShift'])->name('parking.shifts.download');
        Route::post('parking/spaces/{parkingSpace}/open', [ParkingStayController::class, 'openSpace'])->name('parking.spaces.open');
        Route::post('parking/spaces/{parkingSpace}/close', [ParkingStayController::class, 'closeSpace'])->name('parking.spaces.close');
        Route::resource('parking/rates', ParkingRateController::class)->names('parking.rates')->except(['show', 'create', 'edit']);

        Route::post('parking/stays/check', [ParkingStayController::class, 'check'])
            ->name('parking.stays.check');

        // Gestión de turnos
        Route::get('parking/shifts/my-history', [App\Http\Controllers\ParkingShiftController::class, 'myHistory'])
            ->name('parking.shifts.my-history');
        Route::get('parking/shifts/audit', [App\Http\Controllers\ParkingShiftController::class, 'audit'])
            ->name('parking.shifts.audit');
        Route::get('parking/shifts/{shift}', [App\Http\Controllers\ParkingShiftController::class, 'show'])
            ->name('parking.shifts.show');
    });

    // ============ GASTOS ============
    Route::get('/expenses', [ExpenseController::class, 'index'])->name('expenses.index');

    // Gastos de proveedores
    Route::get('/expenses/suppliers', [ExpenseController::class, 'suppliers'])->name('expenses.suppliers');
    Route::post('/expenses/suppliers', [ExpenseController::class, 'storeSupplier'])->name('expenses.suppliers.store');
    Route::put('/expenses/suppliers/{expense}', [ExpenseController::class, 'updateSupplier'])->name('expenses.suppliers.update');
    Route::delete('/expenses/suppliers/{expense}', [ExpenseController::class, 'destroySupplier'])->name('expenses.suppliers.destroy');

    // Gastos de servicios
    Route::get('/expenses/services', [ExpenseController::class, 'services'])->name('expenses.services');
    Route::post('/expenses/services', [ExpenseController::class, 'storeService'])->name('expenses.services.store');
    Route::put('/expenses/services/{expense}', [ExpenseController::class, 'updateService'])->name('expenses.services.update');
    Route::delete('/expenses/services/{expense}', [ExpenseController::class, 'destroyService'])->name('expenses.services.destroy');

    // Servicios de terceros
    Route::get('/expenses/third-party', [ExpenseController::class, 'thirdParty'])->name('expenses.third-party');
    Route::post('/expenses/third-party', [ExpenseController::class, 'storeThirdParty'])->name('expenses.third-party.store');
    Route::put('/expenses/third-party/{service}', [ExpenseController::class, 'updateThirdParty'])->name('expenses.third-party.update');
    Route::delete('/expenses/third-party/{service}', [ExpenseController::class, 'destroyThirdParty'])->name('expenses.third-party.destroy');

    // Gastos de producción
    Route::get('/expenses/production', [ExpenseController::class, 'production'])->name('expenses.production');
    Route::post('/expenses/production', [ExpenseController::class, 'storeProduction'])->name('expenses.production.store');
    Route::put('/expenses/production/{expense}', [ExpenseController::class, 'updateProduction'])->name('expenses.production.update');
    Route::delete('/expenses/production/{expense}', [ExpenseController::class, 'destroyProduction'])->name('expenses.production.destroy');

    // Insumos
    Route::get('/expenses/supplies', [ExpenseController::class, 'supplies'])->name('expenses.supplies');
    Route::post('/expenses/supplies', [ExpenseController::class, 'storeSupply'])->name('expenses.supplies.store');
    Route::put('/expenses/supplies/{supply}', [ExpenseController::class, 'updateSupply'])->name('expenses.supplies.update');
    Route::delete('/expenses/supplies/{supply}', [ExpenseController::class, 'destroySupply'])->name('expenses.supplies.destroy');

    // Gestión de Proveedores (nuevo)
    Route::get('/suppliers', [ExpenseController::class, 'suppliersManagement'])->name('suppliers.index');
    Route::post('/suppliers', [ExpenseController::class, 'storeSupplierEntity'])->name('suppliers.store');
    Route::put('/suppliers/{supplier}', [ExpenseController::class, 'updateSupplierEntity'])->name('suppliers.update');
    Route::delete('/suppliers/{supplier}', [ExpenseController::class, 'destroySupplierEntity'])->name('suppliers.destroy');

    // ============ MÉTODOS DE PAGO ============
    Route::resource('payment-methods', PaymentMethodController::class)->except(['show']);
    Route::post('payment-methods/{paymentMethod}/toggle', [PaymentMethodController::class, 'toggleActive'])->name('payment-methods.toggle');
    Route::post('payment-methods/{paymentMethod}/toggle-global', [PaymentMethodController::class, 'toggleGlobal'])->name('payment-methods.toggle-global');

    // ============ DESCUENTOS Y BONIFICACIONES ============
    Route::resource('discounts', App\Http\Controllers\DiscountController::class)->except(['show']);
    Route::post('discounts/{discount}/toggle', [App\Http\Controllers\DiscountController::class, 'toggle'])->name('discounts.toggle');

    // ============ FACTURACIÓN ELECTRÓNICA ============
    Route::prefix('invoices')->name('invoices.')->group(function () {
        // Configuración ARCA
        Route::get('configuration', [InvoiceController::class, 'configuration'])->name('configuration');
        Route::post('configuration', [InvoiceController::class, 'saveConfiguration'])->name('configuration.save');

        // CRUD de facturas
        Route::get('/', [InvoiceController::class, 'index'])->name('index');
        Route::get('create', [InvoiceController::class, 'create'])->name('create');
        Route::post('/', [InvoiceController::class, 'store'])->name('store');
        Route::get('{invoice}', [InvoiceController::class, 'show'])->name('show');
        Route::get('{invoice}/edit', [InvoiceController::class, 'edit'])->name('edit');
        Route::put('{invoice}', [InvoiceController::class, 'update'])->name('update');
        Route::delete('{invoice}', [InvoiceController::class, 'destroy'])->name('destroy');

        // ARCA y PDF
        Route::post('{invoice}/send-to-arca', [InvoiceController::class, 'sendToArca'])->name('send-to-arca');
        Route::get('{invoice}/download-pdf', [InvoiceController::class, 'downloadPdf'])->name('download-pdf');
        Route::post('{invoice}/regenerate-pdf', [InvoiceController::class, 'regeneratePdf'])->name('regenerate-pdf');
    });

    // ============ GOOGLE CALENDAR ============
    Route::prefix('google')->name('google.')->group(function () {
        Route::get('connect', [GoogleCalendarController::class, 'redirect'])->name('connect');
        Route::get('callback', [GoogleCalendarController::class, 'callback'])->name('callback');
        Route::post('disconnect', [GoogleCalendarController::class, 'disconnect'])->name('disconnect');
        Route::post('toggle-sync', [GoogleCalendarController::class, 'toggleSync'])->name('toggle-sync');
        Route::get('status', [GoogleCalendarController::class, 'status'])->name('status');

        // Test route - remove in production
        Route::get('debug', function () {
            $user = auth()->user();
            return response()->json([
                'user_id' => $user->id,
                'google_access_token' => $user->google_access_token ? 'SET' : 'NULL',
                'google_refresh_token' => $user->google_refresh_token ? 'SET' : 'NULL',
                'google_email' => $user->google_email ?? 'NULL',
                'google_calendar_sync_enabled' => $user->google_calendar_sync_enabled ?? false,
                'has_tokens' => ($user->google_access_token && $user->google_refresh_token) ? 'YES' : 'NO',
            ]);
        })->name('debug');
    });

    // Test Google Calendar - remove in production
    // Route::get('/test-google', fn () => view('test-google'))->name('test-google');

    // (Revert) Recibir productos: eliminado

    // ============ SOPORTE ============
    Route::get('/support', [SupportController::class, 'index'])->name('support.index');
    Route::post('/support', [SupportController::class, 'store'])->name('support.store');
    Route::get('/support/{ticket}', [SupportController::class, 'show'])->name('support.show');
    Route::post('/support/{ticket}/reply', [SupportController::class, 'reply'])->name('support.reply');
    Route::put('/support/{ticket}/status', [SupportController::class, 'updateStatus'])->name('support.status');

    // ============ PUSHER TEST ============
    Route::post('/test-notification', function() {
        $user = auth()->user();
        $notification = \App\Models\UserNotification::create([
            'user_id' => $user->id,
            'type' => 'test',
            'title' => 'Notificación de prueba',
            'message' => 'Esta es una notificación de prueba de Pusher - ' . now()->format('H:i:s'),
            'data' => ['test' => true],
        ]);

        broadcast(new \App\Events\NewNotification($notification))->toOthers();

        return response()->json([
            'success' => true,
            'notification' => $notification,
            'message' => 'Notificación enviada. Revisa la consola del navegador para ver el evento.'
        ]);
    })->name('test.notification');
});

// ------------------------ MASTER: Invitations (UI) ------------------------
Route::prefix('master/invitations')
    ->name('master.invitations.')
    ->middleware(['auth'])
    ->group(function () {
        // Rutas específicas primero (antes de las dinámicas)
        Route::get('/stats/json', [InvitationController::class, 'stats'])->name('stats');
        
        // CRUD básico
        Route::get('/', [InvitationController::class, 'index'])->name('index');
        Route::post('/', [InvitationController::class, 'store'])->name('store');
        Route::get('/{invitation}', [InvitationController::class, 'show'])->name('show');
        
        // Acciones sobre invitación específica
        Route::patch('/{invitation}/revoke', [InvitationController::class, 'revoke'])->name('revoke');
        Route::post('/{invitation}/regenerate', [InvitationController::class, 'regenerate'])->name('regenerate');
    });

// ------------------------ MASTER: Usuarios ------------------------
Route::prefix('master')
    ->name('master.')
    ->middleware(['auth'])
    ->group(function () {
        Route::get('users', [UserController::class, 'index'])->name('users.index');
        Route::get('users/{user}', [UserController::class, 'show'])->name('users.show');
        Route::get('users/{user}/edit', [UserController::class, 'edit'])->name('users.edit');
        Route::put('users/{user}', [UserController::class, 'update'])->name('users.update');

        // Suspender / reactivar (toggle) -> POST por seguridad
        Route::post('users/{user}/toggle-active', [UserController::class, 'toggleActive'])
            ->name('users.toggleActive');

        // Resetear contraseña (master establece una nueva)
        Route::post('users/{user}/reset-password', [UserController::class, 'resetPassword'])->name('users.resetPassword');

        // Eliminar usuario
        Route::delete('users/{user}', [UserController::class, 'destroy'])->name('users.destroy');
    });

/*
|--------------------------------------------------------------------------
| Company area - gestión de sucursales
|--------------------------------------------------------------------------
*/
Route::prefix('company')
    ->middleware(['auth'])
    ->name('company.')
    ->group(function () {
        
        // Rutas tradicionales de sucursales
        Route::get('branches', [BranchController::class, 'index'])->name('branches.index');
        Route::get('branches/create', [BranchController::class, 'create'])->name('branches.create');
        Route::post('branches', [BranchController::class, 'store'])->name('branches.store');
        Route::get('branches/{branch}', [BranchController::class, 'show'])->name('branches.show');
        Route::get('branches/{branch}/edit', [BranchController::class, 'edit'])->name('branches.edit');
        Route::put('branches/{branch}', [BranchController::class, 'update'])->name('branches.update');
        Route::delete('branches/{branch}', [BranchController::class, 'destroy'])->name('branches.destroy');
        
        // Ruta específica para ver usuarios de una sucursal
        Route::get('branches/{branch}/users', [BranchController::class, 'users'])->name('branches.users');
        
        // Ruta para página de creación con Livewire
        Route::get('branches/create/livewire', function () {
            return view('company.branches.create-livewire');
        })->name('branches.create-livewire');
    });

/*
|--------------------------------------------------------------------------
| Branch area - para administradores de sucursal
|--------------------------------------------------------------------------
*/
Route::prefix('branch')
    ->middleware(['auth'])
    ->name('branch.')
    ->group(function () {
        Route::resource('users', BranchUserController::class);
    });
    
/*
|--------------------------------------------------------------------------
| Employee area - para empleados
|--------------------------------------------------------------------------
*/
Route::middleware(['auth'])
    ->name('company.')
    ->group(function () {

        // Resource RESTful principal: company.employees.*
        // Usa binding implícito: {employee} => App\Models\Employee
        Route::resource('employees', EmployeeController::class);

        // Ruta adicional para importaciones masivas (form upload -> POST)
        Route::post('employees/import', [EmployeeController::class, 'bulkImport'])
            ->name('employees.import');

        // Opcional: export CSV (si la implementás)
        Route::get('employees/export', [EmployeeController::class, 'export'])
            ->name('employees.export');

        // Opcional: descargar contrato (si servís archivos desde controlador)
        Route::get('employees/{employee}/contract', [EmployeeController::class, 'downloadContract'])
            ->name('employees.contract.download');

        // Opcional: endpoint AJAX para togglear "has_computer" por ejemplo
        Route::post('employees/{employee}/toggle-computer', [EmployeeController::class, 'toggleComputer'])
            ->name('employees.toggle-computer');

        // Notas y Evaluaciones
        Route::post('employees/{employee}/evaluations', [EmployeeController::class, 'addEvaluation'])
            ->name('employees.evaluations.add');
        Route::post('employees/{employee}/notes', [EmployeeController::class, 'addNote'])
            ->name('employees.notes.add');

        // Si preferís controlar permisos por ruta en vez de policies dentro del controlador:
        // Route::resource('employees', EmployeeController::class)
        //     ->middleware(['can:manage-employees']); // ejemplo de Gate
});

// Inicio (landing con accesos rápidos)
Route::get('/inicio', function () {
    return view('inicio');
})->middleware(['auth'])->name('inicio');

// Clientes (CRM básico)
Route::middleware(['auth'])->group(function () {
    Route::resource('clients', ClientController::class);

    // Prueba de Pusher - comentado para producción
    // Route::get('/test-pusher', function () {
    //     return view('test-pusher');
    // })->name('test.pusher');
});

/*
|--------------------------------------------------------------------------
| API V1 Routes
|--------------------------------------------------------------------------
*/
/*Route::prefix('v1')
    ->middleware(['auth:sanctum'])
    ->name('api.v1.')
    ->group(function () {
        
        // API de sucursales
        Route::prefix('branches')->name('branches.')->group(function () {
            Route::get('/', [ApiBranchController::class, 'index'])->name('index');
            Route::post('/', [ApiBranchController::class, 'store'])->name('store');
            Route::get('/{branch}', [ApiBranchController::class, 'show'])->name('show');
            Route::put('/{branch}', [ApiBranchController::class, 'update'])->name('update');
            Route::delete('/{branch}', [ApiBranchController::class, 'destroy'])->name('destroy');
            Route::get('/{branch}/users', [ApiBranchController::class, 'users'])->name('users');
        });
    });*/

Route::get('/trial-requests', function () {
    return view('trial-requests');
})->name('trial-requests')->middleware(['auth:sanctum', config('jetstream.auth_session')]);

// ── Nexum ────────────────────────────────────────────────────────────────
Route::middleware(['auth:sanctum', config('jetstream.auth_session'), 'verified'])->group(function () {
    Route::get('/nexum', function () {
        return view('nexum');
    })->name('nexum');

    // Descarga autenticada con URL firmada temporalmente (para la app mobile)
    Route::get('/nexum/reports/{report}/signed-download', function (\App\Models\GeneratedReport $report) {
        abort_unless(request()->hasValidSignature(), 403);
        if (! $report->isReady()) {
            abort(404, 'Reporte no disponible.');
        }
        $report->markDownloaded();
        return response()->streamDownload(function () use ($report) {
            echo \Illuminate\Support\Facades\Storage::get($report->file_path);
        }, 'nexum-reporte-' . $report->period_start->format('Y-m') . '.pdf', [
            'Content-Type' => 'application/pdf',
        ]);
    })->name('nexum.reports.signed-download')->withoutMiddleware([\App\Http\Middleware\Authenticate::class]);

    Route::get('/nexum/reports/{report}/download', function (\App\Models\GeneratedReport $report) {
        if ($report->user_id !== auth()->id()) {
            abort(403);
        }
        if (!$report->isReady()) {
            abort(404, 'Reporte no disponible.');
        }
        $report->markDownloaded();
        return response()->streamDownload(function () use ($report) {
            echo \Illuminate\Support\Facades\Storage::get($report->file_path);
        }, 'nexum-reporte-' . $report->period_start->format('Y-m') . '.pdf', [
            'Content-Type' => 'application/pdf',
        ]);
    })->name('nexum.reports.download');

    Route::get('/nexum/reports/{report}/view', function (\App\Models\GeneratedReport $report) {
        if ($report->user_id !== auth()->id()) {
            abort(403);
        }
        if (!$report->isReady()) {
            abort(404, 'Reporte no disponible.');
        }
        $content = \Illuminate\Support\Facades\Storage::get($report->file_path);
        return response($content, 200, [
            'Content-Type'        => 'application/pdf',
            'Content-Disposition' => 'inline; filename="nexum-' . $report->period_start->format('Y-m') . '.pdf"',
        ]);
    })->name('nexum.reports.view');
});
