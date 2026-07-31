<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Models\Recipe;
use App\Models\User;
use App\Services\Media\PexelsCandidateMediaService;
use App\Services\Media\PexelsRecipeImageProvider;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Queue;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

final class PexelsRecipeImageProviderTest extends TestCase
{
    use RefreshDatabase;

    public function test_it_is_safe_when_disabled_or_missing_a_key(): void
    {
        $this->seed();
        config()->set('ai_images.providers.pexels', ['enabled' => false, 'key' => null, 'endpoint' => 'https://api.pexels.com']);

        $provider = app(PexelsRecipeImageProvider::class);
        $this->assertSame('not_configured', $provider->health()['image_capability']);
        $this->assertSame([], $provider->candidates(Recipe::query()->firstOrFail()));
    }

    public function test_it_returns_only_review_candidates_with_safe_metadata(): void
    {
        $this->seed();
        $recipe = Recipe::query()->where('slug', 'domates-corbasi')->firstOrFail();
        config()->set('ai_images.providers.pexels', ['enabled' => true, 'key' => 'test-key', 'endpoint' => 'https://api.pexels.com']);
        Http::fake(['api.pexels.com/v1/search*' => Http::response(['photos' => [[
            'id' => 42, 'width' => 1600, 'height' => 1067, 'alt' => 'tomato soup bowl', 'photographer' => 'Test Fotoğrafçı',
            'photographer_url' => 'https://www.pexels.com/@test', 'url' => 'https://www.pexels.com/photo/42',
            'src' => ['landscape' => 'https://images.pexels.com/photos/42/test.jpeg'],
        ], ['id' => 43, 'width' => 200, 'height' => 100, 'src' => ['landscape' => 'https://images.pexels.com/photos/43/test.jpeg']]]])]);

        $candidates = app(PexelsRecipeImageProvider::class)->candidates($recipe);

        $this->assertCount(1, $candidates);
        $this->assertSame('42', $candidates[0]['external_photo_id']);
        $this->assertStringContainsString('Pexels', $candidates[0]['attribution_text']);
        Http::assertSent(fn ($request): bool => $request->hasHeader('Authorization', 'test-key') && $request['orientation'] === 'landscape');
    }

    public function test_it_maps_provider_authentication_errors_without_exposing_response_body(): void
    {
        $this->seed();
        config()->set('ai_images.providers.pexels', ['enabled' => true, 'key' => 'test-key', 'endpoint' => 'https://api.pexels.com']);
        Http::fake(['api.pexels.com/v1/search*' => Http::response(['error' => 'sensitive detail'], 401)]);

        $result = app(PexelsRecipeImageProvider::class)->search(Recipe::query()->firstOrFail());

        $this->assertSame('PEXELS_INVALID_KEY', $result['error_code']);
        $this->assertStringNotContainsString('sensitive', $result['safe_message']);
        $this->assertSame([], $result['candidates']);
    }

    public function test_a_selected_pexels_candidate_is_imported_privately_and_requires_manual_approval(): void
    {
        $this->seed();
        Storage::fake('local');
        Storage::fake('public');
        Queue::fake();
        $recipe = Recipe::query()->where('slug', 'domates-corbasi')->firstOrFail();
        $admin = User::factory()->create(['role' => 'admin']);
        config()->set('ai_images.providers.pexels', ['enabled' => true, 'key' => 'test-key', 'endpoint' => 'https://api.pexels.com']);
        $image = UploadedFile::fake()->image('candidate.jpg', 1600, 900);
        $bytes = (string) file_get_contents((string) $image->getRealPath());
        Http::fake([
            'api.pexels.com/v1/search*' => Http::response(['photos' => [[
                'id' => 42, 'width' => 1600, 'height' => 900, 'alt' => 'tomato soup bowl', 'photographer' => 'Test Fotoğrafçı',
                'photographer_url' => 'https://www.pexels.com/@test', 'url' => 'https://www.pexels.com/photo/42',
                'src' => ['landscape' => 'https://images.pexels.com/photos/42/test.jpeg'],
            ]]]),
            'images.pexels.com/*' => Http::response($bytes, 200, ['Content-Type' => 'image/jpeg']),
        ]);

        $service = app(PexelsCandidateMediaService::class);
        $media = $service->import($recipe, '42');
        $this->assertSame('private', $media->visibility);
        $this->assertSame('pending_review', $media->verification_status);
        $this->assertSame('42', data_get($media->provider_metadata, 'external_photo_id'));
        Storage::disk('local')->assertExists((string) $media->path);

        $service->approve($media->fresh()->load('mediable'), $admin, 'Domates çorbası için onaylı Pexels görseli', 'Tarif, malzeme ve sunum checklist ile incelendi.');
        $media->refresh();
        $this->assertSame('public', $media->visibility);
        $this->assertSame('verified', $media->verification_status);
        Storage::disk('public')->assertExists((string) $media->path);
        $this->assertDatabaseHas('audit_logs', ['action' => 'pexels_candidate.approve']);
    }
}
