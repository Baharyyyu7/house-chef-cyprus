<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Models\ContentTranslation;
use App\Models\Ingredient;
use App\Models\IngredientUnitConversion;
use App\Models\IntegrityReport;
use App\Models\Recipe;
use App\Models\RecipeIngredient;
use App\Models\Unit;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Queue;
use Illuminate\Support\Str;
use Tests\TestCase;

/**
 * coni:malzeme-ekle adımda kullanılıp listede olmayan malzemeyi ekler.
 * Satır yönetim panelindeki gibi kanonik miktarla kurulur; adı her dilde
 * çevrilmemiş malzeme açık sayfayı kapatacağı için reddedilir.
 */
final class ConiMalzemeEkleTest extends TestCase
{
    use RefreshDatabase;

    private function malzeme(string $ad, bool $cevrili = true, bool $donusum = true): Ingredient
    {
        $malzeme = Ingredient::query()->create(['name' => $ad, 'slug' => Str::slug($ad), 'is_active' => true]);
        $donusum && IngredientUnitConversion::query()->create(['ingredient_id' => $malzeme->id, 'household_measure_code' => 'tatli_kasigi', 'household_quantity' => 1, 'canonical_quantity' => 5, 'canonical_unit' => 'g', 'source_type' => 'editorial', 'verification_status' => 'medium', 'confidence_score' => 70, 'conversion_method' => 'ingredient_specific', 'is_active' => true]);

        $diller = array_diff(array_keys((array) config('locales.supported')), ['tr']);

        foreach ($cevrili ? $diller : array_slice($diller, 1) as $dil) {
            ContentTranslation::query()->create(['translatable_type' => $malzeme->getMorphClass(), 'translatable_id' => $malzeme->id, 'locale' => $dil, 'field' => 'name', 'value' => $ad.' '.$dil]);
        }

        return $malzeme;
    }

    private function tarif(): Recipe
    {
        Unit::query()->create(['public_id' => (string) Str::ulid(), 'code' => 'tatli_kasigi', 'name' => 'tatlı kaşığı', 'singular_name' => 'tatlı kaşığı', 'unit_type' => 'household', 'supports_fractions' => true, 'precision' => 2, 'is_active' => true, 'is_user_visible' => true]);

        $tarif = Recipe::query()->create(['title' => 'Mercimek Çorbası', 'slug' => 'mercimek-test', 'status' => 'published', 'verification_status' => 'verified', 'published_at' => now()]);
        $grup = $tarif->ingredientGroups()->create(['name' => 'Malzemeler', 'sort_order' => 0]);
        $tarif->recipeIngredients()->create(['ingredient_id' => $this->malzeme('Mercimek')->id, 'recipe_ingredient_group_id' => $grup->id, 'group_name' => 'Malzemeler', 'display_quantity' => 1, 'display_measure_code' => 'tatli_kasigi', 'sort_order' => 4]);

        return $tarif;
    }

    private function dosya(array $veri): string
    {
        $dosya = tempnam(sys_get_temp_dir(), 'coni-malzeme-ekle');
        file_put_contents($dosya, (string) json_encode($veri, JSON_UNESCAPED_UNICODE));

        return $dosya;
    }

