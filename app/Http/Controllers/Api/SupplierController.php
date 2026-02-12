<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Supplier;
use Illuminate\Http\Request;

class SupplierController extends Controller
{
    /**
     * Lista de proveedores.
     */
    public function index(Request $request)
    {
        $auth = $request->user();
        $companyId = $auth->rootCompany()?->id ?? $auth->id;

        $query = Supplier::withoutGlobalScope('byUser')
            ->where('user_id', $companyId)
            ->when($request->filled('q'), function ($q) use ($request) {
                $term = trim((string) $request->input('q'));
                $lc = mb_strtolower($term, 'UTF-8');
                $q->where(function ($w) use ($lc) {
                    $w->whereRaw('LOWER(name) LIKE ?', ["%{$lc}%"])
                      ->orWhereRaw('LOWER(contact_name) LIKE ?', ["%{$lc}%"]);
                });
            })
            ->when($request->filled('is_active'), function ($q) use ($request) {
                $q->where('is_active', filter_var($request->is_active, FILTER_VALIDATE_BOOLEAN));
            });

        $perPage = min((int) $request->input('per_page', 20), 100);
        $suppliers = $query->orderBy('name')->paginate($perPage);

        return response()->json([
            'success' => true,
            'data' => $suppliers->items(),
            'meta' => [
                'current_page' => $suppliers->currentPage(),
                'last_page' => $suppliers->lastPage(),
                'per_page' => $suppliers->perPage(),
                'total' => $suppliers->total(),
            ],
        ], 200);
    }

    /**
     * Mostrar proveedor.
     */
    public function show(Request $request, $supplierId)
    {
        $auth = $request->user();
        $companyId = $auth->rootCompany()?->id ?? $auth->id;

        $supplier = Supplier::withoutGlobalScope('byUser')
            ->where('user_id', $companyId)
            ->findOrFail($supplierId);

        return response()->json([
            'success' => true,
            'data' => $supplier,
        ], 200);
    }

    /**
     * Crear proveedor.
     */
    public function store(Request $request)
    {
        $auth = $request->user();
        $companyId = $auth->rootCompany()?->id ?? $auth->id;

        $data = $request->validate([
            'name' => 'required|string|max:255',
            'contact_name' => 'nullable|string|max:255',
            'email' => 'nullable|email|max:255',
            'phone' => 'nullable|string|max:50',
            'address' => 'nullable|string',
            'notes' => 'nullable|string',
            'is_active' => 'boolean',
        ]);

        $data['user_id'] = $companyId;
        $supplier = Supplier::create($data);

        return response()->json([
            'success' => true,
            'message' => 'Proveedor creado exitosamente',
            'data' => $supplier,
        ], 201);
    }

    /**
     * Actualizar proveedor.
     */
    public function update(Request $request, $supplierId)
    {
        $auth = $request->user();
        $companyId = $auth->rootCompany()?->id ?? $auth->id;

        $supplier = Supplier::withoutGlobalScope('byUser')
            ->where('user_id', $companyId)
            ->findOrFail($supplierId);

        $data = $request->validate([
            'name' => 'sometimes|required|string|max:255',
            'contact_name' => 'nullable|string|max:255',
            'email' => 'nullable|email|max:255',
            'phone' => 'nullable|string|max:50',
            'address' => 'nullable|string',
            'notes' => 'nullable|string',
            'is_active' => 'boolean',
        ]);

        $supplier->update($data);

        return response()->json([
            'success' => true,
            'message' => 'Proveedor actualizado exitosamente',
            'data' => $supplier,
        ], 200);
    }

    /**
     * Eliminar proveedor.
     */
    public function destroy(Request $request, $supplierId)
    {
        $auth = $request->user();
        $companyId = $auth->rootCompany()?->id ?? $auth->id;

        $supplier = Supplier::withoutGlobalScope('byUser')
            ->where('user_id', $companyId)
            ->findOrFail($supplierId);

        $supplier->delete();

        return response()->json([
            'success' => true,
            'message' => 'Proveedor eliminado exitosamente',
        ], 200);
    }
}
