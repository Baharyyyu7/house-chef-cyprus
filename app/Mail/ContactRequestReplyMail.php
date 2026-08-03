<?php

declare(strict_types=1);

namespace App\Mail;

use App\Models\ContactRequest;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

final class ContactRequestReplyMail extends Mailable
{
    use Queueable;
    use SerializesModels;

    public function __construct(public readonly ContactRequest $contactRequest, public readonly string $body) {}

    public function envelope(): Envelope
    {
        return new Envelope(subject: 'Talebinize yanıt · '.$this->contactRequest->public_id);
    }

    public function content(): Content
    {
        return new Content(view: 'mail.contact-request-reply');
    }
}
