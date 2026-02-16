<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class EmployeeInvitationMail extends Mailable implements ShouldQueue
{
    use Queueable, SerializesModels;

    public function __construct(
        public string $acceptUrl,
        public string $tenantName,
    ) {}

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: "You've been invited to join {$this->tenantName}",
        );
    }

    public function content(): Content
    {
        return new Content(
            markdown: 'mail.employee-invitation',
        );
    }

    public function retryUntil(): \DateTime
    {
        return now()->addMinutes(5);
    }

    public int $tries = 3;
}
