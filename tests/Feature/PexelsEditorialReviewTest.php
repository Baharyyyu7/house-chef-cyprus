<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Models\Recipe;
use App\Models\RecipeMediaCandidate;
use App\Models\User;
use App\Services\Media\PexelsCandidateMediaService;
use App\Services\Media\PexelsEditorialReviewService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Queue;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

final class PexelsEditorialReviewTest extends TestCase
{
    use RefreshDatabase;

    public function test_scan_persists_ranked_review_metadata_without_importing_or_publishing_media(): void
    {
        $this->seed();
        $recipe = Recipe::query()->where('slug', 'domates-corbasi')->firstOrFail();
        $mediaBefore = $recipe->media()->count();
        $this->fakePexelsWithUniquePhotos();

        $result = app(PexelsEditorialReviewService::class)->scan(1);

        $this->assertSame(10, $result['recipes']);
        $this->assertSame($mediaBefore, $recipe->media()->count());
        $this->assertDatabaseHas('recipe_media_candidates', ['recipe_id' => $recipe->id, 'status' => 'pending_review']);
        $this->assertDatabaseHas('recipe_media_candidates', ['recipe_id' => $recipe->id, 'status' => 'auto_rejected', 'rejection_code' => 'NOT_A_SINGLE_DISH']);
    }

    public function test_only_admin_can_select_a_candidate_and_selection_does_not_publish_it(): void
    {
        $this->seed();
        $recipe = Recipe::query()->firstOrFail();
        $candidate = $this->candidate($recipe, 'review-42');
        $member = User::factory()->create(['role' => 'member']);
        $admin = User::factory()->create(['role' => 'admin']);

        $this->actingAs($member)->withSession(['_token' => 'test-csrf'])->post(route('admin.ai-image-studio.pexels-candidates.select', $candidate), ['_token' => 'test-csrf'])->assertForbidden();
        $this->actingAs($admin)->withSession(['_token' => 'test-csrf'])->post(route('admin.ai-image-studio.pexels-candidates.select', $candidate), ['_token' => 'test-csrf'])->assertRedirect();

        $candidate->refresh();
        $this->assertSame('selected_for_review', $candidate->status);
        $this->assertNull($candidate->media_id);
        $this->assertSame(0, $recipe->media()->where('source_name', 'Pexels')->where('visibility', 'public')->count());
    }

    public function test_fallback_can_be_kept_without_exposing_any_candidate(): void
    {
        $this->seed();
        $recipe = Recipe::query()->firstOrFail();
        $this->candidate($recipe, 'review-99', 25);
        $admin = User::factory()->create(['role' => 'admin']);

        $this->actingAs($admin)->withSession(['_token' => 'test-csrf'])->post(route('admin.ai-image-studio.pexels-candidates.keep-fallback', $recipe), ['_token' => 'test-csrf'])->assertRedirect();

        $this->assertDatabaseHas('recipe_media_candidates', ['recipe_id' => $recipe->id, 'status' => 'fallback_kept']);
        $this->assertSame('needs_real_media', $recipe->fresh()->image_status);
    }

    public function test_admin_can_open_a_ranked_review_batch_without_triggering_a_public_image_request(): void
    {
        $this->seed();
        $recipe = Recipe::query()->firstOrFail();
        $this->candidate($recipe, 'review-77', 76, ['alt_text' => 'İnceleme adayı']);
        $admin = User::factory()->create(['role' => 'admin']);

        $this->actingAs($admin)->get(route('admin.ai-image-studio.index', ['review_batch' => 1]))
            ->assertOk()->assertSee('Pexels Editoryal Review Batch 1')->assertSee('İnceleme adayı');
    }

    public function test_scan_limits_each_recipe_to_the_top_three_candidates_when_requested(): void
    {
        $this->seed();
        $this->fakePexelsWithUniquePhotos(4);

        app(PexelsEditorialReviewService::class)->scan(1, 3);

        $recipe = Recipe::query()->where('slug', 'domates-corbasi')->firstOrFail();
        $this->assertSame(3, RecipeMediaCandidate::query()->where('recipe_id', $recipe->id)->count());
    }

