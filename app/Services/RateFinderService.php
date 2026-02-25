<?php

namespace App\Services;

use App\Models\Rate;

class RateFinderService
{
    /**
     * Busca la tarifa apropiada para un tipo de vehículo.
     * Intenta encontrar una tarifa específica para el tipo de vehículo,
     * si no encuentra, retorna la tarifa de respaldo proporcionada.
     */
    public function findRateForVehicle(int $companyId, ?string $vehicleType, ?int $fallbackRateId): ?int
    {
        $type = trim(mb_strtolower((string) $vehicleType));

        if ($type !== '') {
            $match = Rate::where('company_id', $companyId)
                ->where('is_active', true)
                ->whereNotNull('vehicle_type')
                ->whereRaw('LOWER(vehicle_type) = ?', [$type])
                ->value('id');

            if ($match) {
                return $match;
            }
        }

        return $fallbackRateId;
    }
}
