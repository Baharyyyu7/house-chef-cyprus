<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Models\ContentOpportunity;
use App\Services\Seo\AutonomousGrowthEngine;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

final class AutonomousGrowthEngineTest extends TestCase
{
    use RefreshDatabase;

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
        $this->assertTrue($report['daily_targets']['all_targets_met']);
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
        $this->assertTrue($backlinkProspect->evidence['publication_requires_human_review']);
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
