<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Service;
use App\Models\ServiceCategory;
use App\Models\ServiceVariant;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;

class ServiceController extends Controller
{
    /**
     * Lista de servicios con paginación y filtros.
     */
    public function index(Request $request)
    {
        $auth = $request->user();

        $query = $auth->isMaster()
            ? Service::query()->withoutGlobalScope('byUser')
            : Service::availableFor($auth)->withoutGlobalScope('byUser');

        $query->with(['category:id,name'])
            ->when($request->filled('q'), function ($q) use ($request) {
                $term = trim((string) $request->input('q'));
                $lc = mb_strtolower($term, 'UTF-8');
                $q->whereRaw('LOWER(name) LIKE ?', ["%{$lc}%"]);
            })
            ->when($request->filled('service_category_id'), function ($q) use ($request) {
                $q->where('service_category_id', $request->integer('service_category_id'));
            })
            ->when($request->filled('is_active'), function ($q) use ($request) {
                $q->where('is_active', filter_var($request->is_active, FILTER_VALIDATE_BOOLEAN));
            });

        $perPage = min((int) $request->input('per_page', 20), 100);
        $services = $query->orderBy('name')->paginate($perPage);

        return response()->json([
            'success' => true,
            'data' => $services->items(),
            'meta' => [
                'current_page' => $services->currentPage(),
                'last_page' => $services->lastPage(),
                'per_page' => $services->perPage(),
                'total' => $services->total(),
            ],
        ], 200);
    }

    /**
     * Buscar servicios (para autocompletar).
     */
    public function search(Request $request)
    {
        $request->validate([
            'q' => 'required|string|min:1',
        ]);

        $auth = $request->user();
        $term = trim($request->q);
        $lc = mb_strtolower($term, 'UTF-8');

        $services = Service::availableFor($auth)
            ->active()
            ->where(function ($w) use ($lc) {
                $w->whereRaw('LOWER(name) LIKE ?', ["%{$lc}%"]);
            })
            ->select('id', 'name', 'price', 'service_category_id', 'is_active')
            ->with('category:id,name')
            ->limit(20)
            ->get();

        return response()->json([
            'success' => true,
            'data' => $services,
        ], 200);
    }

    /**
     * Mostrar un servicio.
     */
    public function show(Request $request, Service $service)
    {
        $auth = $request->user();
        if (!$this->canAccessService($auth, $service)) {
            return response()->json([
                'success' => false,
                'message' => 'No tienes acceso a este servicio',
            ], 403);
        }

        $service->load(['category:id,name', 'variants']);

        return response()->json([
            'success' => true,
            'data' => $service,
        ], 200);
    }

    /**
     * Crear servicio.
     */
    public function store(Request $request)
    {
        $auth = $request->user();
        $company = $auth->rootCompany();
        $companyId = $company ? $company->id : $auth->id;

        $data = $request->validate([
            'name' => 'required|string|max:150',
            'description' => 'nullable|string',
            'price' => 'required|numeric|min:0',
            'is_active' => 'boolean',
            'tags' => 'nullable|array',
            'tags.*' => 'string|max:50',
            'service_category_id' => [
                'nullable',
                Rule::exists('service_categories', 'id')->where('company_id', $companyId),
            ],
            'new_category' => 'nullable|string|max:100',
            'variants' => 'nullable|array',
            'variants.*.name' => 'required_with:variants|string|max:150',
            'variants.*.duration_minutes' => 'nullable|integer|min:0',
            'variants.*.price' => 'required_with:variants|numeric|min:0',
            'variants.*.description' => 'nullable|string',
            'variants.*.is_active' => 'nullable|boolean',
        ]);

        return DB::transaction(function () use ($data, $auth, $companyId, $request) {
            $categoryId = $this->resolveCategoryId($request, $auth, $companyId);
            $payload = [
                'user_id' => $auth->id,
                'company_id' => $companyId,
                'service_category_id' => $categoryId,
                'name' => $data['name'],
                'description' => $data['description'] ?? null,
                'price' => $data['price'],
                'is_active' => $data['is_active'] ?? true,
                'tags' => $data['tags'] ?? null,
            ];

            $service = Service::create($payload);

            if (!empty($data['variants'])) {
                $service->variants()->createMany($this->normalizeVariants($data['variants']));
            }

            return response()->json([
                'success' => true,
                'message' => 'Servicio creado exitosamente',
                'data' => $service->load('variants'),
            ], 201);
        });
    }

