<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Models\Cuisine;
use App\Models\Recipe;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use Tests\TestCase;

/**
 * Mutfak sayfaları.
 *
 * Sitenin kendi arama kaydı "kıbrıs mutfağı"nı 24 kez ölçtü ama
 * indekslenebilir tek bir mutfak sayfası yoktu: `/tarifler?cuisine=...`
 * görünümü noindex ve canonical'ı `/tarifler`. Arama motorunun sitenin en
 * ayırt edici konusu için sıralayabileceği hiçbir adres yoktu.
 *
 * Eşik burada asıl mesele: 13 mutfak kaydının dördünde hiç yayındaki tarif
 * yok. Hepsine sayfa açmak ölçekli ince içerik üretmek olurdu ve bu site
 * tam olarak bundan bir kez ceza yedi.
 */
final class CuisineLandingPagesTest extends TestCase
{
    use RefreshDatabase;

    public function test_a_cuisine_with_enough_recipes_gets_a_page(): void
    {
        $mutfak = $this->mutfak('Kıbrıs Mutfağı', 'kibris-mutfagi', 4);

        $this->get(route('cuisines.show', $mutfak->slug))
            ->assertOk()
            ->assertSee('Kıbrıs Mutfağı Tarifleri', false);
    }

    public function test_a_thin_cuisine_has_no_page(): void
    {
        // İki tarifle açılmış bir sayfa ziyaretçiye de arama motoruna da bir
        // şey vermiyor; zayıf sayfa site geneli kalite sinyalini düşürür.
        $mutfak = $this->mutfak('Yunan Mutfağı', 'yunan-mutfagi', 2);

        $this->get(route('cuisines.show', $mutfak->slug))->assertNotFound();
    }

    public function test_an_empty_cuisine_has_no_page(): void
    {
        $mutfak = $this->mutfak('Balkan Mutfağı', 'balkan-mutfagi', 0);

        $this->get(route('cuisines.show', $mutfak->slug))->assertNotFound();
    }

    public function test_drafts_do_not_count_towards_the_threshold(): void
    {
        // Eşiği taslakla doldurmak, boş bir sayfayı yayına açmak olurdu.
        $mutfak = $this->mutfak('Japon Mutfağı', 'japon-mutfagi', 0);
        for ($i = 0; $i < 6; $i++) {
            $this->tarif($mutfak, 'Taslak '.$i, 'draft');
        }

        $this->get(route('cuisines.show', $mutfak->slug))->assertNotFound();
    }

    public function test_the_page_is_indexable_and_self_canonical(): void
    {
        // Asıl amaç buydu: filtreli görünüm noindex'ti ve canonical'ı
        // /tarifler'e dönüyordu.
        $mutfak = $this->mutfak('Kıbrıs Mutfağı', 'kibris-mutfagi', 4);

        $icerik = (string) $this->get(route('cuisines.show', $mutfak->slug))->assertOk()->getContent();

        $this->assertStringNotContainsString('noindex', $icerik);
        $this->assertStringContainsString('rel="canonical" href="'.route('cuisines.show', $mutfak->slug).'"', $icerik);
    }

    public function test_the_sitemap_lists_exactly_the_pages_that_exist(): void
    {
        // Liste ile sitemap ayrışırsa sitemap 404 veren adresleri ilan eder.
        $var = $this->mutfak('Kıbrıs Mutfağı', 'kibris-mutfagi', 4);
        $yok = $this->mutfak('Yunan Mutfağı', 'yunan-mutfagi', 2);

        $xml = (string) $this->get('/sitemaps/pages.xml')->assertOk()->getContent();

        $this->assertStringContainsString(route('cuisines.show', $var->slug), $xml);
        $this->assertStringNotContainsString(route('cuisines.show', $yok->slug), $xml);
    }

    public function test_the_index_only_links_to_pages_that_exist(): void
    {
        $var = $this->mutfak('Kıbrıs Mutfağı', 'kibris-mutfagi', 4);
        $yok = $this->mutfak('Yunan Mutfağı', 'yunan-mutfagi', 2);

        $this->get(route('cuisines.index'))
            ->assertOk()
            ->assertSee(route('cuisines.show', $var->slug), false)
            ->assertDontSee(route('cuisines.show', $yok->slug), false);
    }

    public function test_the_catalogue_links_to_the_cyprus_page_instead_of_an_empty_filter(): void
    {
        // Anasayfadaki çip `cuisine=kibris` kullanıyordu ve hiçbir mutfağa
        // karşılık gelmediği için sıfır sonuç dönüyordu.
        $this->mutfak('Kıbrıs Mutfağı', 'kibris-mutfagi', 4);

        // Çipler anasayfada render ediliyor.
        $this->get(route('home'))
            ->assertOk()
            ->assertSee(route('cuisines.show', 'kibris-mutfagi'), false)
            ->assertDontSee(route('recipes.index', ['cuisine' => 'kibris']).'"', false);
    }

    public function test_a_thin_cuisine_card_still_links_to_the_filtered_view(): void
    {
        // Eşiğin altındaki mutfağın sayfası yok; kart onu 404'e bağlamamalı.
        $this->mutfak('Yunan Mutfağı', 'yunan-mutfagi', 2);

        $this->get(route('home'))
            ->assertOk()
            ->assertSee(route('recipes.index', ['cuisine' => 'yunan-mutfagi']), false)
            ->assertDontSee(route('cuisines.show', 'yunan-mutfagi'), false);
    }

    private function mutfak(string $ad, string $slug, int $tarifAdedi): Cuisine
    {
        $mutfak = Cuisine::query()->create(['name' => $ad, 'slug' => $slug]);

        for ($i = 0; $i < $tarifAdedi; $i++) {
            $this->tarif($mutfak, $ad.' Tarifi '.$i);
        }

        return $mutfak;
    }

    private function tarif(Cuisine $mutfak, string $baslik, string $durum = 'published'): Recipe
    {
        return Recipe::query()->create([
            'title' => $baslik,
            'slug' => Str::slug($baslik).'-'.uniqid(),
            'excerpt' => 'Özet.',
            'description' => 'Açıklama metni.',
            'status' => $durum,
            'verification_status' => 'verified',
            'published_at' => now(),
            'cuisine_id' => $mutfak->id,
        ]);
    }
}
