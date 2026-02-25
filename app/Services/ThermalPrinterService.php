<?php

namespace App\Services;

use App\Models\ParkingStay;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class ThermalPrinterService
{
    private string $serverUrl;
    private bool $enabled;
    private int $timeout;

    public function __construct()
    {
        $this->serverUrl = config('thermal_printer.server_url', 'http://localhost:9876');
        $this->enabled = config('thermal_printer.enabled', true);
        $this->timeout = config('thermal_printer.timeout', 3);
    }

    /**
     * Imprime un ticket de parking en la impresora térmica
     * Solo se imprime al INGRESO (al egreso se escanea el ticket)
     *
     * Prioridad de métodos:
     * 1. Método directo (PHP → USB) - Más simple, sin servidor
     * 2. Método vía servidor (PHP → Node.js → USB) - Para múltiples apps
     */
    public function printParkingTicket(ParkingStay $stay): bool
    {
        $directEnabled = config('thermal_printer.direct.enabled', false);
        $directPrinted = false;

        // Prioridad 1: Método directo (más simple, sin servidor)
        if ($directEnabled) {
            try {
                $directService = new DirectThermalPrinterService();
                $directPrinted = $directService->printParkingTicket($stay);
                if ($directPrinted) {
                    return true;
                }

                Log::warning('Impresión directa no exitosa, intentando con servidor', [
                    'stay_id' => $stay->id,
                    'license_plate' => $stay->license_plate,
                ]);
            } catch (\Exception $e) {
                Log::warning('Error en impresión directa, intentando con servidor', [
                    'error' => $e->getMessage(),
                    'stay_id' => $stay->id,
                ]);
                // Continuar al método del servidor
            }
        }

        // Prioridad 2: Método vía servidor Node.js
        if (!$this->enabled) {
            Log::info('Impresión térmica deshabilitada en configuración');
            return $directPrinted;
        }

        try {
            $ticketService = new ParkingTicketService();
            $ticketData = $ticketService->generateTicketData($stay);
            $ticketData['business_name'] = config('parking.business_name', 'Estacionamiento Moreno S.R.L.');
            $ticketData['app_name'] = config('app.name', 'Gestior');

            $response = Http::timeout($this->timeout)
                ->post($this->serverUrl . '/print/ticket', [
                    'ticket_data' => $ticketData,
                ]);

            if ($response->successful()) {
                Log::info('Ticket impreso correctamente (vía servidor)', [
                    'stay_id' => $stay->id,
                    'license_plate' => $stay->license_plate,
                ]);
                return true;
            }

            Log::warning('Error al imprimir ticket', [
                'stay_id' => $stay->id,
                'status' => $response->status(),
                'response' => $response->body(),
            ]);

            return false;
        } catch (\Exception $e) {
            // No lanzar excepción, solo loguear
            // La impresión es opcional, no debe romper el flujo
            Log::error('Excepción al imprimir ticket', [
                'stay_id' => $stay->id,
                'error' => $e->getMessage(),
            ]);

            return false;
        }
    }

    /**
     * Imprime texto plano en la impresora térmica
     */
    public function printText(string $text): bool
    {
        if (!$this->enabled) {
            return false;
        }

        try {
            $response = Http::timeout($this->timeout)
                ->post($this->serverUrl . '/print/text', [
                    'text' => $text,
                ]);

            return $response->successful();
        } catch (\Exception $e) {
            Log::error('Error al imprimir texto', ['error' => $e->getMessage()]);
            return false;
        }
    }

    /**
     * Verifica si el servidor de impresión está disponible
     */
    public function isAvailable(): bool
    {
        if (!$this->enabled) {
            return false;
        }

        try {
            $response = Http::timeout(2)->get($this->serverUrl . '/');
            return $response->successful();
        } catch (\Exception $e) {
            return false;
        }
    }

    /**
     * Obtiene el estado del servidor de impresión
     */
    public function getStatus(): array
    {
        try {
            $response = Http::timeout(2)->get($this->serverUrl . '/');

            if ($response->successful()) {
                return $response->json();
            }

            return [
                'status' => 'error',
                'printer_connected' => false,
                'error' => 'No se pudo conectar al servidor',
            ];
        } catch (\Exception $e) {
            return [
                'status' => 'offline',
                'printer_connected' => false,
                'error' => $e->getMessage(),
            ];
        }
    }

    /**
     * Imprime un ticket de prueba
     */
    public function printTestTicket(): bool
    {
        if (!$this->enabled) {
            return false;
        }

        try {
            $response = Http::timeout($this->timeout)
                ->post($this->serverUrl . '/test');

            return $response->successful();
        } catch (\Exception $e) {
            Log::error('Error al imprimir ticket de prueba', ['error' => $e->getMessage()]);
            return false;
        }
    }
}
