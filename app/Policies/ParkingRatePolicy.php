<?php

namespace App\Policies;

use App\Models\Rate;
use App\Models\User;
use App\Policies\Concerns\BelongsToCompany;

class RatePolicy
{
    use BelongsToCompany;

    public function viewAny(User $user): bool
    {
        return $user->hasModule('parking');
    }

    public function view(User $user, Rate $parkingRate): bool
    {
        return $this->belongsToUserCompany($user, $parkingRate)
            && $user->hasModule('parking');
    }

    public function create(User $user): bool
    {
        return $user->hasModule('parking');
    }

    public function update(User $user, Rate $parkingRate): bool
    {
        return $this->belongsToUserCompany($user, $parkingRate)
            && $user->hasModule('parking');
    }

    public function delete(User $user, Rate $parkingRate): bool
    {
        return $this->belongsToUserCompany($user, $parkingRate)
            && $user->hasModule('parking');
    }

    public function restore(User $user, Rate $parkingRate): bool
    {
        return $this->belongsToUserCompany($user, $parkingRate)
            && $user->hasModule('parking');
    }

    public function forceDelete(User $user, Rate $parkingRate): bool
    {
        return $this->belongsToUserCompany($user, $parkingRate)
            && $user->hasModule('parking');
    }
}
