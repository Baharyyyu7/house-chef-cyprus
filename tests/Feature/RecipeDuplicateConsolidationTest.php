<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Models\Ingredient;
use App\Models\Recipe;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Şablon kopya, aynı yemeğin gerçek tarifini kanonik gösterir.
 *
 * 17 Eylül 2026: kapıya takılan 759 tarifin 177'sinin aynı adlı, kapıdan
 * geçen bir eşi de yayındaydı. Kopyayı yeniden yazmak gereksiz; sinyaller
 * tek adreste toplanmalı.
 */
final class RecipeDuplicateConsolidationTest extends TestCase
{
    use RefreshDatabase;

    private const SABLON = 'Malzemeleri yikayin, dograyin ve tezgahta sirayla dizerek hazirligi tamamlayin.';

    private function tarif(string $slug, string $baslik, bool $sablon, string $yayin = '-2 days'): Recipe
    {
        $tarif = Recipe::query()->create([
            'title' => $baslik, 'slug' => $slug, 'excerpt' => 'Özet.',
            'description' => $sablon ? 'Şablon açıklama.' : 'Bu tarifin kendine ait açıklaması burada duruyor efendim.',
            'status' => 'published', 'verification_status' => 'verified',
            'published_at' => now()->modify($yayin), 'servings' => 4, 'prep_minutes' => 10, 'cook_minutes' => 20,
        ]);

        $yonergeler = $sablon
            ? [self::SABLON, self::SABLON, self::SABLON]
            : [
                'Hellimi bir santim kalınlığında dilimleyip soğuk suda beş dakika bekletin ve kurulayın.',
                'Tavayı zeytinyağıyla orta ateşte ısıtıp her yüzü kırk beş saniye kızartın.',
                'Dilimlenmiş domates ve taze naneyle sıcak servis edin, karabiber öğütün.',
            ];

        foreach ($yonergeler as $i => $yonerge) {
            $tarif->steps()->create(['position' => $i + 1, 'title' => 'Adım '.($i + 1), 'instruction' => $yonerge]);
        }

        // Kapı üçten az malzemeli tarifi eksik sayar; şablon kopya üretimde de
        // böyleydi (yer tutucu malzeme listesi).
        $malzemeSayisi = $sablon ? 2 : 4;

        for ($i = 1; $i <= $malzemeSayisi; $i++) {
            $malzeme = Ingredient::query()->firstOrCreate(['slug' => 'malzeme-'.$i], ['name' => 'Malzeme '.$i]);
            $tarif->recipeIngredients()->create(['ingredient_id' => $malzeme->id, 'quantity' => 1, 'unit' => 'adet', 'sort_order' => $i]);
        }

        return $tarif;
    }

    public function test_kopya_gercek_esini_kanonik_gosterir(): void
    {
        $iyi = $this->tarif('hellim-tavasi-iyi', 'Hellim Tavası', sablon: false, yayin: '-10 days');
        $kopya1 = $this->tarif('hellim-tavasi-kopya-A', 'Hellim Tavası', sablon: true);
        $this->tarif('baska-yemek', 'Mercimek Çorbası', sablon: true);

        $this->artisan('seo:recipe-duplicate-consolidation --apply')->assertSuccessful();

        $kopya1->refresh()->load('seoMetadata');
        $this->assertSame(route('recipes.show', $iyi), $kopya1->seoMetadata?->canonical_override);
        $this->assertStringContainsString('noindex', (string) $kopya1->seoMetadata?->robots);
        $this->assertSame('published', $kopya1->status, 'İçerik silinmemeli.');
        $this->assertNull($iyi->fresh('seoMetadata')->seoMetadata?->canonical_override, 'Gerçek tarife dokunulmamalı.');
    }

    public function test_esi_olmayan_sablon_tarif_beklemede_kalir(): void
    {
        $tek = $this->tarif('tek-sablon', 'Tek Başına Şablon', sablon: true);

        $this->artisan('seo:recipe-duplicate-consolidation --apply')->assertSuccessful();

        $this->assertNull($tek->fresh('seoMetadata')->seoMetadata?->canonical_override);
    }

    public function test_rapor_modu_hicbir_seyi_yazmaz(): void
    {
        $this->tarif('hellim-tavasi-iyi', 'Hellim Tavası', sablon: false, yayin: '-10 days');
        $kopya = $this->tarif('hellim-tavasi-kopya-A', 'Hellim Tavası', sablon: true);

        $this->artisan('seo:recipe-duplicate-consolidation')->assertSuccessful();

        $this->assertNull($kopya->fresh('seoMetadata')->seoMetadata?->canonical_override);
    }
}
