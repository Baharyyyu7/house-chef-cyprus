<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Cuisine;
use App\Models\Recipe;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * İngilizce sayfada Türkçe sızıntı: "Bu tarifi kaydedin.", DB'ye Türkçe
 * yazılmış "Sentetik görsel · House Chef Cyprus" atfı; dil seçici İngilizce
 * sürüm varken "Bu sayfa bu dilde yok" diyordu.
 */
final class EnglishRecipePageHasNoTurkishLeaksTest extends TestCase
{
    use RefreshDatabase;

    private function tarif(): Recipe
    {
        config()->set('locales.supported.en.enabled', true);
        $mutfak = Cuisine::query()->create(['slug' => 'turk-mutfagi', 'name' => 'Türk Mutfağı']);
        $kategori = Category::query()->create(['slug' => 'tatli', 'name' => 'Tatlı']);
        $tarif = Recipe::query()->create([
            'cuisine_id' => $mutfak->id, 'category_id' => $kategori->id,
            'title' => 'Cennet Çamuru', 'slug' => 'cennet-camuru', 'excerpt' => 'Özet.', 'description' => 'Kadayıflı tatlı.',
            'servings' => 4, 'prep_minutes' => 10, 'cook_minutes' => 25,
            'status' => 'published', 'verification_status' => 'verified', 'published_at' => now(),
            'tips' => ['Şerbeti ılık dökün, kadayıf yumuşamasın.'],
            'common_mistakes' => ['Kadayıfı yüksek ateşte yakmak.'],
            'equipment' => ['Geniş tava'],
        ]);

        foreach (['title' => 'Cennet Camuru', 'excerpt' => 'Summary.', 'description' => 'Kadayif dessert.'] as $alan => $deger) {
            $tarif->translations()->create(['locale' => 'en', 'field' => $alan, 'value' => $deger]);
        }

        foreach (['Boil the sugar and water for five minutes.', 'Toast the kadayif in butter over low heat.', 'Pour in the syrup and finish with pistachio.'] as $sira => $talimat) {
            $adim = $tarif->steps()->create(['position' => $sira + 1, 'title' => 'Adım '.($sira + 1), 'instruction' => 'Türkçe adım '.($sira + 1).'.']);
            $adim->translations()->create(['locale' => 'en', 'field' => 'title', 'value' => 'Step '.($sira + 1)]);
            $adim->translations()->create(['locale' => 'en', 'field' => 'instruction', 'value' => $talimat]);
        }

        return $tarif;
    }

    public function test_turkce_sayfadaki_dil_secici_var_olan_ingilizce_surume_baglanir(): void
    {
        $this->tarif();

        $icerik = (string) $this->get('/tarifler/cennet-camuru')->assertOk()->getContent();
        $this->assertStringContainsString('/en/recipes/cennet-camuru"', $icerik);
        $this->assertDoesNotMatchRegularExpression('#is-pending[^>]*>\s*<span class="hc-locale__flag"[^>]*>[^<]*</span>\s*<span class="hc-locale__name">English#', $icerik);

        $ingilizce = (string) $this->get('/en/recipes/cennet-camuru')->assertOk()->getContent();
        $this->assertStringContainsString('/tarifler/cennet-camuru"', $ingilizce);
        $this->assertStringNotContainsString('Bu sayfa bu dilde yok', $ingilizce);
    }

    public function test_ingilizce_tarif_sayfasi_uyelik_karti_turkce_basmaz(): void
    {
        $this->tarif();

        $this->get('/en/recipes/cennet-camuru')->assertOk()
            ->assertDontSee('Bu tarifi kaydedin.')
            ->assertSee(__('ui.save_this_recipe', [], 'en'));
    }
}
