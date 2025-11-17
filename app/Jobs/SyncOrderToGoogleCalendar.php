<?php

namespace App\Jobs;

use App\Models\Order;
use App\Services\GoogleCalendarService;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\Log;

class SyncOrderToGoogleCalendar implements ShouldQueue
{
    use Queueable;

    /**
     * The number of times the job may be attempted.
     */
    public int $tries = 3;

    /**
     * The number of seconds to wait before retrying the job.
     */
    public int $backoff = 10;

    /**
     * Create a new job instance.
     */
    public function __construct(
        public Order $order,
        public string $action = 'sync' // 'sync' or 'delete'
    ) {
        //
    }

    /**
     * Execute the job.
     */
    public function handle(GoogleCalendarService $googleCalendar): void
    {
        try {
            // Recargar el modelo para asegurar datos frescos
            $this->order->refresh();

            if ($this->action === 'delete') {
                $this->deleteFromGoogleCalendar($googleCalendar);
                return;
            }

            // Acción 'sync'
            $this->syncToGoogleCalendar($googleCalendar);
        } catch (\Exception $e) {
            Log::error('Google Calendar sync job failed', [
                'order_id' => $this->order->id,
                'action' => $this->action,
                'error' => $e->getMessage(),
            ]);

            // Re-lanzar para reintentar
            throw $e;
        }
    }

    /**
     * Sync order to Google Calendar
     */
    protected function syncToGoogleCalendar(GoogleCalendarService $googleCalendar): void
    {
        // Validaciones
        if (!$this->order->user || !$this->order->is_scheduled || !$this->order->scheduled_for) {
            return;
        }

        $googleCalendar->forUser($this->order->user);

        // Verificar si está conectado y si la sincronización está habilitada
        if (!$googleCalendar->isConnected() || !$this->order->user->google_calendar_sync_enabled) {
            return;
        }

        // Preparar datos del evento
        $summary = 'Pedido #' . ($this->order->order_number ?? $this->order->id);
        if ($this->order->client) {
            $summary .= ' - ' . $this->order->client->name;
        }

        $description = "Total: $" . number_format($this->order->total, 2);
        if ($this->order->notes) {
            $description .= "\n\nNotas: " . $this->order->notes;
        }

        $location = null;
        if ($this->order->client && $this->order->client->address) {
            $location = $this->order->client->address;
        }

        // Si ya existe un evento, actualizarlo
        if ($this->order->google_calendar_event_id) {
            $event = $googleCalendar->updateEvent(
                $this->order->google_calendar_event_id,
                $summary,
                $this->order->scheduled_for,
                $this->order->scheduled_for->copy()->addHour(),
                $description,
                $location
            );
        } else {
            // Crear nuevo evento
            $event = $googleCalendar->createEvent(
                $summary,
                $this->order->scheduled_for,
                $this->order->scheduled_for->copy()->addHour(),
                $description,
                $location,
                'order'
            );

            // Guardar el ID del evento en el pedido
            if ($event) {
                $this->order->google_calendar_event_id = $event->getId();
                $this->order->saveQuietly();
            }
        }
    }

    /**
     * Delete order from Google Calendar
     */
    protected function deleteFromGoogleCalendar(GoogleCalendarService $googleCalendar): void
    {
        if (!$this->order->google_calendar_event_id || !$this->order->user) {
            return;
        }

        $googleCalendar
            ->forUser($this->order->user)
            ->deleteEvent($this->order->google_calendar_event_id);

        $this->order->google_calendar_event_id = null;
        $this->order->saveQuietly();
    }
}
