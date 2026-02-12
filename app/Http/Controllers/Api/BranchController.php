<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Branch;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;

class BranchController extends Controller
{
    /**
     * Listar sucursales del usuario autenticado
     */
    public function index(Request $request)
    {
        $auth = $request->user();

        // Master puede ver todas las sucursales
        if ($auth->isMaster()) {
            $query = Branch::with(['company:id,name,email', 'user:id,name,email,is_active'])
                ->latest();
        }
        // Company ve solo sus sucursales
        elseif ($auth->isCompany()) {
            $query = Branch::where('company_id', $auth->id)
                ->with('user:id,name,email,is_active')
                ->latest();
        }
        // Admin ve solo su sucursal
        elseif ($auth->isAdmin()) {
            $branch = $auth->representable;
            if (!$branch) {
                return response()->json([
                    'success' => false,
                    'message' => 'No tienes una sucursal asociada',
                ], 404);
            }

            return response()->json([
                'success' => true,
                'data' => [$branch->load('company:id,name,email')],
                'meta' => [
                    'total' => 1,
                    'current_page' => 1,
                    'last_page' => 1,
                ],
            ]);
        }
        // Usuario regular no tiene acceso
        else {
            return response()->json([
                'success' => false,
                'message' => 'No tienes permisos para ver sucursales',
            ], 403);
        }

        // Filtros opcionales
        if ($request->filled('company_id') && $auth->isMaster()) {
            $query->where('company_id', $request->company_id);
        }

        if ($request->filled('is_active')) {
            $query->where('is_active', filter_var($request->is_active, FILTER_VALIDATE_BOOLEAN));
        }

        if ($request->filled('search')) {
            $term = trim($request->search);
            $query->where(function ($q) use ($term) {
                $q->where('name', 'like', "%{$term}%")
                  ->orWhere('address', 'like', "%{$term}%")
                  ->orWhere('phone', 'like', "%{$term}%");
            });
        }

        $perPage = min((int) $request->input('per_page', 20), 100);
        $branches = $query->paginate($perPage);

        return response()->json([
            'success' => true,
            'data' => $branches->items(),
            'meta' => [
                'current_page' => $branches->currentPage(),
                'last_page' => $branches->lastPage(),
                'per_page' => $branches->perPage(),
                'total' => $branches->total(),
            ],
        ]);
    }

    /**
     * Ver detalles de una sucursal
     */
    public function show(Request $request, Branch $branch)
    {
        $auth = $request->user();

        if (!$this->canAccessBranch($auth, $branch)) {
            return response()->json([
                'success' => false,
                'message' => 'No tienes acceso a esta sucursal',
            ], 403);
        }

        $branch->load([
            'company:id,name,email,phone',
            'user:id,name,email,is_active',
        ]);

        return response()->json([
            'success' => true,
            'data' => $branch,
        ]);
    }

