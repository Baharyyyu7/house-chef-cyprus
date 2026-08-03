<?php

declare(strict_types=1);

namespace App\Listeners;

use App\Events\MemberRegistered;
use App\Mail\NewMemberRegisteredMail;
use App\Models\MemberRegistrationNotification;
use Illuminate\Contracts\Queue\ShouldQueueAfterCommit;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Throwable;

final class SendNewMemberAdminNotification implements ShouldQueueAfterCommit
{
    public int $tries = 4;

    /** @var list<int> */
    public array $backoff = [60, 300, 900];

    public function handle(MemberRegistered $event): void
    {
        $recipient = (string) config('mail.registration_admin_recipient');
        if ($recipient === '') {
            Log::warning('Member registration notification skipped.', ['code' => 'REGISTRATION_RECIPIENT_MISSING']);

            return;
        }

        $notification = MemberRegistrationNotification::query()->firstOrCreate(
            ['idempotency_key' => hash('sha256', 'member.registered|'.$event->member->public_id)],
            ['user_id' => $event->member->id, 'recipient' => $recipient, 'status' => 'pending'],
        );

        if ($notification->status === 'sent') {
            return;
        }

        $notification->forceFill([
            'status' => 'processing',
            'attempt_count' => $notification->attempt_count + 1,
            'failed_at' => null,
            'failure_code' => null,
        ])->save();

        try {
            Mail::to($recipient)->send(new NewMemberRegisteredMail($event->member));
            $notification->forceFill(['status' => 'sent', 'sent_at' => now()])->save();
        } catch (Throwable $exception) {
            $notification->forceFill([
                'status' => 'failed',
                'failed_at' => now(),
                'failure_code' => 'REGISTRATION_MAIL_DELIVERY_FAILED',
            ])->save();
            Log::error('Member registration notification failed.', [
                'code' => 'REGISTRATION_MAIL_DELIVERY_FAILED',
                'notification_public_id' => $notification->public_id,
                'member_public_id' => $event->member->public_id,
                'exception_class' => $exception::class,
            ]);

            throw $exception;
        }
    }
}
