<?php

namespace App\Policies;

use App\Models\ParkingStay;
use App\Models\User;
use App\Policies\Concerns\BelongsToCompany;

class ParkingStayPolicy
{
    use BelongsToCompany;

    /**
     * Determine whether the user can view any models.
     */
    public function viewAny(User $user): bool
    {
        return $user->hasModule('parking');
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(User $user, ParkingStay $parkingStay): bool
    {
        return $this->belongsToUserCompany($user, $parkingStay)
            && $user->hasModule('parking');
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(User $user): bool
    {
        return $user->hasModule('parking');
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, ParkingStay $parkingStay): bool
    {
        return $this->belongsToUserCompany($user, $parkingStay)
            && $user->hasModule('parking');
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, ParkingStay $parkingStay): bool
    {
        return $this->belongsToUserCompany($user, $parkingStay)
            && $user->hasModule('parking');
    }

    /**
     * Determine whether the user can restore the model.
     */
    public function restore(User $user, ParkingStay $parkingStay): bool
    {
        return $this->belongsToUserCompany($user, $parkingStay)
            && $user->hasModule('parking');
    }

    /**
     * Determine whether the user can permanently delete the model.
     */
    public function forceDelete(User $user, ParkingStay $parkingStay): bool
    {
        return $this->belongsToUserCompany($user, $parkingStay)
            && $user->hasModule('parking');
    }
}
