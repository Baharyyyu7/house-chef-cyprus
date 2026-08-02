<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\BlogPost;
use App\Models\Recipe;
use App\Services\AI\AiKitchenService;
use App\Services\Media\MediaExcellenceService;
use App\Services\Seo\SeoAuthorityService;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Route;

/**
 * Read-only RC1 release evidence. This class deliberately distinguishes a
 * repeatable local check from browser, production-hosting and human review.
 */
final class ReleaseCandidateAuditService
{
    public const RULE_VERSION = 'rc1-2026-08-01';

    /** @return array<string, mixed> */
    public function audit(): array
    {
        $catalogue = $this->catalogue();
        $seo = app(SeoAuthorityService::class)->snapshot();
        $media = app(MediaExcellenceService::class)->health();
        $ai = app(AiKitchenService::class)->stats();
        $operations = app(ProductionOperationsService::class)->health();
        $ux = app(ProductExperienceAuditService::class)->audit();
        $findings = $this->findings($catalogue, $seo, $media, $ux);

        $report = [
            'rule_version' => self::RULE_VERSION,
            'generated_at' => now()->toIso8601String(),
            'state' => $findings['blocker'] === 0 && $findings['critical'] === 0 ? 'ready_for_prelaunch_human_qa' : 'attention_required',
            'findings' => $findings,
            'route_inventory' => $this->routeInventory(),
            'catalogue' => $catalogue,
            'health' => [
                'public' => $findings['blocker'] === 0 ? 'checked' : 'attention_required',
                'member' => 'requires_authenticated_browser_qa',
                'admin' => 'requires_authenticated_browser_qa',
                'seo' => ['score' => $seo['score'], 'open_critical_issues' => $seo['issues']['critical']],
                'media' => array_intersect_key($media, array_flip(['score', 'private_leakage', 'approved_real_media', 'branded_fallback', 'scan_required'])),
                'ai_kitchen' => ['requests' => $ai['requests'], 'failures' => $ai['failures'], 'safety_events' => $ai['safety_events'], 'providers' => $ai['providers']],
                'operations' => ['status' => $operations['status'], 'checked_at' => $operations['checked_at']],
                'accessibility' => ['status' => $ux['status'], 'static_score' => $ux['scores']['overall']],
            ],
            'manual_prelaunch_requirements' => [
                'authenticated_member_and_admin_browser_flows',
                'screen_reader_and_200_percent_zoom_review',
                'production_domain_ssl_smtp_monitoring_and_field_cwv',
                'human_editorial_approval_for_pending_content_and_media',
            ],
            'measurement_note' => 'RC1 kontrolü gerçek olmayan başarı, üretim metrikleri veya insan onayı üretmez. not_configured/degraded durumlar açık dış bağımlılık olarak kalır.',
        ];

        Cache::forever('release-candidate.rc1.latest', $report);

        return $report;
    }

    /** @return array<string, mixed> */
    public function latest(): array
    {
        // Release evidence must never present a stale catalogue, especially
        // directly after a review, seed or content-state change. The cache is
        // retained as an operational breadcrumb, while the command centre
        // always calculates its current read-only snapshot.
        return $this->audit();
    }

    /** @return array<string, int> */
    private function catalogue(): array
    {
        $recipes = Recipe::query();
        $blogs = BlogPost::query();

        return [
            'recipes_total' => (clone $recipes)->count(),
            'recipes_published_verified' => (clone $recipes)->where('status', 'published')->where('verification_status', 'verified')->count(),
            'recipes_draft' => (clone $recipes)->where('status', 'draft')->count(),
            'recipes_published_unverified' => (clone $recipes)->where('status', 'published')->where('verification_status', '!=', 'verified')->count(),
            'recipes_published_incomplete' => (clone $recipes)->where('status', 'published')->where('completeness_status', '!=', 'publishable')->count(),
            'blogs_total' => (clone $blogs)->count(),
            'blogs_public' => (clone $blogs)->publiclyVisible()->count(),
            'blogs_draft' => (clone $blogs)->where('status', 'draft')->count(),
            'blogs_draft_with_publication_date' => (clone $blogs)->where('status', 'draft')->whereNotNull('published_at')->count(),
        ];
    }

    /** @param array<string, int> $catalogue @param array<string, mixed> $seo @param array<string, mixed> $media @param array<string, mixed> $ux @return array<string, int> */
    private function findings(array $catalogue, array $seo, array $media, array $ux): array
    {
        $blocker = $catalogue['recipes_published_unverified']
            + $catalogue['blogs_draft_with_publication_date']
            + (int) ($media['private_leakage'] ?? 0);
        $critical = (int) ($seo['issues']['critical'] ?? 0) + count($ux['critical_blockers'] ?? []);

        return [
            'blocker' => $blocker,
            'critical' => $critical,
            'high' => (int) ($seo['issues']['error'] ?? 0) + $catalogue['recipes_published_incomplete'],
            'medium' => (int) ($seo['issues']['warning'] ?? 0),
            'low' => (int) ($seo['issues']['info'] ?? 0),
        ];
    }

    /** @return array{total:int,web:int,api:int,admin:int,auth_protected:int} */
    private function routeInventory(): array
    {
        $routes = collect(Route::getRoutes()->getRoutes());

        return [
            'total' => $routes->count(),
            'web' => $routes->filter(fn ($route): bool => ! str_starts_with((string) $route->uri(), 'api/'))->count(),
            'api' => $routes->filter(fn ($route): bool => str_starts_with((string) $route->uri(), 'api/'))->count(),
            'admin' => $routes->filter(fn ($route): bool => str_starts_with((string) $route->uri(), 'yonetim'))->count(),
            'auth_protected' => $routes->filter(fn ($route): bool => in_array('auth', $route->gatherMiddleware(), true))->count(),
        ];
    }
}
