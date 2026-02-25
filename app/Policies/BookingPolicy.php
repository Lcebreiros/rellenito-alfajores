<?php

namespace App\Policies;

use App\Models\Booking;
use App\Models\User;
use App\Policies\Concerns\BelongsToCompany;

class BookingPolicy
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

    public function view(User $user, Booking $booking): bool
    {
        return $this->belongsToUserCompany($user, $booking)
            && $user->hasModule('alquileres');
    }

    public function create(User $user): bool
    {
        return $user->hasModule('alquileres');
    }

    public function update(User $user, Booking $booking): bool
    {
        return $this->belongsToUserCompany($user, $booking)
            && $user->hasModule('alquileres');
    }

    public function delete(User $user, Booking $booking): bool
    {
        return $this->belongsToUserCompany($user, $booking)
            && $user->hasModule('alquileres');
    }
}
