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
use App\Http\Controllers\ProductCostController;
use App\Http\Controllers\CalculatorController;
// Master Controllers
use App\Http\Controllers\Master\InvitationController;
use App\Http\Controllers\Master\UserController;

// Company Controllers
use App\Http\Controllers\Company\BranchController;
use App\Http\Controllers\Company\EmployeeController; // <-- import correcto para company employees

// Si tenés un EmployeeController específico para el namespace Branch, importalo con alias:
use App\Http\Controllers\Branch\UserController as BranchUserController;
use App\Http\Controllers\Branch\EmployeeController as BranchEmployeeController; // <-- solo si existe realmente


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
    Route::resource('products', ProductController::class)->except('show');
    // web.php
Route::get('/products/{product}', [ProductController::class, 'show'])->name('products.show');


    // Actualizar stock de un producto (un único nombre de ruta)
    Route::patch('products/{product}/stock', [ProductController::class, 'updateStock'])
        ->name('products.stock.update');

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

    // Index / show / edit / update / destroy
    Route::get('orders', [OrderController::class, 'index'])->name('orders.index');
    Route::get('orders/{order}', [OrderController::class, 'show'])->whereNumber('order')->name('orders.show');
    Route::get('orders/{order}/edit', [OrderController::class, 'edit'])->whereNumber('order')->name('orders.edit');
    Route::match(['put','patch'], 'orders/{order}', [OrderController::class, 'update'])->whereNumber('order')->name('orders.update');
    Route::delete('orders/{order}', [OrderController::class, 'destroy'])->whereNumber('order')->name('orders.destroy');

    // ============ STOCK ============
    Route::get('/stock', [StockController::class, 'index'])->name('stock.index');
    Route::get('/stock/export/csv', [StockController::class, 'exportCsv'])->name('stock.export.csv');

    // Historial de stock — una sola ruta canonical
    Route::get('/stock/history', [StockController::class, 'history'])->name('stock.history');

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

    // ============ SOPORTE ============
    Route::get('/support', [SupportController::class, 'index'])->name('support.index');
    Route::post('/support', [SupportController::class, 'store'])->name('support.store');
    Route::get('/support/{ticket}', [SupportController::class, 'show'])->name('support.show');
    Route::post('/support/{ticket}/reply', [SupportController::class, 'reply'])->name('support.reply');
    Route::put('/support/{ticket}/status', [SupportController::class, 'updateStatus'])->name('support.status');
});

// ------------------------ MASTER: Invitations (UI) ------------------------
Route::prefix('master/invitations')
    ->name('master.invitations.')
    ->middleware(['auth'])
    ->group(function () {
        Route::get('/', [InvitationController::class, 'index'])->name('index');
        Route::post('/', [InvitationController::class, 'store'])->name('store');
        Route::get('/{invitation}', [InvitationController::class, 'show'])->name('show');
        Route::post('/{invitation}', [InvitationController::class, 'revoke'])->name('revoke');
        Route::get('/stats/json', [InvitationController::class, 'stats'])->name('stats');
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
        Route::resource('employees', BranchEmployeeController::class);
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