    public function test_duplicate_pexels_id_for_another_recipe_is_automatically_rejected(): void
    {
        $this->seed();
        $recipe = Recipe::query()->where('slug', 'domates-corbasi')->firstOrFail();
        $otherRecipe = Recipe::query()->whereKeyNot($recipe->id)->firstOrFail();
        $this->candidate($otherRecipe, 'already-used');
        config()->set('ai_images.providers.pexels', ['enabled' => true, 'key' => 'test-key', 'endpoint' => 'https://api.pexels.com']);
        Http::fake(['api.pexels.com/v1/search*' => Http::response(['photos' => [
            ['id' => 'already-used', 'width' => 1600, 'height' => 900, 'alt' => 'Tomato soup', 'photographer' => 'Test', 'src' => ['landscape' => 'https://images.pexels.com/photos/already-used/test.jpeg']],
        ]])]);

        app(PexelsEditorialReviewService::class)->scan(1);

        $this->assertDatabaseHas('recipe_media_candidates', ['recipe_id' => $recipe->id, 'external_photo_id' => 'already-used', 'status' => 'auto_rejected', 'rejection_code' => 'DUPLICATE_EXTERNAL_ID']);
    }

    public function test_repeated_scan_keeps_the_same_duplicate_candidate_rejected_without_changing_the_canonical_candidate(): void
    {
        $this->seed();
        $recipe = Recipe::query()->where('slug', 'domates-corbasi')->firstOrFail();
        $otherRecipe = Recipe::query()->whereKeyNot($recipe->id)->firstOrFail();
        $this->candidate($otherRecipe, 'stable-duplicate');
        config()->set('ai_images.providers.pexels', ['enabled' => true, 'key' => 'test-key', 'endpoint' => 'https://api.pexels.com']);
        Http::fake(['api.pexels.com/v1/search*' => Http::response(['photos' => [
            ['id' => 'stable-duplicate', 'width' => 1600, 'height' => 900, 'alt' => 'Tomato soup', 'photographer' => 'Test', 'src' => ['landscape' => 'https://images.pexels.com/photos/stable-duplicate/test.jpeg']],
        ]])]);

        app(PexelsEditorialReviewService::class)->scan(1);
        app(PexelsEditorialReviewService::class)->scan(1);

        $this->assertDatabaseHas('recipe_media_candidates', ['recipe_id' => $recipe->id, 'external_photo_id' => 'stable-duplicate', 'status' => 'auto_rejected', 'rejection_code' => 'DUPLICATE_EXTERNAL_ID']);
        $this->assertDatabaseHas('recipe_media_candidates', ['recipe_id' => $otherRecipe->id, 'external_photo_id' => 'stable-duplicate', 'status' => 'pending_review']);
    }

