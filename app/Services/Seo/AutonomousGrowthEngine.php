<?php

declare(strict_types=1);

namespace App\Services\Seo;

use App\Http\Controllers\LlmsTxtController;
use App\Models\BlogPost;
use App\Models\ContentOpportunity;
use App\Models\Recipe;
use App\Models\SeoBacklinkCandidate;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;

final class AutonomousGrowthEngine
{
    public const DAILY_SEO_TARGET = 100;
    public const DAILY_GEO_TARGET = 100;
    public const DAILY_BACKLINK_TARGET = 100;

    public function __construct(
        private readonly IndexNowService $indexNow,
        private readonly BacklinkGrowthService $backlinks,
        private readonly ContentGapService $contentGaps,
        private readonly LlmsTxtController $llmsTxt
    ) {
    }

    /**
     * Executes the full autonomous background growth, GEO, SEO and backlink cycle.
     *
     * @return array{
     *     status: string,
     *     timestamp: string,
     *     daily_targets: array{
     *         seo: array{target: int, achieved: int, progress_percent: int, target_met: bool},
     *         geo: array{target: int, achieved: int, progress_percent: int, target_met: bool},
     *         backlinks: array{target: int, achieved: int, progress_percent: int, target_met: bool},
     *         all_targets_met: bool,
     *         updated_at: string
     *     },
     *     geo: array{cleared_caches: list<string>, warmed: bool},
     *     indexnow: array{submitted: int, http_status: int, success: bool},
     *     backlinks: array{created: int, updated: int, total_prospects: int},
     *     content_gaps: array{created: int, updated: int, total_opportunities: int},
     *     syndication: array{pinged: bool, hub_status: int},
     *     catalogue: array{published_recipes: int, verified_recipes: int, public_blogs: int}
     * }
     */
    public function runCycle(bool $force = false): array
    {
        Log::info('Starting Autonomous Growth Engine background cycle...');

        // 1. GEO & LLMs Cache Freshness across ALL enabled languages
        $enabledLocales = array_keys(app(LocaleUrlService::class)->enabledLocales());
        $cacheKeys = [];
        foreach ($enabledLocales as $loc) {
            $cacheKeys[] = 'seo:llms_txt_' . $loc;
            $cacheKeys[] = 'seo:llms_full_txt_' . $loc;
        }

        foreach ($cacheKeys as $key) {
            Cache::forget($key);
        }

        $originalLocale = app()->getLocale();
        try {
            foreach ($enabledLocales as $loc) {
                match ($loc) {
                    'tr' => $this->llmsTxt->index(),
                    'en' => $this->llmsTxt->en(),
                    default => $this->llmsTxt->locale($loc),
                };
                match ($loc) {
                    'tr' => $this->llmsTxt->full(),
                    'en' => $this->llmsTxt->enFull(),
                    default => $this->llmsTxt->localeFull($loc),
                };
            }
        } finally {
            app()->setLocale($originalLocale);
        }

        // 2. Continuous IndexNow Protocol Submissions
        $indexNowResult = config('seo.indexnow.enabled')
            ? $this->indexNow->submit()
            : ['success' => false, 'status' => 0, 'count' => 0];

        // 3. Backlink Opportunity & Digital PR Prospecting
        $backlinkResult = $this->backlinks->discoverAndEnrich();

        // 4. RSS Feed Syndication Hub Pings
        $syndicationResult = $this->backlinks->pingSyndicationHubs();

        // 5. Zero-Result Search & Content Gap Mining
        $gapResult = $this->contentGaps->refresh();

        // Discovery does not confer editorial, image identity or licensing approval.
        $mediaResult = ['approved' => 0, 'rejected' => 0, 'skipped' => 0];
        $publishingResult = ['published_blogs' => [], 'published_recipes' => []];

        // 8. Catalogue Metrics
        $publishedRecipes = Recipe::query()->where('status', 'published')->count();
        $verifiedRecipes = Recipe::query()->where('status', 'published')->where('verification_status', 'verified')->count();
        $publicBlogs = BlogPost::query()->publiclyVisible()->count();

        $totalBacklinkOpportunities = ContentOpportunity::query()->where('kind', 'backlink_prospect')->count();
        $totalOpportunities = ContentOpportunity::query()->where('status', 'open')->count();

        // 9. Daily 100 SEO · 100 GEO · 100 Backlink Target Computation
        $todayKey = 'daily_autonomous_growth_kpis_v2_' . date('Y-m-d');
        Cache::forget($todayKey);

        $seoAchieved = $indexNowResult['success'] ? $indexNowResult['count'] : 0;
        // Cache freshness is not evidence of an AI citation.
        $geoAchieved = 0;
        $backlinkAchieved = $this->verifiedLinksToday();

        $dailyTargets = [
            'seo' => [
                'basis' => 'accepted_indexnow_urls_this_cycle_not_rankings',
                'target' => self::DAILY_SEO_TARGET,
                'achieved' => $seoAchieved,
                'progress_percent' => min(100, (int) round(($seoAchieved / self::DAILY_SEO_TARGET) * 100)),
                'target_met' => $seoAchieved >= self::DAILY_SEO_TARGET,
            ],
            'geo' => [
                'basis' => 'unmeasured_ai_citations',
                'measurement_available' => false,
                'target' => self::DAILY_GEO_TARGET,
                'achieved' => $geoAchieved,
                'progress_percent' => min(100, (int) round(($geoAchieved / self::DAILY_GEO_TARGET) * 100)),
                'target_met' => $geoAchieved >= self::DAILY_GEO_TARGET,
            ],
            'backlinks' => [
                'basis' => 'links_verified_today_not_new_acquisitions',
                'target' => self::DAILY_BACKLINK_TARGET,
                'achieved' => $backlinkAchieved,
                'progress_percent' => min(100, (int) round(($backlinkAchieved / self::DAILY_BACKLINK_TARGET) * 100)),
                'target_met' => $backlinkAchieved >= self::DAILY_BACKLINK_TARGET,
            ],
            'all_targets_met' => ($seoAchieved >= self::DAILY_SEO_TARGET) && ($geoAchieved >= self::DAILY_GEO_TARGET) && ($backlinkAchieved >= self::DAILY_BACKLINK_TARGET),
            'updated_at' => now()->toIso8601String(),
        ];

        Cache::put($todayKey, $dailyTargets, now()->addDays(7));

        $report = [
            'status' => 'success',
            'timestamp' => now()->toIso8601String(),
            'daily_targets' => $dailyTargets,
            'geo' => [
                'cleared_caches' => $cacheKeys,
                'warmed' => true,
                'locales' => $enabledLocales,
                'corpora_count' => count($cacheKeys),
            ],
            'indexnow' => [
                'submitted' => $indexNowResult['count'],
                'http_status' => $indexNowResult['status'],
                'success' => $indexNowResult['success'],
            ],
            'backlinks' => [
                'created' => $backlinkResult['created'],
                'updated' => $backlinkResult['updated'],
                'total_prospects' => $totalBacklinkOpportunities,
                'markets' => app(BacklinkMarketService::class)->pazarDagilimi($this->backlinks->prospects()),
            ],
            'content_gaps' => [
                'created' => $gapResult['created'],
                'updated' => $gapResult['updated'],
                'total_opportunities' => $totalOpportunities,
            ],
            'syndication' => [
                'pinged' => $syndicationResult['pinged'],
                'hub_status' => $syndicationResult['hub_status'],
            ],
            'autonomous_media' => [
                'status' => 'human_review_required',
                'approved' => $mediaResult['approved'],
                'rejected' => $mediaResult['rejected'],
                'skipped' => $mediaResult['skipped'],
            ],
            'autonomous_publishing' => [
                'status' => 'human_review_required',
                'published_blogs' => $publishingResult['published_blogs'],
                'published_recipes' => $publishingResult['published_recipes'],
            ],
            'catalogue' => [
                'published_recipes' => $publishedRecipes,
                'verified_recipes' => $verifiedRecipes,
                'public_blogs' => $publicBlogs,
            ],
        ];

        Log::info('Autonomous Growth Engine background cycle completed successfully.', $report);

        return $report;
    }

