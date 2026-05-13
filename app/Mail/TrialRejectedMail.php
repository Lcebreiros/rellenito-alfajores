<?php

namespace App\Mail;

use App\Models\TrialRequest;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class TrialRejectedMail extends Mailable implements ShouldQueue
{
    use Queueable, SerializesModels;

    public function __construct(
        public TrialRequest $request
    ) {}

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Actualización sobre tu solicitud en ' . config('app.name'),
        );
    }

    public function content(): Content
    {
        return new Content(
            markdown: 'emails.trial-rejected',
            with: [
                'trialRequest' => $this->request,
            ],
        );
    }
}
