<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\ParkingStay;
use App\Services\ParkingTicketService;
use App\Services\ThermalPrinterService;
use Illuminate\Http\Request;

class ParkingTicketController extends Controller
{
    /**
    * Devuelve los datos del ticket (y texto plano) para ser impreso por apps externas.
    */
    public function show(ParkingStay $parkingStay, Request $request, ParkingTicketService $ticketService)
    {
        $this->authorizeStay($parkingStay, $request->user());
        $parkingStay->loadMissing('parkingSpace');

        $ticketData = $this->buildTicketPayload($ticketService, $parkingStay);

        return response()->json([
            'success' => true,
            'data' => [
                'ticket' => $ticketData,
                'plain_text' => $ticketService->generatePlainText($ticketData),
            ],
        ]);
    }

    /**
    * Envía a imprimir el ticket y devuelve el resultado (útil para integraciones Node descargables).
    */
    public function print(ParkingStay $parkingStay, Request $request, ThermalPrinterService $printerService, ParkingTicketService $ticketService)
    {
        $this->authorizeStay($parkingStay, $request->user());
        $parkingStay->loadMissing('parkingSpace');

        $printed = $printerService->printParkingTicket($parkingStay);

        return response()->json([
            'success' => $printed,
            'ticket' => $ticketService->generateTicketData($parkingStay),
            'message' => $printed ? 'Ticket enviado a impresora' : 'No se pudo imprimir el ticket',
        ], $printed ? 200 : 500);
    }

    private function buildTicketPayload(ParkingTicketService $ticketService, ParkingStay $parkingStay): array
    {
        $ticketData = $ticketService->generateTicketData($parkingStay);
        $ticketData['business_name'] = config('parking.business_name', 'Estacionamiento Moreno S.R.L.');
        $ticketData['app_name'] = config('app.name', 'Gestior');

        return $ticketData;
    }

    private function authorizeStay(ParkingStay $stay, $user): void
    {
        $companyId = $this->currentCompanyId($user);
        if ((int) $stay->company_id !== $companyId) {
            abort(404);
        }
    }

    private function currentCompanyId($user): int
    {
        if ($user && $user->isCompany()) {
            return (int) $user->id;
        }

        if ($user && $user->parent_id) {
            return (int) $user->parent_id;
        }

        return (int) ($user->id ?? 0);
    }
}
