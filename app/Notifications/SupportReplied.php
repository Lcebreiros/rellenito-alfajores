<?php

namespace App\Notifications;

use App\Models\SupportMessage;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class SupportReplied extends Notification
{
    use Queueable;

    public function __construct(public SupportMessage $message)
    {
        //
    }

    public function via(object $notifiable): array
    {
        return ['database', 'mail'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        $ticket = $this->message->ticket;
        $url = route('support.show', $ticket);
        $author = $this->message->user;

        return (new MailMessage)
            ->subject('Nueva respuesta en tu reclamo #' . $ticket->id)
            ->greeting('Hola ' . ($notifiable->name ?? ''))
            ->line('Tienes una nueva respuesta en tu reclamo de soporte.')
            ->line('**Asunto:** ' . ($ticket->subject ?: 'Sin asunto'))
            ->line('**Tipo:** ' . ucfirst($ticket->type ?? 'N/A'))
            ->line('**Estado actual:** ' . ucfirst(str_replace('_', ' ', $ticket->status ?? 'nuevo')))
            ->line('---')
            ->line('**Mensaje de ' . ($author->name ?? 'Soporte') . ':**')
            ->line('"' . str($this->message->body)->limit(200) . '"')
            ->action('Ver conversación completa', $url)
            ->line('Puedes responder directamente desde la plataforma.')
            ->salutation('Saludos, El equipo de Rellenito');
    }

    public function toDatabase(object $notifiable): array
    {
        $ticket = $this->message->ticket;
        return [
            'type'       => 'support_replied',
            'ticket_id'  => $ticket->id,
            'message_id' => $this->message->id,
            'status'     => $ticket->status,
            'subject'    => $ticket->subject,
            'url'        => route('support.show', $ticket),
        ];
    }
}
