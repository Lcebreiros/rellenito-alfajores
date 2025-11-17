<?php

namespace App\Policies;

use App\Models\Product;
use App\Models\User;

class ProductPolicy
{
    /**
     * Determine whether the user can view any models.
     */
    public function viewAny(User $user): bool
    {
        return true;
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(User $user, Product $product): bool
    {
        // Master puede ver todos
        if (method_exists($user, 'isMaster') && $user->isMaster()) {
            return true;
        }

        // Company puede ver productos de su empresa
        if (method_exists($user, 'isCompany') && $user->isCompany()) {
            return $product->company_id === $user->id || $product->user_id === $user->id;
        }

        // Usuario puede ver productos propios o de su empresa
        return $product->user_id === $user->id;
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(User $user): bool
    {
        return true;
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, Product $product): bool
    {
        // Master puede editar todos
        if (method_exists($user, 'isMaster') && $user->isMaster()) {
            return true;
        }

        // Company puede editar productos de su empresa
        if (method_exists($user, 'isCompany') && $user->isCompany()) {
            return $product->company_id === $user->id || $product->user_id === $user->id;
        }

        // Usuario puede editar sus propios productos
        return $product->user_id === $user->id;
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, Product $product): bool
    {
        // Same logic as update
        return $this->update($user, $product);
    }

    /**
     * Determine whether the user can restore the model.
     */
    public function restore(User $user, Product $product): bool
    {
        return $this->update($user, $product);
    }

    /**
     * Determine whether the user can permanently delete the model.
     */
    public function forceDelete(User $user, Product $product): bool
    {
        return method_exists($user, 'isMaster') && $user->isMaster();
    }
}
