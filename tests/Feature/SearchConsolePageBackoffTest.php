<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Services\Seo\SearchConsoleMeasurementService;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use PHPUnit\Framework\Attributes\DataProvider;
use RuntimeException;
use Tests\TestCase;

final class SearchConsolePageBackoffTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        $this->travelTo(now()->setDate(2026, 9, 26)->setTime(12, 0));
        config([
            'seo.search_console.enabled' => true,
            'seo.search_console.client_id' => 'test-client',
            'seo.search_console.client_secret' => 'test-secret',
            'seo.search_console.refresh_token' => 'test-refresh',
        ]);
        Cache::forget('seo:gsc:retry-after');
        Http::preventStrayRequests();
    }

    public static function retryResponses(): array
    {
        return [
            'rate limit on token, seconds' => [429, false, false],
            'unavailable page query, HTTP date' => [503, true, true],
        ];
    }

    #[DataProvider('retryResponses')]
    public function test_retry_after_stops_all_page_requests_until_expiry(int $status, bool $onAnalytics, bool $dateHeader): void
    {
        $until = now()->addMinutes(10);
        $header = $dateHeader ? $until->copy()->utc()->format('D, d M Y H:i:s').' GMT' : '600';
        $tokens = Http::sequence();
        $analytics = Http::sequence();
        if ($onAnalytics) {
            $tokens->push(['access_token' => 'test-token']);
            $analytics->push([], $status, ['Retry-After' => $header]);
        } else {
            $tokens->push([], $status, ['Retry-After' => $header]);
        }
        $tokens->push(['access_token' => 'test-token'])->push(['access_token' => 'test-token']);
        $analytics->push(['rows' => [
            ['keys' => ['https://housechefcyprus.com/en/recipes/example'], 'clicks' => 2, 'impressions' => 30, 'position' => 7.5],
        ]])->push(['rows' => [
            ['keys' => ['cyprus food'], 'clicks' => 2, 'impressions' => 30, 'position' => 7.5],
        ]]);
        Http::fake([
            'oauth2.googleapis.com/*' => $tokens,
            'www.googleapis.com/webmasters/*' => $analytics,
        ]);
        $service = app(SearchConsoleMeasurementService::class);

        try {
            if ($onAnalytics) {
                $service->pageQueries('https://housechefcyprus.com/en/recipes/example');
            } else {
                $service->pageRowsBetween('2026-08-26', '2026-09-22');
            }
            $this->fail('The first rate-limited response must still report failure.');
        } catch (RuntimeException $e) {
            $this->assertSame('API_REQUEST_FAILED', $e->getMessage());
        }

        $this->assertSame($until->toIso8601String(), Cache::get('seo:gsc:retry-after'));
        $this->assertSame([], $service->pageRowsBetween('2026-08-26', '2026-09-22'));
        $this->assertSame([], $service->pageRows());
        $this->assertSame([], $service->pageQueries('https://housechefcyprus.com/en/recipes/example'));
        Http::assertSentCount($onAnalytics ? 2 : 1);

        $this->travel(601)->seconds();
        $this->assertSame(30, $service->pageRowsBetween('2026-08-26', '2026-09-22')[0]['impressions']);
        $this->assertSame(2, $service->pageQueries('https://housechefcyprus.com/en/recipes/example')[0]['clicks']);
        Http::assertSentCount($onAnalytics ? 6 : 5);
    }
}
