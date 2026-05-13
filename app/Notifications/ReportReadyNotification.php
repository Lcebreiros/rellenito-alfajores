<?php

namespace App\Notifications;

use App\Models\GeneratedReport;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class ReportReadyNotification extends Notification
{
    use Queueable;

    public function __construct(private GeneratedReport $report) {}

    public function via(object $notifiable): array
    {
        $channels = ['database'];

        // Email solo si el usuario tiene email_delivery activado en su config
        $config = $notifiable->reportConfiguration;
        if ($config && $config->email_delivery) {
            $channels[] = 'mail';
        }

        return $channels;
    }

    public function toDatabase(object $notifiable): array
    {
        return [
            'type'         => 'report_ready',
            'report_id'    => $this->report->id,
            'frequency'    => $this->report->periodLabel(),
            'period_start' => $this->report->period_start->format('d/m/Y'),
            'period_end'   => $this->report->period_end->format('d/m/Y'),
            'message'      => 'Tu reporte ' . strtolower($this->report->periodLabel()) . ' está listo para descargar.',
            'url'          => '/nexum',
            'icon'         => 'document-chart-bar',
            'color'        => 'violet',
        ];
    }

    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject('📊 Tu reporte Nexum está listo — ' . $this->report->periodLabel())
            ->greeting('¡Hola ' . ($notifiable->name ?? 'usuario') . '!')
            ->line('Tu reporte ' . strtolower($this->report->periodLabel()) . ' de Nexum fue generado exitosamente.')
            ->line('**Período:** ' . $this->report->period_start->format('d/m/Y') . ' al ' . $this->report->period_end->format('d/m/Y'))
            ->line('**Tamaño:** ' . $this->report->fileSizeFormatted())
            ->action('Descargar reporte', url('/nexum'))
            ->line('Podés descargarlo en cualquier momento desde tu panel de Nexum.')
            ->salutation('— Nexum · Helipso');
    }

    public function toArray(object $notifiable): array
    {
        return $this->toDatabase($notifiable);
    }
}
