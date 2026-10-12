<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Models\ContentTranslation;
use App\Models\Ingredient;
use App\Models\IntegrityReport;
use App\Models\Recipe;
use App\Models\RecipeIngredient;
use App\Models\RecipeStep;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Queue;
use Tests\TestCase;

/**
 * coni:taslak taslaktaki şablon tarifi baştan yazar, yayınlamaz.
 * Ekmek kadayıfı, lokma ve ceviz macunu aynı "200 g un, 150 g şeker"
 * listesini ve "Malzemeleri tezgaha dizin" adımlarını taşıyordu.
 */
final class ConiTaslakTest extends TestCase
{
    use RefreshDatabase;

    private function sablonTaslak(string $durum = 'draft'): Recipe
    {
        $tarif = Recipe::query()->create(['title' => 'Kıbrıs Lokması', 'slug' => 'lokma-test', 'excerpt' => 'Kıbrıs Lokması için enfes ve pratik bir tarif.', 'status' => $durum, 'verification_status' => 'verified', 'servings' => 4]);
        $grup = $tarif->ingredientGroups()->create(['name' => 'Malzemeler', 'sort_order' => 0]);
        $un = Ingredient::query()->create(['name' => 'Un', 'slug' => 'un', 'is_active' => true]);
        $tarif->recipeIngredients()->create(['ingredient_id' => $un->id, 'recipe_ingredient_group_id' => $grup->id, 'group_name' => 'Malzemeler', 'display_quantity' => 200, 'display_measure_code' => 'gram', 'sort_order' => 1]);
        $adim = $tarif->steps()->create(['position' => 1, 'title' => 'Adım 1', 'instruction' => 'Malzemeleri tezgaha dizerek hazırlık yapın.']);
        ContentTranslation::query()->create(['translatable_type' => $adim->getMorphClass(), 'translatable_id' => $adim->id, 'locale' => 'en', 'field' => 'instruction', 'value' => 'In step 1, carefully proceed with the culinary preparation.']);
        ContentTranslation::query()->create(['translatable_type' => $tarif->getMorphClass(), 'translatable_id' => $tarif->id, 'locale' => 'en', 'field' => 'title', 'value' => 'Kıbrıs Lokması (Authentic Mediterranean Recipe)']);

        return $tarif;
    }

    private function dosya(array $adimlar = []): string
    {
        $dosya = tempnam(sys_get_temp_dir(), 'coni-taslak');
        file_put_contents($dosya, (string) json_encode(['lokma-test' => [
            'recipe' => ['title' => 'Kıbrıs Lokması', 'excerpt' => 'Mayalı hamur sıcak yağda kızarır, soğuk şerbete atılır.', 'description' => 'Hamur cıvık ve kabarmış olmalı; yağ 175 derecede tutulursa lokma içi boş ve çıtır kalır.', 'prep_minutes' => 15, 'cook_minutes' => 20, 'rest_minutes' => 60, 'servings' => 6, 'difficulty' => 'medium', 'country' => 'Kıbrıs'],
            'groups' => ['Ana malzemeler', 'Şerbet'],
            'steps' => $adimlar ?: [
                ['title' => 'Hamuru mayala', 'instruction' => 'Un, maya, tuz ve ılık suyu çırpıp cıvık bir hamur yapın; üzerini örtüp 1 saat kabarmaya bırakın.', 'duration_seconds' => 3600],
                ['title' => 'Şerbeti soğut', 'instruction' => 'Şeker, su ve limon suyunu 10 dakika kaynatın, lokmalar kızarana kadar tamamen soğumaya bırakın.'],
                ['title' => 'Lokmaları kızart', 'instruction' => 'Yağı 175 dereceye ısıtın, hamuru elinizle sıkıp kaşıkla koparın ve altın rengi olana kadar kızartın.', 'chef_tip' => 'Kaşığı her seferinde suya batırın, hamur yapışmaz.'],
            ],
        ]], JSON_UNESCAPED_UNICODE));

        return $dosya;
    }

    public function test_taslagi_bastan_yazar_yayinlamaz_ve_geri_alir(): void
    {
        Queue::fake();
        $tarif = $this->sablonTaslak();
        $eskiAdim = $tarif->steps()->firstOrFail()->id;
        $dosya = $this->dosya();

        $this->artisan('coni:taslak', ['dosya' => $dosya])->expectsOutputToContain('1 taslak yazıldı')->assertSuccessful();

        $taze = $tarif->fresh();
        $this->assertSame('draft', $taze->status);
        $this->assertSame([15, 20, 60, 6, 'medium'], [$taze->prep_minutes, $taze->cook_minutes, $taze->rest_minutes, $taze->servings, $taze->difficulty]);
        $this->assertSame(['Hamuru mayala', 'Şerbeti soğut', 'Lokmaları kızart'], $taze->steps()->pluck('title')->all());
        $this->assertSame('Kaşığı her seferinde suya batırın, hamur yapışmaz.', $taze->steps()->where('position', 3)->value('chef_tip'));
        $this->assertSame(['Ana malzemeler', 'Şerbet'], $taze->ingredientGroups()->orderBy('sort_order')->pluck('name')->all());
        $this->assertSame(0, RecipeIngredient::query()->where('recipe_id', $tarif->id)->count());
        $this->assertSame(0, ContentTranslation::query()->count(), 'Kalıp çeviriler yeni adımlara ait değil.');

        $rapor = IntegrityReport::query()->where('guard', 'coni:taslak')->firstOrFail();
        $this->artisan('coni:taslak', ['--geri-al' => $rapor->id])->assertSuccessful();

        $geri = $tarif->fresh();
        $this->assertSame('Kıbrıs Lokması için enfes ve pratik bir tarif.', $geri->excerpt);
        $this->assertSame([$eskiAdim], $geri->steps()->pluck('id')->all());
        $this->assertSame(['Malzemeler'], $geri->ingredientGroups()->pluck('name')->all());
        $this->assertSame(1, RecipeIngredient::query()->where('recipe_id', $tarif->id)->count());
        $this->assertSame(2, ContentTranslation::query()->count());
        $this->assertSame('geri_alindi', $rapor->fresh()->status);
        unlink($dosya);
    }

    public function test_yayindaki_tarife_yazmaz(): void
    {
        $tarif = $this->sablonTaslak('published');
        $dosya = $this->dosya();

        $this->artisan('coni:taslak', ['dosya' => $dosya])->expectsOutputToContain('yayında')->assertFailed();

        $this->assertSame(['Adım 1'], $tarif->steps()->pluck('title')->all());
        unlink($dosya);
    }

    public function test_adim_n_basligi_ve_kisa_yonerge_reddedilir(): void
    {
        $tarif = $this->sablonTaslak();
        $dosya = $this->dosya([
            ['title' => 'Adım 1', 'instruction' => 'Un, maya, tuz ve ılık suyu çırpıp cıvık bir hamur yapın; 1 saat bekletin.'],
            ['title' => 'Şerbeti soğut', 'instruction' => 'Şerbeti kaynatın.'],
            ['title' => 'Kızart', 'instruction' => 'Yağı 175 dereceye ısıtın ve hamuru kaşıkla koparıp kızartın.'],
        ]);

        $this->artisan('coni:taslak', ['dosya' => $dosya])->expectsOutputToContain('1. adım')->assertFailed();

        $this->assertSame(1, RecipeStep::query()->where('recipe_id', $tarif->id)->count());
        unlink($dosya);
    }
}
