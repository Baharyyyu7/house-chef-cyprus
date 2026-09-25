<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Contracts\Search\RecipeSearchInterface;
use App\Models\Recipe;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use Tests\TestCase;

final class RecipeListPrefersRealPhotosTest extends TestCase
{
    use RefreshDatabase;

    public function test_real_photo_then_ai_photo_then_placeholder_regardless_of_featured_or_age(): void
    {
        $real = $this->recipe('gercek-fotografli', false, ['ai' => false], 'publishable');
        $ai = $this->recipe('yz-gorselli', true, ['ai' => true], 'publishable');
        $none = $this->recipe('gorselsiz', true, null, 'publishable');
        $realIncomplete = $this->recipe('gercek-eksik', true, ['ai' => false], 'needs_review');

        $slugs = app(RecipeSearchInterface::class)->search([])->getCollection()->pluck('slug')->all();

        $this->assertSame([$real->slug, $realIncomplete->slug, $ai->slug, $none->slug], $slugs);
    }

    /** @param array{ai:bool}|null $photo */
    private function recipe(string $slug, bool $featured, ?array $photo, string $completeness): Recipe
    {
        $recipe = Recipe::query()->create(['title' => Str::headline($slug), 'slug' => $slug, 'status' => 'published', 'verification_status' => 'verified', 'is_featured' => $featured, 'completeness_status' => $completeness, 'completeness_score' => $completeness === 'publishable' ? 100 : 50]);
        if ($photo !== null) {
            $recipe->media()->create([
                'public_id' => (string) Str::ulid(), 'kind' => 'image', 'disk' => 'public', 'path' => 'media/'.$slug.'.jpg', 'visibility' => 'public',
                'media_status' => 'approved_real_media', 'verification_status' => 'verified', 'license_review_status' => 'approved',
                'attribution_review_status' => 'approved', 'is_placeholder' => false, 'generated_by_ai' => $photo['ai'],
            ]);
        }

        return $recipe;
    }
}
