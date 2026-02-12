<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\SupplierExpense;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class SupplierExpenseController extends Controller
{
    /**
     * Lista de gastos de proveedores.
     */
    public function index(Request $request)
    {
        $auth = $request->user();
        $companyId = $auth->rootCompany()?->id ?? $auth->id;

        $query = SupplierExpense::withoutGlobalScope('byUser')
            ->where('user_id', $companyId)
            ->with(['supplier:id,name', 'product:id,name'])
            ->when($request->filled('supplier_id'), fn($q) => $q->where('supplier_id', $request->integer('supplier_id')))
            ->when($request->filled('product_id'), fn($q) => $q->where('product_id', $request->integer('product_id')))
            ->when($request->filled('frequency'), fn($q) => $q->where('frequency', $request->frequency))
            ->when($request->filled('is_active'), fn($q) => $q->where('is_active', filter_var($request->is_active, FILTER_VALIDATE_BOOLEAN)));

        $perPage = min((int) $request->input('per_page', 20), 100);
        $expenses = $query->orderBy('created_at', 'desc')->paginate($perPage);

        return response()->json([
            'success' => true,
            'data' => $expenses->items(),
            'meta' => [
                'current_page' => $expenses->currentPage(),
                'last_page' => $expenses->lastPage(),
                'per_page' => $expenses->perPage(),
                'total' => $expenses->total(),
            ],
        ], 200);
    }

    /**
     * Crear gasto de proveedor.
     */
    public function store(Request $request)
    {
        $auth = $request->user();
        $companyId = $auth->rootCompany()?->id ?? $auth->id;

        $data = $request->validate([
            'supplier_id' => ['required', Rule::exists('suppliers', 'id')->where('user_id', $companyId)],
            'product_id' => [
                'nullable',
                Rule::exists('products', 'id')->where('company_id', $companyId),
            ],
            'description' => 'nullable|string',
            'cost' => 'required|numeric|min:0',
            'quantity' => 'required|numeric|min:0.001',
            'unit' => 'required|string|max:50',
            'frequency' => 'required|in:unica,diaria,semanal,mensual,anual',
            'is_active' => 'boolean',
        ]);

        $data['user_id'] = $companyId;
        $expense = SupplierExpense::create($data);

        return response()->json([
            'success' => true,
            'message' => 'Gasto de proveedor creado exitosamente',
            'data' => $expense,
        ], 201);
    }

    /**
     * Actualizar gasto.
     */
    public function update(Request $request, $expenseId)
    {
        $auth = $request->user();
        $companyId = $auth->rootCompany()?->id ?? $auth->id;

        $expense = SupplierExpense::withoutGlobalScope('byUser')
            ->where('user_id', $companyId)
            ->findOrFail($expenseId);

        $data = $request->validate([
            'supplier_id' => [
                'sometimes',
                'required',
                Rule::exists('suppliers', 'id')->where('user_id', $companyId),
            ],
            'product_id' => [
                'nullable',
                Rule::exists('products', 'id')->where('company_id', $companyId),
            ],
            'description' => 'nullable|string',
            'cost' => 'sometimes|required|numeric|min:0',
            'quantity' => 'sometimes|required|numeric|min:0.001',
            'unit' => 'sometimes|required|string|max:50',
            'frequency' => 'sometimes|required|in:unica,diaria,semanal,mensual,anual',
            'is_active' => 'boolean',
        ]);

        $expense->update($data);

        return response()->json([
            'success' => true,
            'message' => 'Gasto de proveedor actualizado exitosamente',
            'data' => $expense,
        ], 200);
    }

    /**
     * Eliminar gasto.
     */
    public function destroy(Request $request, $expenseId)
    {
        $auth = $request->user();
        $companyId = $auth->rootCompany()?->id ?? $auth->id;

        $expense = SupplierExpense::withoutGlobalScope('byUser')
            ->where('user_id', $companyId)
            ->findOrFail($expenseId);

        $expense->delete();

        return response()->json([
            'success' => true,
            'message' => 'Gasto de proveedor eliminado exitosamente',
        ], 200);
    }
}
