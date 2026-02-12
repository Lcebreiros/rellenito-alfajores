<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\ProductionExpense;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class ProductionExpenseController extends Controller
{
    public function index(Request $request)
    {
        $auth = $request->user();
        $companyId = $auth->rootCompany()?->id ?? $auth->id;

        $query = ProductionExpense::withoutGlobalScope('byUser')
            ->where('user_id', $companyId)
            ->with('product:id,name')
            ->when($request->filled('product_id'), fn($q) => $q->where('product_id', $request->integer('product_id')))
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

    public function store(Request $request)
    {
        $auth = $request->user();
        $companyId = $auth->rootCompany()?->id ?? $auth->id;

        $data = $request->validate([
            'product_id' => [
                'nullable',
                Rule::exists('products', 'id')->where('company_id', $companyId),
            ],
            'expense_name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'cost_per_unit' => 'required|numeric|min:0',
            'quantity' => 'required|numeric|min:0.001',
            'unit' => 'required|string|max:50',
            'is_active' => 'boolean',
        ]);

        $data['user_id'] = $companyId;
        $expense = ProductionExpense::create($data);

        return response()->json([
            'success' => true,
            'message' => 'Gasto de producción creado exitosamente',
            'data' => $expense,
        ], 201);
    }

    public function update(Request $request, $expenseId)
    {
        $auth = $request->user();
        $companyId = $auth->rootCompany()?->id ?? $auth->id;

        $expense = ProductionExpense::withoutGlobalScope('byUser')
            ->where('user_id', $companyId)
            ->findOrFail($expenseId);

        $data = $request->validate([
            'product_id' => [
                'nullable',
                Rule::exists('products', 'id')->where('company_id', $companyId),
            ],
            'expense_name' => 'sometimes|required|string|max:255',
            'description' => 'nullable|string',
            'cost_per_unit' => 'sometimes|required|numeric|min:0',
            'quantity' => 'sometimes|required|numeric|min:0.001',
            'unit' => 'sometimes|required|string|max:50',
            'is_active' => 'boolean',
        ]);

        $expense->update($data);

        return response()->json([
            'success' => true,
            'message' => 'Gasto de producción actualizado exitosamente',
            'data' => $expense,
        ], 200);
    }

    public function destroy(Request $request, $expenseId)
    {
        $auth = $request->user();
        $companyId = $auth->rootCompany()?->id ?? $auth->id;

        $expense = ProductionExpense::withoutGlobalScope('byUser')
            ->where('user_id', $companyId)
            ->findOrFail($expenseId);

        $expense->delete();

        return response()->json([
            'success' => true,
            'message' => 'Gasto de producción eliminado exitosamente',
        ], 200);
    }
}
