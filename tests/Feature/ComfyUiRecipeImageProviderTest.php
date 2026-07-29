<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Services\Media\ComfyUiRecipeImageProvider;
use App\Services\Media\LocalComfyUiImageGenerationService;
use Database\Seeders\HouseChefSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\Client\Request;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

final class ComfyUiRecipeImageProviderTest extends TestCase
{
    use RefreshDatabase;

    private const PNG = 'iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAQAAAC1HAwCAAAAC0lEQVR42mP8/x8AAusB9Wl6h3IAAAAASUVORK5CYII=';

    public function test_disabled_comfyui_never_sends_a_local_generation_request(): void
    {
        config()->set('ai_images.providers.comfyui.enabled', false);

        $result = app(ComfyUiRecipeImageProvider::class)->generate('domates çorbası', 'metin yok', '4:3');

        $this->assertSame('LOCAL_IMAGE_PROVIDER_NOT_CONFIGURED', $result->errorCode);
        Http::assertNothingSent();
    }

    public function test_comfyui_submits_the_workflow_polls_history_and_downloads_only_returned_output(): void
    {
        config()->set('ai_images.providers.comfyui', [
            'enabled' => true,
            'endpoint' => 'http://127.0.0.1:8188',
            'workflow_path' => resource_path('workflows/comfyui/flux1-schnell-api.json'),
            'model' => 'flux1-schnell',
            'timeout' => 30,
            'poll_interval_ms' => 0,
        ]);
        Http::fake([
            'http://127.0.0.1:8188/prompt' => Http::response(['prompt_id' => 'local-safe-id']),
            'http://127.0.0.1:8188/history/local-safe-id' => Http::response([
                'local-safe-id' => ['status' => ['status' => 'success'], 'outputs' => ['9' => ['images' => [['filename' => 'safe.webp', 'subfolder' => '', 'type' => 'output']]]]],
            ]),
            'http://127.0.0.1:8188/view*' => Http::response(base64_decode(self::PNG), 200, ['Content-Type' => 'image/png']),
        ]);

        $result = app(ComfyUiRecipeImageProvider::class)->generate('domates çorbası', 'metin yok', '4:3');

        $this->assertTrue($result->hasImage());
        $this->assertSame('local-safe-id', $result->providerRequestId);
        Http::assertSent(fn (Request $request): bool => $request->url() === 'http://127.0.0.1:8188/prompt' && $request['prompt']['6']['inputs']['width'] === 1024 && $request['prompt']['6']['inputs']['height'] === 768);
        Http::assertSentCount(3);
    }

    public function test_local_generation_service_creates_no_record_when_the_local_provider_is_unavailable(): void
    {
        $this->seed(HouseChefSeeder::class);

        $result = app(LocalComfyUiImageGenerationService::class)->generateTomatoSoup();

        $this->assertSame('not_configured', $result['status']);
        $this->assertDatabaseCount('ai_image_generations', 0);
        Http::assertNothingSent();
    }

    public function test_comfyui_batch_dry_run_lists_only_verified_published_recipes_without_calling_the_service(): void
    {
        $this->seed(HouseChefSeeder::class);
        $this->artisan('recipe-images:comfyui-batch', ['--limit' => 1, '--dry-run' => true])
            ->assertSuccessful();

        Http::assertNothingSent();
    }
}
