<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Models\SeoBacklinkCandidate;
use App\Services\Seo\BacklinkVerificationService;
use App\Services\Seo\PublicSourceResolver;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

final class BacklinkDailyBudgetTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->travelTo(now()->setDate(2026, 9, 27)->setTime(12, 0));
        config([
            'app.url' => 'https://housechefcyprus.com',
            'seo.backlink_verification_daily_limit' => 2,
        ]);
        Http::preventStrayRequests();
        Http::fake(['*' => Http::response('<html><p>No matching link.</p></html>', 200, ['Content-Type' => 'text/html'])]);
        $this->mock(PublicSourceResolver::class, fn ($mock) => $mock->shouldReceive('resolve')->andReturn(['93.184.216.34']));
    }

    public function test_repeated_runs_share_one_daily_budget(): void
    {
        foreach (['one', 'two', 'three', 'four'] as $name) {
            $this->candidate($name);
        }
        $service = app(BacklinkVerificationService::class);

        $this->assertSame(1, $service->run(1)['checked']);
        $this->assertSame(1, $service->run(50)['checked']);
        $third = $service->run(50);

        $this->assertSame(0, $third['checked']);
        $this->assertSame('deferred', $third['status']);
        $this->assertSame('daily_limit', $third['reason']);
        $this->assertSame(2, SeoBacklinkCandidate::query()->whereNotNull('checked_at')->count());
        Http::assertSentCount(2);
    }

    public function test_rejected_records_checked_today_consume_budget_but_yesterday_does_not(): void
    {
        $this->candidate('checked-today')->update([
            'status' => 'rejected',
            'check_status' => 'not_found',
            'checked_at' => now()->subHour(),
        ]);
        $this->candidate('checked-yesterday')->update([
            'status' => 'rejected',
            'check_status' => 'not_found',
            'checked_at' => now()->subDay(),
        ]);
        $this->candidate('pending-one');
        $this->candidate('pending-two');

        $result = app(BacklinkVerificationService::class)->run(50);

        $this->assertSame(1, $result['checked']);
        $this->assertSame(2, SeoBacklinkCandidate::query()->where('checked_at', '>=', now()->startOfDay())->count());
        Http::assertSentCount(1);
    }

    public function test_zero_daily_budget_sends_no_requests_and_leaves_candidate_unchecked(): void
    {
        config(['seo.backlink_verification_daily_limit' => 0]);
        $candidate = $this->candidate('pending');

        $result = app(BacklinkVerificationService::class)->run(50);

        $this->assertSame('deferred', $result['status']);
        $this->assertSame('daily_limit', $result['reason']);
        $this->assertSame(0, $result['checked']);
        $this->assertNull($candidate->fresh()->checked_at);
        Http::assertNothingSent();
    }

    public function test_new_day_opens_a_fresh_budget_without_rechecking_recent_sources(): void
    {
        config(['seo.backlink_verification_daily_limit' => 1]);
        $first = $this->candidate('first');
        $second = $this->candidate('second');
        $service = app(BacklinkVerificationService::class);

        $this->assertSame(1, $service->run(50)['checked']);
        $firstCheckedAt = $first->fresh()->checked_at;
        $this->assertNotNull($firstCheckedAt);
        $this->assertNull($second->fresh()->checked_at);

        $this->travel(1)->days();

        $this->assertSame(1, $service->run(50)['checked']);
        $this->assertTrue($first->fresh()->checked_at->equalTo($firstCheckedAt));
        $this->assertTrue($second->fresh()->checked_at->equalTo(now()));
        Http::assertSentCount(2);
    }

    private function candidate(string $name): SeoBacklinkCandidate
    {
        $host = $name.'.publisher.org';

        return SeoBacklinkCandidate::create([
            'source_url' => 'https://'.$host.'/guide',
            'source_host' => $host,
            'target_url' => 'https://housechefcyprus.com/tarifler',
            'deduplication_hash' => hash('sha256', $host),
            'status' => 'candidate',
            'evidence' => ['source' => 'test'],
        ]);
    }
}
