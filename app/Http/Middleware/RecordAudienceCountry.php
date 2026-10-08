<?php

declare(strict_types=1);

namespace App\Http\Middleware;

use App\Services\Geo\AudienceCountryBuffer;
use App\Services\Geo\IpCountryResolver;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use Throwable;

/**
 * Ziyaretçinin ülkesini günlük toplama yazar.
 *
 * Ülke önce önümüzdeki CDN/sunucu katmanının verdiği başlıktan okunur. Başlık
 * yoksa ülke, sunucudaki yerel veritabanından IP ile bulunur.
 *
 * Başlangıçta yalnız başlık okunuyordu ve IP'ye hiç bakılmıyordu. 12-13 Eylül
 * 2026'da site Cloudflare'den Hostinger CDN'e geçti; yeni CDN ülke başlığı
 * göndermediği için sayaç hata vermeden durdu (günlük 2.119 → 0).
 *
 * IP yalnız bu isteğin belleğinde, ülke bulmak için kullanılır: veritabanına,
 * önbelleğe ya da loga yazılmaz, dışarıya gönderilmez. Kayda yine yalnız gün
 * ve ülke kodu geçer. Ülke bulunamazsa hiçbir şey kaydedilmez - uydurma veri
 * üretmektense harita eksik kalır.
 */
final class RecordAudienceCountry
{
    public function __construct(
        private readonly IpCountryResolver $ulkeler,
        private readonly AudienceCountryBuffer $tampon,
    ) {
    }

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

    /**
     * Ziyaretçi değil, sitenin kendi altyapısı.
     *
     * Ülke IP'den bulunmaya başlayınca (14 Eylül 2026) bunlar ilk gün sayaca
     * girdi: yayın betiğinin sunucudan attığı kontrol istekleri "Fransa'dan
     * 7 ziyaret", zamanlayıcıyı dakikada bir tetikleyen Cloudflare Worker
     * "Almanya" oldu. Önceden ülke başlığı taşımadıkları için hiç
     * sayılmıyorlardı.
     */
    private const MAKINE_YOLLARI = ['_zamanlayici/*', 'up', 'health', 'api/*'];

    /**
     * Tarayıcı olmayan HTTP istemcileri. Arama motoru botları bu listede yok:
     * CDN başlığı döneminde de sayılıyorlardı ve sayımı değiştirmek geçmişle
     * karşılaştırmayı bozardı.
     */
    private const MAKINE_ISTEMCILERI = '/^(GuzzleHttp|curl|Wget|python-requests|python-urllib|Go-http-client|okhttp|axios|node-fetch|undici|libwww-perl|Java\/|Apache-HttpClient)/i';

    public function handle(Request $request, Closure $next): Response
    {
        return $next($request);
    }

    /**
     * Sayım yanıt gönderildikten sonra yapılır (LiteSpeed/FPM bağlantıyı
     * önce kapatır); ülke araması ve sayaç ziyaretçiyi bekletmez.
     */
    public function terminate(Request $request, Response $response): void
    {
        if ($request->isMethod('GET') && ! $request->ajax() && ! $this->makineTrafigi($request)) {
            $this->kaydet($request);
        }
    }

    private function makineTrafigi(Request $request): bool
    {
        if ($request->is(...self::MAKINE_YOLLARI)) {
            return true;
        }

        $istemci = trim((string) $request->userAgent());

        // Tarayıcılar her zaman bir kimlik gönderir. Boş kimlikle gelen
        // istek bir betik ya da Worker'dır (zamanlayıcı tetikleyicisi böyle).
        return $istemci === '' || preg_match(self::MAKINE_ISTEMCILERI, $istemci) === 1;
    }

    private function kaydet(Request $request): void
    {
        $code = $this->ulkeKodu($request);

        if ($code === null) {
            $this->tanilamaYaz($request);

            return;
        }

        try {
            $this->tampon->record($code);
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

        // Son çare: yerel veritabanı. $request->ip() ancak güvenilen proxy
        // ayarı doğruysa gerçek ziyaretçiyi verir (bkz. TrustCdnProxies);
        // yanlışsa CDN sunucusunun ülkesi bulunur. Çözücü özel ağ adreslerini
        // zaten reddeder.
        return $this->ulkeler->country($request->ip());
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
                // forwarded/real-ip/client-ip: CDN'in gerçek ziyaretçi IP'sini
                // hangi başlıkla ilettiğini öğrenmek için. Yalnız ad tutulur.
                if (preg_match('/(country|geo|cf-|ip-?city|region|forwarded|real-ip|client-ip)/i', (string) $ad) === 1) {
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
