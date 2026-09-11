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
        $this->artisan('seo:indexnow-submit --dry-run')
            ->expectsOutputToContain('IndexNow kuyruğu')
            ->expectsOutputToContain('[DRY-RUN] İstek gönderilmedi.')
            ->assertExitCode(0);
    }
}
