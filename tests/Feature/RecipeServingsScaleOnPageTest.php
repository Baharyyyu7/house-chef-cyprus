<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Models\Ingredient;
use App\Models\Recipe;
use App\Models\Unit;
use App\Services\Measurements\HouseholdMeasurementFormatter;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use Tests\TestCase;

/**
 * 2026-10 denetimi: porsiyon "+" düğmesi sayfayı yeniliyor ve mobilde en başa
 * atıyordu; değerler "1,08 paket Kuru Maya 10.833 g", "4 yarım su bardağı",
 * "1 cay_kasigi", "2 dis", "3 item" gibi çıkıyordu.
 */
final class RecipeServingsScaleOnPageTest extends TestCase
{
    use RefreshDatabase;

    private function birim(string $kod, bool $kesir = true): Unit
    {
        return new Unit(['code' => $kod, 'name' => $kod, 'singular_name' => $kod, 'plural_name' => $kod, 'supports_fractions' => $kesir, 'precision' => 2]);
    }

    public function test_ev_olcusu_kesire_metrik_tam_sayiya_yuvarlanir(): void
    {
        app()->setLocale('tr');
        $bicim = app(HouseholdMeasurementFormatter::class);

        $this->assertSame('4½', $bicim->quantity('4.5', $this->birim('su_bardagi')));
        $this->assertSame('1', $bicim->quantity('1.08', $this->birim('paket', false)));
        $this->assertSame('⅓', $bicim->quantity('0.3333', $this->birim('su_bardagi')));
        $this->assertSame('1¾', $bicim->quantity('1.75', $this->birim('yemek_kasigi')));
        $this->assertSame('2', $bicim->quantity('1.95', $this->birim('cay_kasigi')));
        $this->assertSame('11', $bicim->quantity('10.833', $this->birim('g')));
        $this->assertSame('11', $bicim->kanonikMiktar('10.833', 'g'));
        $this->assertSame('2,5', $bicim->kanonikMiktar('2.5', 'g'));
        $this->assertSame('1,25', $bicim->kanonikMiktar('1.25', 'kg'));
    }

    public function test_turkce_kanonik_birim_insan_adiyla_basilir(): void
    {
        app()->setLocale('tr');
        $bicim = app(HouseholdMeasurementFormatter::class);

        $this->assertSame('çay kaşığı', $bicim->birimKodu('cay_kasigi', '1'));
        $this->assertSame('diş', $bicim->birimKodu('dis', '2'));
        $this->assertSame('adet', $bicim->birimKodu('item', '3'));
        $this->assertSame('g', $bicim->birimKodu('g', '100'));

        app()->setLocale('en');
        $this->assertSame('', $bicim->birimKodu('item', '3'));
    }

    public function test_ikinci_olcu_satiri_yalniz_farkli_birimde(): void
    {
        $bicim = app(HouseholdMeasurementFormatter::class);

        $this->assertFalse($bicim->ikinciSatirGerekli('dis', 'dis'));
        $this->assertFalse($bicim->ikinciSatirGerekli('item', null));
        $this->assertFalse($bicim->ikinciSatirGerekli('g', 'gram'));
        $this->assertTrue($bicim->ikinciSatirGerekli('g', 'su_bardagi'));
    }

    public function test_ingilizce_sayilabilir_ad_sayiya_uyar(): void
    {
        app()->setLocale('en');
        $bicim = app(HouseholdMeasurementFormatter::class);

        $this->assertSame('egg yolk', $bicim->adSayiyla('egg yolks', '1', ''));
        $this->assertSame('onions', $bicim->adSayiyla('onion', '3', ''));
        $this->assertSame('flour', $bicim->adSayiyla('flour', '2', 'tbsp'));

        app()->setLocale('tr');
        $this->assertSame('soğan', $bicim->adSayiyla('soğan', '3', ''));
    }

    public function test_sayfa_porsiyon_haritasini_gomer_ve_form_malzemeye_doner(): void
    {
        Unit::query()->create(['public_id' => (string) Str::ulid(), 'code' => 'su_bardagi', 'name' => 'su bardağı', 'singular_name' => 'su bardağı', 'plural_name' => 'su bardağı', 'unit_type' => 'household', 'supports_fractions' => true, 'precision' => 2, 'is_active' => true]);
        $tarif = Recipe::create(['title' => 'Pilav', 'slug' => 'pilav-'.Str::lower(Str::random(5)), 'excerpt' => 'Özet.', 'servings' => 4, 'prep_minutes' => 5, 'cook_minutes' => 5, 'rest_minutes' => 0, 'difficulty' => 'Kolay', 'status' => 'published', 'verification_status' => 'verified', 'published_at' => now()->subDay()]);
        $pirinc = Ingredient::create(['name' => 'Pirinç', 'canonical_name' => 'Pirinç', 'slug' => 'pirinc-'.Str::lower(Str::random(5)), 'is_active' => true]);
        $tarif->ingredients()->attach($pirinc->id, ['quantity' => '2', 'display_quantity' => '2', 'display_measure_code' => 'su_bardagi', 'canonical_quantity' => '360', 'canonical_unit' => 'g', 'unit' => 'su_bardagi', 'sort_order' => 1]);

        $sayfa = $this->get(route('recipes.show', $tarif))->assertOk();

        $sayfa->assertSee('id="hc-porsiyon-haritasi"', false)
            ->assertSee('action="#ingredients"', false)
            ->assertSee('2 su bardağı Pirinç')
            ->assertSee('360 g');

        // 9 porsiyon = 4½ su bardağı, 810 g; harita sunucunun biçimini taşır.
        preg_match('#<script type="application/json" id="hc-porsiyon-haritasi">(.*?)</script>#s', (string) $sayfa->getContent(), $eslesme);
        $harita = json_decode($eslesme[1] ?? 'null', true);
        $this->assertSame(['4½ su bardağı Pirinç', '810 g'], array_values($harita['9'])[0]);
        $this->assertCount(20, $harita);
        $this->assertStringNotContainsString('yarım', (string) $sayfa->getContent());
    }
}
