<?php

namespace App\Policies;

use App\Models\Employee;
use App\Models\User;

class EmployeePolicy
{
    /**
     * Ver cualquier empleado (listado).
     */
    public function viewAny(User $user): bool
    {
        return true; // listado ya está scopeado por company en el controller
    }

    /**
     * Ver un empleado específico.
     */
    public function view(User $user, Employee $employee): bool
    {
        $companyId = $user->rootCompany()?->id ?? $user->id;
        return (int) $employee->company_id === (int) $companyId;
    }

    /**
     * Crear empleados: permitido para cuentas de nivel empresa.
     */
    public function create(User $user): bool
    {
        return method_exists($user, 'isCompany') && $user->isCompany();
    }

    /**
     * Actualizar empleados: debe pertenecer a su empresa.
     */
    public function update(User $user, Employee $employee): bool
    {
        $companyId = $user->rootCompany()?->id ?? $user->id;
        return (int) $employee->company_id === (int) $companyId;
    }

    /**
     * Eliminar empleados: debe pertenecer a su empresa.
     */
    public function delete(User $user, Employee $employee): bool
    {
        $companyId = $user->rootCompany()?->id ?? $user->id;
        return (int) $employee->company_id === (int) $companyId;
    }
}

