<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Models\ContentOpportunity;
use App\Models\BlogPost;
use App\Models\Recipe;
use App\Models\RecipeMediaCandidate;
use App\Models\User;
use App\Services\Seo\AutonomousGrowthEngine;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

final class AutonomousGrowthEngineTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        Http::fake(['*' => Http::response('', 200)]);
    }

    public function test_autonomous_growth_engine_runs_cycle_and_populates_backlinks(): void
    {
        $engine = app(AutonomousGrowthEngine::class);
        $report = $engine->runCycle();

        $this->assertSame('success', $report['status']);
        $this->assertArrayHasKey('geo', $report);
        $this->assertArrayHasKey('indexnow', $report);
        $this->assertArrayHasKey('backlinks', $report);
        $this->assertArrayHasKey('content_gaps', $report);
        $this->assertArrayHasKey('daily_targets', $report);
        $this->assertSame(100, $report['daily_targets']['seo']['target']);
        $this->assertSame(100, $report['daily_targets']['geo']['target']);
        $this->assertSame(100, $report['daily_targets']['backlinks']['target']);
        $this->assertFalse($report['daily_targets']['all_targets_met']);
        $this->assertSame(0, $report['daily_targets']['backlinks']['achieved']);
        $this->assertFalse($report['daily_targets']['geo']['measurement_available']);
        $this->assertGreaterThanOrEqual(100, $report['backlinks']['total_prospects']);

        $backlinkProspect = ContentOpportunity::query()
            ->where('kind', 'backlink_prospect')
            ->first();

        $this->assertNotNull($backlinkProspect);
        $this->assertSame('open', $backlinkProspect->status);
        $this->assertSame('Dış Bağlantı & Dijital PR', $backlinkProspect->topic_cluster);
        $this->assertArrayHasKey('target_domain', $backlinkProspect->evidence);
        $this->assertArrayHasKey('pitch_draft_tr', $backlinkProspect->evidence);
        $this->assertArrayHasKey('pitch_draft_en', $backlinkProspect->evidence);
        $this->assertArrayHasKey('pitch_draft_de', $backlinkProspect->evidence);
        $this->assertArrayHasKey('pitch_draft_pl', $backlinkProspect->evidence);
        $this->assertArrayHasKey('pitch_draft_ru', $backlinkProspect->evidence);
        $this->assertArrayHasKey('pitch_draft_fa', $backlinkProspect->evidence);
        $this->assertArrayHasKey('pitch_draft_pt', $backlinkProspect->evidence);
        $this->assertTrue($backlinkProspect->evidence['publication_requires_human_review']);
        $this->assertArrayHasKey('markets', $report['backlinks']);
        $this->assertArrayHasKey('locales', $report['geo']);
    }

    public function test_multilingual_llms_endpoints_return_successful_responses(): void
    {
        config()->set('locales.supported.de.enabled', true);

        $responseDe = $this->get('/de/llms.txt');
        $responseDe->assertOk();
        $responseDe->assertHeader('Content-Type', 'text/plain; charset=UTF-8');
        $responseDe->assertSee('GEO Knowledge Graph');
        $responseDe->assertSee('AI Citation Directive');

        $responseDeFull = $this->get('/de/llms-full.txt');
        $responseDeFull->assertOk();
        $responseDeFull->assertHeader('Content-Type', 'text/plain; charset=UTF-8');
        $responseDeFull->assertSee('Full Knowledge Corpus');
    }

    public function test_background_cycle_cannot_approve_content_or_media_and_preserves_locale(): void
    {
        User::factory()->create(['role' => 'admin']);
        $recipe = Recipe::create(['title' => 'Review required', 'slug' => 'review-required', 'status' => 'draft']);
        $post = BlogPost::create([
            'title' => 'A complete guide awaiting an editor', 'slug' => 'awaiting-editor',
            'excerpt' => 'This content has not received editorial approval.',
            'body' => str_repeat('A detailed original cooking observation. ', 60),
            'meta_title' => 'A complete guide awaiting editorial review',
            'meta_description' => str_repeat('Editorial review is still required. ', 4),
            'status' => 'draft',
        ]);
        $candidate = RecipeMediaCandidate::create([
            'recipe_id' => $recipe->id, 'provider' => 'pexels', 'external_photo_id' => 'review-required',
            'source_url' => 'https://images.example.org/dish.jpg', 'status' => 'pending_review',
            'width' => 1600, 'height' => 1000,
        ]);
        app()->setLocale('en');
        config(['seo.indexnow.enabled' => false]);
        $report = app(AutonomousGrowthEngine::class)->runCycle();

        $this->assertSame('draft', $post->fresh()->status);
        $this->assertNull($post->fresh()->human_approved_at);
        $this->assertSame('pending_review', $candidate->fresh()->status);
        $this->assertSame(0, $recipe->media()->count());
        $this->assertSame([], $report['autonomous_publishing']['published_blogs']);
        $this->assertSame(0, $report['autonomous_media']['approved']);
        $this->assertSame('en', app()->getLocale());
        Http::assertNotSent(fn ($request) => str_contains($request->url(), 'api.indexnow.org'));
    }

    public function test_seo_autonomous_growth_command_executes_successfully(): void
    {
        $this->artisan('seo:autonomous-growth')
            ->expectsOutputToContain('GEO Korpusu')
            ->expectsOutputToContain('Backlink Havuzu')
            ->assertSuccessful();
    }

    public function test_seo_autonomous_growth_dry_run(): void
    {
        $this->artisan('seo:autonomous-growth --dry-run')
            ->expectsOutputToContain('[DRY-RUN]')
            ->assertSuccessful();
    }

    public function test_seo_autonomous_growth_json(): void
    {
        $this->artisan('seo:autonomous-growth --json')
            ->expectsOutputToContain('"status":"success"')
            ->assertSuccessful();
    }
}
