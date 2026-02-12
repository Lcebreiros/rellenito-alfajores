<?php

namespace App\Http\Controllers\Api;

use App\Events\MessageSent;
use App\Events\NewNotification;
use App\Http\Controllers\Controller;
use App\Models\SupportMessage;
use App\Models\SupportTicket;
use App\Models\User;
use App\Models\UserNotification;
use App\Notifications\SupportStatusChanged;
use Illuminate\Http\Request;

class SupportController extends Controller
{
    /**
     * Listar tickets del usuario autenticado (o todos si es master).
     */
    public function index(Request $request)
    {
        $request->validate([
            'status' => ['nullable', 'in:nuevo,en_proceso,solucionado'],
            'type' => ['nullable', 'in:consulta,problema,sugerencia'],
            'per_page' => ['nullable', 'integer', 'min:1', 'max:100'],
        ]);

        $user = $request->user();
        $perPage = min((int) $request->input('per_page', 15), 100);

        $query = SupportTicket::with('user:id,name')
            ->when(!$user->isMaster(), fn($q) => $q->where('user_id', $user->id))
            ->when($request->filled('status'), fn($q) => $q->where('status', $request->status))
            ->when($request->filled('type'), fn($q) => $q->where('type', $request->type))
            ->orderBy('status')
            ->orderByDesc('updated_at');

        $tickets = $query->paginate($perPage);

        $data = collect($tickets->items())->map(fn($t) => $this->formatTicket($t))->values();

        return response()->json([
            'success' => true,
            'data' => $data,
            'meta' => [
                'current_page' => $tickets->currentPage(),
                'last_page' => $tickets->lastPage(),
                'per_page' => $tickets->perPage(),
                'total' => $tickets->total(),
            ],
        ]);
    }

    /**
     * Crear ticket y primer mensaje.
     */
    public function store(Request $request)
    {
        $data = $request->validate([
            'subject' => ['nullable', 'string', 'max:200'],
            'message' => ['required', 'string', 'max:5000'],
            'type' => ['required', 'in:problema,sugerencia,consulta'],
        ]);

        $user = $request->user();

        $ticket = SupportTicket::create([
            'user_id' => $user->id,
            'subject' => $data['subject'] ?? null,
            'type' => $data['type'],
            'status' => 'nuevo',
        ]);

        $message = SupportMessage::create([
            'support_chat_id' => $ticket->id,
            'user_id' => $user->id,
            'message' => $data['message'],
            'is_read' => false,
        ]);

        broadcast(new MessageSent($message))->toOthers();

        if (!$user->isMaster()) {
            $masters = User::where('hierarchy_level', User::HIERARCHY_MASTER)->get();
            foreach ($masters as $master) {
                $notification = UserNotification::create([
                    'user_id' => $master->id,
                    'type' => 'support',
                    'title' => 'Nuevo ticket de soporte',
                    'message' => $data['subject'] ?? 'Sin asunto',
                    'data' => [
                        'ticket_id' => $ticket->id,
                        'url' => route('support.show', $ticket),
                    ],
                ]);

                broadcast(new NewNotification($notification))->toOthers();
            }
        }

        $ticket->load(['user:id,name', 'messages.user:id,name']);

        return response()->json([
            'success' => true,
            'message' => 'Ticket creado correctamente',
            'data' => $this->formatTicket($ticket, true),
        ], 201);
    }

    /**
     * Ver ticket + mensajes.
     */
    public function show(Request $request, SupportTicket $ticket)
    {
        $user = $request->user();
        if (!$user->isMaster() && $ticket->user_id !== $user->id) {
            return response()->json([
                'success' => false,
                'message' => 'No tienes permiso para ver este ticket',
            ], 403);
        }

        $ticket->load(['user:id,name', 'messages.user:id,name']);

        // Marcar mensajes de otros como leídos
        SupportMessage::where('support_chat_id', $ticket->id)
            ->where('user_id', '!=', $user->id)
            ->where('is_read', false)
            ->update(['is_read' => true]);

        // Marcar notificaciones de este ticket como leídas
        UserNotification::forUser($user->id)
            ->where('data->ticket_id', $ticket->id)
            ->unread()
            ->update(['is_read' => true, 'read_at' => now()]);

        $user->unreadNotifications()
            ->where('data->ticket_id', $ticket->id)
            ->update(['read_at' => now()]);

        return response()->json([
            'success' => true,
            'data' => $this->formatTicket($ticket, true),
        ]);
    }

