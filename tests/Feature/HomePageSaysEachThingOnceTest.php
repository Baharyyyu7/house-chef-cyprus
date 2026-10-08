<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Models\Recipe;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use Tests\TestCase;

/**
 * 2026-10 denetimi: ana sayfa mobilde 11.500 px'ti; "Tarif arşivine git" 4,
 * "Mutfak seçkisi" 3, "Hızlı giriş" ve "Bugün ne pişirsem?" 2'şer kez
 * geçiyordu. Hero'da CTA'sı olmayan bir "villana özel şef teklifi" vaadi
 * vardı. Gösteri ekran dışındayken de requestAnimationFrame ile dönüyordu.
 */
final class HomePageSaysEachThingOnceTest extends TestCase
{
    use RefreshDatabase;

    public function test_tekrar_eden_bloklar_teke_indi_ve_bos_vaat_yok(): void
    {
        $icerik = (string) $this->get(route('home'))->assertOk()->getContent();

        $this->assertSame(1, mb_substr_count($icerik, 'Hızlı giriş'));
        $this->assertLessThanOrEqual(1, mb_substr_count($icerik, 'Bugün ne pişirsem?'));
        $this->assertLessThanOrEqual(1, mb_substr_count($icerik, 'Mutfak seçkisi'));
        $this->assertLessThanOrEqual(2, mb_substr_count($icerik, 'Tarif arşivine git'));
        $this->assertStringNotContainsString('villana', $icerik);
    }

    public function test_ingilizce_ana_sayfada_kapanis_menusu_turkce_sizdirmaz(): void
    {
        $this->get('/en')->assertOk()->assertDontSee('aria-label="Tarif koleksiyonları"', false);
    }

    public function test_eslestirme_gosterisi_gorunurken_calisir_ve_css_ile_sayar(): void
    {
        $js = (string) file_get_contents(resource_path('js/app.js'));
        $baslangic = strpos($js, "Alpine.data('hcMatchShowcase'");
        $govde = substr($js, $baslangic, strpos($js, "Alpine.data('hcRecipeSteps'") - $baslangic);

        $this->assertStringContainsString('IntersectionObserver', $govde);
        $this->assertStringContainsString('visibilitychange', $govde);
        $this->assertStringContainsString('prefers-reduced-motion', $govde);
        $this->assertStringNotContainsString('requestAnimationFrame', $govde);

        $css = (string) file_get_contents(resource_path('css/app.css'));
        $this->assertStringContainsString('@property --hc-pct', $css);
    }

    public function test_cerez_paneli_backdrop_filter_kullanmaz_ve_cipler_44px(): void
    {
        $css = (string) file_get_contents(resource_path('css/app.css'));
        preg_match('/\.hc-consent__panel \{(.*?)\}/s', $css, $panel);
        $this->assertStringNotContainsString('backdrop-filter', $panel[1] ?? 'yok');
        $this->assertMatchesRegularExpression('/\.hc-home-command__links a \{[^}]*min-height:2\.75rem/', $css);
    }

    public function test_tarif_basi_paylas_dugmeleri_opak_acik_zeminde(): void
    {
        $tarif = Recipe::create(['title' => 'Hellim tava', 'slug' => 'hellim-tava-'.Str::lower(Str::random(5)), 'excerpt' => 'Özet.', 'servings' => 2, 'prep_minutes' => 5, 'cook_minutes' => 5, 'rest_minutes' => 0, 'difficulty' => 'Kolay', 'status' => 'published', 'verification_status' => 'verified', 'published_at' => now()->subDay()]);

        $this->get(route('recipes.show', $tarif))->assertOk()->assertSee('hc-share-btn--light', false);
        $this->assertStringContainsString('.hc-share-btn--light.hc-v3-btn { background-color: #fff', (string) file_get_contents(resource_path('css/app.css')));
    }
}
