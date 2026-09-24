<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Models\ContentOpportunity;
use App\Models\IntegrityReport;
use App\Services\Integrity\IntegrityReportLog;
use App\Services\Seo\ContentGapService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

final class OrganicGrowthBriefTest extends TestCase
{
    use RefreshDatabase;

    public function test_refresh_does_not_reopen_a_human_reviewed_opportunity(): void
    {
        app(ContentGapService::class)->refresh();
        $opportunity = ContentOpportunity::query()->where('normalized_key', 'cluster-kibris-mutfagi')->sole();
        $opportunity->update(['status' => 'dismissed', 'reviewed_at' => now(), 'title' => 'Editör kararı']);

        app(ContentGapService::class)->refresh();

        $this->assertSame('dismissed', $opportunity->fresh()->status);
        $this->assertSame('Editör kararı', $opportunity->fresh()->title);
    }

    public function test_daily_brief_persists_unknown_search_metrics_without_inventing_zero_traffic(): void
    {
        $this->artisan('seo:morning-brief --json')->assertSuccessful();
        $report = IntegrityReport::query()->where('guard', 'seo:morning-brief')->sole();
        $this->assertNull($report->payload['organic_metrics']['google_clicks']);
        $this->assertNull($report->payload['organic_metrics']['ranking_queries']);
        $this->assertNull($report->payload['organic_metrics']['external_referring_domains']);
        $this->assertSame('not_connected', $report->payload['organic_metrics']['status']);
        $this->assertFalse($report->payload['automation']['publishes_content']);
        $this->assertFalse($report->payload['automation']['paid_provider_calls']);
        $summary = collect(app(IntegrityReportLog::class)->summary())->firstWhere('guard', 'seo:morning-brief');
        $this->assertStringContainsString('Bilinmeyen değer sıfır değildir', $summary['sebep']);
        $this->assertDatabaseCount('blog_posts', 0);
    }
}
