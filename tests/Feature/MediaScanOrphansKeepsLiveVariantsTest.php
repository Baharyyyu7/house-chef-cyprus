<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Models\Media;
use App\Models\Recipe;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

final class MediaScanOrphansKeepsLiveVariantsTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        Storage::fake('public');
    }

    public function test_a_served_variant_is_not_an_orphan(): void
    {
        // Tarayıcı yalnız `path` ve `thumbnail_paths`'e bakıyordu. Sayfalarda
        // basılan dosyalar ise responsive_variants içinde duruyor, yani
        // sitenin gösterdiği her görsel yetim sayılıyordu: 6.721 dosyanın
        // 6.557'si.
        $this->kayitOlustur();

        $this->artisan('media:scan-orphans --json')
            ->expectsOutputToContain('"orphans":0')
            ->assertSuccessful();
    }

    public function test_delete_does_not_remove_a_served_variant(): void
    {
        // Bu, hatanın asıl bedeli: --delete sitedeki her fotoğrafı silerdi.
        $this->kayitOlustur();

        $this->artisan('media:scan-orphans --delete')->assertSuccessful();

        Storage::disk('public')->assertExists('media/optimized/AAA/800.jpg');
        Storage::disk('public')->assertExists('media/optimized/AAA/800.webp');
        Storage::disk('public')->assertExists('media/approved-synthetic/recipe/AAA/source.jpg');
    }

    public function test_a_genuinely_unreferenced_file_is_still_reported(): void
    {
        // Daralma tarayıcıyı işe yaramaz hale getirmemeli.
        $this->kayitOlustur();
        Storage::disk('public')->put('media/optimized/ESKI/800.jpg', 'x');

        $this->artisan('media:scan-orphans --json')
            ->expectsOutputToContain('"orphans":1')
            ->assertSuccessful();
    }

    public function test_a_genuinely_unreferenced_file_is_deleted(): void
    {
        $this->kayitOlustur();
        Storage::disk('public')->put('media/optimized/ESKI/800.jpg', 'x');

        $this->artisan('media:scan-orphans --delete')->assertSuccessful();

        Storage::disk('public')->assertMissing('media/optimized/ESKI/800.jpg');
        Storage::disk('public')->assertExists('media/optimized/AAA/800.jpg');
    }

    private function kayitOlustur(): Media
    {
        Storage::disk('public')->put('media/approved-synthetic/recipe/AAA/source.jpg', 'jpg');
        Storage::disk('public')->put('media/optimized/AAA/800.jpg', 'jpg');
        Storage::disk('public')->put('media/optimized/AAA/800.webp', 'webp');
        Storage::disk('public')->put('media/thumbs/AAA/kucuk.jpg', 'jpg');

        $tarif = Recipe::query()->create([
            'title' => 'Varyantlı Tarif',
            'slug' => 'varyantli-'.uniqid(),
            'excerpt' => 'Özet.',
            'description' => 'Açıklama.',
            'status' => 'published',
            'verification_status' => 'verified',
            'published_at' => now(),
        ]);

        return $tarif->media()->create([
            'kind' => 'recipe_synthetic',
            'disk' => 'public',
            'path' => 'media/approved-synthetic/recipe/AAA/source.jpg',
            'mime_type' => 'image/jpeg',
            'visibility' => 'public',
            'thumbnail_paths' => ['kucuk' => 'media/thumbs/AAA/kucuk.jpg'],
            'responsive_variants' => [
                ['name' => 'card', 'jpeg' => 'media/optimized/AAA/800.jpg', 'webp' => 'media/optimized/AAA/800.webp'],
            ],
        ]);
    }
}
