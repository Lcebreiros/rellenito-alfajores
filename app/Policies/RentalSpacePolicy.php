<?php

namespace App\Policies;

use App\Models\RentalSpace;
use App\Models\User;
use App\Policies\Concerns\BelongsToCompany;

class RentalSpacePolicy
{
    use BelongsToCompany;

    public function before(User $user): ?bool
    {
        if (method_exists($user, 'isMaster') && $user->isMaster()) {
            return true;
        }
        return null;
    }

    public function viewAny(User $user): bool
    {
        return $user->hasModule('alquileres');
    }

    public function view(User $user, RentalSpace $rentalSpace): bool
    {
        return $this->belongsToUserCompany($user, $rentalSpace)
            && $user->hasModule('alquileres');
    }

    public function create(User $user): bool
    {
        return $user->hasModule('alquileres');
    }

    public function update(User $user, RentalSpace $rentalSpace): bool
    {
        return $this->belongsToUserCompany($user, $rentalSpace)
            && $user->hasModule('alquileres');
    }

    public function delete(User $user, RentalSpace $rentalSpace): bool
    {
        return $this->belongsToUserCompany($user, $rentalSpace)
            && $user->hasModule('alquileres');
    }
}
