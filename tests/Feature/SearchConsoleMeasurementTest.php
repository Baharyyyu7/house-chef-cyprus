<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Models\ContentOpportunity;
use App\Models\IntegrityReport;
use App\Services\Seo\SearchConsoleMeasurementService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Illuminate\Validation\ValidationException;
use Tests\TestCase;

final class SearchConsoleMeasurementTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        config(['app.url' => 'https://housechefcyprus.com', 'seo.search_console.site_url' => 'https://housechefcyprus.com/']);
        $this->travelTo(now()->setDate(2026, 9, 9));
        Http::preventStrayRequests();
    }

    public function test_import_is_idempotent_preserves_review_and_feeds_real_metrics_to_brief(): void
    {
        $service = app(SearchConsoleMeasurementService::class);
        $service->import($this->report());
        $opportunity = ContentOpportunity::query()->sole();
        $opportunity->update(['status' => 'dismissed', 'reviewed_at' => now()]);
        $service->import($this->report());
        $this->assertDatabaseCount('integrity_reports', 1);
        $this->assertSame('dismissed', $opportunity->fresh()->status);
        $metrics = $service->snapshot();
        $this->assertSame(5, $metrics['google_clicks']);
        $this->assertSame(91, $metrics['google_impressions']);
        $this->assertNull($metrics['ranking_queries']);
        $this->assertNull($metrics['external_referring_domains']);
        $this->assertSame('ui_import', $metrics['source']);
        $this->assertSame('https://housechefcyprus.com/', $metrics['property']);
        $this->artisan('seo:morning-brief --json')->assertSuccessful();
        $this->assertSame(5, IntegrityReport::where('guard', 'seo:morning-brief')->sole()->payload['organic_metrics']['google_clicks']);
        Http::assertNothingSent();
    }

    public function test_invalid_property_does_not_write_anything(): void
    {
        try {
            app(SearchConsoleMeasurementService::class)->import([...$this->report(), 'property' => 'https://other.example/']);
            $this->fail('Wrong property accepted');
        } catch (ValidationException) {
            $this->assertDatabaseCount('integrity_reports', 0);
            $this->assertDatabaseCount('content_opportunities', 0);
        }
    }

    public function test_future_dates_and_impossible_totals_are_rejected(): void
    {
        $this->expectException(ValidationException::class);
        app(SearchConsoleMeasurementService::class)->import([...$this->report(), 'end_date' => '2027-01-01', 'clicks' => 1000]);
    }

    public function test_stale_import_remains_visible_but_never_claims_live_api_connection(): void
    {
        $service = app(SearchConsoleMeasurementService::class);
        $service->import($this->report());
        $this->travel(10)->days();
        $this->assertSame('stale', $service->snapshot()['status']);
        $this->assertFalse($service->snapshot()['api_connected']);
    }

    public function test_api_import_uses_totals_separately_from_partial_query_rows(): void
    {
        config(['seo.search_console.enabled' => true, 'seo.search_console.client_id' => 'client', 'seo.search_console.client_secret' => 'secret', 'seo.search_console.refresh_token' => 'refresh']);
        Http::fake([
            'https://oauth2.googleapis.com/token' => Http::response(['access_token' => 'ephemeral-token']),
            'https://www.googleapis.com/webmasters/v3/*' => Http::sequence()
                ->push(['rows' => [['clicks' => 5, 'impressions' => 91, 'ctr' => 5 / 91, 'position' => 24.2]]])
                ->push(['rows' => [['keys' => ['fırın dereceleri'], 'clicks' => 0, 'impressions' => 4, 'position' => 18]]]),
        ]);
        $this->artisan('seo:search-console-import --json')->assertSuccessful();
        $metrics = app(SearchConsoleMeasurementService::class)->snapshot();
        $this->assertSame(91, $metrics['google_impressions']);
        $this->assertTrue($metrics['api_connected']);
        $this->assertSame(1, $metrics['query_sample_count']);
        $this->assertStringNotContainsString('ephemeral-token', IntegrityReport::query()->first()->toJson());
        Http::assertSentCount(3);
    }

    public function test_rate_limited_api_stops_and_preserves_previous_measurements(): void
    {
        config(['seo.search_console.enabled' => true, 'seo.search_console.client_id' => 'client', 'seo.search_console.client_secret' => 'secret', 'seo.search_console.refresh_token' => 'refresh']);
        app(SearchConsoleMeasurementService::class)->import($this->report());
        Http::fake(['https://oauth2.googleapis.com/token' => Http::response('', 429, ['Retry-After' => '600'])]);
        $this->artisan('seo:search-console-import --json')->assertFailed();
        $this->artisan('seo:search-console-import --json')->assertSuccessful();
        Http::assertSentCount(1);
        $this->assertSame(5, app(SearchConsoleMeasurementService::class)->snapshot()['google_clicks']);
    }

    private function report(): array
    {
        return ['property' => 'https://housechefcyprus.com/', 'source' => 'ui_import', 'start_date' => '2026-08-10', 'end_date' => '2026-09-06', 'observed_at' => now()->toIso8601String(), 'search_type' => 'web', 'clicks' => 5, 'impressions' => 91, 'position' => 24.2, 'queries' => [['query' => 'fırın dereceleri', 'clicks' => 0, 'impressions' => 4, 'position' => 18]]];
    }
}
