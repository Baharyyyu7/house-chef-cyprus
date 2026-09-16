<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Models\Cuisine;
use App\Models\Recipe;
use App\Models\RecipeStep;
use App\Services\StepDurationParser;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use Tests\TestCase;

/**
 * İnteraktif mutfak zamanlayıcısı.
 *
 * AdSense dwell time hedefi: süre bilgisi taşıyan her adımda tek tıkla
 * başlayan yapışkan bir geri sayım. Süre önce duration_seconds alanından,
 * yoksa yönerge metnindeki Türkçe süre ifadesinden gelir.
 */
final class CookingTimerTest extends TestCase
{
    use RefreshDatabase;

    public function test_sure_ifadeleri_dakikaya_cevriliir(): void
    {
        $ayristirici = new StepDurationParser();

        $this->assertSame(15, $ayristirici->minutes('15 dakika dinlendirin.'));
        $this->assertSame(45, $ayristirici->minutes('45 dk fırında pişirin'));
        $this->assertSame(60, $ayristirici->minutes('1 saat pişirin'));
        $this->assertSame(75, $ayristirici->minutes('1 saat 15 dakika kapağı açmadan bekleyin'));
        $this->assertSame(12, $ayristirici->minutes('10-12 dakika kavurun'));
        $this->assertSame(2, $ayristirici->minutes('90 saniye çırpın'));
    }

    public function test_sure_olmayan_metinler_elenir(): void
    {
        $ayristirici = new StepDurationParser();

        $this->assertNull($ayristirici->minutes('180 derecede fırına verin'));
        $this->assertNull($ayristirici->minutes('5 kişilik harcı yoğurun'));
        $this->assertNull($ayristirici->minutes('30 saniye dinlendirin'));
        $this->assertNull($ayristirici->minutes(''));
        $this->assertNull($ayristirici->minutes(null));
        $this->assertNull($ayristirici->minutes('5 saat mayalandırın 6 saat dinlendirin 7 saat bekletin'));
    }

    public function test_sureli_adimda_zamanlayici_butonu_gorunur(): void
    {
        $tarif = $this->tarif('Zamanlayıcılı Tarif');
        RecipeStep::query()->create([
            'recipe_id' => $tarif->id, 'position' => 1,
            'title' => 'Fırında pişir', 'instruction' => 'Tepsiyi fırına verin.',
            'duration_seconds' => 900,
        ]);

        $icerik = (string) $this->get(route('recipes.show', $tarif))->assertOk()->getContent();

        $this->assertStringContainsString('hc-start-timer', $icerik);
        $this->assertStringContainsString('seconds: 900', $icerik);
    }

    public function test_metinde_sure_varsa_buton_uretilir(): void
    {
        $tarif = $this->tarif('Metinden Süreli Tarif');
        RecipeStep::query()->create([
            'recipe_id' => $tarif->id, 'position' => 1,
            'title' => 'Dinlendir', 'instruction' => 'Hamuru 20 dakika dinlendirin.',
        ]);

        $icerik = (string) $this->get(route('recipes.show', $tarif))->assertOk()->getContent();

        $this->assertStringContainsString('hc-start-timer', $icerik);
        $this->assertStringContainsString('seconds: 1200', $icerik);
    }

    public function test_suresiz_adimda_buton_yoktur(): void
    {
        $tarif = $this->tarif('Süresiz Tarif');
        RecipeStep::query()->create([
            'recipe_id' => $tarif->id, 'position' => 1,
            'title' => 'Servis et', 'instruction' => 'Sıcak servis yapın.',
        ]);

        $icerik = (string) $this->get(route('recipes.show', $tarif))->assertOk()->getContent();

        // Yapışkan zamanlayıcı kökü her sayfada durur (gizli); adım butonu yoktur.
        $this->assertStringNotContainsString('seconds:', $icerik);
    }

    private function tarif(string $baslik): Recipe
    {
        $mutfak = Cuisine::query()->create(['name' => 'Kıbrıs Mutfağı', 'slug' => 'kibris-mutfagi']);

        return Recipe::query()->create([
            'title' => $baslik,
            'slug' => Str::slug($baslik).'-'.uniqid(),
            'excerpt' => 'Özet.',
            'description' => 'Açıklama metni.',
            'status' => 'published',
            'verification_status' => 'verified',
            'published_at' => now(),
            'cuisine_id' => $mutfak->id,
        ]);
    }
}
