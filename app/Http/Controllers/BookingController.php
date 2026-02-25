<?php

namespace App\Http\Controllers;

use App\Models\Booking;
use App\Models\Client;
use App\Models\PaymentMethod;
use App\Models\RentalSpace;
use App\Services\BookingService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class BookingController extends Controller
{
    public function __construct(
        protected BookingService $bookingService
    ) {
        $this->middleware(function ($request, $next) {
            $user = Auth::user();
            if ($user && !$user->isMaster() && !$user->hasModule('alquileres')) {
                abort(404);
            }
            return $next($request);
        });
    }

    /**
     * Vista principal: calendario de reservas (Livewire component).
     */
    public function calendar()
    {
        return view('rentals.calendar');
    }

    /**
     * Listado de reservas con filtros.
     */
    public function index(Request $request)
    {
        $user      = Auth::user();
        $companyId = $this->currentCompanyId();

        $query = Booking::forCompany($user)
            ->with(['space', 'client'])
            ->orderByDesc('starts_at');

        if ($request->filled('status')) {
            $query->byStatus($request->status);
        }
        if ($request->filled('space_id')) {
            $query->where('rental_space_id', $request->space_id);
        }
        if ($request->filled('date_from')) {
            $query->whereDate('starts_at', '>=', $request->date_from);
        }
        if ($request->filled('date_to')) {
            $query->whereDate('starts_at', '<=', $request->date_to);
        }

        $bookings = $query->paginate(20)->withQueryString();

        $spacesQuery = RentalSpace::active()->orderBy('name');
        if ($companyId) {
            $spacesQuery->where('company_id', $companyId);
        }
        $spaces = $spacesQuery->get();

        return view('rentals.bookings.index', compact('bookings', 'spaces'));
    }

    /**
     * Formulario de creación.
     */
    public function create()
    {
        $companyId = $this->currentCompanyId();

        $spacesQuery = RentalSpace::with('activeDurationOptions')->active()->orderBy('name');
        if ($companyId) {
            $spacesQuery->where('company_id', $companyId);
        }
        $spaces = $spacesQuery->get();

        $clientsQuery = Client::orderBy('name');
        if ($companyId) {
            $clientsQuery->where('user_id', $companyId);
        }
        $clients = $clientsQuery->get(['id', 'name', 'phone']);

        $paymentMethods = PaymentMethod::availableForUser(Auth::user())->get();

        return view('rentals.bookings.create', compact('spaces', 'clients', 'paymentMethods'));
    }

    /**
     * Guarda una nueva reserva.
     */
    public function store(Request $request)
    {
        $this->authorize('create', Booking::class);

        $data = $request->validate([
            'rental_space_id'           => 'required|exists:rental_spaces,id',
            'rental_duration_option_id' => 'nullable|exists:rental_duration_options,id',
            'client_id'                 => 'nullable|exists:clients,id',
            'client_name'               => 'nullable|string|max:150',
            'client_phone'              => 'nullable|string|max:50',
            'starts_at'                 => 'required|date',
            'duration_minutes'          => 'required|integer|min:15|max:1440',
            'total_amount'              => 'nullable|numeric|min:0',
            'notes'                     => 'nullable|string|max:1000',
        ]);

        $companyId = $this->currentCompanyId();
        if (!$companyId) {
            return back()->withErrors(['general' => 'El usuario master no puede crear reservas sin contexto de empresa.']);
        }

        try {
            $booking = $this->bookingService->createBooking($data, $companyId);
        } catch (\InvalidArgumentException $e) {
            return back()->withInput()->withErrors(['starts_at' => $e->getMessage()]);
        }

        return redirect()
            ->route('rentals.bookings.show', $booking)
            ->with('ok', 'Reserva creada correctamente.');
    }

    /**
     * Detalle de una reserva.
     */
    public function show(Booking $booking)
    {
        $this->authorize('view', $booking);
        $booking->load(['space', 'client', 'durationOption', 'paymentMethods']);

        return view('rentals.bookings.show', compact('booking'));
    }

    /**
     * Formulario de edición.
     */
    public function edit(Booking $booking)
    {
        $this->authorize('update', $booking);

        $companyId = $this->currentCompanyId() ?? $booking->company_id;

        $spaces = RentalSpace::with('activeDurationOptions')
            ->where('company_id', $companyId)
            ->active()
            ->orderBy('name')
            ->get();

        $clients = Client::where('user_id', $companyId)
            ->orderBy('name')
            ->get(['id', 'name', 'phone']);

        $paymentMethods = PaymentMethod::availableForUser(Auth::user())->get();

        return view('rentals.bookings.edit', compact('booking', 'spaces', 'clients', 'paymentMethods'));
    }

    /**
     * Actualiza una reserva existente.
     */
    public function update(Request $request, Booking $booking)
    {
        $this->authorize('update', $booking);

        if (in_array($booking->status, ['finished', 'cancelled'])) {
            return back()->withErrors(['general' => 'No se puede editar una reserva finalizada o cancelada.']);
        }

        $data = $request->validate([
            'rental_space_id'           => 'required|exists:rental_spaces,id',
            'rental_duration_option_id' => 'nullable|exists:rental_duration_options,id',
            'client_id'                 => 'nullable|exists:clients,id',
            'client_name'               => 'nullable|string|max:150',
            'client_phone'              => 'nullable|string|max:50',
            'starts_at'                 => 'required|date',
            'duration_minutes'          => 'required|integer|min:15|max:1440',
            'total_amount'              => 'nullable|numeric|min:0',
            'notes'                     => 'nullable|string|max:1000',
        ]);

        try {
            $this->bookingService->updateBooking($booking, $data, Auth::user());
        } catch (\InvalidArgumentException $e) {
            return back()->withInput()->withErrors(['starts_at' => $e->getMessage()]);
        }

        return redirect()
            ->route('rentals.bookings.show', $booking)
            ->with('ok', 'Reserva actualizada.');
    }

    /**
     * Confirma una reserva (pending → confirmed + Google Calendar).
     */
    public function confirm(Booking $booking)
    {
        $this->authorize('update', $booking);

        if ($booking->status !== 'pending') {
            return back()->withErrors(['general' => 'Solo se pueden confirmar reservas pendientes.']);
        }

        $this->bookingService->confirmBooking($booking, Auth::user());

        return back()->with('ok', 'Reserva confirmada.');
    }

    /**
     * Cancela una reserva y elimina el evento de Google Calendar.
     */
    public function cancel(Booking $booking)
    {
        $this->authorize('update', $booking);

        if (in_array($booking->status, ['finished', 'cancelled'])) {
            return back()->withErrors(['general' => 'Esta reserva ya está finalizada o cancelada.']);
        }

        $this->bookingService->cancelBooking($booking, Auth::user());

        return back()->with('ok', 'Reserva cancelada.');
    }

    /**
     * Elimina (soft delete) una reserva.
     */
    public function destroy(Booking $booking)
    {
        $this->authorize('delete', $booking);
        $booking->delete();

        return redirect()
            ->route('rentals.bookings.index')
            ->with('ok', 'Reserva eliminada.');
    }

    private function currentCompanyId(): ?int
    {
        $user = Auth::user();

        if ($user && method_exists($user, 'isMaster') && $user->isMaster()) {
            return null;
        }

        if ($user && $user->isCompany()) {
            return (int) $user->id;
        }

        if ($user && $user->parent_id) {
            return (int) $user->parent_id;
        }

        return (int) Auth::id();
    }
}
