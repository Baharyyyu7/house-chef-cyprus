<?php

declare(strict_types=1);

namespace App\Services\Integrity;

use App\Contracts\Search\RecipeSearchInterface;
use Illuminate\Http\Client\Response;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Sleep;

/**
 * Sitenin kendi iç bağlantıları gerçekten bir yere götürüyor mu?
 *
 * İki ayrı kusur var ve ikisi de hiçbir denetime takılmıyordu:
 *
 * 1. Kırık bağlantı - 404 dönen adres. Sayfa açılıyor, bağlantı duruyor,
 *    tıklayan hiçbir yere varmıyor.
 * 2. Boş filtre - 200 dönen ama sıfır tarif listeleyen katalog adresi. Bu
 *    daha sinsi: HTTP sağlıklı, sayfa açılıyor, içerik yok.
 *
 * İkincisi üretimde vardı: anasayfadaki "Kıbrıs mutfağı" çipi ve "Kıbrıs
 * mutfağını keşfet" bağlantısı `?cuisine=kibris` kullanıyordu. O slug hiçbir
 * mutfağa karşılık gelmiyor ve sıfır sonuç dönüyordu - sitenin amiral
 * konusundaki iki öne çıkan bağlantı boş sayfaya götürüyordu. Aylarca öyle
 * kaldı çünkü hiçbir şey bakmıyordu.
 *
 * Boş filtre kontrolü HTTP üzerinden değil arama servisinin kendisiyle
 * yapılıyor: sayfanın işaretlemesine bakmak, bir CSS sınıfı değişince sessizce
 * bozulan bir denetim demek olurdu.
 */
final class LinkIntegrityProbe
{
    private array $responses = [];

    private ?string $deferredReason = null;

    private ?float $lastRequestAt = null;

    private ?float $startedAt = null;

    private int $requests = 0;

    /** Katalogda anlamlı olan filtre anahtarları; kontrol bunlara bakar. */
    private const FILTRE_ANAHTARLARI = [
        'q', 'cuisine', 'category', 'difficulty', 'dietary_tag', 'exclude_allergens',
        'max_minutes', 'min_protein', 'max_calories',
    ];

    public function __construct(private readonly RecipeSearchInterface $search) {}

    /**
     * @param  list<string>  $sayfalar  taranacak hub adresleri
     * @param  list<string>  $dogrudanAdresler  Hub'da listelenmeyen fakat kendisi denetlenecek adresler
     * @return array<string, mixed>
     */
    public function probe(array $sayfalar, int $sinir = 60, array $dogrudanAdresler = []): array
    {
        // Doğrudan adresler önce eklenir; limit dolsa bile görev için kritik
        // koleksiyon sayfalarının tarama dışında kalmasına izin verilmez.
        $adresler = array_fill_keys($dogrudanAdresler, true);

        foreach ($sayfalar as $sayfa) {
            foreach ($this->baglantilar($sayfa) as $adres) {
                $adresler[$adres] = true;
            }
        }

        $adresler = array_slice(array_keys($adresler), 0, max(1, $sinir));
        $bulgular = [];

        $checked = 0;
        foreach ($adresler as $adres) {
            if ($this->deferredReason !== null) {
                break;
            }
            $bulgu = $this->tekBaglanti($adres);
            $checked++;

            if ($bulgu !== null) {
                $bulgular[] = $bulgu;
            }
        }

        return [
            'status' => $this->deferredReason !== null ? 'incomplete' : ($bulgular === [] ? 'ok' : 'issues_found'),
            'checked_at' => now()->toIso8601String(),
            'checked' => $checked,
            'deferred' => count($adresler) - $checked,
            'deferred_reason' => $this->deferredReason,
            'http_requests' => $this->requests,
            // Manşet sayı: azalması iyileşmedir.
            'broken' => count($bulgular),
            'findings' => $bulgular,
        ];
    }

    /**
     * Sitemap'teki sayfalara hub'lardan kaç ayrı kaynak sayfanın bağlandığı.
     * Aynı hub'daki tekrarlı bağlantı tek oy sayılır; amaç ham anchor sayısı
     * değil, sayfanın gezinme omurgasında kaç ayrı giriş noktası olduğudur.
     *
     * @param  list<string>  $hublar
     * @return array{sitemap_complete:bool,sitemap_count:?int,orphaned:?int,orphan_urls:list<string>,backlinks:array<string,int>}
     */
    public function orphanPages(string $sitemapUrl, array $hublar): array
    {
        $cevap = $this->request($sitemapUrl);
        $unknown = ['sitemap_complete' => false, 'sitemap_count' => null, 'orphaned' => null, 'orphan_urls' => [], 'backlinks' => []];
        if ($cevap === null || ! $cevap->successful() || ! str_contains($cevap->body(), '<urlset')) {
            return $unknown;
        }

        preg_match_all('#<loc>([^<]+)</loc>#i', $cevap->body(), $eslesmeler);
        $adresler = collect($eslesmeler[1])->map(fn (string $url): string => html_entity_decode(trim($url), ENT_QUOTES | ENT_HTML5))->filter()->unique()->values();
        $geriBaglantilar = array_fill_keys($adresler->all(), 0);

        foreach ($hublar as $hub) {
            $baglantilar = array_fill_keys(array_map([$this, 'normalizeUrl'], $this->baglantilar($hub)), true);
            foreach ($geriBaglantilar as $adres => $adet) {
                if (isset($baglantilar[$this->normalizeUrl($adres)])) {
                    $geriBaglantilar[$adres] = $adet + 1;
                }
            }
        }

        if ($this->deferredReason !== null) {
            return $unknown;
        }

        $oksuzler = array_keys(array_filter($geriBaglantilar, fn (int $adet): bool => $adet === 0));

        return [
            'sitemap_complete' => true,
            'sitemap_count' => count($geriBaglantilar),
            'orphaned' => count($oksuzler),
            'orphan_urls' => $oksuzler,
            'backlinks' => $geriBaglantilar,
        ];
    }

