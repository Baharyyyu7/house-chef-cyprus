<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Models\User;
use App\Services\Media\BflRecipeImageProvider;
use App\Services\Media\FallbackImageComparisonService;
use App\Services\Media\StabilityRecipeImageProvider;
use Database\Seeders\HouseChefSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Http\Client\Request;
use Illuminate\Support\Facades\Http;
use PHPUnit\Framework\Attributes\DataProvider;
use Tests\TestCase;

final class FallbackImageProviderTest extends TestCase
{
    use RefreshDatabase;

    private const PNG = 'iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAQAAAC1HAwCAAAAC0lEQVR42mP8/x8AAusB9Wl6h3IAAAAASUVORK5CYII=';

    public function test_bfl_uses_returned_polling_and_delivery_urls_then_returns_a_validated_image(): void
    {
        config()->set('ai_images.providers.bfl', ['enabled' => true, 'key' => 'test-key', 'model' => 'flux-2-pro', 'endpoint' => 'https://api.bfl.ai', 'poll_limit' => 2, 'poll_interval_ms' => 0]);
        Http::fake([
            'https://api.bfl.ai/v1/flux-2-pro' => Http::response(['id' => 'safe-request', 'polling_url' => 'https://api.bfl.ai/v1/get_result?id=safe-request']),
            'https://api.bfl.ai/v1/get_result?id=safe-request' => Http::response(['status' => 'Ready', 'result' => ['sample' => 'https://delivery.eu.bfl.ai/safe-image']]),
            'https://delivery.eu.bfl.ai/safe-image' => Http::response(base64_decode(self::PNG), 200, ['Content-Type' => 'image/png']),
        ]);

        $result = app(BflRecipeImageProvider::class)->generate('domates çorbası', 'no text', '4:3');

        $this->assertTrue($result->hasImage());
        $this->assertSame('image/png', $result->mime);
        Http::assertSentCount(3);
        Http::assertSent(fn (Request $request): bool => $request->url() === 'https://api.bfl.ai/v1/flux-2-pro' && $request->hasHeader('x-key') && $request['width'] === 1024 && $request['height'] === 768);
    }

    public function test_bfl_rejects_an_untrusted_polling_url_without_following_it(): void
    {
        config()->set('ai_images.providers.bfl', ['enabled' => true, 'key' => 'test-key', 'model' => 'flux-2-pro', 'endpoint' => 'https://api.bfl.ai', 'poll_limit' => 1, 'poll_interval_ms' => 0]);
        Http::fake(['https://api.bfl.ai/*' => Http::response(['id' => 'safe-request', 'polling_url' => 'http://127.0.0.1/private'])]);

        $result = app(BflRecipeImageProvider::class)->generate('domates çorbası', 'no text', '4:3');

        $this->assertSame('parser_failed', $result->status);
        $this->assertSame('IMAGE_PROVIDER_INVALID_POLLING_URL', $result->errorCode);
        Http::assertSentCount(1);
    }

    public function test_stability_accepts_a_binary_webp_or_png_response_and_uses_private_contracts(): void
    {
        config()->set('ai_images.providers.stability', ['enabled' => true, 'key' => 'test-key', 'model' => 'stable-image-core', 'endpoint' => 'https://api.stability.ai']);
        Http::fake(['https://api.stability.ai/*' => Http::response(base64_decode(self::PNG), 200, ['Content-Type' => 'image/png'])]);

        $result = app(StabilityRecipeImageProvider::class)->generate('domates çorbası', 'no text', '4:3');

        $this->assertTrue($result->hasImage());
        $this->assertSame('image/png', $result->mime);
        Http::assertSentCount(1);
    }

    public function test_stability_maps_moderation_errors_without_exposing_response_content(): void
    {
        config()->set('ai_images.providers.stability', ['enabled' => true, 'key' => 'test-key', 'model' => 'stable-image-core', 'endpoint' => 'https://api.stability.ai']);
        Http::fake(['https://api.stability.ai/*' => Http::response(['detail' => 'raw provider data'], 403)]);
        $blocked = app(StabilityRecipeImageProvider::class)->generate('domates çorbası', 'no text', '4:3');
        $this->assertSame('IMAGE_PROVIDER_MODERATION_BLOCKED', $blocked->errorCode);
        $this->assertStringNotContainsString('raw provider data', (string) $blocked->safeMessage);
    }

