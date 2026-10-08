<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Models\SiteFeature;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * "Ne Pişirebilirim?" misafir menüsünde yoktu; footer'a bir iç not
 * ("Sosyal kanallar ... yayına alındığında") sızmıştı ve formu olmayan bir
 * "Bülten" başlığı abonelik vaat ediyordu.
 */
final class PublicChromeIsHonestTest extends TestCase
{
    use RefreshDatabase;

    public function test_misafir_menusunde_ne_pisirebilirim_var(): void
    {
        $icerik = (string) $this->get(route('recipes.index'))->assertOk()->getContent();

        $this->assertGreaterThanOrEqual(2, substr_count($icerik, 'href="'.route('ingredients.explore').'"'));
    }

    public function test_ozellik_kapaliyken_baglanti_yok(): void
    {
        SiteFeature::query()->create(['key' => 'explore', 'enabled' => false]);

        $this->get(route('recipes.index'))->assertOk()->assertDontSee('href="'.route('ingredients.explore').'"', false);
    }

    public function test_footer_ic_not_ve_formsuz_bulten_basmaz(): void
    {
        $this->get(route('recipes.index'))->assertOk()
            ->assertDontSee('yayına alındığında')
            ->assertDontSee('>Bülten<', false)
            ->assertSee('Ücretsiz üyelik');
    }
}