    /**
     * Actualizar servicio.
     */
    public function update(Request $request, Service $service)
    {
        $auth = $request->user();
        if (!$this->canManageService($auth, $service)) {
            return response()->json([
                'success' => false,
                'message' => 'No tienes permiso para editar este servicio',
            ], 403);
        }

        $company = $auth->rootCompany();
        $companyId = $company ? $company->id : $auth->id;

        $data = $request->validate([
            'name' => 'sometimes|required|string|max:150',
            'description' => 'nullable|string',
            'price' => 'sometimes|required|numeric|min:0',
            'is_active' => 'boolean',
            'tags' => 'nullable|array',
            'tags.*' => 'string|max:50',
            'service_category_id' => [
                'nullable',
                Rule::exists('service_categories', 'id')->where('company_id', $companyId),
            ],
            'new_category' => 'nullable|string|max:100',
            'variants' => 'nullable|array',
            'variants.*.name' => 'required_with:variants|string|max:150',
            'variants.*.duration_minutes' => 'nullable|integer|min:0',
            'variants.*.price' => 'required_with:variants|numeric|min:0',
            'variants.*.description' => 'nullable|string',
            'variants.*.is_active' => 'nullable|boolean',
        ]);

        return DB::transaction(function () use ($service, $data, $request, $auth, $companyId) {
            $payload = $data;
            $payload['service_category_id'] = $this->resolveCategoryId($request, $auth, $companyId, $service->service_category_id);
            $service->update($payload);

            if (array_key_exists('variants', $data)) {
                $service->variants()->delete();
                if (!empty($data['variants'])) {
                    $service->variants()->createMany($this->normalizeVariants($data['variants']));
                }
            }

            return response()->json([
                'success' => true,
                'message' => 'Servicio actualizado exitosamente',
                'data' => $service->load('variants'),
            ], 200);
        });
    }

    /**
     * Eliminar servicio.
     */
    public function destroy(Request $request, Service $service)
    {
        $auth = $request->user();
        if (!$this->canManageService($auth, $service)) {
            return response()->json([
                'success' => false,
                'message' => 'No tienes permiso para eliminar este servicio',
            ], 403);
        }

        $service->delete();

        return response()->json([
            'success' => true,
            'message' => 'Servicio eliminado exitosamente',
        ], 200);
    }

    private function resolveCategoryId(Request $request, User $user, int $companyId, ?int $current = null): ?int
    {
        $newName = trim((string) $request->input('new_category', ''));
        if ($newName !== '') {
            $category = ServiceCategory::firstOrCreate(
                ['company_id' => $companyId, 'name' => $newName],
                []
            );
            return $category->id;
        }

        return $request->input('service_category_id', $current);
    }

    private function normalizeVariants(array $variants): array
    {
        return collect($variants)
            ->filter(fn ($v) => !empty($v['name']))
            ->map(function ($v) {
                return [
                    'name' => $v['name'],
                    'duration_minutes' => $v['duration_minutes'] ?? null,
                    'price' => $v['price'] ?? 0,
                    'description' => $v['description'] ?? null,
                    'is_active' => !empty($v['is_active']),
                ];
            })
            ->values()
            ->all();
    }

    private function canAccessService(User $user, Service $service): bool
    {
        if ($user->isMaster()) return true;
        if ($user->isCompany()) return $service->company_id === $user->id;
        if ($user->isAdmin()) {
            $company = $user->rootCompany();
            return $service->company_id === $company?->id || $service->user_id === $user->id;
        }
        return $service->user_id === $user->id || $service->user_id === $user->parent_id;
    }

    private function canManageService(User $user, Service $service): bool
    {
        if ($user->isMaster()) return true;
        if ($user->isCompany()) return $service->company_id === $user->id;
        return $service->user_id === $user->id;
    }

    /**
     * Obtener insumos del servicio
     */
    public function getSupplies(Request $request, Service $service)
    {
        $auth = $request->user();

        if (!$this->canAccessService($auth, $service)) {
            return response()->json([
                'success' => false,
                'message' => 'No tienes acceso a este servicio',
            ], 403);
        }

        $supplies = $service->supplies()->with('supply')->get();

        return response()->json([
            'success' => true,
            'data' => $supplies,
        ], 200);
    }

    /**
     * Agregar o actualizar insumo en servicio
     */
    public function addSupply(Request $request, Service $service)
    {
        $auth = $request->user();

        if (!$this->canManageService($auth, $service)) {
            return response()->json([
                'success' => false,
                'message' => 'No tienes permiso para gestionar este servicio',
            ], 403);
        }

        $validated = $request->validate([
            'supply_id' => 'required|integer|exists:supplies,id',
            'qty' => 'required|numeric|gt:0',
            'unit' => 'required|string|max:10',
            'waste_pct' => 'nullable|numeric|min:0|max:100',
        ]);

        $validated['waste_pct'] = $validated['waste_pct'] ?? 0;

        // Usar updateOrCreate para agregar o actualizar
        $serviceSupply = $service->supplies()->updateOrCreate(
            ['supply_id' => $validated['supply_id']],
            $validated
        );

        $serviceSupply->load('supply');

        return response()->json([
            'success' => true,
            'message' => 'Insumo agregado al servicio',
            'data' => $serviceSupply,
        ], 200);
    }