    public function test_stability_maps_rate_limit_errors(): void
    {
        config()->set('ai_images.providers.stability', ['enabled' => true, 'key' => 'test-key', 'model' => 'stable-image-core', 'endpoint' => 'https://api.stability.ai']);
        Http::fake(['https://api.stability.ai/*' => Http::response([], 429)]);

        $result = app(StabilityRecipeImageProvider::class)->generate('domates çorbası', 'no text', '4:3');

        $this->assertSame('IMAGE_PROVIDER_RATE_LIMITED', $result->errorCode);
    }

    public function test_bfl_maps_a_failed_poll_response_without_retrying(): void
    {
        config()->set('ai_images.providers.bfl', ['enabled' => true, 'key' => 'test-key', 'model' => 'flux-2-pro', 'endpoint' => 'https://api.bfl.ai', 'poll_limit' => 1, 'poll_interval_ms' => 0]);
        Http::fake(function (Request $request) {
            return str_contains($request->url(), 'get_result')
                ? Http::response(['status' => 'Failed'])
                : Http::response(['id' => 'one', 'polling_url' => 'https://api.bfl.ai/v1/get_result?id=one']);
        });

        $failed = app(BflRecipeImageProvider::class)->generate('domates çorbası', '', '4:3');

        $this->assertSame('IMAGE_PROVIDER_FAILED', $failed->errorCode);
        Http::assertSentCount(2);
    }

    public function test_bfl_maps_a_pending_poll_limit_to_timeout_without_retrying(): void
    {
        config()->set('ai_images.providers.bfl', ['enabled' => true, 'key' => 'test-key', 'model' => 'flux-2-pro', 'endpoint' => 'https://api.bfl.ai', 'poll_limit' => 1, 'poll_interval_ms' => 0]);
        Http::fake(function (Request $request) {
            return str_contains($request->url(), 'get_result')
                ? Http::response(['status' => 'Pending'])
                : Http::response(['id' => 'two', 'polling_url' => 'https://api.bfl.ai/v1/get_result?id=two']);
        });

        $timeout = app(BflRecipeImageProvider::class)->generate('domates çorbası', '', '4:3');

        $this->assertSame('IMAGE_PROVIDER_POLL_TIMEOUT', $timeout->errorCode);
        Http::assertSentCount(2);
    }

    public function test_bfl_timeout_is_safe_and_does_not_retry(): void
    {
        config()->set('ai_images.providers.bfl', ['enabled' => true, 'key' => 'test-key', 'model' => 'flux-2-pro', 'endpoint' => 'https://api.bfl.ai', 'poll_limit' => 1, 'poll_interval_ms' => 0]);
        Http::fake(fn (): never => throw new ConnectionException('network failure'));

        $result = app(BflRecipeImageProvider::class)->generate('domates çorbası', '', '4:3');

        $this->assertSame('IMAGE_PROVIDER_TIMEOUT', $result->errorCode);
        $this->assertStringNotContainsString('network failure', (string) $result->safeMessage);
    }

    public function test_stability_accepts_json_base64_for_future_response_compatibility(): void
    {
        config()->set('ai_images.providers.stability', ['enabled' => true, 'key' => 'test-key', 'model' => 'stable-image-core', 'endpoint' => 'https://api.stability.ai']);
        Http::fake(['https://api.stability.ai/*' => Http::response(['artifacts' => [['base64' => self::PNG, 'mime_type' => 'image/png']]], 200, ['Content-Type' => 'application/json'])]);

        $result = app(StabilityRecipeImageProvider::class)->generate('domates çorbası', 'no text', '4:3');

        $this->assertTrue($result->hasImage());
        $this->assertSame('image/png', $result->mime);
    }

    #[DataProvider('stabilityFailureCases')]
    public function test_stability_maps_http_failures_to_safe_error_codes(int $status, string $errorCode): void
    {
        config()->set('ai_images.providers.stability', ['enabled' => true, 'key' => 'test-key', 'model' => 'stable-image-core', 'endpoint' => 'https://api.stability.ai']);
        Http::fake(['https://api.stability.ai/*' => Http::response(['unsafe' => 'provider response'], $status)]);

        $result = app(StabilityRecipeImageProvider::class)->generate('domates çorbası', '', '4:3');

        $this->assertSame($errorCode, $result->errorCode);
        $this->assertStringNotContainsString('provider response', (string) $result->safeMessage);
    }

