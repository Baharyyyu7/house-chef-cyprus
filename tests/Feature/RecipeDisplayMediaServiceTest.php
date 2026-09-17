<?php

declare(strict_types=1);

namespace Tests\Feature;

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

    public function test_uses_brand_mark_when_no_approved_media_instead_of_another_dish_photo(): void
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
        $this->assertStringNotContainsString('images.unsplash.com', $result['card_url']);
        $this->assertStringContainsString('brand/og-default.jpg', $result['card_url']);
        $this->assertSame(__('card.photo_preparing'), $result['badge_label']);
    }

    public function test_uses_recipe_branded_fallback_when_available(): void
    {
        $recipe = Recipe::query()->create([
            'title' => 'Egzotik Ada Muhallebisi',
            'slug' => 'egzotik-ada-muhallebisi',
            'status' => 'published',
            'verification_status' => 'under_review',
            'published_at' => now(),
        ]);
        $path = 'fallbacks/recipes/'.$recipe->public_id.'.svg';
        Storage::disk('public')->put($path, '<svg/>');
        $recipe->media()->create([
            'kind' => 'image',
            'disk' => 'public',
            'path' => $path,
            'mime_type' => 'image/svg+xml',
            'size' => 6,
            'visibility' => 'public',
            'verification_status' => 'branded_fallback',
            'media_status' => 'branded_fallback',
            'is_placeholder' => false,
        ]);

        $result = $this->service->resolve($recipe);

        $this->assertFalse($result['has_specific_media']);
        $this->assertTrue($result['is_fallback']);
        $this->assertStringContainsString('/storage/'.$path, $result['card_url']);
        $this->assertStringNotContainsString('images.unsplash.com', $result['card_url']);
    }
}
