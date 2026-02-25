<?php

namespace App\Services;

use App\Models\ParkingShift;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Storage;

class ShiftReportService
{
    /**
     * Genera un reporte CSV del turno de parking con los movimientos y conteos de caja.
     * El formato es compatible con Excel.
     *
     * @param ParkingShift $shift El turno a reportar
     * @param Collection $stays Colección de estadías cerradas durante el turno
     * @param array $data Datos del cierre (cash_counted, envelope_amount, mp_amount)
     * @return string Ruta del archivo CSV generado en storage
     */
    public function exportShiftReport(ParkingShift $shift, Collection $stays, array $data): string
    {
        $rows = [];
        $totalMovements = 0;
        $reportDate = $shift->started_at?->format('Y-m-d') ?? now()->format('Y-m-d');

        foreach ($stays as $stay) {
            $detail = '';
            if ($stay->discount) {
                $detail = 'Bono: ' . $stay->discount->name;
            }

            $rows[] = [
                $reportDate,
                trim(strtoupper($stay->license_plate) . ' ' . ($stay->vehicle_type ?? '')),
                $detail,
                (float) $stay->total_amount,
                '',
                '',
            ];
            $totalMovements += (float) $stay->total_amount;
        }

        // Total en la última fila de movimientos
        if (!empty($rows)) {
            $rows[count($rows) - 1][5] = $totalMovements;
        }

        // Movimientos de caja (ingresos y egresos)
        $cashMovements = $shift->cashMovements()->orderBy('created_at')->get();
        foreach ($cashMovements as $movement) {
            $rows[] = [
                $movement->created_at->format('Y-m-d'),
                $movement->type === 'ingreso' ? 'INGRESO CAJA' : 'EGRESO CAJA',
                $movement->description . ($movement->notes ? ' - ' . $movement->notes : ''),
                $movement->type === 'ingreso' ? (float) $movement->amount : '',
                $movement->type === 'egreso' ? (float) $movement->amount : '',
                '',
            ];
        }

        // Caja contada
        $rows[] = [
            $reportDate,
            'Caja',
            'Conteo manual',
            '',
            '',
            (float) ($data['cash_counted'] ?? 0),
        ];

        // Egreso sobre jefes
        if (!empty($data['envelope_amount'])) {
            $rows[] = [
                $reportDate,
                'Sobre',
                'Puerta (jefes)',
                '',
                (float) $data['envelope_amount'],
                '',
            ];
        }

        // MP informativo
        if (!empty($data['mp_amount'])) {
            $rows[] = [
                $reportDate,
                'MP',
                'Cobrado por Mercado Pago (no efectivo)',
                '',
                (float) $data['mp_amount'],
                '',
            ];
        }

        $filename = 'parking/shifts/' . $shift->id . '-' . ($shift->started_at?->format('Ymd_His') ?? now()->format('Ymd_His')) . '.csv';

        $handle = fopen('php://temp', 'r+');
        fputcsv($handle, ['Fecha', 'Patente', 'Detalle', 'Ingreso', 'Egreso', 'Total']);

        foreach ($rows as $row) {
            fputcsv($handle, $row, ',');
        }

        rewind($handle);
        $csv = stream_get_contents($handle);
        fclose($handle);

        Storage::disk('local')->put($filename, $csv);

        return $filename;
    }
}
