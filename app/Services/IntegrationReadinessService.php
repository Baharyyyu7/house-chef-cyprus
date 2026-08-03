<?php

declare(strict_types=1);

namespace App\Services;

final class IntegrationReadinessService
{
    /** @return array<string, array<string, mixed>> */
    public function snapshot(): array
    {
        $gaId = (string) config('integrations.google.analytics.measurement_id');
        $gtmId = (string) config('integrations.google.tag_manager.container_id');

        return [
            'search_console' => $this->entry(
                filled(config('integrations.google.site_verification')) || filled(config('integrations.google.search_console.property')),
                'Google Search Console',
                'Arama görünürlüğü ve sitemap takibi',
                'Domain sahipliği doğrulaması ve sitemap gönderimi Google hesabında tamamlanır.'
            ),
            'ga4' => $this->entry(
                (bool) config('integrations.google.analytics.enabled') && preg_match('/^G-[A-Z0-9]+$/', $gaId) === 1,
                'Google Analytics 4',
                'Onay yönetimli anonim ürün analitiği',
                'GA4 web stream oluşturup G-… Measurement ID değerini production secret olarak ekleyin.'
            ),
            'gtm' => $this->entry(
                (bool) config('integrations.google.tag_manager.enabled') && preg_match('/^GTM-[A-Z0-9]+$/', $gtmId) === 1,
                'Google Tag Manager',
                'Consent-aware dataLayer ve etiket yönetimi',
                'GTM container oluşturup GTM-… ID değerini production secret olarak ekleyin.'
            ),
            'google_auth' => $this->entry(false, 'Google ile Giriş', 'OpenID Connect adaptörü henüz kurulmadı; mevcut e-posta/parola akışı çalışır.', null, 'NOT_AVAILABLE'),
            'recaptcha' => $this->entry(false, 'Google reCAPTCHA', 'Provider adaptörü henüz kurulmadı; mevcut Turnstile korunur.', null, 'NOT_AVAILABLE'),
            'smtp' => $this->entry(
                config('mail.default') === 'smtp' && filled(config('mail.mailers.smtp.host')),
                'Production SMTP',
                'Transactional e-posta',
                'Mevcut Hostinger mailbox tercih edilir; Google Workspace zorunlu değildir.'
            ),
            'scheduler' => $this->entry(true, 'Hostinger Scheduler', 'Laravel scheduler cron', null, 'CONNECTED_VERIFIED'),
            'queue' => $this->entry(config('queue.default') !== 'sync', 'Bounded Queue', 'Shared-hosting uyumlu sınırlı worker', null),
            'first_party' => $this->entry(true, 'First-party SEO ve ölçüm', 'Harici kimlik bilgisi olmadan çalışan temel operasyonlar', null, 'CONNECTED_VERIFIED'),
        ];
    }

    /** @return array<string, mixed> */
    private function entry(bool $configured, string $name, string $purpose, ?string $ownerAction, ?string $forcedStatus = null): array
    {
        return [
            'name' => $name,
            'purpose' => $purpose,
            'status' => $forcedStatus ?? ($configured ? 'CONNECTED_VERIFIED' : 'READY_NOT_CONNECTED'),
            'owner_action' => $configured ? null : $ownerAction,
            'secret_visible' => false,
        ];
    }
}
