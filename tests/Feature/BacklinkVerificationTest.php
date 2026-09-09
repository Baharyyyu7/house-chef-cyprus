<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Models\SeoBacklinkCandidate;
use App\Services\Seo\BacklinkVerificationService;
use App\Services\Seo\PublicSourceResolver;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

final class BacklinkVerificationTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        config(['app.url' => 'https://housechefcyprus.com']);
        Http::preventStrayRequests();
        $this->mock(PublicSourceResolver::class, fn ($mock) => $mock->shouldReceive('resolve')->andReturn(['93.184.216.34']));
    }

    public function test_detects_actual_anchor_and_keeps_editorial_status_separate(): void
    {
        $candidate = $this->candidate();
        Http::fake(['*' => Http::response('<html><a href="https://housechefcyprus.com/tarifler#menu" rel="nofollow sponsored">Tarifler</a></html>', 200, ['Content-Type' => 'text/html'])]);
        app(BacklinkVerificationService::class)->run(3);
        $candidate->refresh();
        $this->assertSame('present', $candidate->check_status);
        $this->assertSame('nofollow sponsored', $candidate->link_rel);
        $this->assertSame('candidate', $candidate->status);
        $this->assertNotNull($candidate->checked_at);
        app(BacklinkVerificationService::class)->run(3);
        Http::assertSentCount(1);
    }

    public function test_plain_text_and_similar_host_are_not_backlinks(): void
    {
        $candidate = $this->candidate();
        Http::fake(['*' => Http::response('<p>https://housechefcyprus.com/tarifler</p><a href="https://housechefcyprus.com.attacker.org/tarifler">No</a>', 200, ['Content-Type' => 'text/html'])]);
        app(BacklinkVerificationService::class)->run(1);
        $this->assertSame('not_found', $candidate->fresh()->check_status);
    }

    public function test_redirect_is_not_followed_and_not_reported_as_missing_link(): void
    {
        $candidate = $this->candidate();
        Http::fake(['*' => Http::response('', 302, ['Location' => 'http://127.0.0.1/private'])]);
        app(BacklinkVerificationService::class)->run(1);
        $this->assertSame('redirect', $candidate->fresh()->check_status);
        Http::assertSentCount(1);
    }

    public function test_private_dns_is_never_requested(): void
    {
        $this->mock(PublicSourceResolver::class, fn ($mock) => $mock->shouldReceive('resolve')->andReturn([]));
        $candidate = $this->candidate();
        app(BacklinkVerificationService::class)->run(1);
        $this->assertSame('blocked', $candidate->fresh()->check_status);
        Http::assertNothingSent();
    }

    public function test_rate_limit_stops_the_batch_and_honors_retry_after(): void
    {
        $first = $this->candidate();
        $second = $this->candidate('/second');
        Http::fake(['*' => Http::response('', 429, ['Retry-After' => '7200'])]);
        app(BacklinkVerificationService::class)->run(3);
        $this->assertSame('rate_limited', $first->fresh()->check_status);
        $this->assertTrue($first->fresh()->next_check_at->gte(now()->addSeconds(7198)));
        $this->assertNull($second->fresh()->checked_at);
        app(BacklinkVerificationService::class)->run(3);
        Http::assertSentCount(1);
    }

    private function candidate(string $path = '/guide'): SeoBacklinkCandidate
    {
        return SeoBacklinkCandidate::create(['source_url' => 'https://publisher.org'.$path, 'source_host' => 'publisher.org', 'target_url' => 'https://housechefcyprus.com/tarifler', 'deduplication_hash' => hash('sha256', $path), 'status' => 'candidate', 'evidence' => ['source' => 'test']]);
    }
}
