<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Models\Recipe;
use App\Models\RecipeMediaCandidate;
use App\Models\User;
use App\Services\Media\UnsplashCandidateMediaService;
use App\Services\Media\UnsplashEditorialReviewService;
use App\Services\Media\UnsplashRecipeImageProvider;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

final class UnsplashRecipeImageProviderTest extends TestCase
{
    use RefreshDatabase;

    public function test_it_is_safe_when_disabled_or_missing_an_access_key(): void
    {
        $this->seed();
        config()->set('ai_images.providers.unsplash', $this->settings(false, null));
        $result = app(UnsplashRecipeImageProvider::class)->search(Recipe::query()->firstOrFail());
        $this->assertSame('disabled', $result['status']);
        $this->assertSame([], $result['candidates']);
    }

    public function test_it_maps_official_candidates_with_attribution_utm_and_rate_limit_without_raw_payload(): void
    {
        $this->seed();
        config()->set('ai_images.providers.unsplash', $this->settings());
        Http::fake(['api.unsplash.com/search/photos*' => Http::response(['results' => [$this->photo('a')]], 200, ['X-Ratelimit-Limit' => '50', 'X-Ratelimit-Remaining' => '49'])]);
        $result = app(UnsplashRecipeImageProvider::class)->search(Recipe::query()->firstOrFail());
        $candidate = $result['candidates'][0];
        $this->assertSame('a', $candidate['external_photo_id']);
        $this->assertStringContainsString('Unsplash', $candidate['attribution_text']);
        $this->assertStringContainsString('utm_source=house-chef-cyprus', $candidate['photographer_url']);
        $this->assertSame(49, $result['rate_limit']['remaining']);
        Http::assertSent(fn ($request): bool => $request->hasHeader('Authorization', 'Client-ID test-access-key'));
    }

    public function test_search_only_creates_review_metadata_and_never_tracks_or_publishes(): void
    {
        $this->seed();
        config()->set('ai_images.providers.unsplash', $this->settings());
        $recipe = Recipe::query()->firstOrFail();
        Http::fake(['api.unsplash.com/search/photos*' => Http::response(['results' => [$this->photo('review')]])]);
        app(UnsplashEditorialReviewService::class)->search($recipe->load(['ingredients', 'dietaryTags']));
        $this->assertDatabaseHas('recipe_media_candidates', ['recipe_id' => $recipe->id, 'provider' => 'unsplash', 'external_photo_id' => 'review', 'status' => 'pending_review']);
        $this->assertSame(0, $recipe->media()->where('source_name', 'Unsplash')->count());
        Http::assertSentCount(1);
    }

    public function test_human_approval_tracks_once_and_uses_safe_external_hotlink(): void
    {
        $this->seed();
        config()->set('ai_images.providers.unsplash', $this->settings());
        $recipe = Recipe::query()->firstOrFail();
        $admin = User::factory()->create(['role' => 'admin']);
        $candidate = $this->candidate($recipe, 'approved');
        Http::fake(['api.unsplash.com/photos/approved/download*' => Http::response([], 200)]);
        $media = app(UnsplashCandidateMediaService::class)->approve($candidate->load('recipe'), $admin, 'Bitmiş yemek için açıklayıcı alt metin', 'İnsan editoryal kontrolü tamamlandı.');
        $this->assertSame('external_hotlinked_media', $media->kind);
        $this->assertSame('public', $media->visibility);
        $this->assertSame('verified', $media->verification_status);
        $this->assertSame('https://images.unsplash.com/photo-approved', $media->publicUrl());
        $this->assertStringContainsString('w=768', $media->responsiveUrl(768));
        $this->assertNotNull($media->unsplashAttribution());
        app(UnsplashCandidateMediaService::class)->approve($candidate->fresh()->load(['recipe', 'media']), $admin, 'Bitmiş yemek için açıklayıcı alt metin', 'İnsan editoryal kontrolü tamamlandı.');
        Http::assertSentCount(1);
    }

    public function test_unapproved_or_unsafe_hotlinks_cannot_leak_publicly_and_members_are_forbidden(): void
    {
        $this->seed();
        $recipe = Recipe::query()->firstOrFail();
        $member = User::factory()->create(['role' => 'member']);
        $candidate = $this->candidate($recipe, 'unsafe', ['source_url' => 'https://evil.example/image.jpg']);
        $this->actingAs($member)->post(route('admin.ai-image-studio.unsplash-candidates.select', $candidate))->assertForbidden();
        $media = $recipe->media()->create(['kind' => 'external_hotlinked_media', 'disk' => 'external', 'visibility' => 'public', 'source_name' => 'Unsplash', 'source_url' => 'https://evil.example/image.jpg', 'verification_status' => 'verified']);
        $this->assertNull($media->publicUrl());
    }

    /** @return array<string,mixed> */
    private function settings(bool $enabled = true, ?string $key = 'test-access-key'): array
    {
        return ['enabled' => $enabled, 'access_key' => $key, 'secret_key' => null, 'endpoint' => 'https://api.unsplash.com', 'app_name' => 'house-chef-cyprus', 'max_results' => 5];
    }

    /** @return array<string,mixed> */
    private function photo(string $id): array
    {
        return ['id' => $id, 'width' => 1600, 'height' => 900, 'orientation' => 'landscape', 'color' => '#abcdef', 'blur_hash' => 'abc', 'description' => 'Tomato soup', 'alt_description' => 'Tomato soup in a bowl', 'urls' => ['regular' => 'https://images.unsplash.com/photo-'.$id, 'small' => 'https://images.unsplash.com/small-'.$id, 'thumb' => 'https://images.unsplash.com/thumb-'.$id], 'links' => ['html' => 'https://unsplash.com/photos/'.$id, 'download_location' => 'https://api.unsplash.com/photos/'.$id.'/download'], 'user' => ['name' => 'Ada Fotoğrafçı', 'links' => ['html' => 'https://unsplash.com/@ada']]];
    }

    private function candidate(Recipe $recipe, string $id, array $attributes = []): RecipeMediaCandidate
    {
        return RecipeMediaCandidate::query()->create(array_merge(['recipe_id' => $recipe->id, 'provider' => 'unsplash', 'external_photo_id' => $id, 'rank' => 1, 'review_score' => 90, 'status' => 'selected_for_review', 'photographer' => 'Ada Fotoğrafçı', 'photographer_url' => 'https://unsplash.com/@ada?utm_source=house-chef-cyprus&utm_medium=referral', 'external_page_url' => 'https://unsplash.com/photos/'.$id.'?utm_source=house-chef-cyprus&utm_medium=referral', 'source_url' => 'https://images.unsplash.com/photo-'.$id, 'small_url' => 'https://images.unsplash.com/small-'.$id, 'thumb_url' => 'https://images.unsplash.com/thumb-'.$id, 'download_location' => 'https://api.unsplash.com/photos/'.$id.'/download', 'alt_text' => 'Tomato soup in a bowl', 'width' => 1600, 'height' => 900], $attributes));
    }
}
