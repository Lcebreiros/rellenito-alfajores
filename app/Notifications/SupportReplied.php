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
        return (new MailMessage)
            ->subject('Nueva respuesta en tu reclamo #' . $ticket->id)
            ->greeting('Hola ' . ($notifiable->name ?? ''))
            ->line('Tienes una nueva respuesta en el reclamo: ' . ($ticket->subject ?: 'Sin asunto'))
            ->line('Mensaje: "' . str($this->message->body)->limit(140) . '"')
            ->action('Ver conversación', $url)
            ->line('Gracias por usar Rellenito.');
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
