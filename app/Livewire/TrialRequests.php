<?php

namespace App\Livewire;

use App\Models\TrialRequest;
use App\Models\User;
use Livewire\Component;
use Livewire\WithPagination;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class TrialRequests extends Component
{
    use WithPagination;

    public $filter = 'pending'; // pending, approved, rejected, all
    public $rejectionNotes = [];

    public function approve($requestId)
    {
        $request = TrialRequest::findOrFail($requestId);

        if ($request->status !== 'pending') {
            session()->flash('error', 'Esta solicitud ya fue procesada.');
            return;
        }

        // Crear el usuario
        $temporaryPassword = Str::random(12);

        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($temporaryPassword),
            'subscription_level' => $request->plan,
            'is_active' => true,
            'hierarchy_level' => User::HIERARCHY_COMPANY,
        ]);

        // Actualizar la solicitud
        $request->update([
            'status' => 'approved',
            'approved_by' => auth()->id(),
            'approved_at' => now(),
            'user_id' => $user->id,
        ]);

        // TODO: Enviar email al usuario con sus credenciales
        // Mail::to($user->email)->send(new TrialApprovedMail($user, $temporaryPassword));

        session()->flash('success', "Solicitud aprobada. Usuario creado: {$user->email}");
    }

    public function reject($requestId)
    {
        $request = TrialRequest::findOrFail($requestId);

        if ($request->status !== 'pending') {
            session()->flash('error', 'Esta solicitud ya fue procesada.');
            return;
        }

        $notes = $this->rejectionNotes[$requestId] ?? null;

        $request->update([
            'status' => 'rejected',
            'approved_by' => auth()->id(),
            'rejected_at' => now(),
            'notes' => $notes,
        ]);

        // TODO: Enviar email de rechazo
        // Mail::to($request->email)->send(new TrialRejectedMail($request));

        session()->flash('success', 'Solicitud rechazada.');
    }

    public function setFilter($filter)
    {
        $this->filter = $filter;
        $this->resetPage();
    }

    public function render()
    {
        $query = TrialRequest::query()->with(['approvedBy', 'user']);

        if ($this->filter === 'pending') {
            $query->pending();
        } elseif ($this->filter === 'approved') {
            $query->approved();
        } elseif ($this->filter === 'rejected') {
            $query->rejected();
        }

        $requests = $query->latest()->paginate(10);

        $stats = [
            'pending' => TrialRequest::pending()->count(),
            'approved' => TrialRequest::approved()->count(),
            'rejected' => TrialRequest::rejected()->count(),
        ];

        return view('livewire.trial-requests', [
            'requests' => $requests,
            'stats' => $stats,
        ]);
    }
}
