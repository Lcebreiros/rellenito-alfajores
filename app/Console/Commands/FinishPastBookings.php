<?php

namespace App\Console\Commands;

use App\Models\Booking;
use App\Services\BookingService;
use Illuminate\Console\Command;

class FinishPastBookings extends Command
{
    protected $signature = 'rental:finish-past-bookings';

    protected $description = 'Marca como finalizadas las reservas confirmadas cuyo horario ya pasó.';

    public function __construct(protected BookingService $bookingService)
    {
        parent::__construct();
    }

    public function handle(): int
    {
        $bookings = Booking::where('status', 'confirmed')
            ->where('ends_at', '<=', now())
            ->get();

        if ($bookings->isEmpty()) {
            return self::SUCCESS;
        }

        foreach ($bookings as $booking) {
            $this->bookingService->finishBooking($booking);
        }

        $this->info("Se finalizaron {$bookings->count()} reserva(s).");

        return self::SUCCESS;
    }
}
