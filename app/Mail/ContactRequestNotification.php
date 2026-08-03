<?php

declare(strict_types=1);

namespace App\Mail;

use App\Models\ContactRequest;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

final class ContactRequestNotification extends Mailable implements ShouldQueue
{
    use Queueable;
    use SerializesModels;

    public int $tries = 4;

    /** @var list<int> */
    public array $backoff = [60, 300, 900];

    public function __construct(public readonly ContactRequest $contactRequest) {}

    public function envelope(): Envelope
    {
        return new Envelope(subject: 'Yeni iletişim talebi · '.$this->contactRequest->public_id);
    }

    public function content(): Content
    {
        return new Content(view: 'mail.contact-request-notification', text: 'mail.contact-request-notification-text');
    }
}