    private function normalizeUrl(string $url): string
    {
        $url = strtok($url, '#');

        return rtrim($url, '/') ?: rtrim((string) config('app.url'), '/');
    }

    /**
     * Bir sayfadaki kendi alan adımıza ait bağlantılar.
     *
     * @return list<string>
     */
    public function baglantilar(string $sayfa): array
    {
        $cevap = $this->request($sayfa);
        if ($cevap === null || ! $cevap->successful()) {
            return [];
        }

        $taban = rtrim((string) config('app.url'), '/');
        $bulunan = [];

        preg_match_all('#href="([^"]+)"#i', $cevap->body(), $eslesme);

        foreach ($eslesme[1] as $adres) {
            $adres = trim(html_entity_decode($adres, ENT_QUOTES | ENT_HTML5));

            // Çapa, e-posta, telefon ve dış bağlantılar bizim sorumluluğumuzda
            // değil.
            if ($adres === '' || ! str_starts_with($adres, $taban)) {
                continue;
            }

            $bulunan[strtok($adres, '#')] = true;
        }

        return array_keys($bulunan);
    }

    /** @return array<string, mixed>|null kusur yoksa null */
    private function tekBaglanti(string $adres): ?array
    {
        $parcalar = parse_url($adres);
        $yol = $parcalar['path'] ?? '/';

        // Katalog bağlantısı filtreliyse sıfır sonuç dönüp dönmediğine
        // bakılıyor: 200 dönen boş sayfa, kırık bağlantı kadar zararlı.
        if ($yol === '/tarifler' && isset($parcalar['query'])) {
            parse_str($parcalar['query'], $sorgu);
            $filtreler = array_intersect_key($sorgu, array_flip(self::FILTRE_ANAHTARLARI));

            if ($filtreler !== [] && $this->search->search($filtreler)->total() === 0) {
                return ['url' => $adres, 'reason' => 'EMPTY_FILTER', 'detail' => 'Bağlantı hiçbir tarif listelemiyor.'];
            }
        }

        $cevap = $this->request($adres);
        if ($cevap === null) {
            return null;
        }
        if (! $cevap->successful()) {
            return ['url' => $adres, 'reason' => 'HTTP_'.$cevap->status()];
        }

        return null;
    }

    private function request(string $url): ?Response
    {
        if (array_key_exists($url, $this->responses)) {
            return $this->responses[$url];
        }
        $key = 'integrity:http:retry:'.hash('sha256', (string) parse_url($url, PHP_URL_HOST));
        if ($this->deferredReason !== null || Cache::has($key)) {
            $this->deferredReason ??= 'RETRY_AFTER';

            return null;
        }
        $this->startedAt ??= microtime(true);
        if ($this->requests >= 80 || microtime(true) - $this->startedAt > 25) {
            $this->deferredReason = 'RUN_BUDGET';

            return null;
        }
        if ($this->lastRequestAt !== null && ! app()->runningUnitTests()) {
            $delay = max(0, 500 - (int) ((microtime(true) - $this->lastRequestAt) * 1000));
            Sleep::for($delay)->milliseconds();
        }
        $this->lastRequestAt = microtime(true);
        $this->requests++;
        try {
            $response = Http::withUserAgent('HouseChefIntegrity/1.0 (+'.config('app.url').')')->connectTimeout(2)->timeout(5)->get($url);
        } catch (\Throwable) {
            $this->deferredReason = 'NETWORK_UNAVAILABLE';

            return null;
        }
        if (in_array($response->status(), [429, 503], true)) {
            $retry = $response->header('Retry-After');
            $seconds = is_numeric($retry) ? (int) $retry : max(300, (int) strtotime($retry) - time());
            Cache::put($key, true, now()->addSeconds(max(60, min(86400, $seconds))));
            $this->deferredReason = 'HTTP_'.$response->status();

            return null;
        }

        return $this->responses[$url] = $response;
    }
}
