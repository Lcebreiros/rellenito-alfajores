<?php

namespace App\Mail;

use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class TrialApprovedMail extends Mailable implements ShouldQueue
{
    use Queueable, SerializesModels;

    public function __construct(
        public User $user,
        public ?string $temporaryPassword = null
    ) {}

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: '¡Tu cuenta en ' . config('app.name') . ' está activa!',
        );
    }

    public function content(): Content
    {
        return new Content(
            markdown: 'emails.trial-approved',
            with: [
                'user'              => $this->user,
                'temporaryPassword' => $this->temporaryPassword,
                'loginUrl'          => route('login'),
            ],
        );
    }
}
