<?php

namespace App\Observers;

use App\Models\Order;
use App\Jobs\SyncOrderToGoogleCalendar;
use App\Services\GoogleCalendarService;
use App\Enums\OrderStatus;
use Illuminate\Support\Facades\Log;

class OrderObserver
{
    protected GoogleCalendarService $googleCalendar;

    public function __construct(GoogleCalendarService $googleCalendar)
    {
        $this->googleCalendar = $googleCalendar;
    }

    /**
     * Handle the Order "created" event.
     */
    public function created(Order $order): void
    {
        // Solo sincronizar si el pedido está agendado
        if (!$order->is_scheduled || !$order->scheduled_for || !$order->user) {
            return;
        }

        // Despachar job asíncrono
        SyncOrderToGoogleCalendar::dispatch($order, 'sync');
    }

    /**
     * Handle the Order "updated" event.
     */
    public function updated(Order $order): void
    {
        // Si el pedido fue cancelado y tenía un evento en Google Calendar, eliminarlo
        if ($order->status === OrderStatus::CANCELED && $order->google_calendar_event_id) {
            SyncOrderToGoogleCalendar::dispatch($order, 'delete');
            return;
        }

        // Si el pedido no está agendado pero tenía un evento en Google Calendar, eliminarlo
        if (!$order->is_scheduled && $order->google_calendar_event_id) {
            SyncOrderToGoogleCalendar::dispatch($order, 'delete');
            return;
        }

        // Si está agendado, sincronizar
        if ($order->is_scheduled && $order->scheduled_for && $order->user) {
            SyncOrderToGoogleCalendar::dispatch($order, 'sync');
        }
    }

    /**
     * Handle the Order "deleted" event.
     */
    public function deleted(Order $order): void
    {
        if ($order->google_calendar_event_id && $order->user) {
            SyncOrderToGoogleCalendar::dispatch($order, 'delete');
        }
    }

    /**
     * Handle the Order "restored" event.
     */
    public function restored(Order $order): void
    {
        // Si se restaura un pedido agendado, volver a crear el evento
        if ($order->is_scheduled && $order->scheduled_for && $order->user) {
            SyncOrderToGoogleCalendar::dispatch($order, 'sync');
        }
    }

    /**
     * Handle the Order "force deleted" event.
     */
    public function forceDeleted(Order $order): void
    {
        if ($order->google_calendar_event_id && $order->user) {
            SyncOrderToGoogleCalendar::dispatch($order, 'delete');
        }
    }

    /**
     * Sync order to Google Calendar
     */
    protected function syncToGoogleCalendar(Order $order): void
    {
        try {
            $this->googleCalendar->forUser($order->user);

            // Verificar si está conectado y si la sincronización está habilitada
            if (!$this->googleCalendar->isConnected() || !$order->user->google_calendar_sync_enabled) {
                return;
            }

            // Preparar datos del evento
            $summary = 'Pedido #' . ($order->order_number ?? $order->id);
            if ($order->client) {
                $summary .= ' - ' . $order->client->name;
            }

            $description = "Total: $" . number_format($order->total, 2);
            if ($order->notes) {
                $description .= "\n\nNotas: " . $order->notes;
            }

            $location = null;
            if ($order->client && $order->client->address) {
                $location = $order->client->address;
            }

            // Si ya existe un evento, actualizarlo
            if ($order->google_calendar_event_id) {
                $event = $this->googleCalendar->updateEvent(
                    $order->google_calendar_event_id,
                    $summary,
                    $order->scheduled_for,
                    $order->scheduled_for->copy()->addHour(),
                    $description,
                    $location
                );
            } else {
                // Crear nuevo evento
                $event = $this->googleCalendar->createEvent(
                    $summary,
                    $order->scheduled_for,
                    $order->scheduled_for->copy()->addHour(),
                    $description,
                    $location,
                    'order'
                );

                // Guardar el ID del evento en el pedido
                if ($event) {
                    $order->google_calendar_event_id = $event->getId();
                    $order->saveQuietly(); // Usar saveQuietly para evitar loop infinito
                }
            }
        } catch (\Exception $e) {
            Log::error('Error syncing order to Google Calendar', [
                'order_id' => $order->id,
                'error' => $e->getMessage(),
            ]);
        }
    }

    /**
     * Delete order from Google Calendar
     */
    protected function deleteFromGoogleCalendar(Order $order): void
    {
        if (!$order->google_calendar_event_id || !$order->user) {
            return;
        }

        try {
            $this->googleCalendar
                ->forUser($order->user)
                ->deleteEvent($order->google_calendar_event_id);

            $order->google_calendar_event_id = null;
            $order->saveQuietly();
        } catch (\Exception $e) {
            Log::error('Error deleting order from Google Calendar', [
                'order_id' => $order->id,
                'event_id' => $order->google_calendar_event_id,
                'error' => $e->getMessage(),
            ]);
        }
    }
}
