<?php

namespace App\Services;

use App\Models\ParkingStay;
use App\Models\Discount;

class ParkingPricingService
{
    /**
     * Calcular y asignar total a una estadía.
     */
    public function closeStay(ParkingStay $stay, ?Discount $discount = null): ParkingStay
    {
        $calc = $stay->calculateTotal($discount);
        $stay->total_amount = $calc['total'];
        $stay->pricing_breakdown = $calc['breakdown'];
        $stay->status = 'closed';
        if ($discount) {
            $stay->discount_id = $discount->id;
            $stay->discount_amount = $calc['discount_amount'] ?? 0;
        }
        $stay->save();
        return $stay;
    }
}
