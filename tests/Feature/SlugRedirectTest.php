<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Models\BlogPost;
use App\Models\Recipe;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Adresi değişen sayfa eski adresinden kalıcı olarak yönlenir.
 *
 * 19 Eylül 2026: Google'da gösterim alan tarif ve yazıların adresleri
 * değiştirilmiş, yönlendirme konmamıştı; sıralanan adresler 404 veriyordu.
 */
final class SlugRedirectTest extends TestCase
{
    use RefreshDatabase;

    private function tarif(string $slug): Recipe
    {
        $tarif = Recipe::query()->create([
            'title' => 'Çiğ Köfte', 'slug' => $slug, 'excerpt' => 'Özet.', 'description' => 'Açıklama metni.',
            'status' => 'published', 'verification_status' => 'verified', 'published_at' => now()->subDay(),
            'servings' => 4, 'prep_minutes' => 10, 'cook_minutes' => 0,
        ]);
        $this->makeRecipePubliclyEligible($tarif);

        return $tarif;
    }

    public function test_adresi_degisen_tarif_eski_adresten_301_ile_yonlenir(): void
    {
        $tarif = $this->tarif('cig-kofte-A96HF');
        $tarif->update(['slug' => 'etsiz-cig-kofte-A96HF']);

        $this->get('/tarifler/cig-kofte-A96HF')
            ->assertStatus(301)
            ->assertRedirect(url('/tarifler/etsiz-cig-kofte-A96HF'));
    }

    public function test_dil_onekli_eski_adres_ayni_dilde_yonlenir(): void
    {
        $tarif = $this->tarif('cig-kofte-A96HF');
        foreach (['title' => 'Chiğ Köfte', 'excerpt' => 'Summary.', 'description' => 'Description text.'] as $alan => $deger) {
            $tarif->translations()->create(['locale' => 'en', 'field' => $alan, 'value' => $deger]);
        }
        $tarif->update(['slug' => 'etsiz-cig-kofte-A96HF']);

        $this->get('/en/recipes/cig-kofte-A96HF')->assertRedirect(url('/en/recipes/etsiz-cig-kofte-A96HF'));
    }

    public function test_adresi_degisen_blog_yazisi_yonlenir(): void
    {
        $yazi = BlogPost::query()->create([
            'title' => 'Soğan Karamelizasyonu', 'slug' => 'sogan-karamelizasyonu', 'excerpt' => 'Özet.',
            'body' => str_repeat('Özgün bilgi. ', 50), 'status' => 'published', 'published_at' => now()->subDay(),
        ]);
        $yazi->update(['slug' => 'sogan-karamelizasyonu-ATNFE']);

        $this->get('/blog/sogan-karamelizasyonu')->assertStatus(301)->assertRedirect(url('/blog/sogan-karamelizasyonu-ATNFE'));
    }

    public function test_eski_adrese_geri_donulurse_dongu_olmaz(): void
    {
        $tarif = $this->tarif('ilk-adres');
        $tarif->update(['slug' => 'ikinci-adres']);
        $tarif->update(['slug' => 'ilk-adres']);

        $this->get('/tarifler/ilk-adres')->assertOk();
        $this->get('/tarifler/ikinci-adres')->assertRedirect(url('/tarifler/ilk-adres'));
    }

    public function test_hic_var_olmamis_adres_404_kalir(): void
    {
        $this->get('/tarifler/hic-olmamis-tarif')->assertNotFound();
    }
}
