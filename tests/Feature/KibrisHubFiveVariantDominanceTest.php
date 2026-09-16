<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Models\Cuisine;
use App\Models\Recipe;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use Tests\TestCase;

/**
 * Kıbrıs 5 varyant dominansı.
 *
 * "Kıbrıs", "Kıbrıs Yemek", "Kıbrıs Yemek Tarif", "Yemek Kıbrıs" ve
 * "Kıbrıs Yemekleri" aramalarının tamamı tek kanonik ada üzerinde toplanır.
 * Ayrı 5 sayfa açmak %100 kanibalizasyon yaratır; bu yüzden kısa anahtar
 * kelime URL'leri 301 ile hub'a akar ve yetki tek adreste birikir.
 */
final class KibrisHubFiveVariantDominanceTest extends TestCase
{
    use RefreshDatabase;

    private const HUB = 'kibris-mutfagi';

    public function test_every_keyword_variant_redirects_permanently_to_the_hub(): void
    {
        $this->mutfak('Kıbrıs Mutfağı', self::HUB, 5);

        $hedef = route('cuisines.show', self::HUB);

        foreach (['kibris', 'kibris-yemek', 'kibris-yemekleri', 'kibris-yemek-tarifi', 'kibris-yemek-tarifleri', 'yemek-kibris'] as $kisa) {
            $this->get('/'.$kisa)
                ->assertRedirect($hedef)
                ->assertStatus(301);
        }
    }

    public function test_english_keyword_variants_redirect_to_the_hub(): void
    {
        $this->mutfak('Kıbrıs Mutfağı', self::HUB, 5);

        foreach (['kibris', 'kibris-yemek', 'kibris-yemekleri', 'kibris-yemek-tarifi', 'kibris-yemek-tarifleri', 'yemek-kibris'] as $kisa) {
            $response = $this->get('/en/'.$kisa);
            $response->assertStatus(301);
            $this->assertStringContainsString(self::HUB, $response->getTargetUrl());
        }
    }

    public function test_the_cyprus_hub_speaks_all_five_variants(): void
    {
        $this->mutfak('Kıbrıs Mutfağı', self::HUB, 5);

        $icerik = (string) $this->get(route('cuisines.show', self::HUB))->assertOk()->getContent();

        // H1: anahtar kelime cümlesi tek başlıkta.
        $this->assertStringContainsString('<h1', $icerik);
        $this->assertStringContainsString('Kıbrıs Yemekleri', $icerik);
        // Varyant şeridi görünür: hedef kelimelerin tamamı insan gözüyle de okunur.
        $this->assertStringContainsString('Kıbrıs Yemek Tarif', $icerik);
        $this->assertStringContainsString('Yemek Kıbrıs', $icerik);
        $this->assertStringContainsString('Kıbrıs Yemekleri', $icerik);
        // Çıkmaz sokak değil: dahili linkler hub'dan ileri taşır.
        $this->assertStringContainsString('Kıbrıs ara', $icerik);
    }

    public function test_the_cyprus_hub_carries_faqpage_schema(): void
    {
        $this->mutfak('Kıbrıs Mutfağı', self::HUB, 5);

        $icerik = (string) $this->get(route('cuisines.show', self::HUB))->assertOk()->getContent();

        $this->assertStringContainsString('"@type":"FAQPage"', $icerik);
        $this->assertStringContainsString('"name":"Kıbrıs Yemekleri nelerdir?"', $icerik);
        $this->assertStringContainsString('"name":"Kıbrıs Yemek Tarifi nereden bulunur?"', $icerik);
        // Görünür FAQ bloğu da var — schema ve sayfa içeriği birbirini doğrular.
        $this->assertStringContainsString('Hellim, şeftali kebabı, molehiya', $icerik);
    }

    public function test_other_cuisines_keep_their_generic_title_without_faq(): void
    {
        $this->mutfak('Yunan Mutfağı', 'yunan-mutfagi', 5);

        $icerik = (string) $this->get(route('cuisines.show', 'yunan-mutfagi'))->assertOk()->getContent();

        $this->assertStringContainsString('Yunan Mutfağı Tarifleri', $icerik);
        $this->assertStringNotContainsString('"@type":"FAQPage"', $icerik);
        $this->assertStringNotContainsString('Kıbrıs Yemekleri —', $icerik);
    }

    public function test_cuisine_sitemap_still_lists_the_cyprus_hub(): void
    {
        $this->mutfak('Kıbrıs Mutfağı', self::HUB, 5);

        $xml = (string) $this->get('/sitemaps/pages.xml')->assertOk()->getContent();

        $this->assertStringContainsString(route('cuisines.show', self::HUB), $xml);
    }

    private function mutfak(string $ad, string $slug, int $tarifAdedi): Cuisine
    {
        $mutfak = Cuisine::query()->create(['name' => $ad, 'slug' => $slug]);

        for ($i = 0; $i < $tarifAdedi; $i++) {
            Recipe::query()->create([
                'title' => $ad.' Tarifi '.$i,
                'slug' => Str::slug($ad).'-'.$i.'-'.uniqid(),
                'excerpt' => 'Özet.',
                'description' => 'Açıklama metni.',
                'status' => 'published',
                'verification_status' => 'verified',
                'published_at' => now(),
                'cuisine_id' => $mutfak->id,
            ]);
        }

        return $mutfak;
    }
}