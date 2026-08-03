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
        $pendingMailJobs = Schema::hasTable('jobs')
            ? (int) DB::table('jobs')->where('payload', 'like', '%Mail%')->count()
            : 0;
        $lastSent = Schema::hasTable('member_registration_notifications')
            ? MemberRegistrationNotification::query()->whereNotNull('sent_at')->max('sent_at')
            : null;
        $lastFailed = Schema::hasTable('member_registration_notifications')
            ? MemberRegistrationNotification::query()->whereNotNull('failed_at')->max('failed_at')
            : null;
        $attempts = Schema::hasTable('member_registration_notifications')
            ? (int) MemberRegistrationNotification::query()->sum('attempt_count')
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
                'pending_mail_jobs' => $pendingMailJobs,
                'failed_mail_jobs' => $failedJobs,
            ],
            'registration_notifications' => [
                'pending' => (int) ($notifications['pending'] ?? 0),
                'sent' => (int) ($notifications['sent'] ?? 0),
                'failed' => (int) ($notifications['failed'] ?? 0),
                'attempts' => $attempts,
                'last_sent_at' => $lastSent,
                'last_failed_at' => $lastFailed,
                'duplicate_prevention' => 'IDEMPOTENT',
            ],
            'templates' => [
                ['key' => 'new-member', 'name' => 'Yeni üye yönetici bildirimi', 'html' => true, 'text' => true, 'transactional' => true],
                ['key' => 'contact-notification', 'name' => 'İletişim talebi bildirimi', 'html' => true, 'text' => true, 'transactional' => true],
                ['key' => 'contact-reply', 'name' => 'İletişim talebi yanıtı', 'html' => true, 'text' => true, 'transactional' => true],
                ['key' => 'verification', 'name' => 'E-posta doğrulama', 'html' => true, 'text' => true, 'transactional' => true],
                ['key' => 'password-reset', 'name' => 'Parola sıfırlama', 'html' => true, 'text' => true, 'transactional' => true],
            ],
            'sender' => [
                'name' => (string) config('mail.from.name'),
                'address' => $smtpConfigured ? (string) config('mail.from.address') : 'NOT_CONFIGURED',
            ],
            'bounce' => ['status' => 'NOT_AVAILABLE', 'reason' => 'Hostinger Mail connector bu oturumda callable mailbox/bounce API sunmadı.'],
            'marketing' => ['status' => 'DISABLED', 'reason' => 'Newsletter gönderimi açık rıza ve unsubscribe altyapısı tamamlanmadan etkinleştirilmez.'],
        ];
    }

    /** @return array<string, string>|null */
    public function preview(string $template): ?array
    {
        $previews = [
            'new-member' => ['category' => 'INTERNAL_OPERATIONAL', 'subject' => 'Yeni Üye Kaydı — House Chef Cyprus', 'preheader' => 'Yeni üyelik kaydı güvenli yönetim incelemesine hazır.', 'heading' => 'Yeni üye kaydı', 'body' => 'Önizleme yalnız sentetik alanlar gösterir. Gerçek kullanıcı verisi, şifre, token, IP veya cihaz bilgisi içermez.', 'action' => 'Üye profilini aç'],
            'contact-notification' => ['category' => 'INTERNAL_OPERATIONAL', 'subject' => 'Yeni iletişim talebi', 'preheader' => 'Yeni iletişim talebi yönetim panelinde bekliyor.', 'heading' => 'Yeni iletişim talebi', 'body' => 'Talep referansı ve güvenli yönetim bağlantısı gösterilir; hassas veri operasyon ekranına taşınmaz.', 'action' => 'Talebi güvenli panelde aç'],
            'contact-reply' => ['category' => 'TRANSACTIONAL', 'subject' => 'Talebinize yanıt', 'preheader' => 'House Chef Cyprus iletişim talebinize yanıt verdi.', 'heading' => 'Talebinize yanıt', 'body' => 'Yanıt gövdesi, talep referansı, iletişim ve gizlilik bağlantılarıyla sunulur.', 'action' => 'Yasal bilgilendirmeyi aç'],
            'verification' => ['category' => 'TRANSACTIONAL', 'subject' => 'E-posta adresinizi doğrulayın', 'preheader' => 'House Chef Cyprus hesabınızı güvenle etkinleştirin.', 'heading' => 'E-posta adresinizi doğrulayın', 'body' => 'Süreli ve tek kullanımlık doğrulama bağlantısı yalnız hedef üyeye gönderilir.', 'action' => 'E-postamı doğrula'],
            'password-reset' => ['category' => 'TRANSACTIONAL', 'subject' => 'Parolanızı sıfırlayın', 'preheader' => 'Talep ettiğiniz parola sıfırlama bağlantısı hazır.', 'heading' => 'Parola sıfırlama', 'body' => 'Süreli reset bağlantısı e-posta içinde gösterilir; token hiçbir log veya admin görünümüne yazılmaz.', 'action' => 'Parolamı sıfırla'],
        ];

        return $previews[$template] ?? null;
    }
}
