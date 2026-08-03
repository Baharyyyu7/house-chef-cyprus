<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\MemberRegistrationNotification;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

final class MailOperationsService
{
    /** @return array<string, mixed> */
    public function snapshot(): array
    {
        $smtpConfigured = config('mail.default') === 'smtp'
            && filled(config('mail.mailers.smtp.host'))
            && filled(config('mail.mailers.smtp.username'))
            && filled(config('mail.from.address'));

        $notifications = Schema::hasTable('member_registration_notifications')
            ? MemberRegistrationNotification::query()
                ->selectRaw('status, COUNT(*) as aggregate')
                ->groupBy('status')
                ->pluck('aggregate', 'status')
                ->map(fn (mixed $value): int => (int) $value)
                ->all()
            : [];

        $failedJobs = Schema::hasTable('failed_jobs')
            ? (int) DB::table('failed_jobs')->where('payload', 'like', '%Mail%')->count()
            : 0;

        return [
            'status' => $smtpConfigured ? 'READY_NOT_LIVE_TESTED' : 'NOT_CONFIGURED',
            'smtp' => [
                'configured' => $smtpConfigured,
                'mailer' => (string) config('mail.default'),
                'host_present' => filled(config('mail.mailers.smtp.host')),
                'username_present' => filled(config('mail.mailers.smtp.username')),
                'from_present' => filled(config('mail.from.address')),
                'secret_visible' => false,
            ],
            'dns' => [
                'spf' => (bool) config('mail.dns.spf_verified'),
                'dkim' => (bool) config('mail.dns.dkim_verified'),
                'dmarc' => (bool) config('mail.dns.dmarc_verified'),
            ],
            'queue' => [
                'connection' => (string) config('queue.default'),
                'async' => config('queue.default') !== 'sync',
                'failed_mail_jobs' => $failedJobs,
            ],
            'registration_notifications' => [
                'pending' => (int) ($notifications['pending'] ?? 0),
                'sent' => (int) ($notifications['sent'] ?? 0),
                'failed' => (int) ($notifications['failed'] ?? 0),
            ],
            'templates' => [
                ['name' => 'Yeni üye yönetici bildirimi', 'html' => true, 'text' => true, 'transactional' => true],
                ['name' => 'İletişim talebi bildirimi', 'html' => true, 'text' => true, 'transactional' => true],
                ['name' => 'İletişim talebi yanıtı', 'html' => true, 'text' => true, 'transactional' => true],
                ['name' => 'E-posta doğrulama', 'html' => true, 'text' => true, 'transactional' => true],
                ['name' => 'Parola sıfırlama', 'html' => true, 'text' => true, 'transactional' => true],
            ],
            'bounce' => ['status' => 'NOT_AVAILABLE', 'reason' => 'Hostinger Mail connector bu oturumda callable mailbox/bounce API sunmadı.'],
            'marketing' => ['status' => 'DISABLED', 'reason' => 'Newsletter gönderimi açık rıza ve unsubscribe altyapısı tamamlanmadan etkinleştirilmez.'],
        ];
    }
}
