<?php

namespace App\Policies;

use App\Models\ParkingShift;
use App\Models\User;
use App\Policies\Concerns\BelongsToCompany;

class ParkingShiftPolicy
{
    use BelongsToCompany;

    public function viewAny(User $user): bool
    {
        return $user->hasModule('parking');
    }

    public function view(User $user, ParkingShift $parkingShift): bool
    {
        return $this->belongsToUserCompany($user, $parkingShift)
            && $user->hasModule('parking');
    }

    public function create(User $user): bool
    {
        return $user->hasModule('parking');
    }

    public function update(User $user, ParkingShift $parkingShift): bool
    {
        return $this->belongsToUserCompany($user, $parkingShift)
            && $user->hasModule('parking');
    }

    public function delete(User $user, ParkingShift $parkingShift): bool
    {
        return $this->belongsToUserCompany($user, $parkingShift)
            && $user->hasModule('parking');
    }

    public function restore(User $user, ParkingShift $parkingShift): bool
    {
        return $this->belongsToUserCompany($user, $parkingShift)
            && $user->hasModule('parking');
    }

    public function forceDelete(User $user, ParkingShift $parkingShift): bool
    {
        return $this->belongsToUserCompany($user, $parkingShift)
            && $user->hasModule('parking');
    }
}
