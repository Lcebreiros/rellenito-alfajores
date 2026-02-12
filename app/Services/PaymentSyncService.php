<?php

namespace App\Services;

use App\Models\ParkingStay;
use App\Models\PaymentMethod;
use App\Models\User;

class PaymentSyncService
{
    /**
     * Sincroniza los métodos de pago para una estadía de parking.
     * Divide el monto total equitativamente entre los métodos seleccionados.
     */
    public function syncPaymentMethods(ParkingStay $stay, array $methodIds, User $user): void
    {
        $ids = collect($methodIds)->filter()->unique()->map(fn($id) => (int) $id);

        if ($ids->isEmpty()) {
            $stay->paymentMethods()->detach();
            return;
        }

        $allowedIds = PaymentMethod::availableForUser($user)
            ->whereIn('id', $ids)
            ->pluck('id');

        $finalIds = $ids->intersect($allowedIds);

        if ($finalIds->isEmpty()) {
            $stay->paymentMethods()->detach();
            return;
        }

        $amountPerMethod = $stay->total_amount > 0 && $finalIds->count() > 0
            ? ((float) $stay->total_amount) / $finalIds->count()
            : 0;

        $pivotData = [];
        foreach ($finalIds as $pmId) {
            $pivotData[$pmId] = [
                'amount' => $amountPerMethod,
                'created_at' => now(),
                'updated_at' => now(),
            ];
        }

        $stay->paymentMethods()->sync($pivotData);
    }
}
