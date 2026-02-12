<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Supply;
use App\Models\SupplyPurchase;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class SupplyController extends Controller
{
    /**
     * Lista de insumos.
     */
    public function index(Request $request)
    {
        $auth = $request->user();

        $query = Supply::availableFor($auth)
            ->select('id', 'name', 'description', 'base_unit', 'stock_base_qty', 'avg_cost_per_base', 'supplier_id')
            ->when($request->filled('q'), function ($q) use ($request) {
                $term = trim((string) $request->input('q'));
                $lc = mb_strtolower($term, 'UTF-8');
                $q->whereRaw('LOWER(name) LIKE ?', ["%{$lc}%"]);
            });

        $perPage = min((int) $request->input('per_page', 20), 100);
        $supplies = $query->orderBy('name')->paginate($perPage);

        return response()->json([
            'success' => true,
            'data' => $supplies->items(),
            'meta' => [
                'current_page' => $supplies->currentPage(),
                'last_page' => $supplies->lastPage(),
                'per_page' => $supplies->perPage(),
                'total' => $supplies->total(),
            ],
        ], 200);
    }

    /**
     * Mostrar un insumo.
     */
    public function show(Request $request, $supplyId)
    {
        $auth = $request->user();
        $supply = Supply::availableFor($auth)->findOrFail($supplyId);

        return response()->json([
            'success' => true,
            'data' => $supply,
        ], 200);
    }

    /**
     * Crear insumo.
     */
    public function store(Request $request)
    {
        $auth = $request->user();

        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'base_unit' => ['required', Rule::in(['g', 'ml', 'u'])],
            'stock_base_qty' => ['nullable', 'numeric', 'min:0'],
            'avg_cost_per_base' => ['nullable', 'numeric', 'min:0'],
            'supplier_id' => ['nullable', 'integer'],
        ]);

        $data['user_id'] = $auth->rootCompany()?->id ?? $auth->id;

        $supply = Supply::create($data);

        return response()->json([
            'success' => true,
            'message' => 'Insumo creado exitosamente',
            'data' => $supply,
        ], 201);
    }

    /**
     * Actualizar insumo.
     */
    public function update(Request $request, $supplyId)
    {
        $auth = $request->user();
        $supply = Supply::availableFor($auth)->findOrFail($supplyId);

        $data = $request->validate([
            'name' => ['sometimes', 'required', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'base_unit' => ['sometimes', 'required', Rule::in(['g', 'ml', 'u'])],
            'stock_base_qty' => ['nullable', 'numeric', 'min:0'],
            'avg_cost_per_base' => ['nullable', 'numeric', 'min:0'],
            'supplier_id' => ['nullable', 'integer'],
        ]);

        $supply->update($data);

        return response()->json([
            'success' => true,
            'message' => 'Insumo actualizado exitosamente',
            'data' => $supply,
        ], 200);
    }

    /**
     * Registrar compra y ajustar stock promedio.
     */
    public function purchase(Request $request, $supplyId)
    {
        $auth = $request->user();
        $supply = Supply::availableFor($auth)->findOrFail($supplyId);

        $data = $request->validate([
            'qty' => ['required', 'numeric', 'gt:0'],
            'unit' => ['required', 'string', 'max:10'],
            'total_cost' => ['required', 'numeric', 'gt:0'],
        ]);

        $factor = $this->getConversionFactor($data['unit'], $supply->base_unit);
        $baseQty = $data['qty'] * $factor;

        SupplyPurchase::create([
            'supply_id' => $supply->id,
            'qty' => $data['qty'],
            'unit' => $data['unit'],
            'unit_to_base' => $factor,
            'total_cost' => $data['total_cost'],
        ]);

        $oldStock = (float) $supply->stock_base_qty;
        $oldAvg = (float) $supply->avg_cost_per_base;
        $newStock = $oldStock + $baseQty;
        $newAvg = $newStock > 0 ? (($oldStock * $oldAvg) + $data['total_cost']) / $newStock : 0;

        $supply->update([
            'stock_base_qty' => $newStock,
            'avg_cost_per_base' => $newAvg,
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Compra registrada y stock actualizado',
            'data' => $supply->fresh(),
        ], 200);
    }

    /**
     * Eliminar insumo.
     */
    public function destroy(Request $request, $supplyId)
    {
        $auth = $request->user();
        $supply = Supply::availableFor($auth)->findOrFail($supplyId);

        $supply->delete();

        return response()->json([
            'success' => true,
            'message' => 'Insumo eliminado exitosamente',
        ], 200);
    }

    private function canAccessSupply($user, Supply $supply): bool
    {
        if ($user->isMaster()) return true;
        if ($user->isCompany()) return $supply->user_id === $user->id;
        if ($user->isAdmin()) {
            $company = $user->rootCompany();
            return $supply->user_id === $user->id || $supply->user_id === $company?->id;
        }
        return $supply->user_id === $user->id || $supply->user_id === $user->parent_id;
    }

    private function canManageSupply($user, Supply $supply): bool
    {
        if ($user->isMaster()) return true;
        if ($user->isCompany()) return $supply->user_id === $user->id;
        return $supply->user_id === $user->id;
    }

    private function getConversionFactor(string $unit, string $base): float
    {
        $map = [
            'g' => ['g' => 1, 'kg' => 1000],
            'ml' => ['ml' => 1, 'l' => 1000, 'cm3' => 1],
            'u' => ['u' => 1],
        ];

        return $map[$base][$unit] ?? 1;
    }
}
