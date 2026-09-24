<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Models\Media;
use App\Models\Recipe;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Tests\TestCase;

final class MediaPhotographicAuditTest extends TestCase
{
    use RefreshDatabase;

    public function test_it_demotes_non_photographic_media_only_when_applied(): void
    {
        Storage::fake('public');
        $recipe = $this->makeRecipe();

        $gradient = $this->attach($recipe, 'media/gradient.png', $this->gradientImage());
        $photo = $this->attach($recipe, 'media/photo.png', $this->noisyImage());

        $this->artisan('media:photographic-audit')->assertSuccessful();

        // Dry-run hiçbir kaydı değiştirmez.
        $this->assertSame('approved_real_media', $gradient->fresh()?->media_status);
        $this->assertSame('approved_real_media', $photo->fresh()?->media_status);

        $this->artisan('media:photographic-audit --apply')->assertSuccessful();

        $this->assertSame('no_media', $gradient->fresh()?->media_status);
        $this->assertFalse($gradient->fresh()?->isApprovedRealMedia());

        // Gerçek fotoğrafa dokunulmaz.
        $this->assertSame('approved_real_media', $photo->fresh()?->media_status);
        $this->assertTrue($photo->fresh()?->isApprovedRealMedia());
    }

    /**
     * fidelity_status "görsel bu yemeğe ait mi" sorusunu yanıtlar ve strong_match
     * sözlüğünü başka akışlar okur. Bu komut oraya yazarsa tamamlama denetimi
     * gerçek fotoğrafları WRONG_DISH_MEDIA sanır.
     */
    public function test_it_never_writes_to_the_fidelity_status_vocabulary(): void
    {
        Storage::fake('public');
        $recipe = $this->makeRecipe();
        $gradient = $this->attach($recipe, 'media/gradient.png', $this->gradientImage());
        $photo = $this->attach($recipe, 'media/photo.png', $this->noisyImage());
        $gradient->forceFill(['fidelity_status' => 'strong_match'])->save();
        $photo->forceFill(['fidelity_status' => 'strong_match'])->save();

        $this->artisan('media:photographic-audit --apply')->assertSuccessful();

        $this->assertSame('strong_match', $gradient->fresh()?->fidelity_status);
        $this->assertSame('strong_match', $photo->fresh()?->fidelity_status);
    }

    public function test_it_reports_missing_files_as_inconclusive_without_demoting(): void
    {
        Storage::fake('public');
        $recipe = $this->makeRecipe();
        $orphan = $this->attach($recipe, 'media/missing.png', null);

        $this->artisan('media:photographic-audit --apply')->assertSuccessful();

        $this->assertSame('approved_real_media', $orphan->fresh()?->media_status);
        $this->assertNull($orphan->fresh()?->fidelity_status);
    }

    private function makeRecipe(): Recipe
    {
        return Recipe::query()->create(['title' => 'Fidelity Test Tarifi', 'slug' => 'fidelity-test-tarifi', 'status' => 'published', 'verification_status' => 'verified']);
    }

    private function attach(Recipe $recipe, string $path, ?string $contents): Media
    {
        if ($contents !== null) {
            Storage::disk('public')->put($path, $contents);
        }

        return $recipe->media()->create([
            'public_id' => (string) Str::ulid(),
            'kind' => 'image',
            'disk' => 'public',
            'path' => $path,
            'visibility' => 'public',
            'media_status' => 'approved_real_media',
            'verification_status' => 'verified',
            'license_review_status' => 'approved',
            'attribution_review_status' => 'approved',
            'is_placeholder' => false,
        ]);
    }

    /** Düz gradient: komşu pikseller arasında neredeyse hiç fark yok. */
    private function gradientImage(): string
    {
        $image = imagecreatetruecolor(64, 64);
        for ($y = 0; $y < 64; $y++) {
            $color = imagecolorallocate($image, 40 + $y, 90, 140);
            imageline($image, 0, $y, 63, $y, $color);
        }

        return $this->encode($image);
    }

    /** Yüksek frekanslı detay: fotoğrafı taklit eder. */
    private function noisyImage(): string
    {
        $image = imagecreatetruecolor(64, 64);
        mt_srand(1);
        for ($y = 0; $y < 64; $y++) {
            for ($x = 0; $x < 64; $x++) {
                imagesetpixel($image, $x, $y, imagecolorallocate($image, mt_rand(0, 255), mt_rand(0, 255), mt_rand(0, 255)));
            }
        }

        return $this->encode($image);
    }

    private function encode(\GdImage $image): string
    {
        ob_start();
        imagepng($image);
        $data = (string) ob_get_clean();
        imagedestroy($image);

        return $data;
    }
}
