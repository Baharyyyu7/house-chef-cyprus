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

    /** @param list<string> $adresler @return array<string, mixed> */
    private function probe(array $adresler): array
    {
        return app(AssetDeliveryProbe::class)->probe($adresler);
    }
}
