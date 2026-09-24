<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Models\BlogPost;
use App\Models\Recipe;
use App\Services\Seo\IndexNowService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * IndexNow her açık dilin adreslerini bildirir.
 *
 * IndexNow, Bing ve Yandex'e "şu adres değişti" diyen kanaldır. Yalnız Türkçe
 * adres bildirildiği sürece bir tarifin İngilizce sürümü yayına girdiğinde
 * arama motoruna haber gitmiyordu; /en/ sayfası kendiliğinden taranmayı
 * bekliyordu. Çeviri emeğinin karşılığını geciktiren nokta buydu.
 *
 * Kural: çevrilmemiş kaydın dil adresi bildirilmez. O adres 404 verir ve
 * 404'ü IndexNow'a bildirmek arama motoruna olmayan sayfa vaat etmektir.
 */
final class IndexNowCoversEveryLocaleTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        config()->set('locales.supported.en.enabled', true);
    }

    private function tarif(string $slug, bool $cevir): Recipe
    {
        $tarif = Recipe::query()->create([
            'title' => 'Hellim Izgara',
            'slug' => $slug,
            'excerpt' => 'Özet.',
            'description' => 'Açıklama.',
            'status' => 'published',
            'verification_status' => 'verified',
            'published_at' => now(),
        ]);

        if ($cevir) {
            foreach (['title' => 'Grilled Halloumi', 'excerpt' => 'Summary.', 'description' => 'Description.'] as $alan => $deger) {
                $tarif->translations()->create(['locale' => 'en', 'field' => $alan, 'value' => $deger]);
            }
        }

        return $tarif;
    }

    public function test_cevrilmis_tarifin_dil_adresi_bildirilir(): void
    {
        $this->tarif('cevrili-tarif', true);

        $adresler = app(IndexNowService::class)->collectRecentUrls();

        $this->assertContains(url('/en/tarifler/cevrili-tarif'), $adresler);
        $this->assertContains(url('/tarifler/cevrili-tarif'), $adresler);
    }

    public function test_cevrilmemis_tarifin_dil_adresi_bildirilmez(): void
    {
        $this->tarif('cevrisiz-tarif', false);

        $adresler = app(IndexNowService::class)->collectRecentUrls();

        $this->assertNotContains(
            url('/en/tarifler/cevrisiz-tarif'),
            $adresler,
            '404 veren bir adres IndexNow ile arama motoruna bildirildi.'
        );
    }

    public function test_dil_listeleme_sayfalari_bildirilir(): void
    {
        $adresler = app(IndexNowService::class)->collectRecentUrls();

        foreach (['/en', '/en/tarifler', '/en/blog'] as $yol) {
            $this->assertContains(url($yol), $adresler, $yol.' bildirilmedi.');
        }
    }

    public function test_kapali_dil_hic_bildirilmez(): void
    {
        config()->set('locales.supported.en.enabled', false);
        $this->tarif('kapali-dil-tarifi', true);

        $adresler = app(IndexNowService::class)->collectRecentUrls();

        foreach ($adresler as $adres) {
            $this->assertStringNotContainsString('/en/tarifler/', $adres);
        }
    }

    public function test_cevrilmis_blog_yazisi_bildirilir(): void
    {
        $yazi = BlogPost::query()->create([
            'title' => 'Başlık',
            'slug' => 'cevrili-yazi',
            'excerpt' => 'Özet.',
            'body' => 'Gövde.',
            'status' => 'published',
            'published_at' => now()->subDay(),
        ]);

        foreach (['title' => 'Title', 'excerpt' => 'Summary.', 'body' => 'Body.'] as $alan => $deger) {
            $yazi->translations()->create(['locale' => 'en', 'field' => $alan, 'value' => $deger]);
        }

        $this->assertContains(url('/en/blog/cevrili-yazi'), app(IndexNowService::class)->collectRecentUrls());
    }
}
