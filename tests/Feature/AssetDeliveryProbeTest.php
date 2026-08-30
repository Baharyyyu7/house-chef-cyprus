<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Services\Integrity\AssetDeliveryProbe;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

/**
 * Varlıkların tarayıcıya doğru içerik türüyle ulaştığını ölçen sonda.
 *
 * Üretimde sunucu `.avif` uzantısını tanımıyordu ve bütün AVIF dosyalarını
 * `text/plain` olarak veriyordu. Dosyalar sağlamdı, HTTP 200 dönüyordu,
 * HTML doğruydu - ama tarayıcı `<picture>` içinde o kaynağı seçip
 * çözümleyemiyordu ve sitedeki her AVIF gri yer tutucuya düşüyordu.
 * Hiçbir sunucu tarafı denetimi bunu yakalayamazdı.
 */
final class AssetDeliveryProbeTest extends TestCase
{
    public function test_a_wrong_content_type_is_reported(): void
    {
        // Tam üretimdeki durum: 200 dönen, sağlam ama yanlış türle
        // etiketlenmiş bir AVIF.
        Http::fake(['*' => Http::response('', 200, ['Content-Type' => 'text/plain'])]);

        $rapor = $this->probe(['https://ornek.test/images/hero.avif']);

        $this->assertSame('failed', $rapor['status']);
        $this->assertSame('CONTENT_TYPE_MISMATCH', $rapor['results'][0]['reason']);
        $this->assertSame('image/avif', $rapor['results'][0]['expected']);
        $this->assertSame('text/plain', $rapor['results'][0]['actual']);
    }

    public function test_a_correct_content_type_passes(): void
    {
        Http::fake(['*' => Http::response('', 200, ['Content-Type' => 'image/avif'])]);

        $rapor = $this->probe(['https://ornek.test/images/hero.avif']);

        $this->assertSame('ok', $rapor['status']);
        $this->assertSame(0, $rapor['failed']);
    }

    public function test_a_charset_suffix_does_not_cause_a_false_alarm(): void
    {
        // Sunucular sık sık "text/css; charset=UTF-8" gibi ek veriyor.
        Http::fake(['*' => Http::response('', 200, ['Content-Type' => 'text/css; charset=UTF-8'])]);

        $this->assertSame('ok', $this->probe(['https://ornek.test/build/app.css'])['status']);
    }

    public function test_a_missing_asset_is_reported(): void
    {
        Http::fake(['*' => Http::response('', 404)]);

        $rapor = $this->probe(['https://ornek.test/images/yok.avif']);

        $this->assertSame('failed', $rapor['status']);
        $this->assertSame('HTTP_404', $rapor['results'][0]['reason']);
    }

    public function test_an_unknown_extension_is_skipped_not_failed(): void
    {
        // Bilmediği türü hata saymak sondayı gürültüye boğardı.
        Http::fake(['*' => Http::response('', 200, ['Content-Type' => 'application/octet-stream'])]);

        $rapor = $this->probe(['https://ornek.test/dosya.bilinmeyen']);

        $this->assertSame('ok', $rapor['status']);
        $this->assertSame('skipped', $rapor['results'][0]['status']);
    }

    public function test_every_checked_asset_appears_in_the_report(): void
    {
        Http::fake(['*' => Http::response('', 200, ['Content-Type' => 'image/webp'])]);

        $rapor = $this->probe([
            'https://ornek.test/a.webp',
            'https://ornek.test/b.webp',
            'https://ornek.test/c.webp',
        ]);

        $this->assertSame(3, $rapor['checked']);
        $this->assertCount(3, $rapor['results']);
    }

    public function test_the_versioned_url_the_page_requests_is_the_one_checked(): void
    {
        // Bu testin sebebi gerçek bir yanlış ölçüm: şablon hero AVIF'lerini
        // `?v=2` ile istiyordu, prob elle tutulan çıplak adresi ölçüyordu.
        // Kimsenin talep etmediği bayat bir kenar kopyası hata sanıldı.
        $adresler = $this->adresler('<source srcset="https://ornek.test/hero-900.avif?v=2 900w">');

        $this->assertSame(['https://ornek.test/hero-900.avif?v=2'], $adresler);
    }

    public function test_a_srcset_with_several_candidates_yields_each_one(): void
    {
        $adresler = $this->adresler(
            '<source srcset="https://ornek.test/a.avif 900w, https://ornek.test/b.avif 1440w">'
            .'<img src="https://ornek.test/c.png">'
        );

        $this->assertSame([
            'https://ornek.test/a.avif',
            'https://ornek.test/b.avif',
            'https://ornek.test/c.png',
        ], $adresler);
    }

    public function test_external_and_untyped_assets_are_left_out(): void
    {
        // Başka bir sunucunun içerik türünden sorumlu değiliz; türünü
        // bilmediğimiz dosya da yanlış servis edildiğini bildiğimiz dosya
        // demek değil.
        $adresler = $this->adresler(
            '<img src="https://baska.test/uzak.avif">'
            .'<img src="https://ornek.test/veri.bilinmeyen">'
            .'<img src="https://ornek.test/gercek.webp">'
        );

        $this->assertSame(['https://ornek.test/gercek.webp'], $adresler);
    }

    public function test_the_same_asset_referenced_twice_is_checked_once(): void
    {
        $adresler = $this->adresler(
            '<img src="https://ornek.test/a.webp"><img src="https://ornek.test/a.webp">'
        );

        $this->assertCount(1, $adresler);
    }

    /** @return list<string> */
    private function adresler(string $html): array
    {
        return app(AssetDeliveryProbe::class)->adresler($html, 'https://ornek.test');
    }

    /** @param list<string> $adresler @return array<string, mixed> */
    private function probe(array $adresler): array
    {
        return app(AssetDeliveryProbe::class)->probe($adresler);
    }
}
