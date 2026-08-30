<?php

declare(strict_types=1);

namespace App\Services\Integrity;

use Illuminate\Support\Facades\Http;

/**
 * Yayındaki varlıkların tarayıcıya doğru biçimde ulaşıp ulaşmadığını ölçer.
 *
 * Neden gerekli: sunucu tarafı kusursuz görünürken tarayıcı tarafı kırık
 * olabiliyor ve hiçbir denetim bunu yakalamıyor. Üretimde tam olarak bu
 * oldu - sunucu `.avif` uzantısını tanımıyordu ve bütün AVIF dosyalarını
 * `text/plain` olarak veriyordu. Dosyalar sağlamdı (ftypavif imzası,
 * doğru boyut, HTTP 200) ama `<picture>` içinde tarayıcı
 * `type="image/avif"` diyerek o kaynağı seçiyor, `text/plain` alıyor ve
 * çözümleyemiyordu. Kırık medya koruması da onları gri yer tutucuyla
 * değiştiriyordu: anasayfanın hero görseli dahil sitedeki her AVIF.
 *
 * HTML doğruydu, dosyalar 200 veriyordu, alt metinleri yerindeydi. Sorun
 * yalnız gerçek bir tarayıcı gerçek bir sayfa yüklerken görünüyordu.
 */
final class AssetDeliveryProbe
{
    /** Uzantı başına beklenen içerik türü. */
    private const BEKLENEN = [
        'avif' => 'image/avif',
        'webp' => 'image/webp',
        'png' => 'image/png',
        'jpg' => 'image/jpeg',
        'jpeg' => 'image/jpeg',
        'svg' => 'image/svg+xml',
        'css' => 'text/css',
        'js' => 'javascript',
        'woff2' => 'font/woff2',
    ];

    /**
     * Sayfanın gerçekten istediği varlık adreslerini HTML'den çıkarır.
     *
     * Adres listesini elle tutmak bir kez yanlış ölçüme yol açtı: şablon
     * hero AVIF'lerini `?v=2` ile istiyordu, prob çıplak adresi ölçüyordu
     * ve kimsenin talep etmediği bayat bir kenar kopyasını hata sandı.
     * Ziyaretçinin aldığı yanıtı ölçmek istiyorsak adres de ziyaretçinin
     * istediği adres olmalı - o yüzden tek kaynak sayfanın kendisi.
     *
     * @return list<string>
     */
    public function adresler(string $html, string $taban): array
    {
        $bulunan = [];

        // srcset virgülle ayrılır ve her aday "url 900w" biçimindedir.
        preg_match_all('#(?:src|srcset)="([^"]+)"#i', $html, $eslesme);

        foreach ($eslesme[1] as $oznitelik) {
            foreach (explode(',', $oznitelik) as $aday) {
                $adres = trim(explode(' ', trim($aday))[0]);

                if ($adres === '' || ! str_starts_with($adres, $taban)) {
                    continue;
                }

                $uzanti = strtolower(pathinfo(parse_url($adres, PHP_URL_PATH) ?: '', PATHINFO_EXTENSION));

                if (isset(self::BEKLENEN[$uzanti])) {
                    $bulunan[$adres] = true;
                }
            }
        }

        return array_keys($bulunan);
    }

    /** @param list<string> $adresler @return array<string, mixed> */
    public function probe(array $adresler): array
    {
        $sonuclar = [];
        $hatali = 0;

        foreach ($adresler as $adres) {
            $sonuc = $this->tekVarlik($adres);
            $sonuclar[] = $sonuc;

            // "skipped" hata değil: türünü bilmediğimiz bir dosya, yanlış
            // servis edildiğini bildiğimiz bir dosya demek değil.
            if ($sonuc['status'] === 'failed') {
                $hatali++;
            }
        }

        return [
            'status' => $hatali === 0 ? 'ok' : 'failed',
            'checked_at' => now()->toIso8601String(),
            'checked' => count($sonuclar),
            'failed' => $hatali,
            'results' => $sonuclar,
        ];
    }

    /** @return array<string, mixed> */
    private function tekVarlik(string $adres): array
    {
        $uzanti = strtolower(pathinfo(parse_url($adres, PHP_URL_PATH) ?: '', PATHINFO_EXTENSION));
        $beklenen = self::BEKLENEN[$uzanti] ?? null;

        if ($beklenen === null) {
            return ['url' => $adres, 'status' => 'skipped', 'reason' => 'UNKNOWN_EXTENSION'];
        }

        try {
            $cevap = Http::timeout(10)->head($adres);
        } catch (\Throwable $hata) {
            return ['url' => $adres, 'status' => 'failed', 'reason' => 'UNREACHABLE', 'detail' => $hata->getMessage()];
        }

        $tur = strtolower((string) $cevap->header('Content-Type'));

        if (! $cevap->successful()) {
            return ['url' => $adres, 'status' => 'failed', 'reason' => 'HTTP_'.$cevap->status()];
        }

        // Sunucu uzantıyı tanımadığında text/plain'e düşüyor: dosya iner ama
        // tarayıcı onu görsel olarak çözümlemez.
        if (! str_contains($tur, $beklenen)) {
            return [
                'url' => $adres,
                'status' => 'failed',
                'reason' => 'CONTENT_TYPE_MISMATCH',
                'expected' => $beklenen,
                'actual' => $tur !== '' ? $tur : '(boş)',
            ];
        }

        return ['url' => $adres, 'status' => 'ok', 'content_type' => $tur];
    }
}
