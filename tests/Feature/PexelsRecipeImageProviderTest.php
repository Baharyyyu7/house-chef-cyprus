<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Models\Recipe;
use App\Services\Media\PexelsRecipeImageProvider;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
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
        Http::assertSent(fn ($request): bool => $request->hasHeader('Authorization', 'Bearer test-key') && $request['orientation'] === 'landscape');
    }
}