    public function test_selected_candidate_is_imported_from_the_persisted_review_metadata_without_a_second_search(): void
    {
        $this->seed();
        Storage::fake('local');
        Queue::fake();
        $recipe = Recipe::query()->where('slug', 'domates-corbasi')->firstOrFail();
        $candidate = $this->candidate($recipe, 'stored-photo', 91, [
            'status' => 'selected_for_review',
            'source_url' => 'https://images.pexels.com/photos/stored-photo/test.jpeg',
            'alt_text' => 'Domates çorbası',
            'photographer' => 'Editoryal test',
            'pexels_page_url' => 'https://www.pexels.com/photo/stored-photo',
        ]);
        // A valid 1×1 JPEG avoids allocating a GD bitmap in an already large
        // integration suite; import semantics do not depend on dimensions.
        $bytes = (string) base64_decode('/9j/4AAQSkZJRgABAQEAYABgAAD//gA7Q1JFQVRPUjogZ2QtanBlZyB2MS4wICh1c2luZyBJSkcgSlBFRyB2NjIpLCBxdWFsaXR5ID0gOTAK/9sAQwADAgIDAgIDAwMDBAMDBAUIBQUEBAUKBwcGCAwKDAwLCgsLDQ4SEA0OEQ4LCxAWEBETFBUVFQwPFxgWFBgSFBUU/9sAQwEDBAQFBAUJBQUJFA0LDRQUFBQUFBQUFBQUFBQUFBQUFBQUFBQUFBQUFBQUFBQUFBQUFBQUFBQUFBQUFBQUFBQUFBQU/8AAEQgAAQABAwEiAAIRAQMRAf/EAB8AAAEFAQEBAQEBAAAAAAAAAAABAgMEBQYHCAkKC//EALUQAAIBAwMCBAMFBQQEAAABfQECAwAEEQUSITFBBhNRYQcicRQygZGhCCNCscEVUtHwJDNicoIJChYXGBkaJSYnKCkqNDU2Nzg5OkNERUZHSElKU1RVVldYWVpjZGVmZ2hpanN0dXZ3eHl6g4SFhoeIiYqSk5SVlpeYmZqio6Slpqeoqaqys7S1tre4ubrCw8TFxsfIycrS09TV1tfY2drh4uPk5ebn6Onq8fLz9PX29/j5+v/EAB8BAAMBAQEBAQEBAQEAAAAAAAABAgMEBQYHCAkKC//EALURAAIBAgQEAwQHBQQEAAECdwABAgMRBAUhMQYSQVEHYXETIjKBCBRCkaGxwQkjM1LwFWJy0QoWJDThJfEXGBkaJicoKSo1Njc4OTpDREVGR0hJSlNUVVZXWFlaY2RlZmdoaWpzdHV2d3h5eoKDhIWGh4iJipKTlJWWl5iZmqKjpKWmp6ipqrKztLW2t7i5usLDxMXGx8jJytLT1NXW19jZ2uLj5OXm5+jp6vLz9PX29/j5+v/aAAwDAQACEQMRAD8A/KqiiigD/9k=');
        Http::fake(['images.pexels.com/*' => Http::response($bytes, 200, ['Content-Type' => 'image/jpeg'])]);

        $media = app(PexelsCandidateMediaService::class)->importStoredCandidate($candidate->load('recipe'));

        $this->assertSame('private', $media->visibility);
        $this->assertSame('pending_review', $media->verification_status);
        Http::assertSentCount(1);
        $this->assertDatabaseMissing('media', ['visibility' => 'public', 'source_name' => 'Pexels']);
    }

    public function test_admin_can_reject_all_candidates_for_a_recipe_without_publishing_media(): void
    {
        $this->seed();
        $recipe = Recipe::query()->firstOrFail();
        $this->candidate($recipe, 'one');
        $this->candidate($recipe, 'two', 70, ['rank' => 2]);
        $admin = User::factory()->create(['role' => 'admin']);

        $this->actingAs($admin)->withSession(['_token' => 'test-csrf'])->post(route('admin.ai-image-studio.pexels-candidates.reject-all', $recipe), ['_token' => 'test-csrf'])->assertRedirect();

        $this->assertSame(2, RecipeMediaCandidate::query()->where('recipe_id', $recipe->id)->where('status', 'rejected')->count());
        $this->assertDatabaseHas('audit_logs', ['action' => 'pexels_editorial_candidate.all_rejected']);
        $this->assertSame(0, $recipe->media()->where('visibility', 'public')->where('source_name', 'Pexels')->count());
    }

    private function candidate(Recipe $recipe, string $externalId, int $score = 82, array $attributes = []): RecipeMediaCandidate
    {
        return RecipeMediaCandidate::create(array_merge([
            'recipe_id' => $recipe->id,
            'provider' => 'pexels',
            'external_photo_id' => $externalId,
            'review_batch' => 1,
            'rank' => 1,
            'review_score' => $score,
            'status' => 'pending_review',
        ], $attributes));
    }

    private function fakePexelsWithUniquePhotos(int $count = 2): void
    {
        config()->set('ai_images.providers.pexels', ['enabled' => true, 'key' => 'test-key', 'endpoint' => 'https://api.pexels.com']);
        $photoId = 100;
        Http::fake(['api.pexels.com/v1/search*' => function () use (&$photoId, $count) {
            $base = $photoId;
            $photoId += 10;
            $photos = [];
            for ($index = 0; $index < $count; $index++) {
                $id = $base + $index;
                $photos[] = [
                    'id' => $id,
                    'width' => 1600,
                    'height' => 900,
                    'alt' => $index === 1 ? 'Raw city market vegetables' : 'Fresh tomato soup in a bowl',
                    'photographer' => 'Test',
                    'src' => ['landscape' => 'https://images.pexels.com/photos/'.$id.'/test.jpeg'],
                ];
            }

            return Http::response(['photos' => $photos]);
        }]);
    }
}
