<?php

declare(strict_types=1);

namespace App\Http\Middleware;

use App\Models\AudienceCountryDay;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use Throwable;

/**
 * Ziyaretçinin ülkesini günlük toplama yazar.
 *
 * Ülke bilgisi yalnız önümüzdeki CDN/sunucu katmanının verdiği başlıktan
 * okunur; IP adresine bakılmaz ve hiçbir yere yazılmaz. Başlık yoksa hiçbir
 * şey kaydedilmez - uydurma veri üretmektense harita boş kalır.
 */
final class RecordAudienceCountry
{
    /**
     * Farklı barındırıcılar aynı bilgiyi farklı adla gönderiyor.
     */
    private const HEADERS = [
        'CF-IPCountry',
        'X-Geo-Country',
        'X-Country-Code',
        'X-Hcdn-Country',
        'GeoIP-Country-Code',
    ];

    /**
     * Başlık yerine sunucu değişkeni olarak gelen karşılıklar.
     */
    private const SERVER_KEYS = [
        'GEOIP_COUNTRY_CODE',
        'HTTP_CF_IPCOUNTRY',
        'HTTP_X_GEO_COUNTRY',
        'COUNTRY_CODE',
    ];

    public function handle(Request $request, Closure $next): Response
    {
        $response = $next($request);

        if ($request->isMethod('GET') && ! $request->ajax()) {
            $this->kaydet($request);
        }

        return $response;
    }

    private function kaydet(Request $request): void
    {
        $code = $this->ulkeKodu($request);

        if ($code === null) {
            $this->tanilamaYaz($request);

            return;
        }

        try {
            // Önce satırın var olduğundan emin ol (varsa dokunma), sonra artır:
            // iki adım da tek isabette bir artışa karşılık gelir.
            AudienceCountryDay::query()->upsert(
                [['day' => now()->toDateString(), 'country_code' => $code, 'hits' => 0, 'created_at' => now(), 'updated_at' => now()]],
                ['day', 'country_code'],
                []
            );

            AudienceCountryDay::query()
                ->where('day', now()->toDateString())
                ->where('country_code', $code)
                ->increment('hits');
        } catch (Throwable) {
            // Sayaç sayfayı düşürmez.
        }
    }

    private function ulkeKodu(Request $request): ?string
    {
        foreach (self::HEADERS as $header) {
            $value = strtoupper(trim((string) $request->header($header)));

            if (preg_match('/^[A-Z]{2}$/', $value) === 1 && $value !== 'XX') {
                return $value;
            }
        }

        // Bazı LiteSpeed/Apache kurulumları ülkeyi başlık olarak değil, sunucu
        // değişkeni olarak verir.
        foreach (self::SERVER_KEYS as $key) {
            $value = strtoupper(trim((string) ($request->server($key) ?? '')));

            if (preg_match('/^[A-Z]{2}$/', $value) === 1 && $value !== 'XX') {
                return $value;
            }
        }

        return null;
    }

    /**
     * Ülke bulunamadığında, bu isteğe hangi coğrafi anahtarların geldiğini bir
     * saatliğine önbelleğe yazar.
     *
     * Yalnız anahtar ADLARI tutulur, değerleri değil: amaç barındırıcının ne
     * gönderdiğini öğrenmek, ziyaretçi hakkında bir şey saklamak değil. Böyle
     * bir ölçüm olmadan "hangi başlığı okumalıyız" sorusu tahminle yanıtlanıyor.
     */
    private function tanilamaYaz(Request $request): void
    {
        try {
            $adaylar = [];

            foreach (array_keys($request->headers->all()) as $ad) {
                if (preg_match('/(country|geo|cf-|ip-?city|region)/i', (string) $ad) === 1) {
                    $adaylar[] = strtolower((string) $ad);
                }
            }

            foreach (array_keys((array) $request->server()) as $ad) {
                if (preg_match('/(COUNTRY|GEOIP)/i', (string) $ad) === 1) {
                    $adaylar[] = strtolower((string) $ad);
                }
            }

            cache()->put('audience:cografi-anahtar-adlari', array_values(array_unique($adaylar)), now()->addHour());
        } catch (Throwable) {
            // Tanılama sayfayı düşürmez.
        }
    }
}
