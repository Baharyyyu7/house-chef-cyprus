<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Models\Ingredient;
use App\Models\Recipe;
use App\Models\RecipeStep;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\File;
use Tests\TestCase;

/**
 * İçe aktarma, gövdesi çevrilmemiş tarifi reddeder.
 *
 * Kapı (Recipe::hasTranslation) gövdeye bakıyor. İçe aktarma bakmasaydı
 * ajanlar teslim edebilir ama hiçbir tarif asla yayına giremezdi: dosya
 * "alındı" der, sayfa 404 verirdi. İkisi aynı tanımı paylaşmak zorunda.
 */
final class ImportRejectsHalfTranslatedRecipeTest extends TestCase
{
    use RefreshDatabase;

    private string $kok;

    protected function setUp(): void
    {
        parent::setUp();

        $this->kok = storage_path('app/content-import/translations/en');
        File::ensureDirectoryExists($this->kok);
    }

    protected function tearDown(): void
    {
        File::deleteDirectory(storage_path('app/content-import/translations'));

        parent::tearDown();
    }

    private function tarif(): Recipe
    {
        $tarif = Recipe::query()->create([
            'title' => 'Hellimli Börek',
            'slug' => 'hellimli-borek-testi',
            'excerpt' => 'Kıbrıs usulü',
            'description' => 'Ince yufka ile',
            'status' => 'published',
            'verification_status' => 'verified',
            'published_at' => now(),
        ]);

        RecipeStep::query()->create([
            'recipe_id' => $tarif->id,
            'position' => 1,
            'title' => 'Hamuru aç',
            'instruction' => 'Yufkayı ince aç.',
        ]);

        $malzeme = Ingredient::query()->create(['name' => 'hellim', 'slug' => 'hellim']);
        $tarif->ingredients()->attach($malzeme->id, ['quantity' => 200, 'unit' => 'g']);

        return $tarif;
    }

    /** @param array<string, mixed> $veri */
    private function dosya(array $veri): void
    {
        File::put($this->kok.'/hellimli-borek.json', json_encode($veri, JSON_UNESCAPED_UNICODE));
    }

    private function govdesiz(): array
    {
        return [
            'type' => 'recipe',
            'slug' => 'hellimli-borek-testi',
            'fields' => [
                'title' => 'Halloumi Pastry',
                'excerpt' => 'Cypriot style',
                'description' => 'With thin phyllo',
            ],
        ];
    }

    public function test_govdesiz_dosya_reddedilir(): void
    {
        $this->tarif();
        $this->dosya($this->govdesiz());

        $this->artisan('content:import-translations --apply')
            ->expectsOutputToContain('gövde çevrilmemiş')
            ->assertSuccessful();

        $this->assertSame(0, Recipe::query()->translatedInto('en')->count());
    }

    public function test_turkce_kopyasi_reddedilir(): void
    {
        $this->tarif();
        $veri = $this->govdesiz();
        // Alanı doldurmuş gibi görünüp Türkçe bırakmak, engellemeye
        // çalıştığımız durumun ta kendisi.
        $veri['steps'] = [['position' => 1, 'title' => 'Hamuru aç', 'instruction' => 'Yufkayı ince aç.']];
        $veri['ingredients'] = [['slug' => 'hellim', 'name' => 'hellim']];
        $this->dosya($veri);

        $this->artisan('content:import-translations --apply')
            ->expectsOutputToContain('kaynak metnin kopyası')
            ->assertSuccessful();

        $this->assertSame(0, Recipe::query()->translatedInto('en')->count());
    }

    public function test_tam_dosya_alinir_ve_tarif_yayinlanabilir_olur(): void
    {
        $this->tarif();
        $veri = $this->govdesiz();
        $veri['steps'] = [['position' => 1, 'title' => 'Roll the dough', 'instruction' => 'Roll the phyllo thin.']];
        $veri['ingredients'] = [['slug' => 'hellim', 'name' => 'halloumi']];
        $this->dosya($veri);

        $this->artisan('content:import-translations --apply')->assertSuccessful();

        $this->assertSame(
            1,
            Recipe::query()->translatedInto('en')->count(),
            'Tamamı çevrilmiş tarif içeri alındığı halde yayınlanabilir olmadı.'
        );
    }
}
