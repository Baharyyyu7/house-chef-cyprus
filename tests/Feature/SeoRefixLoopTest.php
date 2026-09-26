<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Models\ContentOpportunity;
use App\Models\IntegrityReport;
use App\Services\Seo\RefixSeoLoopService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

final class SeoRefixLoopTest extends TestCase
{
    use RefreshDatabase;

    public function test_service_classifies_almost_there_flag(): void
    {
        $service = app(RefixSeoLoopService::class);

        $row = [
            'query' => 'kıbrıs hellim böreği',
            'page' => 'https://housechefcyprus.com/tarifler/hellim-boregi',
            'impressions' => 15,
            'clicks' => 1,
            'position' => 6.5,
            'ctr' => 0.066,
        ];

        $result = $service->classifyQueryRow($row);

        $this->assertSame(RefixSeoLoopService::FLAG_ALMOST_THERE, $result['flag']);
        $this->assertSame(1.5, $result['weight']);
        $this->assertGreaterThan(0, $result['opportunity_score']);
        $this->assertSame('enrich_content', $result['fix_plan']['type']);
    }

    public function test_service_classifies_no_clicks_flag(): void
    {
        $service = app(RefixSeoLoopService::class);

        $row = [
            'query' => 'kapya biber fırında közleme',
            'page' => 'https://housechefcyprus.com/tarifler/kapya-biber',
            'impressions' => 200,
            'clicks' => 0,
            'position' => 8.5,
            'ctr' => 0.0,
        ];

        $result = $service->classifyQueryRow($row);

        $this->assertSame(RefixSeoLoopService::FLAG_NO_CLICKS, $result['flag']);
        $this->assertSame(1.3, $result['weight']);
        $this->assertSame('rewrite_meta', $result['fix_plan']['type']);
        $this->assertStringContainsString('Nasıl Yapılır', $result['fix_plan']['proposed_meta_title']);
    }

    public function test_service_classifies_decaying_flag_when_wow_clicks_drop_30_percent(): void
    {
        $service = app(RefixSeoLoopService::class);

        $currentRow = [
            'query' => 'molohiya yemeği tarifi',
            'page' => 'https://housechefcyprus.com/tarifler/molohiya',
            'impressions' => 45,
            'clicks' => 2,
            'position' => 4.0,
        ];

        $prevRow = [
            'query' => 'molohiya yemeği tarifi',
            'clicks' => 10,
            'impressions' => 50,
            'position' => 3.2,
        ];

        $result = $service->classifyQueryRow($currentRow, $prevRow);

        $this->assertSame(RefixSeoLoopService::FLAG_DECAYING, $result['flag']);
        $this->assertSame(1.2, $result['weight']);
        $this->assertSame('refresh_freshness', $result['fix_plan']['type']);
    }

    public function test_service_classifies_ai_mode_for_conversational_queries(): void
    {
        $service = app(RefixSeoLoopService::class);

        $row = [
            'query' => '1 yemek kaşığı kuru maya ne kadar yaş maya eder',
            'page' => 'https://housechefcyprus.com/blog/maya-olculeri',
            'impressions' => 18,
            'clicks' => 1,
            'position' => 7.0,
        ];

        $result = $service->classifyQueryRow($row);

        $this->assertSame(RefixSeoLoopService::FLAG_AI_MODE, $result['flag']);
        $this->assertSame(1.0, $result['weight']);
        $this->assertSame('add_direct_answer_faq', $result['fix_plan']['type']);
        $this->assertArrayHasKey('tldr_box', $result['fix_plan']);
        $this->assertArrayHasKey('faq_schema', $result['fix_plan']);
    }

    public function test_service_classifies_wrong_intent(): void
    {
        $service = app(RefixSeoLoopService::class);

        $row = [
            'query' => 'samarella nasıl yapılır',
            'page' => 'https://housechefcyprus.com/tr/tarifler', // Liste sayfasına düşüyor
            'impressions' => 10,
            'clicks' => 0,
            'position' => 7.0,
        ];

        $inventory = [
            'recipe_slugs' => ['samarella'],
            'blog_slugs' => [],
            'published_titles' => ['Samarella Tarifi'],
        ];

        $result = $service->classifyQueryRow($row, null, $inventory);

        $this->assertSame(RefixSeoLoopService::FLAG_WRONG_INTENT, $result['flag']);
        $this->assertSame(1.1, $result['weight']);
        $this->assertSame('align_intent', $result['fix_plan']['type']);
    }

    public function test_service_classifies_untargeted_and_applies_drafts(): void
    {
        $service = app(RefixSeoLoopService::class);

        $injected = [
            [
                'query' => 'kıbrıs lalangı tatlısı tarifi',
                'page' => 'https://housechefcyprus.com/tr', // Anasayfaya düşen ve hedef sayfası olmayan sorgu
                'impressions' => 30,
                'clicks' => 0,
                'position' => 11.0,
            ],
        ];

        $result = $service->runLoop(
            dryRun: false,
            applyDrafts: true,
            injectedRows: $injected
        );

        $this->assertSame(1, $result['drafts_created']);
        $this->assertDatabaseHas('content_opportunities', [
            'kind' => 'refix_loop_draft',
            'status' => 'open',
        ]);

        $opportunity = ContentOpportunity::query()->where('kind', 'refix_loop_draft')->firstOrFail();
        $this->assertSame('draft_only', $opportunity->evidence['delivery_policy']);
        $this->assertTrue($opportunity->evidence['publication_requires_human_review']);
    }

    public function test_artisan_command_runs_with_dry_run_and_json(): void
    {
        $this->artisan('seo:refix-loop', ['--dry-run' => true])
            ->assertSuccessful()
            ->expectsOutputToContain('REFIX SEO LOOP: OTONOM GOOGLE SEARCH CONSOLE BÜYÜME MOTORU');

        $this->artisan('seo:refix-loop', ['--json' => true])
            ->assertSuccessful();
    }
}