    /** @return array<string,array{int,string}> */
    public static function stabilityFailureCases(): array
    {
        return [
            'bad request' => [400, 'IMAGE_PROVIDER_UNAVAILABLE'],
            'unauthorized' => [401, 'IMAGE_PROVIDER_UNAUTHORIZED'],
            'moderation' => [403, 'IMAGE_PROVIDER_MODERATION_BLOCKED'],
            'payload too large' => [413, 'IMAGE_PROVIDER_PAYLOAD_TOO_LARGE'],
            'rejected' => [422, 'IMAGE_PROVIDER_REQUEST_REJECTED'],
            'rate limited' => [429, 'IMAGE_PROVIDER_RATE_LIMITED'],
            'server error' => [500, 'IMAGE_PROVIDER_UNAVAILABLE'],
        ];
    }

    public function test_stability_rejects_non_image_non_json_success_responses(): void
    {
        config()->set('ai_images.providers.stability', ['enabled' => true, 'key' => 'test-key', 'model' => 'stable-image-core', 'endpoint' => 'https://api.stability.ai']);
        Http::fake(['https://api.stability.ai/*' => Http::response('not an image', 200, ['Content-Type' => 'text/plain'])]);

        $result = app(StabilityRecipeImageProvider::class)->generate('domates çorbası', '', '4:3');

        $this->assertSame('IMAGE_PROVIDER_COMPLETED_WITHOUT_IMAGE', $result->errorCode);
    }

    public function test_comparison_does_not_create_a_generation_or_call_a_provider_when_keys_are_missing(): void
    {
        $this->seed(HouseChefSeeder::class);
        $result = app(FallbackImageComparisonService::class)->run();

        $this->assertSame(0, $result['requested']);
        $this->assertSame('not_configured', $result['providers']['bfl']['status']);
        $this->assertSame('not_configured', $result['providers']['stability']['status']);
        Http::assertNothingSent();
    }

    public function test_provider_specific_comparison_only_reports_the_requested_provider_when_not_configured(): void
    {
        $this->seed(HouseChefSeeder::class);

        $result = app(FallbackImageComparisonService::class)->run('bfl');

        $this->assertSame(['bfl'], array_keys($result['providers']));
        $this->assertSame('not_configured', $result['providers']['bfl']['status']);
        Http::assertNothingSent();
    }

    public function test_dry_run_never_calls_an_enabled_provider(): void
    {
        config()->set('ai_images.providers.bfl', ['enabled' => true, 'key' => 'test-key', 'model' => 'flux-2-pro', 'endpoint' => 'https://api.bfl.ai', 'poll_limit' => 1, 'poll_interval_ms' => 0]);

        $result = app(FallbackImageComparisonService::class)->dryRun();

        $this->assertSame('ready_for_controlled_call', $result['providers']['bfl']['status']);
        $this->assertSame(0, $result['requested']);
        Http::assertNothingSent();
    }

    public function test_per_provider_hard_limit_blocks_a_live_request_before_http_is_called(): void
    {
        $this->seed(HouseChefSeeder::class);
        config()->set('ai_images.providers.bfl', ['enabled' => true, 'key' => 'test-key', 'model' => 'flux-2-pro', 'endpoint' => 'https://api.bfl.ai', 'poll_limit' => 1, 'poll_interval_ms' => 0]);
        config()->set('ai_images.fallback_provider_comparison.max_requests_per_provider', 0);

        $result = app(FallbackImageComparisonService::class)->run('bfl');

        $this->assertSame('budget_hard_stop', $result['providers']['bfl']['status']);
        $this->assertSame(0, $result['requested']);
        Http::assertNothingSent();
    }

    public function test_admin_comparison_surface_only_renders_safe_missing_provider_states_without_http_calls(): void
    {
        $this->seed(HouseChefSeeder::class);
        $admin = User::factory()->create(['role' => 'admin', 'email_verified_at' => now()]);

        $response = $this->actingAs($admin)->get(route('admin.ai-image-studio.index'));
        $response->assertOk();
        $response->assertSee('Kontrollü karşılaştırma');
        $response->assertSee('BFL API anahtarı yapılandırılmadı.');
        $response->assertSee('Stability API anahtarı yapılandırılmadı.');
        $response->assertDontSee('test-key');

        Http::assertNothingSent();
    }
}
