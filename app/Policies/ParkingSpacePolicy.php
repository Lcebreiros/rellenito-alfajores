<?php

namespace App\Policies;

use App\Models\ParkingSpace;
use App\Models\User;
use App\Policies\Concerns\BelongsToCompany;

class ParkingSpacePolicy
{
    use BelongsToCompany;

    public function viewAny(User $user): bool
    {
        return $user->hasModule('parking');
    }

    public function view(User $user, ParkingSpace $parkingSpace): bool
    {
        return $this->belongsToUserCompany($user, $parkingSpace)
            && $user->hasModule('parking');
    }

    public function create(User $user): bool
    {
        return $user->hasModule('parking');
    }

    public function update(User $user, ParkingSpace $parkingSpace): bool
    {
        return $this->belongsToUserCompany($user, $parkingSpace)
            && $user->hasModule('parking');
    }

    public function delete(User $user, ParkingSpace $parkingSpace): bool
    {
        return $this->belongsToUserCompany($user, $parkingSpace)
            && $user->hasModule('parking');
    }

    public function restore(User $user, ParkingSpace $parkingSpace): bool
    {
        return $this->belongsToUserCompany($user, $parkingSpace)
            && $user->hasModule('parking');
    }

    public function forceDelete(User $user, ParkingSpace $parkingSpace): bool
    {
        return $this->belongsToUserCompany($user, $parkingSpace)
            && $user->hasModule('parking');
    }
}
