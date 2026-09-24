<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Models\Recipe;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Yabancı dil adresleri o dilin yolunu kullanır.
 *
 * İngilizce tarif sayfaları /en/tarifler/... adresinde yaşıyordu. İngilizce
 * arayan da İngilizce tarayan da yolu okur; bildikleri kelime "recipes".
 * Türkçe yazımla gelen istek kalıcı olarak yerelleşmiş adrese gider, yoksa
 * aynı içerik iki adreste durur.
 */
final class LocalizedRecipePathTest extends TestCase
{
    use RefreshDatabase;

    private function tarif(): Recipe
    {
        $tarif = Recipe::query()->create([
            'title' => 'Ispanaklı Gözleme',
            'slug' => 'ispanakli-gozleme',
            'excerpt' => 'Özet metni.',
            'description' => 'Tarif açıklaması burada yer alıyor ve yeterince uzun bir cümledir.',
            'status' => 'published',
            'verification_status' => 'verified',
            'published_at' => now()->subDay(),
            'servings' => 4,
            'prep_minutes' => 10,
            'cook_minutes' => 20,
        ]);
        $this->makeRecipePubliclyEligible($tarif);

        // Çevirisi olmayan tarif yabancı dilde 404 verir; bu test yolu ölçüyor.
        foreach (['en', 'de'] as $dil) {
            $tarif->translations()->create(['locale' => $dil, 'field' => 'title', 'value' => 'Spinach Gozleme']);
            $tarif->translations()->create(['locale' => $dil, 'field' => 'excerpt', 'value' => 'Spinach filled flatbread.']);
            $tarif->translations()->create(['locale' => $dil, 'field' => 'description', 'value' => 'A thin flatbread filled with spinach, cooked on a griddle.']);
        }

        return $tarif;
    }

    public function test_ingilizce_turkce_yol_kalici_olarak_yerellesmis_adrese_gider(): void
    {
        $this->tarif();

        $this->get('/en/tarifler/ispanakli-gozleme')
            ->assertRedirect('/en/recipes/ispanakli-gozleme')
            ->assertStatus(301);

        $this->get('/en/tarifler')->assertRedirect('/en/recipes');
    }

    public function test_yerellesmis_adres_sayfayi_acar_ve_kendini_kanonik_gosterir(): void
    {
        $this->tarif();

        $this->get('/en/recipes/ispanakli-gozleme')
            ->assertOk()
            ->assertSee('rel="canonical" href="'.url('/en/recipes/ispanakli-gozleme').'"', false)
            ->assertSee('hreflang="en" href="'.url('/en/recipes/ispanakli-gozleme').'"', false)
            ->assertSee('hreflang="tr" href="'.url('/tarifler/ispanakli-gozleme').'"', false);
    }

    public function test_turkce_adres_degismez(): void
    {
        $this->tarif();

        $this->get('/tarifler/ispanakli-gozleme')
            ->assertOk()
            ->assertSee('rel="canonical" href="'.url('/tarifler/ispanakli-gozleme').'"', false);
    }

    public function test_takma_adi_olmayan_bolum_oldugu_gibi_calisir(): void
    {
        $this->get('/en/blog')->assertOk();
    }

    public function test_almanca_kendi_yazimini_kullanir(): void
    {
        $this->tarif();

        $this->get('/de/tarifler/ispanakli-gozleme')->assertRedirect('/de/rezepte/ispanakli-gozleme');
        $this->get('/de/rezepte/ispanakli-gozleme')->assertOk();
    }
}
