<?php

declare(strict_types=1);

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;

final class OperationsDigestMail extends Mailable
{
    use Queueable;

    /** @param array{failed_tasks: array<string, array<string, mixed>>, stale_tasks: array<string, array<string, mixed>>, errors: list<array{key: string, message: string, count: int, last_seen_at: string}>} $ozet */
    public function __construct(public readonly array $ozet) {}

    public function envelope(): Envelope
    {
        $sayi = count($this->ozet['failed_tasks']) + count($this->ozet['stale_tasks']);

        return new Envelope(subject: "Operasyon özeti: {$sayi} görev sorunlu, ".count($this->ozet['errors']).' hata türü - House Chef Cyprus');
    }

    public function content(): Content
    {
        return new Content(text: 'mail.internal.operations-digest-text');
    }
}
