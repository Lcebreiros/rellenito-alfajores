<?php

namespace App\Services;

use App\Models\User;
use App\Models\Branch;
use App\DTOs\CreateBranchDTO;
use App\DTOs\UpdateBranchDTO;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class BranchService
{
    public function createBranch(User $company, CreateBranchDTO $data): array
    {
        // Validaciones de negocio
        if (!$company->isCompany()) {
            throw new \Exception('Solo las empresas pueden crear sucursales');
        }

        if (!$company->canCreateBranch()) {
            throw new \Exception('Se alcanzó el límite de sucursales permitido');
        }

        return DB::transaction(function () use ($company, $data) {
            // 1. Crear Branch (entidad de negocio)
            $branch = Branch::create([
                'company_id' => $company->id,
                'name' => $data->name,
                'slug' => $this->generateUniqueSlug($data->name),
                'address' => $data->address,
                'phone' => $data->phone,
                'contact_email' => $data->contact_email,
                'is_active' => $data->is_active,
            ]);

            Log::info('Branch creado', [
                'branch_id' => $branch->id, 
                'company_id' => $company->id,
                'name' => $branch->name
            ]);

            // 2. Crear User representante (autenticación)
            $user = User::create([
                'name' => $data->name,
                'email' => $data->email,
                'password' => Hash::make($data->password),
                'parent_id' => $company->id,
                'hierarchy_level' => User::HIERARCHY_ADMIN,
                'organization_context' => $company->organization_context,
                'representable_id' => $branch->id,
                'representable_type' => Branch::class,
                'is_active' => $data->is_active,
                'user_limit' => $data->user_limit,
            ]);

            Log::info('User representante creado', [
                'user_id' => $user->id,
                'branch_id' => $branch->id,
                'email' => $user->email
            ]);

            // 3. Actualizar hierarchy_path
            $user->updateHierarchyPath();

            // 4. Cargar relaciones
            $branch->load('user', 'company');
            $user->load('representable');

            Log::info('Sucursal creada completamente', [
                'branch_id' => $branch->id,
                'user_id' => $user->id,
                'hierarchy_path' => $user->hierarchy_path
            ]);

            return [
                'branch' => $branch,
                'user' => $user
            ];
        });
    }

    public function updateBranch(Branch $branch, UpdateBranchDTO $data): Branch
    {
        return DB::transaction(function () use ($branch, $data) {
            // Actualizar Branch
            $branch->update([
                'name' => $data->name,
                'address' => $data->address,
                'phone' => $data->phone,
                'contact_email' => $data->contact_email,
                'is_active' => $data->is_active,
            ]);

            // Actualizar User representante si existe
            if ($branch->user) {
                $userData = [
                    'name' => $data->name,
                    'email' => $data->email,
                    'user_limit' => $data->user_limit,
                    'is_active' => $data->is_active,
                ];

                if ($data->password) {
                    $userData['password'] = Hash::make($data->password);
                }

                $branch->user->update($userData);

                Log::info('Branch y User actualizados', [
                    'branch_id' => $branch->id,
                    'user_id' => $branch->user->id
                ]);
            }

            return $branch->load('user', 'company');
        });
    }

    public function deleteBranch(Branch $branch): bool
    {
        return DB::transaction(function () use ($branch) {
            $branchId = $branch->id;
            $branchName = $branch->name;
            
            // El User se elimina automáticamente por el evento en Branch::booted()
            $branch->delete();
            
            Log::info('Sucursal eliminada', [
                'branch_id' => $branchId,
                'name' => $branchName
            ]);
            
            return true;
        });
    }

    public function getBranchesForCompany(User $company, int $perPage = 20)
    {
        return Branch::with(['user', 'company'])
                     ->where('company_id', $company->id)
                     ->latest()
                     ->paginate($perPage);
    }

    public function getAllBranches(int $perPage = 20)
    {
        return Branch::with(['user', 'company'])
                     ->latest()
                     ->paginate($perPage);
    }

    public function getBranchesForMaster(?int $companyId = null, int $perPage = 20)
    {
        $query = Branch::with(['user', 'company']);
        
        if ($companyId) {
            $query->where('company_id', $companyId);
        }
        
        return $query->latest()->paginate($perPage);
    }

    protected function generateUniqueSlug(string $name): string
    {
        $baseSlug = Str::slug($name);
        $slug = $baseSlug;
        $counter = 1;

        while (Branch::where('slug', $slug)->exists()) {
            $slug = $baseSlug . '-' . $counter;
            $counter++;
        }

        return $slug;
    }
}