<?php

namespace Tests;

use App\Models\Recipe;
use App\Models\User;
use Illuminate\Foundation\Testing\TestCase as BaseTestCase;
use Illuminate\Support\Facades\Storage;

abstract class TestCase extends BaseTestCase
{
    private bool $publicTestDiskIsReady = false;

    private static bool $sinifKaynagiDogrulandi = false;

    /**
     * Testler bu deponun kodunu mu koşuyor?
     *
     * vendor/composer/autoload_classmap.php başka bir çalışma kopyasının
     * yolunu gösterebiliyor: bu depoda birden çok ajan çalışıyor ve biri
     * kendi worktree'sinde composer çalıştırınca harita onun yoluna göre
     * yeniden yazılıyor. O zaman App\* sınıfları oradan yükleniyor ve testler
     * yeşil dönerken aslında başka birinin kodunu doğruluyor. 2026-09-23 ve
     * 2026-09-26'da iki kez oldu; ikisinde de bir düzeltme "çalışmıyor"
     * göründü, çünkü koşulan kod o düzeltmeyi hiç içermiyordu.
     *
     * Sessizce yanlış kodu koşmak yerine burada dur.
     */
    protected function setUp(): void
    {
        parent::setUp();

        if (self::$sinifKaynagiDogrulandi) {
            return;
        }

        $kok = realpath(dirname(__DIR__)) ?: dirname(__DIR__);
        $dosya = (string) (new \ReflectionClass(Recipe::class))->getFileName();
        $gercek = realpath($dosya) ?: $dosya;

        if (! str_starts_with($gercek, $kok.DIRECTORY_SEPARATOR)) {
            throw new \RuntimeException(
                "Uygulama sınıfları bu deponun dışından yükleniyor: {$gercek}\n"
                ."Testler başka bir çalışma kopyasının kodunu doğrulardı. Düzeltmek için: composer dump-autoload"
            );
        }

        self::$sinifKaynagiDogrulandi = true;
    }

    /** Build the complete public-media contract used by production. */
    protected function makeRecipePubliclyEligible(Recipe $recipe): void
    {
        if (! $this->publicTestDiskIsReady) {
            Storage::fake('public');
            $this->publicTestDiskIsReady = true;
        }
        $reviewer = User::factory()->create(['role' => 'admin', 'email_verified_at' => now()]);
        $source = 'media/tests/'.$recipe->id.'/source.jpg';
        Storage::disk('public')->put($source, 'source');
        $variants = collect(['thumbnail', 'square', 'card', 'open_graph', 'hero'])->map(function (string $name) use ($recipe): array {
            $jpeg = 'media/tests/'.$recipe->id.'/'.$name.'.jpg';
            $webp = 'media/tests/'.$recipe->id.'/'.$name.'.webp';
            Storage::disk('public')->put($jpeg, 'jpeg');
            Storage::disk('public')->put($webp, 'webp');

            return ['name' => $name, 'width' => 1200, 'height' => 800, 'jpeg' => $jpeg, 'webp' => $webp];
        })->all();

        $recipe->media()->create([
            'kind' => 'recipe_synthetic', 'disk' => 'public', 'path' => $source, 'mime_type' => 'image/jpeg',
            'visibility' => 'public', 'media_status' => 'approved_real_media', 'verification_status' => 'verified',
            'license_review_status' => 'approved', 'attribution_review_status' => 'approved',
            'fidelity_status' => 'strong_match', 'fidelity_confidence' => 95,
            'media_reviewer_user_id' => $reviewer->id, 'approved_by_user_id' => $reviewer->id,
            'media_reviewed_at' => now(), 'approved_at' => now(), 'responsive_variants' => $variants,
            'checksum_sha256' => hash('sha256', $source), 'alt_text' => $recipe->title.' servis edilmiş hâlde',
        ]);

        // Yayın tarihi olmayan tarif herkese açık sayılmaz: site haritası
        // 16 Eylül 2026'da yayın tarihi boş 637 tarifi listeliyordu.
        if ($recipe->published_at === null) {
            $recipe->forceFill(['published_at' => now()->subDay()])->save();
        }
    }
}
