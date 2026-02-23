<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\ServiceExpense;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class ServiceExpenseController extends Controller
{
    public function index(Request $request)
    {
        $auth = $request->user();
        $companyId = $auth->rootCompany()?->id ?? $auth->id;

        $query = ServiceExpense::withoutGlobalScope('byUser')
            ->where('user_id', $companyId)
            ->with('service:id,name')
            ->when($request->filled('service_id'), fn($q) => $q->where('service_id', $request->integer('service_id')))
            ->when($request->filled('expense_type'), fn($q) => $q->where('expense_type', $request->expense_type))
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
            'service_id' => [
                'nullable',
                Rule::exists('services', 'id')->where('company_id', $companyId),
            ],
            'expense_name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'cost' => 'required|numeric|min:0',
            'expense_type' => 'required|in:material,mano_obra,herramienta,otro,impuesto',
            'is_active' => 'boolean',
        ]);

        $data['user_id'] = $companyId;
        $expense = ServiceExpense::create($data);

        return response()->json([
            'success' => true,
            'message' => 'Gasto de servicio creado exitosamente',
            'data' => $expense,
        ], 201);
    }

    public function update(Request $request, $expenseId)
    {
        $auth = $request->user();
        $companyId = $auth->rootCompany()?->id ?? $auth->id;

        $expense = ServiceExpense::withoutGlobalScope('byUser')
            ->where('user_id', $companyId)
            ->findOrFail($expenseId);

        $data = $request->validate([
            'service_id' => [
                'nullable',
                Rule::exists('services', 'id')->where('company_id', $companyId),
            ],
            'expense_name' => 'sometimes|required|string|max:255',
            'description' => 'nullable|string',
            'cost' => 'sometimes|required|numeric|min:0',
            'expense_type' => 'sometimes|required|in:material,mano_obra,herramienta,otro,impuesto',
            'is_active' => 'boolean',
        ]);

        $expense->update($data);

        return response()->json([
            'success' => true,
            'message' => 'Gasto de servicio actualizado exitosamente',
            'data' => $expense,
        ], 200);
    }

    public function destroy(Request $request, $expenseId)
    {
        $auth = $request->user();
        $companyId = $auth->rootCompany()?->id ?? $auth->id;

        $expense = ServiceExpense::withoutGlobalScope('byUser')
            ->where('user_id', $companyId)
            ->findOrFail($expenseId);

        $expense->delete();

        return response()->json([
            'success' => true,
            'message' => 'Gasto de servicio eliminado exitosamente',
        ], 200);
    }
}
