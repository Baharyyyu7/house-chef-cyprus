<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Cuisine;
use App\Models\Recipe;
use App\Models\User;
use App\Services\Media\RecipeDisplayMediaService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

final class RecipeDisplayMediaServiceTest extends TestCase
{
    use RefreshDatabase;

    private RecipeDisplayMediaService $service;

    protected function setUp(): void
    {
        parent::setUp();
        $this->service = app(RecipeDisplayMediaService::class);
    }

    public function test_uses_specific_approved_media_when_available(): void
    {
        Storage::fake('public');
        $reviewer = User::factory()->create(['role' => 'admin', 'email_verified_at' => now()]);
        $path = 'media/recipes/kleftiko.jpg';
        Storage::disk('public')->put($path, 'dummy-content');

        $variants = collect(['thumbnail', 'square', 'card', 'open_graph', 'hero'])->map(function (string $profile) use ($path): array {
            $prefix = str($path)->beforeLast('.')->toString().'-'.$profile;
            $jpeg = $prefix.'.jpg';
            $webp = $prefix.'.webp';
            Storage::disk('public')->put($jpeg, 'jpeg');
            Storage::disk('public')->put($webp, 'webp');

            return ['name' => $profile, 'width' => 1200, 'height' => 800, 'jpeg' => $jpeg, 'webp' => $webp];
        })->all();

        $recipe = Recipe::query()->create([
            'title' => 'Kıbrıs Usulü Kleftiko',
            'slug' => 'kibris-usulu-kleftiko',
            'status' => 'published',
            'verification_status' => 'verified',
            'published_at' => now(),
        ]);

        $recipe->media()->create([
            'kind' => 'recipe_hero',
            'disk' => 'public',
            'path' => $path,
            'mime_type' => 'image/jpeg',
            'size' => 1024,
            'visibility' => 'public',
            'media_status' => 'approved_real_media',
            'verification_status' => 'verified',
            'license_review_status' => 'approved',
            'attribution_review_status' => 'approved',
            'is_placeholder' => false,
            'alt_text' => 'Kıbrıs Kleftiko Fotoğrafı',
            'fidelity_status' => 'strong_match',
            'fidelity_confidence' => 95,
            'responsive_variants' => $variants,
            'media_reviewer_user_id' => $reviewer->id,
            'media_reviewed_at' => now(),
            'approved_by_user_id' => $reviewer->id,
            'approved_at' => now(),
        ]);

        $result = $this->service->resolve($recipe);

        $this->assertTrue($result['has_specific_media']);
        $this->assertFalse($result['is_fallback']);
        $this->assertNotNull($result['media']);
        $this->assertSame('Kıbrıs Kleftiko Fotoğrafı', $result['alt']);
    }

    public function test_matches_dish_specific_photo_when_no_approved_media(): void
    {
        $recipe = Recipe::query()->create([
            'title' => 'Geleneksel Kırmızı Mercimek Çorbası',
            'slug' => 'geleneksel-kirmizi-mercimek-corbasi',
            'status' => 'published',
            'verification_status' => 'under_review',
            'published_at' => now(),
        ]);

        $result = $this->service->resolve($recipe);

        $this->assertFalse($result['has_specific_media']);
        $this->assertTrue($result['is_fallback']);
        $this->assertNull($result['media']);
        $this->assertStringContainsString('images.unsplash.com', $result['card_url']);
        $this->assertStringContainsString('photo-1547592166-23ac45744acd', $result['card_url']);
        $this->assertStringContainsString('800', $result['card_url']);
        $this->assertStringContainsString('1600', $result['hero_url']);
    }

    public function test_falls_back_to_category_hero_when_unmatched(): void
    {
        $category = Category::query()->create([
            'name' => 'Tatlılar',
            'slug' => 'tatli',
        ]);

        $recipe = Recipe::query()->create([
            'title' => 'Egzotik Ada Muhallebisi',
            'slug' => 'egzotik-ada-muhallebisi',
            'category_id' => $category->id,
            'status' => 'published',
            'verification_status' => 'under_review',
            'published_at' => now(),
        ]);

        $result = $this->service->resolve($recipe);

        $this->assertFalse($result['has_specific_media']);
        $this->assertTrue($result['is_fallback']);
        $this->assertStringContainsString('images.unsplash.com', $result['card_url']);
        $this->assertStringContainsString('Tatlılar', $result['badge_label']);
    }

    public function test_falls_back_to_cypriot_cuisine_hero_for_cypriot_recipes(): void
    {
        $cuisine = Cuisine::query()->create([
            'name' => 'Kıbrıs Mutfağı',
            'slug' => 'kibris-mutfagi',
        ]);

        $recipe = Recipe::query()->create([
            'title' => 'Özel Ada Yemeği',
            'slug' => 'ozel-ada-yemegi',
            'cuisine_id' => $cuisine->id,
            'status' => 'published',
            'verification_status' => 'under_review',
            'published_at' => now(),
        ]);

        $result = $this->service->resolve($recipe);

        $this->assertFalse($result['has_specific_media']);
        $this->assertTrue($result['is_fallback']);
        $this->assertStringContainsString('photo-1555396273-367ea4eb4db5', $result['card_url']);
    }
}