    /**
     * Enviar mensaje en un ticket existente.
     */
    public function reply(Request $request, SupportTicket $ticket)
    {
        $user = $request->user();
        if (!$user->isMaster() && $ticket->user_id !== $user->id) {
            return response()->json([
                'success' => false,
                'message' => 'No tienes permiso para responder este ticket',
            ], 403);
        }

        if ($ticket->status === 'solucionado') {
            return response()->json([
                'success' => false,
                'message' => 'El ticket está solucionado y no acepta más mensajes.',
            ], 400);
        }

        $data = $request->validate([
            'message' => ['required', 'string', 'max:5000'],
        ]);

        $message = SupportMessage::create([
            'support_chat_id' => $ticket->id,
            'user_id' => $user->id,
            'message' => $data['message'],
            'is_read' => false,
        ]);

        broadcast(new MessageSent($message))->toOthers();

        if ($user->isMaster()) {
            if ($ticket->status === 'nuevo') {
                $ticket->status = 'en_proceso';
                $ticket->save();
            }

            if ($ticket->user) {
                $notification = UserNotification::create([
                    'user_id' => $ticket->user->id,
                    'type' => 'support',
                    'title' => 'Respuesta en tu ticket',
                    'message' => 'Han respondido tu ticket: ' . ($ticket->subject ?? 'Sin asunto'),
                    'data' => [
                        'ticket_id' => $ticket->id,
                        'url' => route('support.show', $ticket),
                    ],
                ]);

                broadcast(new NewNotification($notification))->toOthers();
            }
        } else {
            $masters = User::where('hierarchy_level', User::HIERARCHY_MASTER)->get();
            foreach ($masters as $master) {
                $notification = UserNotification::create([
                    'user_id' => $master->id,
                    'type' => 'support',
                    'title' => 'Nueva respuesta en ticket',
                    'message' => $ticket->subject ?? 'Sin asunto',
                    'data' => [
                        'ticket_id' => $ticket->id,
                        'url' => route('support.show', $ticket),
                    ],
                ]);

                broadcast(new NewNotification($notification))->toOthers();
            }
        }

        $message->load('user:id,name');

        return response()->json([
            'success' => true,
            'message' => 'Mensaje enviado correctamente',
            'data' => [
                'ticket' => $this->formatTicket($ticket->fresh(), false),
                'message' => $this->formatMessage($message),
            ],
        ]);
    }

    /**
     * Solo Master: actualizar estado de ticket.
     */
    public function updateStatus(Request $request, SupportTicket $ticket)
    {
        $user = $request->user();
        if (!$user->isMaster()) {
            return response()->json([
                'success' => false,
                'message' => 'Solo los usuarios master pueden actualizar estados',
            ], 403);
        }

        $data = $request->validate([
            'status' => ['required', 'in:nuevo,en_proceso,solucionado'],
        ]);

        $ticket->status = $data['status'];
        $ticket->save();

        $ticket->user?->notify(new SupportStatusChanged($ticket));

        if ($ticket->user) {
            $statusLabel = match ($ticket->status) {
                'nuevo' => 'Nuevo',
                'en_proceso' => 'En proceso',
                'solucionado' => 'Solucionado',
                default => ucfirst(str_replace('_', ' ', $ticket->status)),
            };

            $notification = UserNotification::create([
                'user_id' => $ticket->user->id,
                'type' => 'support',
                'title' => 'Estado de reclamo actualizado',
                'message' => 'Tu reclamo #' . $ticket->id . ' ahora está: ' . $statusLabel,
                'data' => [
                    'ticket_id' => $ticket->id,
                    'url' => route('support.show', $ticket),
                ],
            ]);

            broadcast(new NewNotification($notification))->toOthers();
        }

        return response()->json([
            'success' => true,
            'message' => 'Estado actualizado correctamente',
            'data' => $this->formatTicket($ticket->fresh()),
        ]);
    }

    private function formatTicket(SupportTicket $ticket, bool $withMessages = false): array
    {
        $base = [
            'id' => $ticket->id,
            'subject' => $ticket->subject ?? 'Sin asunto',
            'type' => $ticket->type,
            'status' => $ticket->status,
            'user' => $ticket->user ? [
                'id' => $ticket->user->id,
                'name' => $ticket->user->name,
            ] : null,
            'created_at' => optional($ticket->created_at)->toIso8601String(),
            'updated_at' => optional($ticket->updated_at)->toIso8601String(),
        ];

        if ($withMessages) {
            $base['messages'] = $ticket->messages->map(fn($m) => $this->formatMessage($m))->values();
        }

        return $base;
    }

    private function formatMessage(SupportMessage $message): array
    {
        return [
            'id' => $message->id,
            'message' => $message->message,
            'is_read' => (bool) $message->is_read,
            'user' => $message->user ? [
                'id' => $message->user->id,
                'name' => $message->user->name,
            ] : null,
            'created_at' => optional($message->created_at)->toIso8601String(),
        ];
    }
}