    public function test_satiri_kanonik_miktarla_ekler_ve_geri_alir(): void
    {
        Queue::fake();
        $tarif = $this->tarif();
        $tuz = $this->malzeme('Tuz');
        $su = $this->malzeme('Su', donusum: false);
        $dosya = $this->dosya(['mercimek-test' => [
            ['ingredient_id' => $tuz->id, 'display_quantity' => 1.5, 'display_measure_code' => 'tatli_kasigi', 'scaling_behavior' => 'taste_adjusted'],
            ['ingredient_id' => $su->id, 'display_quantity' => 2, 'display_measure_code' => 'tatli_kasigi', 'scaling_behavior' => 'scalable', 'canonical_quantity' => 20, 'canonical_unit' => 'ml', 'manual_override_reason' => 'Site ölçüsü: 1 tatlı kaşığı = 10 ml'],
        ]]);

        $this->artisan('coni:malzeme-ekle', ['dosya' => $dosya])->expectsOutputToContain('1 tarife 2 satır eklendi')->assertSuccessful();

        $satir = RecipeIngredient::query()->where('recipe_id', $tarif->id)->where('ingredient_id', $tuz->id)->firstOrFail();
        $this->assertSame('7.5000', (string) $satir->canonical_quantity);
        $this->assertSame('g', $satir->canonical_unit);
        $this->assertSame(5, $satir->sort_order);
        $this->assertSame($tarif->ingredientGroups()->value('id'), $satir->recipe_ingredient_group_id);
        $this->assertSame(1, $tarif->ingredientGroups()->count());
        $elle = RecipeIngredient::query()->where('recipe_id', $tarif->id)->where('ingredient_id', $su->id)->firstOrFail();
        $this->assertSame(['20.0000', 'ml', 'manual_override', 'medium', null], [(string) $elle->canonical_quantity, $elle->canonical_unit, $elle->conversion_method, $elle->conversion_confidence, $elle->grams]);

        $rapor = IntegrityReport::query()->where('guard', 'coni:malzeme-ekle')->firstOrFail();
        $this->artisan('coni:malzeme-ekle', ['--geri-al' => $rapor->id])->assertSuccessful();

        $this->assertSame(1, RecipeIngredient::query()->where('recipe_id', $tarif->id)->count());
        $this->assertSame('geri_alindi', $rapor->fresh()->status);
        unlink($dosya);
    }

    public function test_cevrilmemis_ya_da_tekrar_eden_malzemeyi_reddeder_ve_hicbir_seyi_yazmaz(): void
    {
        $tarif = $this->tarif();
        $tuz = $this->malzeme('Tuz');
        $kimyon = $this->malzeme('Kimyon', cevrili: false);
        $su = $this->malzeme('Su', donusum: false);
        $mercimek = Ingredient::query()->where('name', 'Mercimek')->value('id');
        $dosya = $this->dosya(['mercimek-test' => [
            ['ingredient_id' => $tuz->id, 'display_quantity' => 1, 'display_measure_code' => 'tatli_kasigi', 'scaling_behavior' => 'taste_adjusted'],
            ['ingredient_id' => $kimyon->id, 'display_quantity' => 1, 'display_measure_code' => 'tatli_kasigi', 'scaling_behavior' => 'taste_adjusted'],
            ['ingredient_id' => $mercimek, 'display_quantity' => 1, 'display_measure_code' => 'tatli_kasigi', 'scaling_behavior' => 'scalable'],
            ['ingredient_id' => $su->id, 'display_quantity' => 1, 'display_measure_code' => 'tatli_kasigi', 'scaling_behavior' => 'scalable'],
        ]]);

        $this->artisan('coni:malzeme-ekle', ['dosya' => $dosya])
            ->expectsOutputToContain('Kimyon adı çevrilmemiş')
            ->expectsOutputToContain('Mercimek zaten listede')
            ->expectsOutputToContain('Su için tatli_kasigi dönüşümü yok')
            ->assertFailed();

        $this->assertSame(1, RecipeIngredient::query()->where('recipe_id', $tarif->id)->count());
        $this->assertSame(0, IntegrityReport::query()->where('guard', 'coni:malzeme-ekle')->count());
        unlink($dosya);
    }

    public function test_deneme_yazmaz(): void
    {
        $tarif = $this->tarif();
        $dosya = $this->dosya(['mercimek-test' => [['ingredient_id' => $this->malzeme('Tuz')->id, 'display_quantity' => 1, 'display_measure_code' => 'tatli_kasigi', 'scaling_behavior' => 'taste_adjusted']]]);

        $this->artisan('coni:malzeme-ekle', ['dosya' => $dosya, '--dene' => true])->expectsOutputToContain('1 satır eklenebilir')->assertSuccessful();

        $this->assertSame(1, RecipeIngredient::query()->where('recipe_id', $tarif->id)->count());
        $this->assertSame(0, IntegrityReport::query()->where('guard', 'coni:malzeme-ekle')->count());
        unlink($dosya);
    }
}
