<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Models\Ingredient;
use App\Models\Recipe;
use App\Models\RecipeStep;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Bir tarifin çevirisi gövdesini de kapsar.
 *
 * Tarifin gövdesi kendi sütunlarında değil, adımlarında ve malzemelerinde
 * durur. Yalnız başlık/özet/açıklama çevrilirse ortaya İngilizce başlıklı,
 * Türkçe yönergeli bir sayfa çıkar. Kapı tam olarak bunu engellemek için var.
 */
final class RecipeTranslationCoversTheBodyTest extends TestCase
{
    use RefreshDatabase;

    private function ceviri(object $kayit, string $alan, string $deger): void
    {
        $kayit->translations()->create([
            'locale' => 'en',
            'field' => $alan,
            'value' => $deger,
        ]);
    }

    private function tarifKur(): Recipe
    {
        $tarif = Recipe::query()->create([
            'title' => 'Hellimli Börek',
            'slug' => 'hellimli-borek-'.uniqid(),
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
            'instruction' => 'Yufkayı ince aç ve hellimi serp.',
        ]);

        $malzeme = Ingredient::query()->create(['name' => 'hellim', 'slug' => 'hellim']);
        $tarif->ingredients()->attach($malzeme->id, ['quantity' => 200, 'unit' => 'g']);

        foreach (['title' => 'Halloumi Pastry', 'excerpt' => 'Cypriot style', 'description' => 'With thin phyllo'] as $alan => $deger) {
            $this->ceviri($tarif, $alan, $deger);
        }

        return $tarif->fresh();
    }

    public function test_govdesi_turkce_kalan_tarif_cevrilmis_sayilmaz(): void
    {
        $tarif = $this->tarifKur();

        $this->assertFalse(
            $tarif->hasTranslation('en'),
            'Adımları ve malzemeleri Türkçe kalan tarif İngilizce sayıldı.'
        );

        $this->assertSame(
            0,
            Recipe::query()->translatedInto('en')->count(),
            'Yarı çevrilmiş tarif listelemeye/sitemap\'e sızdı.'
        );
    }

    public function test_govdesi_de_cevrilen_tarif_yayinlanabilir(): void
    {
        $tarif = $this->tarifKur();

        $adim = $tarif->steps()->first();
        $this->ceviri($adim, 'title', 'Roll the dough');
        $this->ceviri($adim, 'instruction', 'Roll the phyllo thin and scatter the halloumi.');
        $this->ceviri($tarif->ingredients()->first(), 'name', 'halloumi');

        $tarif = $tarif->fresh();

        $this->assertTrue($tarif->hasTranslation('en'), 'Tamamı çevrilmiş tarif reddedildi.');
        $this->assertSame(1, Recipe::query()->translatedInto('en')->count());
    }

    public function test_kapsam_ile_kapi_ayni_sonucu_verir(): void
    {
        // hreflang bu ikisinin aynı cevabı vermesine dayanıyor: ayrışırlarsa
        // hreflang listede olmayan bir sayfayı gösterir ve Google kümenin
        // tamamını atar.
        $tarif = $this->tarifKur();
        $adim = $tarif->steps()->first();
        $this->ceviri($adim, 'title', 'Roll the dough');

        $kapi = $tarif->fresh()->hasTranslation('en');
        $kapsam = Recipe::query()->translatedInto('en')->count() === 1;

        $this->assertSame($kapi, $kapsam, 'Kapı ve kapsam farklı cevap verdi.');
        $this->assertFalse($kapi, 'Yönergesi çevrilmemiş adım kapıdan geçti.');
    }
}
