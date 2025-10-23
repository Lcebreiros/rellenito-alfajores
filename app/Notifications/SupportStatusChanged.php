<?php

namespace App\Notifications;

use App\Models\SupportTicket;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class SupportStatusChanged extends Notification
{
    use Queueable;

    public function __construct(public SupportTicket $ticket)
    {
        //
    }

    public function via(object $notifiable): array
    {
        return ['database', 'mail'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        $url = route('support.show', $this->ticket);
        $status = str_replace('_',' ', $this->ticket->status);
        return (new MailMessage)
            ->subject('Actualización de estado del reclamo #' . $this->ticket->id)
            ->greeting('Hola ' . ($notifiable->name ?? ''))
            ->line('El estado de tu reclamo cambió a: ' . ucfirst($status))
            ->action('Ver reclamo', $url)
            ->line('Gracias por usar Rellenito.');
    }

    public function toDatabase(object $notifiable): array
    {
        return [
            'type'      => 'support_status_changed',
            'ticket_id' => $this->ticket->id,
            'status'    => $this->ticket->status,
            'subject'   => $this->ticket->subject,
            'url'       => route('support.show', $this->ticket),
        ];
    }
}
