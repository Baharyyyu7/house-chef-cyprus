<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Models\Ingredient;
use App\Models\Recipe;
use App\Models\RecipeStep;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use ReflectionMethod;
use Tests\TestCase;

/**
 * scopeTranslatedInto artık dil başına önbelleklenmiş id kümesiyle
 * çalışıyor. Sonuç, önbelleksiz tam tanımla ve hasTranslation() ile birebir
 * aynı kalmalı; hreflang, sitemap ve listeleme bu üçünün eşitliğine dayanıyor.
 */
final class RecipeTranslatedIdsCacheTest extends TestCase
{
    use RefreshDatabase;

    public function test_cached_scope_matches_the_uncached_definition_and_the_gate(): void
    {
        $tam = $this->tarif('tam', adimCevrili: true, malzemeCevrili: true);
        $adimEksik = $this->tarif('adim-eksik', adimCevrili: false, malzemeCevrili: true);
        $malzemeEksik = $this->tarif('malzeme-eksik', adimCevrili: true, malzemeCevrili: false);
        $taslak = $this->tarif('taslak', adimCevrili: true, malzemeCevrili: true, durum: 'draft');

        $onbellekli = Recipe::query()->translatedInto('en')->pluck('id')->sort()->values()->all();
        $kapi = Recipe::query()->get()->filter(fn (Recipe $r): bool => $r->hasTranslation('en'))->pluck('id')->sort()->values()->all();

        $this->assertSame($this->onbelleksiz('en'), $onbellekli);
        $this->assertSame($kapi, $onbellekli);
        $this->assertContains($tam->id, $onbellekli);
        $this->assertContains($taslak->id, $onbellekli, 'Kapsam yalnız çeviriye bakar; durum filtresi dış sorgunun işi.');
        $this->assertNotContains($adimEksik->id, $onbellekli);
        $this->assertNotContains($malzemeEksik->id, $onbellekli);

        // Dış sorgunun kısıtları korunur.
        $this->assertSame([$tam->id], Recipe::query()->where('status', 'published')->translatedInto('en')->orderBy('id')->pluck('id')->all());
        // Varsayılan dil önbelleğe gitmez, her şeyi döndürür.
        $this->assertSame(4, Recipe::query()->translatedInto('tr')->count());
    }

    public function test_the_second_query_skips_the_expensive_subqueries(): void
    {
        $this->tarif('tam', adimCevrili: true, malzemeCevrili: true);
        Recipe::query()->translatedInto('en')->count();

        DB::enableQueryLog();
        Recipe::query()->translatedInto('en')->count();
        $sorgular = DB::getQueryLog();
        DB::disableQueryLog();

        $this->assertCount(1, $sorgular);
        $this->assertStringNotContainsString('not exists', strtolower($sorgular[0]['query']));
    }

    public function test_saving_a_translation_or_step_refreshes_the_set(): void
    {
        $tarif = $this->tarif('sonradan', adimCevrili: false, malzemeCevrili: true);
        $this->assertSame(0, Recipe::query()->translatedInto('en')->count());

        $adim = $tarif->steps()->first();
        $adim->translations()->create(['locale' => 'en', 'field' => 'instruction', 'value' => 'Roll the phyllo thin.']);
        $this->assertSame(1, Recipe::query()->translatedInto('en')->count(), 'Çeviri kaydı önbelleği düşürmedi.');

        RecipeStep::query()->create(['recipe_id' => $tarif->id, 'position' => 2, 'title' => 'Pişir', 'instruction' => 'Fırına ver.']);
        $this->assertSame(0, Recipe::query()->translatedInto('en')->count(), 'Yeni çevrilmemiş adım önbelleği düşürmedi.');
    }

    private function tarif(string $ad, bool $adimCevrili, bool $malzemeCevrili, string $durum = 'published'): Recipe
    {
        $tarif = Recipe::query()->create([
            'title' => 'Tarif '.$ad, 'slug' => 'tarif-'.$ad, 'excerpt' => 'Özet', 'description' => 'Açıklama',
            'status' => $durum, 'verification_status' => 'verified', 'published_at' => now(),
        ]);
        foreach (['title' => 'Recipe '.$ad, 'excerpt' => 'Summary', 'description' => 'Description'] as $alan => $deger) {
            $tarif->translations()->create(['locale' => 'en', 'field' => $alan, 'value' => $deger]);
        }

        $adim = RecipeStep::query()->create(['recipe_id' => $tarif->id, 'position' => 1, 'title' => 'Hamur', 'instruction' => 'Yufkayı ince aç.']);
        $adim->translations()->create(['locale' => 'en', 'field' => 'title', 'value' => 'Dough']);
        if ($adimCevrili) {
            $adim->translations()->create(['locale' => 'en', 'field' => 'instruction', 'value' => 'Roll the phyllo thin.']);
        }

        $malzeme = Ingredient::query()->create(['name' => 'hellim '.$ad, 'slug' => 'hellim-'.$ad]);
        $tarif->ingredients()->attach($malzeme->id, ['quantity' => 200, 'unit' => 'g']);
        if ($malzemeCevrili) {
            $malzeme->translations()->create(['locale' => 'en', 'field' => 'name', 'value' => 'halloumi '.$ad]);
        }

        return $tarif->fresh();
    }

    /** @return list<int> */
    private function onbelleksiz(string $locale): array
    {
        $yontem = new ReflectionMethod(Recipe::class, 'tamCeviriKapsami');

        return $yontem->invoke(new Recipe, Recipe::query(), $locale)->pluck('id')->map(fn ($id): int => (int) $id)->sort()->values()->all();
    }
}
