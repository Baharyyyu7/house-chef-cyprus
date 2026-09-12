<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Models\ContentOpportunity;
use App\Models\IntegrityReport;
use App\Models\SeoBacklinkCandidate;
use App\Services\Seo\BacklinkVerificationService;
use App\Services\Seo\OrganicGrowthService;
use App\Services\Seo\PublicSourceResolver;
use App\Services\Seo\SearchConsoleMeasurementService;
use App\Services\Seo\SeoProviderHealthService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

final class GlobalGrowthReadinessTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->travelTo(now()->setDate(2026, 9, 13)->startOfDay());
        config(['app.url' => 'https://housechefcyprus.com', 'seo.search_console.site_url' => 'https://housechefcyprus.com/']);
        Http::preventStrayRequests();
    }

    public function test_reordered_and_numeric_string_reports_are_idempotent_and_refresh_observation(): void
    {
        $service = app(SearchConsoleMeasurementService::class);
        $report = $this->report();
        $service->import($report);
        ContentOpportunity::query()->update(['status' => 'dismissed']);
        $this->travel(1)->hours();
        $report['observed_at'] = now()->toIso8601String();
        $report['clicks'] = '5';
        $report['queries'] = array_reverse($report['queries']);
        $report['queries'][0]['position'] = '12.0';
        $result = $service->import(array_reverse($report, true));

        $this->assertSame('unchanged', $result['status']);
        $this->assertDatabaseCount('integrity_reports', 1);
        $this->assertSame(now()->toIso8601String(), $service->snapshot()['observed_at']);
        $this->assertSame(2, ContentOpportunity::where('status', 'dismissed')->count());
        Http::assertNothingSent();
    }

    public function test_measurements_and_opportunities_are_scoped_to_property(): void
    {
        $service = app(SearchConsoleMeasurementService::class);
        $service->import($this->report());
        config(['seo.search_console.site_url' => 'sc-domain:other.org']);
        $this->assertSame('not_connected', $service->snapshot()['status']);
        $this->assertNull($service->snapshot()['google_clicks']);
        $service->import([...$this->report(), 'property' => 'sc-domain:other.org', 'clicks' => 9]);
        $this->assertSame(9, $service->snapshot()['google_clicks']);
        $this->assertDatabaseCount('content_opportunities', 4);
        config(['seo.search_console.site_url' => 'https://housechefcyprus.com/']);
        $this->assertSame(5, $service->snapshot()['google_clicks']);
    }

    public function test_recent_period_is_not_hidden_by_thirty_later_historical_imports(): void
    {
        $service = app(SearchConsoleMeasurementService::class);
        $service->import($this->report());
        for ($i = 0; $i < 31; $i++) {
            $service->import([...$this->report(), 'start_date' => '2026-07-01', 'end_date' => '2026-07-28', 'impressions' => 100 + $i]);
        }
        $this->assertSame('2026-09-10', $service->snapshot()['end_date']);
        $this->assertSame('measured', $service->snapshot()['status']);
    }

    public function test_legacy_review_decisions_are_preserved_only_for_their_original_property(): void
    {
        $service = app(SearchConsoleMeasurementService::class);
        $service->import($this->report());
        foreach (ContentOpportunity::all() as $opportunity) {
            $opportunity->update(['normalized_key' => 'gsc-'.hash('sha256', $opportunity->evidence['query']), 'status' => 'dismissed']);
        }
        $service->import([...$this->report(), 'clicks' => 6]);
        $this->assertDatabaseCount('content_opportunities', 2);
        $this->assertSame(2, ContentOpportunity::where('status', 'dismissed')->count());
        config(['seo.search_console.site_url' => 'sc-domain:other.org']);
        $service->import([...$this->report(), 'property' => 'sc-domain:other.org']);
        $this->assertDatabaseCount('content_opportunities', 4);
    }

    public function test_disabled_or_unconfigured_provider_is_not_ready_from_historical_api_data(): void
    {
        app(SearchConsoleMeasurementService::class)->import([...$this->report(), 'source' => 'api_import'], true);
        config(['seo.search_console.enabled' => false]);
        $this->assertFalse(app(SeoProviderHealthService::class)->snapshot()['search_console']['ready']);
        config(['seo.search_console.enabled' => true, 'seo.search_console.refresh_token' => null]);
        $this->assertFalse(app(SeoProviderHealthService::class)->snapshot()['search_console']['ready']);
    }

    public function test_explicit_zero_measurement_is_distinct_from_unavailable_api_data(): void
    {
        $service = app(SearchConsoleMeasurementService::class);
        $this->assertNull($service->snapshot()['google_clicks']);
        $service->import([...$this->report(), 'clicks' => 0, 'impressions' => 0, 'position' => 0, 'queries' => []]);
        $this->assertSame(0, $service->snapshot()['google_clicks']);
        $this->configureApi();
        Http::fake([
            'oauth2.googleapis.com/*' => Http::response(['access_token' => 'test']),
            'www.googleapis.com/*' => Http::response(['rows' => []]),
        ]);
        $this->assertSame('failed', $service->importApi()['status']);
        $this->assertSame(0, $service->snapshot()['google_clicks']);
        Http::assertSentCount(3);
    }

    public function test_failed_api_attempt_revokes_readiness_but_preserves_measurement(): void
    {
        $this->configureApi();
        $service = app(SearchConsoleMeasurementService::class);
        $service->import([...$this->report(), 'source' => 'api_import'], true);
        $this->assertTrue($service->snapshot()['api_connected']);
        $this->travel(1)->minutes();
        Http::fake(['*' => Http::response('', 401)]);
        $this->assertSame('failed', $service->importApi()['status']);
        $this->assertFalse($service->snapshot()['api_connected']);
        $this->assertFalse(app(SeoProviderHealthService::class)->snapshot()['search_console']['ready']);
        $this->assertSame(5, $service->snapshot()['google_clicks']);
        $this->travel(1)->minutes();
        $service->import([...$this->report(), 'source' => 'api_import'], true);
        $this->assertTrue($service->snapshot()['api_connected']);
        Http::assertSentCount(1);
    }

    public function test_api_http_date_retry_is_respected_and_no_more_requests_are_sent(): void
    {
        $this->configureApi();
        Http::fake(['*' => Http::response('', 429, ['Retry-After' => now()->addHours(2)->toRfc7231String()])]);
        $service = app(SearchConsoleMeasurementService::class);
        $this->assertSame('failed', $service->importApi()['status']);
        $this->assertSame(now()->addHours(2)->toIso8601String(), Cache::get('seo:gsc:retry-after'));
        $this->assertSame('deferred', $service->importApi()['status']);
        Http::assertSentCount(1);
    }

    public function test_backlink_errors_fail_command_and_never_count_candidates_as_acquired(): void
    {
        $this->candidate();
        Http::fake(['*' => Http::response('', 403)]);
        $this->artisan('seo:backlinks-verify --json')->assertFailed();
        $snapshot = app(OrganicGrowthService::class)->snapshot();
        $this->assertSame(1, $snapshot['research_candidates']);
        $this->assertSame(0, $snapshot['machine_verified_links']);
        $this->assertSame(0, $snapshot['observed_links']);
        $report = IntegrityReport::where('guard', 'seo:backlinks-verify')->sole();
        $this->assertSame('incomplete', $report->status);
        $this->assertSame(1, $report->payload['unresolved']);
        Http::assertSentCount(1);
    }

    public function test_backlink_retry_is_capped_at_seven_days(): void
    {
        $candidate = $this->candidate();
        Http::fake(['*' => Http::response('', 429, ['Retry-After' => '999999999999'])]);
        app(BacklinkVerificationService::class)->run();
        $this->assertTrue($candidate->fresh()->next_check_at->equalTo(now()->addDays(7)));
        app(BacklinkVerificationService::class)->run();
        Http::assertSentCount(1);
    }

    public function test_equivalent_paths_match_but_query_and_encoded_slash_stay_distinct(): void
    {
        $candidate = $this->candidate();
        $candidate->update(['target_url' => 'https://housechefcyprus.com/en/recipes/%73oup']);
        Http::fake(['*' => Http::response('<a href="https://housechefcyprus.com/en/recipes/old/../soup#ingredients">Soup</a>', 200, ['Content-Type' => 'text/html'])]);
        $this->assertSame(1, app(BacklinkVerificationService::class)->run()['present']);
        $growth = app(OrganicGrowthService::class);
        $plain = $growth->candidateUrls('https://publisher.org', 'https://housechefcyprus.com/en/recipes/soup');
        $query = $growth->candidateUrls('https://publisher.org', 'https://housechefcyprus.com/en/recipes/soup?variant=2');
        $encoded = $growth->candidateUrls('https://publisher.org', 'https://housechefcyprus.com/en/recipes%2fsoup');
        $this->assertNotSame($plain['target_url'], $query['target_url']);
        $this->assertNotSame($plain['target_url'], $encoded['target_url']);
    }

    private function configureApi(): void
    {
        config(['seo.search_console.enabled' => true, 'seo.search_console.client_id' => 'client', 'seo.search_console.client_secret' => 'secret', 'seo.search_console.refresh_token' => 'refresh']);
    }

    private function candidate(): SeoBacklinkCandidate
    {
        $this->mock(PublicSourceResolver::class, fn ($mock) => $mock->shouldReceive('resolve')->andReturn(['93.184.216.34']));

        return SeoBacklinkCandidate::create(['source_url' => 'https://publisher.org/guide', 'source_host' => 'publisher.org', 'target_url' => 'https://housechefcyprus.com/tarifler', 'deduplication_hash' => hash('sha256', 'test'), 'status' => 'candidate', 'evidence' => ['source' => 'test']]);
    }

    private function report(): array
    {
        return ['property' => 'https://housechefcyprus.com/', 'source' => 'ui_import', 'search_type' => 'web', 'start_date' => '2026-08-14', 'end_date' => '2026-09-10', 'observed_at' => now()->toIso8601String(), 'clicks' => 5, 'impressions' => 91, 'position' => 24.2, 'queries' => [
            ['query' => 'soup recipe', 'clicks' => 1, 'impressions' => 12, 'position' => 12],
            ['query' => 'corba tarifi', 'clicks' => 1, 'impressions' => 12, 'position' => 12],
        ]];
    }
}