    /**
     * @return array{
     *     seo: array{target: int, achieved: int, progress_percent: int, target_met: bool},
     *     geo: array{target: int, achieved: int, progress_percent: int, target_met: bool},
     *     backlinks: array{target: int, achieved: int, progress_percent: int, target_met: bool},
     *     all_targets_met: bool,
     *     updated_at: string
     * }
     */
    public function getDailyTargetsSnapshot(): array
    {
        $todayKey = 'daily_autonomous_growth_kpis_v2_' . date('Y-m-d');

        return Cache::remember($todayKey, 3600, function (): array {
            $seoAchieved = 0;
            $geoAchieved = 0;
            $backlinkAchieved = $this->verifiedLinksToday();

            return [
                'seo' => [
                    'basis' => 'no_submission_observed',
                    'measurement_available' => false,
                    'target' => self::DAILY_SEO_TARGET,
                    'achieved' => $seoAchieved,
                    'progress_percent' => min(100, (int) round(($seoAchieved / self::DAILY_SEO_TARGET) * 100)),
                    'target_met' => $seoAchieved >= self::DAILY_SEO_TARGET,
                ],
                'geo' => [
                    'basis' => 'unmeasured_ai_citations',
                    'measurement_available' => false,
                    'target' => self::DAILY_GEO_TARGET,
                    'achieved' => $geoAchieved,
                    'progress_percent' => min(100, (int) round(($geoAchieved / self::DAILY_GEO_TARGET) * 100)),
                    'target_met' => $geoAchieved >= self::DAILY_GEO_TARGET,
                ],
                'backlinks' => [
                    'basis' => 'links_verified_today_not_new_acquisitions',
                    'target' => self::DAILY_BACKLINK_TARGET,
                    'achieved' => $backlinkAchieved,
                    'progress_percent' => min(100, (int) round(($backlinkAchieved / self::DAILY_BACKLINK_TARGET) * 100)),
                    'target_met' => $backlinkAchieved >= self::DAILY_BACKLINK_TARGET,
                ],
                'all_targets_met' => ($seoAchieved >= self::DAILY_SEO_TARGET) && ($geoAchieved >= self::DAILY_GEO_TARGET) && ($backlinkAchieved >= self::DAILY_BACKLINK_TARGET),
                'updated_at' => now()->toIso8601String(),
            ];
        });
    }

    private function verifiedLinksToday(): int
    {
        return SeoBacklinkCandidate::query()
            ->where('status', '<>', 'rejected')
            ->where('check_status', 'present')
            ->whereBetween('checked_at', [now()->startOfDay(), now()])
            ->count();
    }
}
