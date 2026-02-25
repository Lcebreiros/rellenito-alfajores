<?php

namespace App\Services;

use App\Models\ParkingStay;
use Carbon\Carbon;

class ParkingTicketService
{
    /**
     * Genera los datos del ticket de parking para impresora térmica
     * El ticket solo se imprime al INGRESO. Al egreso se escanea.
     */
    public function generateTicketData(ParkingStay $stay): array
    {
        $stay->loadMissing(['parkingSpace']);

        $data = [
            'stay_id' => $stay->id,
            'license_plate' => strtoupper($stay->license_plate),
            'vehicle_type' => $stay->vehicle_type ?? 'Auto',
            'space_name' => $stay->parkingSpace?->name ?? '-',
            'entry_at' => $stay->entry_at?->format('d/m/Y H:i'),
            'entry_timestamp' => $stay->entry_at?->timestamp,
            // Código de barras para escanear (ID de la estadía con padding)
            'barcode' => str_pad($stay->id, 10, '0', STR_PAD_LEFT),
        ];

        return $data;
    }

    /**
     * Genera el texto plano para impresora térmica ESC/POS
     * Solo ticket de ingreso (al egreso se escanea)
     */
    public function generatePlainText(array $ticketData): string
    {
        $lines = [];
        $width = 42; // Ancho típico de impresora térmica de 80mm

        // Encabezado
        $businessName = config('parking.business_name', 'Estacionamiento Moreno S.R.L.');
        $lines[] = '';
        $lines[] = $this->center($businessName, $width);
        $lines[] = $this->separator('=', $width);
        $lines[] = '';

        // Datos del vehículo
        $lines[] = $this->leftRight('Patente:', $ticketData['license_plate'], $width);
        $lines[] = $this->leftRight('Vehículo:', $ticketData['vehicle_type'], $width);
        $lines[] = $this->leftRight('Cochera:', $ticketData['space_name'], $width);
        $lines[] = '';
        $lines[] = $this->leftRight('Fecha:', $ticketData['entry_at'], $width);
        $lines[] = '';

        // Código de barras centrado
        $lines[] = $this->center($ticketData['barcode'], $width);
        $lines[] = '';

        $lines[] = $this->separator('=', $width);
        $lines[] = $this->center('Conserve este ticket', $width);
        $lines[] = $this->center('Gracias por su visita', $width);

        // Espacio final para corte
        $lines[] = '';
        $lines[] = '';
        $lines[] = '';

        return implode("\n", $lines);
    }

    /**
     * Centra un texto en el ancho dado
     */
    private function center(string $text, int $width): string
    {
        $len = mb_strlen($text);
        if ($len >= $width) {
            return $text;
        }

        $padding = floor(($width - $len) / 2);
        return str_repeat(' ', $padding) . $text;
    }

    /**
     * Línea separadora
     */
    private function separator(string $char, int $width): string
    {
        return str_repeat($char, $width);
    }

    /**
     * Texto alineado a izquierda y derecha
     */
    private function leftRight(string $left, string $right, int $width): string
    {
        $leftLen = mb_strlen($left);
        $rightLen = mb_strlen($right);
        $totalLen = $leftLen + $rightLen;

        if ($totalLen >= $width) {
            return $left . ' ' . $right;
        }

        $spaces = $width - $totalLen;
        return $left . str_repeat(' ', $spaces) . $right;
    }
}
