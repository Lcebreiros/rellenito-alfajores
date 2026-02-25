<?php

namespace App\Policies\Concerns;

use App\Models\User;

trait BelongsToCompany
{
    /**
     * Obtiene el company_id apropiado según el tipo de usuario.
     */
    protected function getCurrentCompanyId(User $user): int
    {
        if ($user && method_exists($user, 'isCompany') && $user->isCompany()) {
            return (int) $user->id;
        }

        if ($user && $user->parent_id) {
            return (int) $user->parent_id;
        }

        return (int) $user->id;
    }

    /**
     * Verifica si un modelo con company_id pertenece a la compañía del usuario.
     */
    protected function belongsToUserCompany(User $user, $model): bool
    {
        return (int) $model->company_id === $this->getCurrentCompanyId($user);
    }

    /**
     * Verifica si el módulo está activo para la empresa del usuario.
     * Usa la empresa padre cuando el usuario es empleado/admin,
     * igual que el middleware EnsureUserHasModule.
     */
    protected function companyHasModule(User $user, string $module): bool
    {
        $company = $user->isCompany() ? $user : $user->parent;
        return $company && $company->hasModule($module);
    }
}
