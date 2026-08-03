<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Models\Recipe;
use App\Services\Media\MediaOptimizationService;
use App\Services\Media\PublicRecipeMediaCompletionService;
use Database\Seeders\HouseChefSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

final class PublicRecipeMediaCompletionTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(HouseChefSeeder::class);
    }

    public function test_all_published_seed_recipes_are_truthfully_classified_as_placeholder_only(): void
    {
        $report = app(PublicRecipeMediaCompletionService::class)->audit();

        $this->assertSame(50, $report['published_recipes_audited']);
        $this->assertSame(50, $report['classifications']['PLACEHOLDER_ONLY']);
        $this->assertSame(0, $report['approved_media']);
        $this->assertFalse($report['auto_approve']);
        $this->assertFalse($report['auto_attach']);
    }

    public function test_only_fully_approved_existing_media_with_required_variants_enters_apply_manifest(): void
    {
        Storage::fake('public');
        Storage::disk('public')->put('media/approved/dish.jpg', $this->jpeg());
        $recipe = Recipe::query()->where('status', 'published')->firstOrFail();
        $media = $recipe->media()->create($this->approvedAttributes('media/approved/dish.jpg'));

        $service = app(PublicRecipeMediaCompletionService::class);
        $row = collect($service->audit()['recipes'])->firstWhere('recipe_public_id', $recipe->public_id);
        $manifest = $service->applyManifest();

        $this->assertSame('READY', $row['classification']);
        $this->assertSame(1, $manifest['entry_count']);
        $this->assertSame($media->public_id, $manifest['entries'][0]['target_media_public_id']);
        $this->assertSame($recipe->updated_at?->toIso8601String(), $manifest['entries'][0]['expected_recipe_version']);
        $this->assertTrue($manifest['optimistic_concurrency_required']);
    }

    public function test_missing_approved_file_is_classified_without_exposing_a_broken_public_image(): void
    {
        Storage::fake('public');
        $recipe = Recipe::query()->where('status', 'published')->firstOrFail();
        $recipe->media()->create($this->approvedAttributes('media/approved/missing.jpg'));

        $row = collect(app(PublicRecipeMediaCompletionService::class)->audit()['recipes'])
            ->firstWhere('recipe_public_id', $recipe->public_id);

        $this->assertSame('FILE_MISSING', $row['classification']);
        $this->assertFalse($row['storage_file_exists']);
    }

    public function test_optimizer_creates_exact_recipe_profiles_and_modern_formats_when_supported(): void
    {
        Storage::fake('public');
        Storage::disk('public')->put('media/source.jpg', $this->jpeg(1800, 1200));
        $recipe = Recipe::query()->where('status', 'published')->firstOrFail();
        $media = $recipe->media()->create(array_merge($this->approvedAttributes('media/source.jpg'), [
            'responsive_variants' => null,
            'optimization_status' => 'pending',
            'focal_point_x' => 0.5,
            'focal_point_y' => 0.5,
        ]));

        $result = app(MediaOptimizationService::class)->optimize(1);
        $variants = (array) $media->fresh()->responsive_variants;

        $this->assertSame(1, $result['processed']);
        $this->assertSame([400, 640, 800, 1200, 1600], collect($variants)->pluck('width')->sort()->values()->all());
        $this->assertSame([300, 600, 630, 640, 1000], collect($variants)->pluck('height')->sort()->values()->all());
        foreach ($variants as $variant) {
            Storage::disk('public')->assertExists($variant['jpeg']);
            if (function_exists('imagewebp')) {
                Storage::disk('public')->assertExists($variant['webp']);
            }
            if (function_exists('imageavif')) {
                Storage::disk('public')->assertExists($variant['avif']);
            }
        }
        $fresh = $media->fresh();
        $this->assertStringContainsString('.webp', (string) $fresh->responsiveUrl(800, 'webp'));
        $this->assertStringContainsString('800w', (string) $fresh->responsiveSrcset([400, 800], 'webp'));
    }

    /** @return array<string, mixed> */
    private function approvedAttributes(string $path): array
    {
        return [
            'kind' => 'recipe_photo', 'disk' => 'public', 'path' => $path, 'mime_type' => 'image/jpeg', 'size' => 100,
            'visibility' => 'public', 'media_status' => 'approved_real_media', 'verification_status' => 'verified',
            'license_review_status' => 'approved', 'attribution_review_status' => 'approved', 'license' => 'Owned',
            'source_name' => 'House Chef Cyprus', 'source_url' => 'https://housechefcyprus.com', 'attribution' => 'House Chef Cyprus',
            'fidelity_status' => 'strong_match', 'alt_text' => 'Tarife ait doğrulanmış yemek görseli', 'width' => 1600, 'height' => 1000,
            'checksum_sha256' => hash('sha256', $path), 'approved_at' => now(), 'media_reviewed_at' => now(),
            'responsive_variants' => [
                'thumbnail' => ['name' => 'thumbnail', 'width' => 400, 'height' => 300, 'jpeg' => 'a/400.jpg', 'webp' => 'a/400.webp'],
                'square' => ['name' => 'square', 'width' => 640, 'height' => 640, 'jpeg' => 'a/640.jpg', 'webp' => 'a/640.webp'],
                'card' => ['name' => 'card', 'width' => 800, 'height' => 600, 'jpeg' => 'a/800.jpg', 'webp' => 'a/800.webp'],
                'open_graph' => ['name' => 'open_graph', 'width' => 1200, 'height' => 630, 'jpeg' => 'a/1200.jpg', 'webp' => 'a/1200.webp'],
                'hero' => ['name' => 'hero', 'width' => 1600, 'height' => 1000, 'jpeg' => 'a/1600.jpg', 'webp' => 'a/1600.webp'],
            ],
        ];
    }

    private function jpeg(int $width = 32, int $height = 24): string
    {
        $image = imagecreatetruecolor($width, $height);
        imagefill($image, 0, 0, imagecolorallocate($image, 220, 120, 40));
        ob_start();
        imagejpeg($image);
        $bytes = ob_get_clean();
        imagedestroy($image);

        return is_string($bytes) ? $bytes : '';
    }
}
