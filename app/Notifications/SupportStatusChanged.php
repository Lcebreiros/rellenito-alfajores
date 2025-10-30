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
        $status = str_replace('_', ' ', $this->ticket->status);

        // Personalizar mensaje según el estado
        $statusMessages = [
            'nuevo' => 'Tu reclamo ha sido recibido y está siendo revisado.',
            'en proceso' => 'Estamos trabajando en tu reclamo.',
            'en_proceso' => 'Estamos trabajando en tu reclamo.',
            'solucionado' => '¡Tu reclamo ha sido solucionado! Revisa los detalles en la conversación.',
        ];

        $message = $statusMessages[$this->ticket->status] ?? 'El estado de tu reclamo ha sido actualizado.';

        $mail = (new MailMessage)
            ->subject('Actualización: Reclamo #' . $this->ticket->id . ' - ' . ucfirst($status))
            ->greeting('Hola ' . ($notifiable->name ?? ''))
            ->line('Tenemos novedades sobre tu reclamo de soporte.');

        if ($this->ticket->subject) {
            $mail->line('**Asunto:** ' . $this->ticket->subject);
        }

        $mail->line('**Tipo:** ' . ucfirst($this->ticket->type ?? 'N/A'))
            ->line('**Nuevo estado:** ' . ucfirst($status))
            ->line('---')
            ->line($message)
            ->action('Ver detalles del reclamo', $url);

        if ($this->ticket->status === 'solucionado') {
            $mail->line('Si consideras que el problema persiste, puedes responder en el mismo reclamo.');
        }

        return $mail->salutation('Saludos, El equipo de Rellenito');
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
