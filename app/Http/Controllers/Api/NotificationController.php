<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\UserNotification;
use Illuminate\Http\Request;

class NotificationController extends Controller
{
    /**
     * Listar notificaciones del usuario autenticado.
     */
    public function index(Request $request)
    {
        $request->validate([
            'type' => ['nullable', 'string', 'max:100'],
            'status' => ['nullable', 'in:all,read,unread'],
            'per_page' => ['nullable', 'integer', 'min:1', 'max:100'],
        ]);

        $userId = $request->user()->id;
        $status = $request->input('status', 'all');
        $perPage = min((int) $request->input('per_page', 20), 100);

        $query = UserNotification::forUser($userId)
            ->when($request->filled('type'), fn($q) => $q->ofType($request->type))
            ->when($status === 'read', fn($q) => $q->where('is_read', true))
            ->when($status === 'unread', fn($q) => $q->unread())
            ->latest();

        $notifications = $query->paginate($perPage);
        $data = collect($notifications->items())
            ->map(fn(UserNotification $n) => $this->formatNotification($n))
            ->values();

        return response()->json([
            'success' => true,
            'data' => $data,
            'meta' => [
                'current_page' => $notifications->currentPage(),
                'last_page' => $notifications->lastPage(),
                'per_page' => $notifications->perPage(),
                'total' => $notifications->total(),
            ],
        ]);
    }

    /**
     * Contador de no leídas.
     */
    public function unreadCount(Request $request)
    {
        $count = UserNotification::forUser($request->user()->id)
            ->unread()
            ->count();

        return response()->json([
            'success' => true,
            'data' => ['unread' => $count],
        ]);
    }

    /**
     * Marcar una notificación como leída.
     */
    public function markAsRead(Request $request, UserNotification $notification)
    {
        if ($notification->user_id !== $request->user()->id) {
            return response()->json([
                'success' => false,
                'message' => 'No autorizado para esta notificación',
            ], 403);
        }

        if (!$notification->is_read) {
            $notification->markAsRead();
        }

        return response()->json([
            'success' => true,
            'data' => $this->formatNotification($notification->fresh()),
        ]);
    }

    /**
     * Marcar todas las notificaciones como leídas.
     */
    public function markAllAsRead(Request $request)
    {
        $userId = $request->user()->id;
        $updated = UserNotification::forUser($userId)
            ->unread()
            ->update([
                'is_read' => true,
                'read_at' => now(),
            ]);

        return response()->json([
            'success' => true,
            'data' => ['updated' => $updated],
        ]);
    }

    private function formatNotification(UserNotification $notification): array
    {
        return [
            'id' => $notification->id,
            'type' => $notification->type,
            'title' => $notification->title,
            'message' => $notification->message,
            'data' => $notification->data ?? [],
            'is_read' => (bool) $notification->is_read,
            'read_at' => optional($notification->read_at)->toIso8601String(),
            'created_at' => optional($notification->created_at)->toIso8601String(),
        ];
    }
}
