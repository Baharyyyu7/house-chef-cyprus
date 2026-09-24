<?php

declare(strict_types=1);

namespace App\Services\Seo;

use Illuminate\Support\Facades\Log;
use RuntimeException;

/**
 * PrePublishSafetyHook
 *
 * X Post / Machina SEO Kuralı:
 * "Claude Code supports hooks that force the agent to ask before publishing,
 * editing a live page or submitting a URL. Cloud routines have read-only access only."
 *
 * Bu sınıf, hiçbir yapay zeka ajanının veya otomatik arka plan görevinin
 * doğrudan insan onayı olmaksızın yayına içerik almasını, canlı sayfayı
 * değiştirmesini veya URL göndermesini engeller.
 */
final class PrePublishSafetyHook
{
    public const ACTION_PUBLISH_PAGE = 'publish_page';
    public const ACTION_EDIT_LIVE_METADATA = 'edit_live_metadata';
    public const ACTION_SUBMIT_URL_INDEXING = 'submit_url_indexing';
    public const ACTION_MODIFY_CORE_CONFIG = 'modify_core_config';

    /**
     * Eylemin yürütülmesine izin verilip verilmediğini denetler.
     * İnsan onayı yoksa katı bir RuntimeException fırlatır.
     *
     * @throws RuntimeException
     */
    public function assertCanExecute(string $action, string $targetIdentifier, bool $humanApproved = false, ?string $approvedBy = null): void
    {
        if (! $humanApproved) {
            $msg = sprintf(
                '[GÜVENLİK ENGELİ] PrePublishSafetyHook: "%s" eylemi "%s" hedefi için durduruldu. İnsan onayı (Human-in-the-loop YES) olmadan otomatik yayın yapılamaz.',
                $action,
                $targetIdentifier
            );

            Log::warning($msg, [
                'action' => $action,
                'target' => $targetIdentifier,
                'time' => now()->toIso8601String(),
            ]);

            throw new RuntimeException($msg);
        }

        Log::info(sprintf(
            '[GÜVENLİK ONAYLANDI] PrePublishSafetyHook: "%s" eylemi "%s" hedefi için insan onaylı (%s) olarak çalıştırıldı.',
            $action,
            $targetIdentifier,
            $approvedBy ?? 'Admin'
        ));
    }

    /**
     * Güvenlik durumu özeti
     *
     * @return array<string, mixed>
     */
    public function getStatus(): array
    {
        return [
            'hook_active' => true,
            'enforce_human_approval' => true,
            'protected_actions' => [
                self::ACTION_PUBLISH_PAGE,
                self::ACTION_EDIT_LIVE_METADATA,
                self::ACTION_SUBMIT_URL_INDEXING,
                self::ACTION_MODIFY_CORE_CONFIG,
            ],
            'autonomous_publish_allowed' => false,
            'rule_source' => 'X Post Machina Protocol & AGENTS.md Draft Policy',
        ];
    }
}
