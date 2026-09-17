<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Models\Recipe;
use App\Services\Seo\IndexNowService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

final class IndexNowTest extends TestCase
{
    use RefreshDatabase;

    public function test_indexnow_key_endpoint_serves_key(): void
    {
        $key = (string) config('services.indexnow.key', IndexNowService::DEFAULT_KEY);
        $response = $this->get('/' . $key . '.txt');

        $response->assertOk()
            ->assertHeader('Content-Type', 'text/plain; charset=UTF-8')
            ->assertSee($key);
    }

    public function test_indexnow_service_submits_payload_to_api(): void
    {
        Http::fake([
            'https://api.indexnow.org/*' => Http::response(null, 200),
        ]);

        Recipe::query()->create([
            'title' => 'Test Tarifi',
            'slug' => 'test-tarifi',
            'status' => 'published',
            'verification_status' => 'verified',
            'prep_minutes' => 10,
            'cook_minutes' => 10,
            'servings' => 2,
        ]);

        $service = app(IndexNowService::class);
        $result = $service->submit();

        $this->assertTrue($result['success']);
        $this->assertSame(200, $result['status']);
        $this->assertGreaterThan(0, $result['count']);

        Http::assertSent(function ($request) {
            return $request->url() === 'https://api.indexnow.org/indexnow'
                && isset($request['host'])
                && isset($request['key'])
                && !empty($request['urlList']);
        });
    }

    public function test_indexnow_command_runs_dry_run_successfully(): void
    {
        // Sağlayıcı yapılandırmada kapalıyken komut erken dönüp "kapalı"
        // uyarısı basıyor - doğru davranış, çünkü kapalı bir entegrasyon
        // kuru çalıştırmada bile kuyruk raporlamamalı. Test kuyruk çıktısını
        // ölçüyor, dolayısıyla sağlayıcıyı açması gerekiyor.
        config()->set('seo.indexnow.enabled', true);

        $this->artisan('seo:indexnow-submit --dry-run')
            ->expectsOutputToContain('IndexNow kuyruğu')
            ->expectsOutputToContain('[DRY-RUN] İstek gönderilmedi.')
            ->assertExitCode(0);
    }

    public function test_rate_limit_stops_chunks_and_defers_subsequent_calls(): void
    {
        Http::fakeSequence()->push(null, 200)->push(null, 429, ['Retry-After' => '120'])->push(null, 200);
        $urls = array_map(fn ($id) => "https://localhost/tarifler/{$id}", range(1, 110));
        $service = app(IndexNowService::class);
        $result = $service->submit($urls);
        $this->assertFalse($result['success']);
        $this->assertSame(429, $result['status']);
        $this->assertSame(50, $result['submitted']);
        $this->assertSame(array_slice($urls, 0, 50), $result['urls']);
        $this->assertSame(120, $result['retry_after']);
        Http::assertSentCount(2);
        $this->assertSame(0, $service->submit($urls)['submitted']);
        Http::assertSentCount(2);
        $this->travel(121)->seconds();
        $this->assertTrue($service->submit([$urls[50]])['success']);
        Http::assertSentCount(3);
    }

    public function test_accepted_validation_pending_response_and_duplicates_are_reported_correctly(): void
    {
        Http::fakeSequence()->push(null, 202);
        $result = app(IndexNowService::class)->submit(['https://localhost/a', 'https://localhost/a']);
        $this->assertTrue($result['success']);
        $this->assertSame(202, $result['status']);
        $this->assertSame(1, $result['count']);
        Http::assertSent(fn ($request) => $request['urlList'] === ['https://localhost/a']);
    }

    public function test_rejected_http_status_is_not_reported_as_success(): void
    {
        Http::fakeSequence()->push(null, 403)->push(null, 200);
        $result = app(IndexNowService::class)->submit(array_map(fn ($id) => "https://localhost/{$id}", range(1, 60)));
        $this->assertFalse($result['success']);
        $this->assertSame(403, $result['status']);
        $this->assertSame(0, $result['count']);
        Http::assertSentCount(1);
    }

    public function test_connection_failure_preserves_only_previously_accepted_urls(): void
    {
        $calls = 0;
        Http::fake(function () use (&$calls) {
            if (++$calls === 2) {
                throw new \Illuminate\Http\Client\ConnectionException('timeout');
            }
            return Http::response(null, 200);
        });
        $result = app(IndexNowService::class)->submit(array_map(fn ($id) => "https://localhost/{$id}", range(1, 60)));
        $this->assertFalse($result['success']);
        $this->assertSame(500, $result['status']);
        $this->assertSame(50, $result['submitted']);
    }

    public function test_command_returns_failure_in_json_and_text_modes(): void
    {
        config()->set('seo.indexnow.enabled', true);
        Http::fake(['https://api.indexnow.org/*' => Http::response(null, 403)]);
        $this->artisan('seo:indexnow-submit --json')->expectsOutputToContain('"success":false')->assertExitCode(1);
        $this->artisan('seo:indexnow-submit')->expectsOutputToContain('HTTP 403')->assertExitCode(1);
    }

    public function test_retry_after_http_date_is_respected(): void
    {
        $this->freezeTime();
        Http::fakeSequence()->push(null, 429, ['Retry-After' => now()->addMinutes(5)->toRfc7231String()]);
        $result = app(IndexNowService::class)->submit(['https://localhost/a']);
        $this->assertSame(300, $result['retry_after']);
        $this->assertFalse($result['success']);
    }

    public function test_indeks_disi_tarif_bildirilmez(): void
    {
        config()->set('seo.indexnow.enabled', true);

        $acik = Recipe::query()->create([
            'title' => 'Gerçek Tarif', 'slug' => 'gercek-tarif-indexnow', 'excerpt' => 'Özet.', 'description' => 'Açıklama.',
            'status' => 'published', 'verification_status' => 'verified', 'published_at' => now()->subDay(),
        ]);
        $kapali = Recipe::query()->create([
            'title' => 'Şablon Tarif', 'slug' => 'sablon-tarif-indexnow', 'excerpt' => 'Özet.', 'description' => 'Açıklama.',
            'status' => 'published', 'verification_status' => 'verified', 'published_at' => now()->subDay(),
        ]);
        app(\App\Services\Seo\SeoMetadataService::class)->update($kapali, ['robots' => 'noindex,follow']);

        $urls = app(\App\Services\Seo\IndexNowService::class)->collectRecentUrls();

        $this->assertContains(route('recipes.show', $acik), $urls);
        $this->assertNotContains(route('recipes.show', $kapali), $urls, 'noindex taşıyan sayfa arama motoruna bildirilmemeli.');
    }
}
