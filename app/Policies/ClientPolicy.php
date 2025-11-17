<?php

namespace App\Policies;

use App\Models\Client;
use App\Models\User;

class ClientPolicy
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
    public function view(User $user, Client $client): bool
    {
        // Master puede ver todos
        if (method_exists($user, 'isMaster') && $user->isMaster()) {
            return true;
        }

        // Company puede ver clientes de su empresa
        if (method_exists($user, 'isCompany') && $user->isCompany()) {
            return $client->user_id === $user->id ||
                   ($client->user && $client->user->parent_id === $user->id);
        }

        // Usuario puede ver sus propios clientes
        return $client->user_id === $user->id;
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
    public function update(User $user, Client $client): bool
    {
        // Master puede editar todos
        if (method_exists($user, 'isMaster') && $user->isMaster()) {
            return true;
        }

        // Company puede editar clientes de su empresa
        if (method_exists($user, 'isCompany') && $user->isCompany()) {
            return $client->user_id === $user->id ||
                   ($client->user && $client->user->parent_id === $user->id);
        }

        // Usuario puede editar sus propios clientes
        return $client->user_id === $user->id;
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, Client $client): bool
    {
        return $this->update($user, $client);
    }

    /**
     * Determine whether the user can restore the model.
     */
    public function restore(User $user, Client $client): bool
    {
        return $this->update($user, $client);
    }

    /**
     * Determine whether the user can permanently delete the model.
     */
    public function forceDelete(User $user, Client $client): bool
    {
        return method_exists($user, 'isMaster') && $user->isMaster();
    }
}