    /**
     * Crear nueva sucursal (solo Company)
     */
    public function store(Request $request)
    {
        $auth = $request->user();

        if (!$auth->isCompany() && !$auth->isMaster()) {
            return response()->json([
                'success' => false,
                'message' => 'Solo empresas pueden crear sucursales',
            ], 403);
        }

        // Validar límite de sucursales
        if ($auth->isCompany()) {
            $currentBranches = Branch::where('company_id', $auth->id)->count();
            if ($auth->branch_limit && $currentBranches >= $auth->branch_limit) {
                return response()->json([
                    'success' => false,
                    'message' => "Has alcanzado el límite de {$auth->branch_limit} sucursales",
                ], 422);
            }
        }

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email',
            'password' => 'required|string|min:8',
            'address' => 'nullable|string|max:500',
            'phone' => 'nullable|string|max:30',
            'contact_email' => 'nullable|email',
            'user_limit' => 'nullable|integer|min:0|max:100',
            'is_active' => 'boolean',
            'use_company_inventory' => 'boolean',
            'company_id' => $auth->isMaster() ? 'required|exists:users,id' : 'nullable',
        ]);

        $companyId = $auth->isMaster()
            ? $validated['company_id']
            : $auth->id;

        return DB::transaction(function () use ($validated, $companyId) {
            // Crear Branch
            $branch = Branch::create([
                'company_id' => $companyId,
                'name' => $validated['name'],
                'slug' => $this->generateUniqueSlug($validated['name']),
                'address' => $validated['address'] ?? null,
                'phone' => $validated['phone'] ?? null,
                'contact_email' => $validated['contact_email'] ?? null,
                'is_active' => $validated['is_active'] ?? true,
                'use_company_inventory' => $validated['use_company_inventory'] ?? false,
            ]);

            // Crear User representante de la sucursal
            $user = User::create([
                'name' => $validated['name'],
                'email' => $validated['email'],
                'password' => Hash::make($validated['password']),
                'parent_id' => $companyId,
                'hierarchy_level' => User::HIERARCHY_ADMIN,
                'is_active' => $validated['is_active'] ?? true,
                'user_limit' => $validated['user_limit'] ?? 10,
                'representable_id' => $branch->id,
                'representable_type' => Branch::class,
            ]);

            // Asignar rol admin
            $user->assignRole('admin');

            // Actualizar hierarchy_path
            $user->updateHierarchyPath();

            $branch->load('user', 'company');

            return response()->json([
                'success' => true,
                'message' => 'Sucursal creada exitosamente',
                'data' => $branch,
            ], 201);
        });
    }

    /**
     * Actualizar sucursal
     */
    public function update(Request $request, Branch $branch)
    {
        $auth = $request->user();

        if (!$this->canManageBranch($auth, $branch)) {
            return response()->json([
                'success' => false,
                'message' => 'No tienes permisos para editar esta sucursal',
            ], 403);
        }

        $validated = $request->validate([
            'name' => 'sometimes|string|max:255',
            'email' => [
                'sometimes',
                'email',
                Rule::unique('users', 'email')->ignore($branch->user->id),
            ],
            'password' => 'nullable|string|min:8',
            'address' => 'nullable|string|max:500',
            'phone' => 'nullable|string|max:30',
            'contact_email' => 'nullable|email',
            'user_limit' => 'nullable|integer|min:0|max:100',
            'is_active' => 'boolean',
            'use_company_inventory' => 'boolean',
        ]);

        return DB::transaction(function () use ($branch, $validated) {
            // Actualizar Branch
            $branch->update([
                'name' => $validated['name'] ?? $branch->name,
                'address' => $validated['address'] ?? $branch->address,
                'phone' => $validated['phone'] ?? $branch->phone,
                'contact_email' => $validated['contact_email'] ?? $branch->contact_email,
                'is_active' => $validated['is_active'] ?? $branch->is_active,
                'use_company_inventory' => $validated['use_company_inventory'] ?? $branch->use_company_inventory,
            ]);

            // Actualizar User representante
            $user = $branch->user;
            $user->update([
                'name' => $validated['name'] ?? $user->name,
                'email' => $validated['email'] ?? $user->email,
                'user_limit' => $validated['user_limit'] ?? $user->user_limit,
                'is_active' => $validated['is_active'] ?? $user->is_active,
            ]);

            if (isset($validated['password'])) {
                $user->update(['password' => Hash::make($validated['password'])]);
            }

            $branch->load('user', 'company');

            return response()->json([
                'success' => true,
                'message' => 'Sucursal actualizada exitosamente',
                'data' => $branch,
            ]);
        });
    }

    /**
     * Eliminar sucursal
     */
    public function destroy(Request $request, Branch $branch)
    {
        $auth = $request->user();

        if (!$this->canManageBranch($auth, $branch)) {
            return response()->json([
                'success' => false,
                'message' => 'No tienes permisos para eliminar esta sucursal',
            ], 403);
        }

        // Verificar que no tenga usuarios activos
        $activeUsers = User::where('parent_id', $branch->user->id)
            ->where('is_active', true)
            ->count();

        if ($activeUsers > 0) {
            return response()->json([
                'success' => false,
                'message' => "No puedes eliminar una sucursal con {$activeUsers} usuario(s) activo(s)",
            ], 422);
        }

        return DB::transaction(function () use ($branch) {
            $user = $branch->user;
            $branch->delete();
            $user->delete();

            return response()->json([
                'success' => true,
                'message' => 'Sucursal eliminada exitosamente',
            ]);
        });
    }

    /**
     * Activar/desactivar sucursal
     */
    public function toggle(Request $request, Branch $branch)
    {
        $auth = $request->user();

        if (!$this->canManageBranch($auth, $branch)) {
            return response()->json([
                'success' => false,
                'message' => 'No tienes permisos para cambiar el estado de esta sucursal',
            ], 403);
        }

        $newStatus = !$branch->is_active;

        DB::transaction(function () use ($branch, $newStatus) {
            $branch->update(['is_active' => $newStatus]);
            $branch->user->update(['is_active' => $newStatus]);
        });

        return response()->json([
            'success' => true,
            'message' => $newStatus ? 'Sucursal activada' : 'Sucursal desactivada',
            'data' => ['is_active' => $newStatus],
        ]);
    }

    /**
     * Verificar si el usuario puede acceder a la sucursal
     */
    private function canAccessBranch(User $user, Branch $branch): bool
    {
        if ($user->isMaster()) {
            return true;
        }

        if ($user->isCompany()) {
            return $branch->company_id === $user->id;
        }

        if ($user->isAdmin()) {
            return $branch->id === $user->representable_id;
        }

        return false;
    }

    /**
     * Verificar si el usuario puede gestionar la sucursal
     */
    private function canManageBranch(User $user, Branch $branch): bool
    {
        if ($user->isMaster()) {
            return true;
        }

        if ($user->isCompany()) {
            return $branch->company_id === $user->id;
        }

        return false;
    }

    /**
     * Generar slug único
     */
    private function generateUniqueSlug(string $name): string
    {
        $slug = \Str::slug($name);
        $original = $slug;
        $count = 1;

        while (Branch::where('slug', $slug)->exists()) {
            $slug = $original . '-' . $count;
            $count++;
        }

        return $slug;
    }
}
