<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Models\ProductionExpense;
use App\Models\Service;
use App\Models\ServiceExpense;
use App\Models\Supplier;
use App\Models\SupplierExpense;
use App\Models\Supply;
use App\Models\SupplyPurchase;
use App\Services\UnitConverter;
use App\Models\ThirdPartyService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ExpenseController extends Controller
{
    /**
     * Vista principal de gastos
     */
    public function index()
    {
        $user = Auth::user();

        // Obtener todos los gastos del usuario (BelongsToUser trait aplica filtro automático)
        $supplierExpenses = SupplierExpense::where('is_active', true)
            ->with(['product', 'supplier'])
            ->get();

        // Obtener todos los proveedores con sus gastos
        $suppliers = Supplier::where('user_id', Auth::id())
            ->with(['expenses' => function($query) {
                $query->where('is_active', true);
            }])
            ->withCount(['supplies', 'expenses'])
            ->orderBy('name')
            ->get();

        $serviceExpenses = ServiceExpense::where('is_active', true)
            ->with('service')
            ->get();

        $thirdPartyServices = ThirdPartyService::where('is_active', true)
            ->get();

        $productionExpenses = ProductionExpense::where('is_active', true)
            ->with('product')
            ->get();

        // Los insumos ya se filtran automáticamente por BelongsToUser trait
        $supplies = Supply::all();

        // Calcular totales
        $totalSupplier = $supplierExpenses->sum(fn($e) => $e->annualized_cost);
        $totalService = $serviceExpenses->sum('cost');
        $totalThirdParty = $thirdPartyServices->sum(fn($e) => $e->annualized_cost);
        $totalProduction = $productionExpenses->sum(fn($e) => $e->total_cost);
        $totalSupplies = $supplies->sum(fn($s) => $s->stock_base_qty * $s->avg_cost_per_base);

        return view('expenses.index', compact(
            'supplierExpenses',
            'serviceExpenses',
            'thirdPartyServices',
            'productionExpenses',
            'supplies',
            'suppliers',
            'totalSupplier',
            'totalService',
            'totalThirdParty',
            'totalProduction',
            'totalSupplies'
        ));
    }

    /**
     * Gastos de proveedores
     */
    public function suppliers()
    {
        $user = Auth::user();
        $expenses = SupplierExpense::with(['product', 'supplier'])
            ->latest()
            ->get();

        $products = Product::availableFor($user)->get();

        // Obtener proveedores activos del usuario
        $suppliers = Supplier::where('user_id', Auth::id())
            ->where('is_active', true)
            ->orderBy('name')
            ->get();

        return view('expenses.suppliers', compact('expenses', 'products', 'suppliers'));
    }

    public function storeSupplier(Request $request)
    {
        $validated = $request->validate([
            'supplier_id' => 'required|exists:suppliers,id',
            'product_id' => 'nullable|exists:products,id',
            'description' => 'nullable|string',
            'cost' => 'required|numeric|min:0',
            'quantity' => 'required|numeric|min:0.001',
            'unit' => 'required|string|max:50',
            'frequency' => 'required|in:unica,diaria,semanal,mensual,anual',
        ]);

        $validated['user_id'] = Auth::id();

        SupplierExpense::create($validated);

        return redirect()->route('expenses.suppliers')
            ->with('success', 'Gasto de proveedor agregado exitosamente');
    }

    public function updateSupplier(Request $request, SupplierExpense $expense)
    {
        $validated = $request->validate([
            'supplier_id' => 'required|exists:suppliers,id',
            'product_id' => 'nullable|exists:products,id',
            'description' => 'nullable|string',
            'cost' => 'required|numeric|min:0',
            'quantity' => 'required|numeric|min:0.001',
            'unit' => 'required|string|max:50',
            'frequency' => 'required|in:unica,diaria,semanal,mensual,anual',
            'is_active' => 'boolean',
        ]);

        $expense->update($validated);

        return redirect()->route('expenses.suppliers')
            ->with('success', 'Gasto de proveedor actualizado exitosamente');
    }

    public function destroySupplier(SupplierExpense $expense)
    {
        $expense->delete();

        return redirect()->route('expenses.suppliers')
            ->with('success', 'Gasto de proveedor eliminado exitosamente');
    }

    /**
     * Gastos de servicios
     */
    public function services()
    {
        $user = Auth::user();
        $expenses = ServiceExpense::with('service')
            ->latest()
            ->get();

        $services = Service::availableFor($user)->get();

        return view('expenses.services', compact('expenses', 'services'));
    }

    public function storeService(Request $request)
    {
        $validated = $request->validate([
            'service_id' => 'nullable|exists:services,id',
            'expense_name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'cost' => 'required|numeric|min:0',
            'expense_type' => 'required|in:material,mano_obra,herramienta,otro,impuesto',
        ]);

        $validated['user_id'] = Auth::id();

        ServiceExpense::create($validated);

        return redirect()->route('expenses.services')
            ->with('success', 'Gasto de servicio agregado exitosamente');
    }

    public function updateService(Request $request, ServiceExpense $expense)
    {
        $validated = $request->validate([
            'service_id' => 'nullable|exists:services,id',
            'expense_name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'cost' => 'required|numeric|min:0',
            'expense_type' => 'required|in:material,mano_obra,herramienta,otro,impuesto',
            'is_active' => 'boolean',
        ]);

        $expense->update($validated);

        return redirect()->route('expenses.services')
            ->with('success', 'Gasto de servicio actualizado exitosamente');
    }

    public function destroyService(ServiceExpense $expense)
    {
        $expense->delete();

        return redirect()->route('expenses.services')
            ->with('success', 'Gasto de servicio eliminado exitosamente');
    }

    /**
     * Servicios de terceros
     */
    public function thirdParty()
    {
        $services = ThirdPartyService::latest()
            ->get();

        return view('expenses.third-party', compact('services'));
    }

    public function storeThirdParty(Request $request)
    {
        $validated = $request->validate([
            'service_name' => 'required|string|max:255',
            'provider_name' => 'nullable|string|max:255',
            'description' => 'nullable|string',
            'cost' => 'required|numeric|min:0',
            'frequency' => 'required|in:unica,diaria,semanal,mensual,anual',
            'next_payment_date' => 'nullable|date',
        ]);

        $validated['user_id'] = Auth::id();

        ThirdPartyService::create($validated);

        return redirect()->route('expenses.third-party')
            ->with('success', 'Servicio de tercero agregado exitosamente');
    }

    public function updateThirdParty(Request $request, ThirdPartyService $service)
    {
        $validated = $request->validate([
            'service_name' => 'required|string|max:255',
            'provider_name' => 'nullable|string|max:255',
            'description' => 'nullable|string',
            'cost' => 'required|numeric|min:0',
            'frequency' => 'required|in:unica,diaria,semanal,mensual,anual',
            'next_payment_date' => 'nullable|date',
            'is_active' => 'boolean',
        ]);

        $service->update($validated);

        return redirect()->route('expenses.third-party')
            ->with('success', 'Servicio de tercero actualizado exitosamente');
    }

    public function destroyThirdParty(ThirdPartyService $service)
    {
        $service->delete();

        return redirect()->route('expenses.third-party')
            ->with('success', 'Servicio de tercero eliminado exitosamente');
    }

    /**
     * Gastos de producción
     */
    public function production()
    {
        $user = Auth::user();
        $expenses = ProductionExpense::with('product')
            ->latest()
            ->get();

        $products = Product::availableFor($user)->get();

        return view('expenses.production', compact('expenses', 'products'));
    }

    public function storeProduction(Request $request)
    {
        $validated = $request->validate([
            'product_id' => 'nullable|exists:products,id',
            'expense_name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'cost_per_unit' => 'required|numeric|min:0',
            'quantity' => 'required|numeric|min:0.001',
            'unit' => 'required|string|max:50',
        ]);

        $validated['user_id'] = Auth::id();

        ProductionExpense::create($validated);

        return redirect()->route('expenses.production')
            ->with('success', 'Gasto de producción agregado exitosamente');
    }

    public function updateProduction(Request $request, ProductionExpense $expense)
    {
        $validated = $request->validate([
            'product_id' => 'nullable|exists:products,id',
            'expense_name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'cost_per_unit' => 'required|numeric|min:0',
            'quantity' => 'required|numeric|min:0.001',
            'unit' => 'required|string|max:50',
            'is_active' => 'boolean',
        ]);

        $expense->update($validated);

        return redirect()->route('expenses.production')
            ->with('success', 'Gasto de producción actualizado exitosamente');
    }

    public function destroyProduction(ProductionExpense $expense)
    {
        $expense->delete();

        return redirect()->route('expenses.production')
            ->with('success', 'Gasto de producción eliminado exitosamente');
    }

    // =============================
    // Insumos
    // =============================

    public function supplies()
    {
        // BelongsToUser trait filtra automáticamente por usuario
        $supplies = Supply::with(['purchases', 'supplier'])
            ->latest()
            ->get();

        // Obtener proveedores activos del usuario
        $suppliers = Supplier::where('user_id', Auth::id())
            ->where('is_active', true)
            ->orderBy('name')
            ->get();

        return view('expenses.supplies', compact('supplies', 'suppliers'));
    }

    public function storeSupply(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'base_unit' => 'required|in:g,ml,u',
            'supplier_id' => 'nullable|exists:suppliers,id',
            // Campos opcionales para crear la primera compra (misma lógica que calculadora)
            'qty' => 'nullable|numeric|gt:0',
            'unit' => 'nullable|string|in:g,kg,ml,l,cm3,u',
            'total_cost' => 'nullable|numeric|gt:0',
        ]);

        // Crear insumo con stock/costo inicial en 0
        $supply = Supply::create([
            'user_id' => Auth::id(),
            'supplier_id' => $validated['supplier_id'] ?? null,
            'name' => $validated['name'],
            'description' => $validated['description'] ?? null,
            'base_unit' => $validated['base_unit'],
            'stock_base_qty' => 0,
            'avg_cost_per_base' => 0,
        ]);

        // Si se enviaron datos de compra inicial, registrarla y recalcular
        if (!empty($validated['qty']) && !empty($validated['unit']) && !empty($validated['total_cost'])) {
            // Validar consistencia entre unidad base y unidad ingresada
            $base = $supply->base_unit; // g | ml | u
            $unit = strtolower($validated['unit']);

            $families = [
                'g'  => ['g','kg'],
                'ml' => ['ml','l','cm3'],
                'u'  => ['u'],
            ];

            if (!in_array($unit, $families[$base] ?? [], true)) {
                // Si no coincide la familia, cancelamos con error de validación
                return redirect()->route('expenses.supplies')
                    ->withErrors(['unit' => 'La unidad seleccionada no coincide con la unidad base del insumo.'])
                    ->withInput();
            }

            // Factor hacia la unidad base usando el mismo servicio que la calculadora
            $factorToBase = UnitConverter::factorToBase($unit, $base);

            SupplyPurchase::create([
                'user_id' => Auth::id(),
                'supply_id' => $supply->id,
                'qty' => (float) $validated['qty'],
                'unit' => $unit,
                'unit_to_base' => $factorToBase,
                'total_cost' => (float) $validated['total_cost'],
            ]);

            // Recalcular stock y costo promedio desde TODAS las compras
            $supply->recomputeFromPurchases();

            // Si el insumo tiene proveedor, registrar el gasto automáticamente
            if ($supply->supplier_id) {
                SupplierExpense::create([
                    'user_id' => Auth::id(),
                    'supplier_id' => $supply->supplier_id,
                    'product_id' => null,
                    'description' => 'Compra de insumo: ' . $supply->name,
                    'cost' => (float) $validated['total_cost'],
                    'quantity' => (float) $validated['qty'],
                    'unit' => $unit,
                    'frequency' => 'unica',
                    'is_active' => true,
                ]);
            }
        }

        return redirect()->route('expenses.supplies')
            ->with('success', 'Insumo creado exitosamente');
    }

    public function updateSupply(Request $request, Supply $supply)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'base_unit' => 'required|in:g,ml,u',
            'supplier_id' => 'nullable|exists:suppliers,id',
        ]);

        $supply->update($validated);

        return redirect()->route('expenses.supplies')
            ->with('success', 'Insumo actualizado exitosamente');
    }

    public function destroySupply(Supply $supply)
    {
        $supply->delete();

        return redirect()->route('expenses.supplies')
            ->with('success', 'Insumo eliminado exitosamente');
    }

    // =============================
    // Gestión de Proveedores
    // =============================

    public function suppliersManagement()
    {
        $suppliers = Supplier::where('user_id', Auth::id())
            ->withCount('supplies')
            ->latest()
            ->get();

        return view('suppliers.index', compact('suppliers'));
    }

    public function storeSupplierEntity(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'contact_name' => 'nullable|string|max:255',
            'email' => 'nullable|email|max:255',
            'phone' => 'nullable|string|max:50',
            'address' => 'nullable|string',
            'notes' => 'nullable|string',
            'is_active' => 'nullable|boolean',
        ]);

        Supplier::create([
            'user_id' => Auth::id(),
            'name' => $validated['name'],
            'contact_name' => $validated['contact_name'] ?? null,
            'email' => $validated['email'] ?? null,
            'phone' => $validated['phone'] ?? null,
            'address' => $validated['address'] ?? null,
            'notes' => $validated['notes'] ?? null,
            'is_active' => $validated['is_active'] ?? true,
        ]);

        return redirect()->route('suppliers.index')
            ->with('success', 'Proveedor creado exitosamente');
    }

    public function updateSupplierEntity(Request $request, Supplier $supplier)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'contact_name' => 'nullable|string|max:255',
            'email' => 'nullable|email|max:255',
            'phone' => 'nullable|string|max:50',
            'address' => 'nullable|string',
            'notes' => 'nullable|string',
            'is_active' => 'nullable|boolean',
        ]);

        $supplier->update($validated);

        return redirect()->route('suppliers.index')
            ->with('success', 'Proveedor actualizado exitosamente');
    }

    public function destroySupplierEntity(Supplier $supplier)
    {
        $supplier->delete();

        return redirect()->route('suppliers.index')
            ->with('success', 'Proveedor eliminado exitosamente');
    }
}
