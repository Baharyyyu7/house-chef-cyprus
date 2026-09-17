<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Models\Recipe;
use App\Services\Media\RecipeDisplayMediaService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/** A recipe without its own approved media must not inherit a dish photo. */
final class DishPhotoMatcherTest extends TestCase
{
    use RefreshDatabase;

    public function test_missing_recipe_photo_does_not_match_a_stock_photo_by_title(): void
    {
        $recipe = Recipe::query()->create([
            'title' => 'Kıbrıs Usulü Pilavuna',
            'slug' => 'kibris-usulu-pilavuna-ABCDE',
            'status' => 'published',
            'verification_status' => 'verified',
            'published_at' => now(),
        ]);

        $result = app(RecipeDisplayMediaService::class)->resolve($recipe);

        $this->assertTrue($result['is_fallback']);
        $this->assertStringNotContainsString('images.unsplash.com', $result['card_url']);
    }
}
