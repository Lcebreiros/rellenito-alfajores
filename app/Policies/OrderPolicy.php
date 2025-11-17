<?php

namespace App\Policies;

use App\Models\Order;
use App\Models\User;
use Illuminate\Auth\Access\Response;

class OrderPolicy
{
    /**
     * Determine whether the user can view any models.
     */
    public function viewAny(User $user): bool
    {
        return true; // Todos pueden ver órdenes (filtradas por scope)
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(User $user, Order $order): bool
    {
        // Master puede ver todas
        if (method_exists($user, 'isMaster') && $user->isMaster()) {
            return true;
        }

        // Company/Admin puede ver de su jerarquía
        if (method_exists($user, 'isCompany') && $user->isCompany()) {
            return $order->company_id === $user->id || $order->user_id === $user->id;
        }

        // Usuario solo puede ver las propias
        return $order->user_id === $user->id;
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(User $user): bool
    {
        return true; // Todos los usuarios autenticados pueden crear órdenes
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, Order $order): bool
    {
        // Solo el propietario puede editar
        return $order->user_id === $user->id;
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, Order $order): bool
    {
        // Solo el propietario puede eliminar
        return $order->user_id === $user->id;
    }

    /**
     * Determine whether the user can finalize the model.
     */
    public function finalize(User $user, Order $order): bool
    {
        // Solo el propietario puede finalizar
        return $order->user_id === $user->id;
    }

    /**
     * Determine whether the user can cancel the model.
     */
    public function cancel(User $user, Order $order): bool
    {
        // Solo el propietario o su superior pueden cancelar
        if ($order->user_id === $user->id) {
            return true;
        }

        // Company/Admin puede cancelar órdenes de su jerarquía
        if (method_exists($user, 'isCompany') && $user->isCompany()) {
            return $order->company_id === $user->id;
        }

        return false;
    }

    /**
     * Determine whether the user can restore the model.
     */
    public function restore(User $user, Order $order): bool
    {
        return $order->user_id === $user->id;
    }

    /**
     * Determine whether the user can permanently delete the model.
     */
    public function forceDelete(User $user, Order $order): bool
    {
        // Solo master puede hacer hard delete
        return method_exists($user, 'isMaster') && $user->isMaster();
    }
}