    /**
     * Actualizar insumo en servicio
     */
    public function updateSupply(Request $request, Service $service, $serviceSupplyId)
    {
        $auth = $request->user();

        if (!$this->canManageService($auth, $service)) {
            return response()->json([
                'success' => false,
                'message' => 'No tienes permiso para gestionar este servicio',
            ], 403);
        }

        $serviceSupply = $service->supplies()->findOrFail($serviceSupplyId);

        $validated = $request->validate([
            'qty' => 'numeric|gt:0',
            'unit' => 'string|max:10',
            'waste_pct' => 'nullable|numeric|min:0|max:100',
        ]);

        $serviceSupply->update($validated);
        $serviceSupply->load('supply');

        return response()->json([
            'success' => true,
            'message' => 'Insumo actualizado',
            'data' => $serviceSupply,
        ], 200);
    }

    /**
     * Eliminar insumo de servicio
     */
    public function removeSupply(Request $request, Service $service, $serviceSupplyId)
    {
        $auth = $request->user();

        if (!$this->canManageService($auth, $service)) {
            return response()->json([
                'success' => false,
                'message' => 'No tienes permiso para gestionar este servicio',
            ], 403);
        }

        $serviceSupply = $service->supplies()->findOrFail($serviceSupplyId);
        $serviceSupply->delete();

        return response()->json([
            'success' => true,
            'message' => 'Insumo eliminado del servicio',
        ], 200);
    }

    /**
     * Listar variantes de un servicio
     */
    public function getVariants(Request $request, Service $service)
    {
        $auth = $request->user();

        if (!$this->canAccessService($auth, $service)) {
            return response()->json([
                'success' => false,
                'message' => 'No tienes acceso a este servicio',
            ], 403);
        }

        $variants = $service->variants()->get();

        return response()->json([
            'success' => true,
            'data' => $variants,
        ], 200);
    }

    /**
     * Crear variante para un servicio
     */
    public function createVariant(Request $request, Service $service)
    {
        $auth = $request->user();

        if (!$this->canManageService($auth, $service)) {
            return response()->json([
                'success' => false,
                'message' => 'No tienes permiso para gestionar este servicio',
            ], 403);
        }

        $validated = $request->validate([
            'name' => 'required|string|max:150',
            'duration_minutes' => 'nullable|integer|min:0',
            'price' => 'required|numeric|min:0',
            'description' => 'nullable|string',
            'is_active' => 'nullable|boolean',
        ]);

        $validated['is_active'] = $validated['is_active'] ?? true;

        $variant = $service->variants()->create($validated);

        return response()->json([
            'success' => true,
            'message' => 'Variante creada exitosamente',
            'data' => $variant,
        ], 201);
    }

    /**
     * Actualizar variante
     */
    public function updateVariant(Request $request, Service $service, ServiceVariant $variant)
    {
        $auth = $request->user();

        if (!$this->canManageService($auth, $service)) {
            return response()->json([
                'success' => false,
                'message' => 'No tienes permiso para gestionar este servicio',
            ], 403);
        }

        // Verificar que la variante pertenece al servicio
        if ($variant->service_id !== $service->id) {
            return response()->json([
                'success' => false,
                'message' => 'La variante no pertenece a este servicio',
            ], 422);
        }

        $validated = $request->validate([
            'name' => 'sometimes|required|string|max:150',
            'duration_minutes' => 'nullable|integer|min:0',
            'price' => 'sometimes|required|numeric|min:0',
            'description' => 'nullable|string',
            'is_active' => 'nullable|boolean',
        ]);

        $variant->update($validated);

        return response()->json([
            'success' => true,
            'message' => 'Variante actualizada exitosamente',
            'data' => $variant,
        ], 200);
    }

    /**
     * Eliminar variante
     */
    public function deleteVariant(Request $request, Service $service, ServiceVariant $variant)
    {
        $auth = $request->user();

        if (!$this->canManageService($auth, $service)) {
            return response()->json([
                'success' => false,
                'message' => 'No tienes permiso para gestionar este servicio',
            ], 403);
        }

        // Verificar que la variante pertenece al servicio
        if ($variant->service_id !== $service->id) {
            return response()->json([
                'success' => false,
                'message' => 'La variante no pertenece a este servicio',
            ], 422);
        }

        $variant->delete();

        return response()->json([
            'success' => true,
            'message' => 'Variante eliminada exitosamente',
        ], 200);
    }

    /**
     * Activar/desactivar variante
     */
    public function toggleVariant(Request $request, Service $service, ServiceVariant $variant)
    {
        $auth = $request->user();

        if (!$this->canManageService($auth, $service)) {
            return response()->json([
                'success' => false,
                'message' => 'No tienes permiso para gestionar este servicio',
            ], 403);
        }

        // Verificar que la variante pertenece al servicio
        if ($variant->service_id !== $service->id) {
            return response()->json([
                'success' => false,
                'message' => 'La variante no pertenece a este servicio',
            ], 422);
        }

        $variant->is_active = !$variant->is_active;
        $variant->save();

        return response()->json([
            'success' => true,
            'message' => $variant->is_active ? 'Variante activada' : 'Variante desactivada',
            'data' => $variant,
        ], 200);
    }
}
