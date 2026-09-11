<?php

declare(strict_types=1);

namespace App\Services\Seo;

use App\Http\Controllers\LlmsTxtController;
use App\Models\BlogPost;
use App\Models\ContentOpportunity;
use App\Models\Recipe;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;

final class AutonomousGrowthEngine
{
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

        // 1. GEO & LLMs Cache Freshness
        $cacheKeys = [
            'seo:llms_txt_tr',
            'seo:llms_txt_en',
            'seo:llms_full_txt_tr',
            'seo:llms_full_txt_en',
        ];

        foreach ($cacheKeys as $key) {
            Cache::forget($key);
        }

        // Prime the caches with fresh seasonal and recipe intelligence
        $this->llmsTxt->index();
        $this->llmsTxt->en();
        $this->llmsTxt->full();
        $this->llmsTxt->enFull();

        // 2. Continuous IndexNow Protocol Submissions
        $indexNowResult = $this->indexNow->submit();

        // 3. Backlink Opportunity & Digital PR Prospecting
        $backlinkResult = $this->backlinks->discoverAndEnrich();

        // 4. RSS Feed Syndication Hub Pings
        $syndicationResult = $this->backlinks->pingSyndicationHubs();

        // 5. Zero-Result Search & Content Gap Mining
        $gapResult = $this->contentGaps->refresh();

        // 6. Catalogue Metrics
        $publishedRecipes = Recipe::query()->where('status', 'published')->count();
        $verifiedRecipes = Recipe::query()->where('status', 'published')->where('verification_status', 'verified')->count();
        $publicBlogs = BlogPost::query()->publiclyVisible()->count();

        $totalBacklinkOpportunities = ContentOpportunity::query()->where('kind', 'backlink_prospect')->count();
        $totalOpportunities = ContentOpportunity::query()->where('status', 'open')->count();

        $report = [
            'status' => 'success',
            'timestamp' => now()->toIso8601String(),
            'geo' => [
                'cleared_caches' => $cacheKeys,
                'warmed' => true,
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
            'catalogue' => [
                'published_recipes' => $publishedRecipes,
                'verified_recipes' => $verifiedRecipes,
                'public_blogs' => $publicBlogs,
            ],
        ];

        Log::info('Autonomous Growth Engine background cycle completed successfully.', $report);

        return $report;
    }
}
