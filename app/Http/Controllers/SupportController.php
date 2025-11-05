<?php

namespace App\Http\Controllers;

use App\Models\SupportTicket;
use App\Models\SupportMessage;
use Illuminate\Http\Request;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;
use App\Notifications\SupportReplied;
use App\Notifications\SupportStatusChanged;
use App\Models\User;

class SupportController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    public function index(Request $request): View
    {
        $user = $request->user();
        $status = $request->get('status');
        $type = $request->get('type');

        $query = SupportTicket::with('user')
            ->when(!$user->isMaster(), fn($q) => $q->where('user_id', $user->id))
            ->when($status, fn($q) => $q->where('status', $status))
            ->when($type, fn($q) => $q->where('type', $type))
            ->orderBy('status')
            ->orderByDesc('updated_at');

        $tickets = $query->paginate(20)->withQueryString();

        return view('support.index', compact('tickets','status','type'));
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'subject' => ['nullable','string','max:200'],
            'message' => ['required','string','max:5000'],
            'type'    => ['required','in:problema,sugerencia,consulta'],
        ]);

        $ticket = SupportTicket::create([
            'user_id' => $request->user()->id,
            'subject' => $data['subject'] ?? null,
            'type'    => $data['type'],
            'status'  => 'nuevo',
        ]);

        $message = SupportMessage::create([
            'support_chat_id' => $ticket->id,
            'user_id'         => $request->user()->id,
            'message'         => $data['message'],
        ]);

        // Notificar a Masters sobre nuevo reclamo
        if (!$request->user()->isMaster()) {
            $masters = User::where('hierarchy_level', User::HIERARCHY_MASTER)->get();
            foreach ($masters as $m) {
                // Crear notificación en user_notifications
                $notification = \App\Models\UserNotification::create([
                    'user_id' => $m->id,
                    'type' => 'support',
                    'title' => 'Nuevo ticket de soporte',
                    'message' => $data['subject'] ?? 'Sin asunto',
                    'data' => [
                        'ticket_id' => $ticket->id,
                        'url' => route('support.show', $ticket),
                    ],
                ]);

                // Disparar evento de Pusher
                broadcast(new \App\Events\NewNotification($notification))->toOthers();
            }
        }

        return redirect()->route('support.show', $ticket)->with('ok', 'Reclamo creado.');
    }

    public function show(SupportTicket $ticket, Request $request): View
    {
        $user = $request->user();
        abort_unless($user->isMaster() || $ticket->user_id === $user->id, 403);
        $ticket->load(['user','messages.user']);

        // Marcar notificaciones de este ticket como leídas para el usuario actual
        $user->unreadNotifications()
            ->where('data->ticket_id', $ticket->id)
            ->update(['read_at' => now()]);
        return view('support.show', compact('ticket'));
    }

    public function reply(SupportTicket $ticket, Request $request): RedirectResponse
    {
        $user = $request->user();
        abort_unless($user->isMaster() || $ticket->user_id === $user->id, 403);

        $data = $request->validate(['message' => ['required','string','max:5000']]);

        $message = SupportMessage::create([
            'support_chat_id' => $ticket->id,
            'user_id'         => $user->id,
            'message'         => $data['message'],
        ]);

        // Notificaciones: si responde master, avisar al autor; si responde autor, avisar a masters
        if ($user->isMaster()) {
            if ($ticket->status === 'nuevo') {
                $ticket->status = 'en_proceso';
                $ticket->save();
            }

            // Notificar al autor del ticket
            if ($ticket->user) {
                $notification = \App\Models\UserNotification::create([
                    'user_id' => $ticket->user->id,
                    'type' => 'support',
                    'title' => 'Respuesta en tu ticket',
                    'message' => 'Han respondido tu ticket: ' . ($ticket->subject ?? 'Sin asunto'),
                    'data' => [
                        'ticket_id' => $ticket->id,
                        'url' => route('support.show', $ticket),
                    ],
                ]);

                broadcast(new \App\Events\NewNotification($notification))->toOthers();
            }
        } else {
            // Notificar a todos los masters
            $masters = User::where('hierarchy_level', User::HIERARCHY_MASTER)->get();
            foreach ($masters as $m) {
                $notification = \App\Models\UserNotification::create([
                    'user_id' => $m->id,
                    'type' => 'support',
                    'title' => 'Nueva respuesta en ticket',
                    'message' => $ticket->subject ?? 'Sin asunto',
                    'data' => [
                        'ticket_id' => $ticket->id,
                        'url' => route('support.show', $ticket),
                    ],
                ]);

                broadcast(new \App\Events\NewNotification($notification))->toOthers();
            }
        }

        // Si master responde y ticket está nuevo, pasarlo a en_proceso

        return back()->with('ok', 'Mensaje enviado.');
    }

    public function updateStatus(SupportTicket $ticket, Request $request): RedirectResponse
    {
        $user = $request->user();
        abort_unless($user->isMaster(), 403);
        $data = $request->validate(['status' => ['required','in:nuevo,en_proceso,solucionado']]);
        $ticket->status = $data['status'];
        $ticket->save();

        // Notificar al autor del ticket
        $ticket->user?->notify(new SupportStatusChanged($ticket));
        return back()->with('ok', 'Estado actualizado.');
    }
}
