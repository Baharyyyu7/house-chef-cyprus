<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Cuisine;
use App\Models\Recipe;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Yabancı tarif sayfası Türkçe arayüz ve Türkçe ticari blok taşımaz.
 *
 * 28 Eylül 2026'da canlıda tarandı: İngilizce sayfada "İçeriğe geç", çerez
 * bandı, "Yazdır / PDF", Türkçe e-kitap tanıtımı, Amazon TR ekipman kutusu,
 * Kuzey Kıbrıs özel şef widget'ı ve tarifin yalnız Türkçe tutulan ipucu ve
 * sık hata listeleri Türkçe basılıyordu.
 */
final class ForeignRecipePageHasNoTurkishChromeTest extends TestCase
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

    public function test_the_english_page_has_no_turkish_chrome_or_turkish_only_blocks(): void
    {
        $this->tarif();

        $icerik = (string) $this->get('/en/recipes/cennet-camuru')->assertOk()->getContent();

        $this->assertStringContainsString(__('ui.skip_to_content', [], 'en'), $icerik);

        foreach ([
            'İçeriğe geç', 'Yalnız zorunlu', 'Siteyi çalıştıran çerezler', 'Yazdır / PDF',
            'Özel Hizmetler', 'Özel Şef & Menü Hesaplayıcı', 'Kıbrıs Mutfağı Seçmeler', 'Şefin Tavsiyesi',
            'Şerbeti ılık dökün', 'Kadayıfı yüksek ateşte', 'Geniş tava',
        ] as $turkce) {
            $this->assertStringNotContainsString($turkce, $icerik, $turkce);
        }
    }

    public function test_related_recipes_and_labels_follow_the_page_language(): void
    {
        $tarif = $this->tarif();
        // Aynı kategoride ama İngilizceye çevrilmemiş tarif: benzer tarif
        // olarak basılırsa bağlantısı İngilizcede 404 verir.
        Recipe::query()->create([
            'cuisine_id' => $tarif->cuisine_id, 'category_id' => $tarif->category_id,
            'title' => 'Çevrilmemiş Tatlı', 'slug' => 'cevrilmemis-tatli', 'excerpt' => 'Özet.', 'description' => 'Açıklama.',
            'servings' => 2, 'prep_minutes' => 5, 'cook_minutes' => 5,
            'status' => 'published', 'verification_status' => 'verified', 'published_at' => now(),
        ]);

        $icerik = (string) $this->get('/en/recipes/cennet-camuru')->assertOk()->getContent();

        $this->assertStringNotContainsString('Çevrilmemiş Tatlı', $icerik);
        $this->assertStringNotContainsString('cevrilmemis-tatli', $icerik);
        $this->assertStringNotContainsString('Vejetaryen', $icerik);
        $this->assertStringNotContainsString('Glutensiz', $icerik);
    }

    public function test_the_english_blog_index_speaks_english(): void
    {
        config()->set('locales.supported.en.enabled', true);

        $this->get('/en/blog')->assertOk()
            ->assertSee(__('blog.index.title', [], 'en'))
            ->assertDontSee('Mutfak rehberi')
            ->assertDontSee('Mutfak teknikleri, malzeme rehberleri');
    }

    public function test_the_turkish_page_keeps_its_content_and_blocks(): void
    {
        $this->tarif();

        $this->get('/tarifler/cennet-camuru')->assertOk()
            ->assertSee('İçeriğe geç')
            ->assertSee('Şerbeti ılık dökün')
            ->assertSee('Geniş tava');
    }
}
