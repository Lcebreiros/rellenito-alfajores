<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\ServiceCategory;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;

class ServiceCategoryController extends Controller
{
    /**
     * Listar categorías de servicios
     */
    public function index(Request $request)
    {
        $user = Auth::user();
        $companyId = $this->getCompanyId($user);

        $query = ServiceCategory::where('company_id', $companyId)
            ->withCount('services');

        // Búsqueda
        if ($request->has('search')) {
            $search = $request->input('search');
            $query->where('name', 'LIKE', "%{$search}%");
        }

        // Ordenar
        $sortBy = $request->input('sort_by', 'name');
        $sortOrder = $request->input('sort_order', 'asc');
        $query->orderBy($sortBy, $sortOrder);

        // Paginación
        $perPage = min($request->input('per_page', 15), 100);

        if ($request->input('paginate', true)) {
            $categories = $query->paginate($perPage);

            return response()->json([
                'success' => true,
                'data' => $categories->items(),
                'pagination' => [
                    'current_page' => $categories->currentPage(),
                    'last_page' => $categories->lastPage(),
                    'per_page' => $categories->perPage(),
                    'total' => $categories->total(),
                ],
            ]);
        } else {
            $categories = $query->get();

            return response()->json([
                'success' => true,
                'data' => $categories,
            ]);
        }
    }

    /**
     * Ver categoría
     */
    public function show(ServiceCategory $category)
    {
        $user = Auth::user();
        $companyId = $this->getCompanyId($user);

        if ($category->company_id != $companyId) {
            return response()->json([
                'success' => false,
                'message' => 'No autorizado',
            ], 403);
        }

        $category->loadCount('services');
        $category->load(['services' => function ($query) {
            $query->where('is_active', true)
                ->select('id', 'service_category_id', 'name', 'price', 'is_active');
        }]);

        return response()->json([
            'success' => true,
            'data' => $category,
        ]);
    }

    /**
     * Crear categoría
     */
    public function store(Request $request)
    {
        $user = Auth::user();
        $companyId = $this->getCompanyId($user);

        $validated = $request->validate([
            'name' => 'required|string|max:255',
        ]);

        // Verificar duplicados
        $exists = ServiceCategory::where('company_id', $companyId)
            ->where('name', $validated['name'])
            ->exists();

        if ($exists) {
            throw ValidationException::withMessages([
                'name' => 'Ya existe una categoría con este nombre',
            ]);
        }

        $validated['company_id'] = $companyId;

        $category = ServiceCategory::create($validated);

        return response()->json([
            'success' => true,
            'message' => 'Categoría creada exitosamente',
            'data' => $category,
        ], 201);
    }

    /**
     * Actualizar categoría
     */
    public function update(Request $request, ServiceCategory $category)
    {
        $user = Auth::user();
        $companyId = $this->getCompanyId($user);

        if ($category->company_id != $companyId) {
            return response()->json([
                'success' => false,
                'message' => 'No autorizado',
            ], 403);
        }

        $validated = $request->validate([
            'name' => 'required|string|max:255',
        ]);

        // Verificar duplicados (excepto la categoría actual)
        $exists = ServiceCategory::where('company_id', $companyId)
            ->where('name', $validated['name'])
            ->where('id', '!=', $category->id)
            ->exists();

        if ($exists) {
            throw ValidationException::withMessages([
                'name' => 'Ya existe una categoría con este nombre',
            ]);
        }

        $category->update($validated);

        return response()->json([
            'success' => true,
            'message' => 'Categoría actualizada exitosamente',
            'data' => $category,
        ]);
    }

    /**
     * Eliminar categoría
     */
    public function destroy(ServiceCategory $category)
    {
        $user = Auth::user();
        $companyId = $this->getCompanyId($user);

        if ($category->company_id != $companyId) {
            return response()->json([
                'success' => false,
                'message' => 'No autorizado',
            ], 403);
        }

        // Verificar si tiene servicios asociados
        $hasServices = $category->services()->exists();

        if ($hasServices) {
            return response()->json([
                'success' => false,
                'message' => 'No se puede eliminar la categoría porque tiene servicios asociados',
            ], 422);
        }

        $category->delete();

        return response()->json([
            'success' => true,
            'message' => 'Categoría eliminada exitosamente',
        ]);
    }

    /**
     * Obtener ID de la compañía del usuario autenticado
     */
    private function getCompanyId($user): int
    {
        if ($user->isCompany()) {
            return $user->id;
        }

        if ($user->parent_id) {
            return $user->parent_id;
        }

        return $user->id;
    }
}
