<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Models\Ingredient;
use App\Models\Recipe;
use App\Models\RecipeIngredient;
use App\Models\RecipeStep;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Tarif detayındaki etkileşimli öğeler: adım zamanlayıcı düğmesi, oylama
 * widget'ı, yüzen sayaç ve "evde yoksa" ikame kutusu.
 *
 * Süresi olmayan adımda düğme basılmaz; ikamesi olmayan tarifte kutu
 * basılmaz. Boş öğe basmak hem CRO hem tarama gürültüsüdür.
 */
final class RecipeInteractiveElementsTest extends TestCase
{
    use RefreshDatabase;

    public function test_sureli_adimda_zamanlayici_dugmesi_vardir(): void
    {
        $tarif = $this->tarif();
        $this->adim($tarif, ['title' => "Anne'nin fırını", 'instruction' => 'Tepsiyi fırına verin.', 'duration_seconds' => 900]);

        $icerik = (string) $this->get(route('recipes.show', $tarif))->assertOk()->getContent();

        // 27 Eylül 2026'ya kadar öznitelik tek tırnakla açılıyordu ve @js de
        // tek tırnaklı dize üretiyordu: ifade "label: " da kesiliyor, hiçbir
        // zamanlayıcı düğmesi çalışmıyordu. Bu test o kesik hâli arıyordu.
        $this->assertStringContainsString('x-on:click="$dispatch(\'hc-start-timer\', { seconds: 900, label: \'Anne\u0027nin f', $icerik);
        $this->assertStringContainsString(' })">', $icerik);
    }

    public function test_yonergedeki_sure_dugmeye_donusebilir(): void
    {
        $tarif = $this->tarif();
        $this->adim($tarif, ['title' => 'Pişir', 'instruction' => 'Kısık ateşte 20 dakika pişirin.']);

        $icerik = (string) $this->get(route('recipes.show', $tarif))->assertOk()->getContent();

        $this->assertStringContainsString('x-on:click="$dispatch(\'hc-start-timer\', { seconds: 1200, label: \'Pişir\' })"', $icerik);
    }

    public function test_suresiz_adimda_dugme_yoktur(): void
    {
        $tarif = $this->tarif();
        $this->adim($tarif, ['title' => 'Karıştır', 'instruction' => 'Tuz ekleyip karıştırın.']);

        $icerik = (string) $this->get(route('recipes.show', $tarif))->assertOk()->getContent();

        $this->assertStringNotContainsString('$dispatch("hc-start-timer"', $icerik);
    }

    public function test_oylama_ve_yuzen_sayac_isaretlemesi_vardir(): void
    {
        $tarif = $this->tarif();

        $icerik = (string) $this->get(route('recipes.show', $tarif))->assertOk()->getContent();

        $this->assertStringContainsString('hcRecipeRating', $icerik);
        $this->assertStringContainsString('hcCookTimer', $icerik);
        $this->assertStringContainsString('role="timer"', $icerik);
    }

    public function test_ikame_kutusu_veriyle_basar_verisiz_basılmaz(): void
    {
        $tarif = $this->tarif();
        $this->ikame($tarif, 'Krema', 'Süzme yoğurt + tereyağı');

        $icerik = (string) $this->get(route('recipes.show', $tarif))->assertOk()->getContent();

        $this->assertStringContainsString('Evde Yoksa Ne Kullanılır?', $icerik);
        $this->assertStringContainsString('Süzme yoğurt + tereyağı', $icerik);
    }

    public function test_ikamesiz_tarifin_sayfasinda_kutu_yoktur(): void
    {
        $tarif = $this->tarif();

        $icerik = (string) $this->get(route('recipes.show', $tarif))->assertOk()->getContent();

        $this->assertStringNotContainsString('Evde Yoksa Ne Kullanılır?', $icerik);
    }

    public function test_ipucu_ve_ikame_semaya_yorum_olarak_girer(): void
    {
        $tarif = $this->tarif();
        $tarif->update(['tips' => ['Pişerken kapağı açmayın.']]);
        $this->makeRecipePubliclyEligible($tarif);
        $this->ikame($tarif, 'Krema', 'Süzme yoğurt + tereyağı');

        $dugum = $this->recipeNode($tarif->fresh());

        $yorumlar = collect($dugum['comment'] ?? [])->pluck('text')->all();
        $this->assertContains('Pişerken kapağı açmayın.', $yorumlar);
        $this->assertContains('Krema yoksa: Süzme yoğurt + tereyağı', $yorumlar);
    }

    private function tarif(): Recipe
    {
        return Recipe::query()->create([
            'title' => 'Yayla Çorbası',
            'slug' => 'yayla-corbasi-'.uniqid(),
            'excerpt' => 'Özet.',
            'description' => 'Yeterince uzun bir açıklama metni burada duruyor.',
            'servings' => 4,
            'prep_minutes' => 10,
            'cook_minutes' => 20,
            'status' => 'published',
            'verification_status' => 'verified',
            'published_at' => now(),
        ]);
    }

    /** @param array<string, mixed> $alanlar */
    private function adim(Recipe $tarif, array $alanlar): void
    {
        RecipeStep::query()->create($alanlar + ['recipe_id' => $tarif->id, 'position' => 1]);
    }

    private function ikame(Recipe $tarif, string $ad, string $yerine): void
    {
        $malzeme = Ingredient::query()->create(['name' => $ad, 'slug' => 'malzeme-'.uniqid()]);
        RecipeIngredient::query()->create(['recipe_id' => $tarif->id, 'ingredient_id' => $malzeme->id, 'substitution' => $yerine]);
    }

    /** @return array<string, mixed> */
    private function recipeNode(Recipe $tarif): array
    {
        $icerik = (string) $this->get(route('recipes.show', $tarif))->assertOk()->getContent();

        preg_match_all('#<script type="application/ld\+json">(.*?)</script>#s', $icerik, $eslesme);

        foreach ($eslesme[1] as $ham) {
            $veri = json_decode(trim($ham), true);

            foreach ($veri['@graph'] ?? [$veri] as $dugum) {
                if (($dugum['@type'] ?? null) === 'Recipe') {
                    return $dugum;
                }
            }
        }

        $this->fail('Sayfada Recipe işaretlemesi bulunamadı.');
    }
}
