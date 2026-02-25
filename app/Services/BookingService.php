<?php

namespace App\Services;

use App\Models\Booking;
use App\Models\RentalSpace;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Support\Facades\Log;

class BookingService
{
    public function __construct(
        protected GoogleCalendarService $googleCalendar
    ) {}

    /**
     * Verifica si existe solapamiento de reservas para un espacio en un rango horario.
     */
    public function checkOverlap(int $spaceId, Carbon $start, Carbon $end, ?int $excludeBookingId = null): bool
    {
        return Booking::overlapping($spaceId, $start, $end, $excludeBookingId)->exists();
    }

    /**
     * Crea una nueva reserva, validando que no haya solapamiento.
     *
     * @throws \InvalidArgumentException si hay solapamiento
     */
    public function createBooking(array $data, int $companyId): Booking
    {
        $start = Carbon::parse($data['starts_at']);
        $end = $start->copy()->addMinutes((int) $data['duration_minutes']);

        if ($this->checkOverlap((int) $data['rental_space_id'], $start, $end)) {
            throw new \InvalidArgumentException('El espacio ya tiene una reserva en ese horario.');
        }

        return Booking::create([
            'company_id'                 => $companyId,
            'rental_space_id'            => $data['rental_space_id'],
            'rental_duration_option_id'  => $data['rental_duration_option_id'] ?? null,
            'client_id'                  => $data['client_id'] ?? null,
            'client_name'                => $data['client_name'] ?? null,
            'client_phone'               => $data['client_phone'] ?? null,
            'starts_at'                  => $start,
            'ends_at'                    => $end,
            'duration_minutes'           => (int) $data['duration_minutes'],
            'status'                     => 'pending',
            'total_amount'               => $data['total_amount'] ?? 0,
            'notes'                      => $data['notes'] ?? null,
        ]);
    }

    /**
     * Actualiza una reserva existente. Si ya estaba confirmada y tiene Google event, lo sincroniza.
     *
     * @throws \InvalidArgumentException si hay solapamiento con otra reserva
     */
    public function updateBooking(Booking $booking, array $data, User $user): Booking
    {
        $start = Carbon::parse($data['starts_at']);
        $durationMinutes = (int) ($data['duration_minutes'] ?? $booking->duration_minutes);
        $end = $start->copy()->addMinutes($durationMinutes);

        if ($this->checkOverlap((int) ($data['rental_space_id'] ?? $booking->rental_space_id), $start, $end, $booking->id)) {
            throw new \InvalidArgumentException('El espacio ya tiene una reserva en ese horario.');
        }

        $booking->update([
            'rental_space_id'            => $data['rental_space_id'] ?? $booking->rental_space_id,
            'rental_duration_option_id'  => $data['rental_duration_option_id'] ?? $booking->rental_duration_option_id,
            'client_id'                  => $data['client_id'] ?? $booking->client_id,
            'client_name'                => $data['client_name'] ?? $booking->client_name,
            'client_phone'               => $data['client_phone'] ?? $booking->client_phone,
            'starts_at'                  => $start,
            'ends_at'                    => $end,
            'duration_minutes'           => $durationMinutes,
            'total_amount'               => $data['total_amount'] ?? $booking->total_amount,
            'notes'                      => $data['notes'] ?? $booking->notes,
        ]);

        // Si ya está confirmada con un event de Google Calendar, sincronizamos
        if ($booking->status === 'confirmed' && $booking->google_calendar_event_id) {
            $this->syncUpdateToGoogleCalendar($booking->fresh(), $user);
        }

        return $booking->fresh();
    }

    /**
     * Confirma una reserva y crea el evento en Google Calendar si el usuario está conectado.
     */
    public function confirmBooking(Booking $booking, User $user): void
    {
        $booking->update(['status' => 'confirmed']);

        $this->syncCreateToGoogleCalendar($booking->fresh()->load('space'), $user);
    }

    /**
     * Cancela una reserva y elimina el evento de Google Calendar si existe.
     */
    public function cancelBooking(Booking $booking, User $user): void
    {
        if ($booking->google_calendar_event_id) {
            $this->googleCalendar->forUser($user)->deleteEvent($booking->google_calendar_event_id);
        }

        $booking->update([
            'status'                   => 'cancelled',
            'google_calendar_event_id' => null,
        ]);
    }

    /**
     * Finaliza una reserva (llamado por el scheduler automáticamente).
     * No altera el evento de Google Calendar — la reserva quedó pasada naturalmente.
     */
    public function finishBooking(Booking $booking): void
    {
        $booking->update(['status' => 'finished']);
    }

    // ---------- Google Calendar Helpers ----------

    private function syncCreateToGoogleCalendar(Booking $booking, User $user): void
    {
        try {
            $gcService = $this->googleCalendar->forUser($user);

            if (!$gcService->isConnected()) {
                return;
            }

            $spaceName  = $booking->space->name ?? 'Espacio';
            $clientName = $booking->getClientDisplayName();
            $clientPhone = $booking->getClientDisplayPhone();

            $description = "Duración: {$booking->duration_minutes} min";
            if ($clientPhone) {
                $description .= "\nTeléfono: {$clientPhone}";
            }
            if ($booking->notes) {
                $description .= "\nNotas: {$booking->notes}";
            }

            $event = $gcService->createEvent(
                summary:     "{$spaceName} · {$clientName}",
                start:       $booking->starts_at,
                end:         $booking->ends_at,
                description: $description,
                location:    $spaceName,
                eventType:   'booking'
            );

            if ($event) {
                $booking->update(['google_calendar_event_id' => $event->getId()]);
            }
        } catch (\Exception $e) {
            Log::warning('BookingService: no se pudo crear evento en Google Calendar', [
                'booking_id' => $booking->id,
                'error'      => $e->getMessage(),
            ]);
        }
    }

    private function syncUpdateToGoogleCalendar(Booking $booking, User $user): void
    {
        try {
            $gcService = $this->googleCalendar->forUser($user);

            if (!$gcService->isConnected() || !$booking->google_calendar_event_id) {
                return;
            }

            $spaceName  = $booking->space->name ?? 'Espacio';
            $clientName = $booking->getClientDisplayName();
            $clientPhone = $booking->getClientDisplayPhone();

            $description = "Duración: {$booking->duration_minutes} min";
            if ($clientPhone) {
                $description .= "\nTeléfono: {$clientPhone}";
            }
            if ($booking->notes) {
                $description .= "\nNotas: {$booking->notes}";
            }

            $gcService->updateEvent(
                eventId:     $booking->google_calendar_event_id,
                summary:     "{$spaceName} · {$clientName}",
                start:       $booking->starts_at,
                end:         $booking->ends_at,
                description: $description,
                location:    $spaceName
            );
        } catch (\Exception $e) {
            Log::warning('BookingService: no se pudo actualizar evento en Google Calendar', [
                'booking_id' => $booking->id,
                'error'      => $e->getMessage(),
            ]);
        }
    }
}
