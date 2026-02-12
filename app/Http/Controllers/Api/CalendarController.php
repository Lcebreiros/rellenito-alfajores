<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\CalendarEventResource;
use App\Models\CalendarEvent;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class CalendarController extends Controller
{
    /**
     * Obtener resumen del calendario
     */
    public function summary(Request $request): JsonResponse
    {
        $userId = $request->user()->id;
        $now = Carbon::now();
        $startOfWeek = $now->copy()->startOfWeek();
        $endOfWeek = $now->copy()->endOfWeek();
        $startOfMonth = $now->copy()->startOfMonth();
        $endOfMonth = $now->copy()->endOfMonth();

        // Actualizar estados vencidos
        CalendarEvent::forUser($userId)
            ->pending()
            ->where(function ($q) use ($now) {
                $q->where('due_date', '<', $now)
                  ->orWhere(function ($q2) use ($now) {
                      $q2->whereNull('due_date')
                         ->where('event_date', '<', $now);
                  });
            })
            ->update(['status' => 'overdue']);

        $today = CalendarEvent::forUser($userId)
            ->whereDate('event_date', $now->toDateString())
            ->where('status', '!=', 'cancelled')
            ->count();

        $thisWeek = CalendarEvent::forUser($userId)
            ->whereBetween('event_date', [$startOfWeek, $endOfWeek])
            ->where('status', '!=', 'cancelled')
            ->count();

        $thisMonth = CalendarEvent::forUser($userId)
            ->whereBetween('event_date', [$startOfMonth, $endOfMonth])
            ->where('status', '!=', 'cancelled')
            ->count();

        $overdue = CalendarEvent::forUser($userId)
            ->where('status', 'overdue')
            ->count();

        $pending = CalendarEvent::forUser($userId)
            ->where('status', 'pending')
            ->count();

        $completedThisMonth = CalendarEvent::forUser($userId)
            ->where('status', 'completed')
            ->whereBetween('completed_at', [$startOfMonth, $endOfMonth])
            ->count();

        return response()->json([
            'data' => [
                'today' => $today,
                'thisWeek' => $thisWeek,
                'thisMonth' => $thisMonth,
                'overdue' => $overdue,
                'pending' => $pending,
                'completedThisMonth' => $completedThisMonth,
            ],
        ]);
    }

    /**
     * Obtener eventos del mes
     */
    public function monthEvents(Request $request, int $year, int $month): JsonResponse
    {
        $userId = $request->user()->id;

        $events = CalendarEvent::forUser($userId)
            ->forMonth($year, $month)
            ->where('status', '!=', 'cancelled')
            ->orderBy('event_date')
            ->get();

        return response()->json([
            'data' => CalendarEventResource::collection($events),
        ]);
    }

    /**
     * Obtener eventos en un rango de fechas
     */
    public function rangeEvents(Request $request): JsonResponse
    {
        $request->validate([
            'start_date' => 'required|date',
            'end_date' => 'required|date|after_or_equal:start_date',
        ]);

        $userId = $request->user()->id;

        $events = CalendarEvent::forUser($userId)
            ->forDateRange($request->start_date, $request->end_date)
            ->where('status', '!=', 'cancelled')
            ->orderBy('event_date')
            ->get();

        return response()->json([
            'data' => CalendarEventResource::collection($events),
        ]);
    }

    /**
     * Obtener próximos eventos
     */
    public function upcoming(Request $request): JsonResponse
    {
        $userId = $request->user()->id;
        $limit = $request->input('limit', 10);

        $events = CalendarEvent::forUser($userId)
            ->upcoming()
            ->limit($limit)
            ->get();

        return response()->json([
            'data' => CalendarEventResource::collection($events),
        ]);
    }

    /**
     * Obtener eventos vencidos
     */
    public function overdue(Request $request): JsonResponse
    {
        $userId = $request->user()->id;

        $events = CalendarEvent::forUser($userId)
            ->where('status', 'overdue')
            ->orderBy('event_date', 'desc')
            ->get();

        return response()->json([
            'data' => CalendarEventResource::collection($events),
        ]);
    }

    /**
     * Obtener eventos por tipo
     */
    public function byType(Request $request, string $type): JsonResponse
    {
        $userId = $request->user()->id;

        $events = CalendarEvent::forUser($userId)
            ->ofType($type)
            ->where('status', '!=', 'cancelled')
            ->orderBy('event_date', 'desc')
            ->get();

        return response()->json([
            'data' => CalendarEventResource::collection($events),
        ]);
    }

    /**
     * Crear nuevo evento
     */
    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'event_type' => ['required', Rule::in([
                'order_delivery', 'payment_deadline', 'tax_due',
                'supply_reorder', 'expense_payment', 'reminder', 'custom'
            ])],
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'color' => 'nullable|string|max:20',
            'event_date' => 'required|date',
            'due_date' => 'nullable|date',
            'reminder_date' => 'nullable|date',
            'related_type' => 'nullable|string|max:50',
            'related_id' => 'nullable|integer',
            'metadata' => 'nullable|array',
            'is_recurring' => 'nullable|boolean',
            'recurrence_pattern' => 'nullable|string|max:50',
        ]);

        $event = CalendarEvent::create([
            'user_id' => $request->user()->id,
            'organization_id' => $request->user()->organization_id ?? null,
            ...$validated,
        ]);

        return response()->json([
            'data' => new CalendarEventResource($event),
            'message' => 'Evento creado exitosamente',
        ], 201);
    }

    /**
     * Mostrar evento específico
     */
    public function show(Request $request, CalendarEvent $event): JsonResponse
    {
        if ($event->user_id !== $request->user()->id) {
            return response()->json(['message' => 'No autorizado'], 403);
        }

        return response()->json([
            'data' => new CalendarEventResource($event),
        ]);
    }

    /**
     * Actualizar evento
     */
    public function update(Request $request, CalendarEvent $event): JsonResponse
    {
        if ($event->user_id !== $request->user()->id) {
            return response()->json(['message' => 'No autorizado'], 403);
        }

        $validated = $request->validate([
            'title' => 'sometimes|string|max:255',
            'description' => 'nullable|string',
            'color' => 'nullable|string|max:20',
            'event_date' => 'sometimes|date',
            'due_date' => 'nullable|date',
            'reminder_date' => 'nullable|date',
            'status' => ['sometimes', Rule::in(['pending', 'completed', 'overdue', 'cancelled'])],
            'metadata' => 'nullable|array',
        ]);

        $event->update($validated);

        return response()->json([
            'data' => new CalendarEventResource($event->fresh()),
            'message' => 'Evento actualizado exitosamente',
        ]);
    }

    /**
     * Marcar evento como completado
     */
    public function complete(Request $request, CalendarEvent $event): JsonResponse
    {
        if ($event->user_id !== $request->user()->id) {
            return response()->json(['message' => 'No autorizado'], 403);
        }

        $event->markAsCompleted();

        return response()->json([
            'data' => new CalendarEventResource($event->fresh()),
            'message' => 'Evento marcado como completado',
        ]);
    }

    /**
     * Eliminar evento
     */
    public function destroy(Request $request, CalendarEvent $event): JsonResponse
    {
        if ($event->user_id !== $request->user()->id) {
            return response()->json(['message' => 'No autorizado'], 403);
        }

        $event->delete();

        return response()->json([
            'message' => 'Evento eliminado exitosamente',
        ]);
    }
}
