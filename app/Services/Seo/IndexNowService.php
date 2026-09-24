<?php

declare(strict_types=1);

namespace App\Services\Seo;

use App\Models\BlogPost;
use App\Models\Recipe;
use App\Services\Seo\LocaleUrlService;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;

final class IndexNowService
{
    public const DEFAULT_KEY = 'c032a1e8e8214371b658ef9c7e0b57e1';
    public const ENDPOINT = 'https://api.indexnow.org/indexnow';

    public function getKey(): string
    {
        return (string) config('services.indexnow.key', self::DEFAULT_KEY);
    }

    /**
     * @param array<int, string>|null $urls
     * @return array{success: bool, status: int, count: int, submitted: int, urls: array<int, string>, retry_after: int}
     */
    public function submit(?array $urls = null): array
    {
        if ($urls === null || empty($urls)) {
            $urls = $this->collectRecentUrls();
        }

        $urls = array_values(array_unique($urls));
        $submitted = [];
        $result = static fn (bool $success, int $status, array $accepted, int $retryAfter = 0): array => [
            'success' => $success,
            'status' => $status,
            'count' => count($accepted),
            'submitted' => count($accepted),
            'urls' => $accepted,
            'retry_after' => $retryAfter,
        ];
        if (empty($urls)) {
            return $result(true, 200, []);
        }

        $key = $this->getKey();
        $configuredHost = (string) parse_url((string) (config('seo.search_console.site_url') ?: config('app.url', 'https://housechefcyprus.com')), PHP_URL_HOST);
        // Local development uses 127.0.0.1/localhost but the key file lives on production.
        // Submitting 127.0.0.1 to IndexNow would 403 and waste quota.
        if ($configuredHost === '' || $configuredHost === 'localhost' || $configuredHost === '127.0.0.1' || str_ends_with($configuredHost, '.test') || str_ends_with($configuredHost, '.local')) {
            $configuredHost = 'housechefcyprus.com';
        }
        $host = $configuredHost;
        $cooldownKey = 'indexnow:retry-at:' . hash('sha256', $host . ':' . $key);
        $retryAt = (int) Cache::get($cooldownKey, 0);
        if ($retryAt > now()->timestamp) {
            return $result(false, 429, [], $retryAt - now()->timestamp);
        }

        $payload = [
            'host' => $host,
            'key' => $key,
            'keyLocation' => "https://{$host}/{$key}.txt",
        ];
        $acceptedStatus = 200;
        try {
            foreach (array_chunk($urls, 50) as $chunk) {
                $payload['urlList'] = $chunk;
                $response = Http::timeout(10)
                    ->withHeaders(['Content-Type' => 'application/json; charset=utf-8'])
                    ->post(self::ENDPOINT, $payload);
                $status = $response->status();
                if (!in_array($status, [200, 202], true)) {
                    $retryAfter = 0;
                    if ($status === 429) {
                        $header = trim($response->header('Retry-After'));
                        $retryAfter = ctype_digit($header) ? (int) $header : max(0, (strtotime($header) ?: 0) - now()->timestamp);
                        $retryAfter = max(60, $retryAfter);
                        Cache::put($cooldownKey, now()->timestamp + $retryAfter, $retryAfter);
                    }
                    Log::warning('IndexNow chunk rejected', ['status' => $status, 'submitted' => count($submitted)]);
                    return $result(false, $status, $submitted, $retryAfter);
                }
                $submitted = array_merge($submitted, $chunk);
                if ($status === 202) {
                    $acceptedStatus = 202;
                }
                Log::info('IndexNow chunk accepted', ['status' => $status, 'count' => count($chunk)]);
            }
            return $result(true, $acceptedStatus, $submitted);
        } catch (\Throwable $e) {
            Log::warning('IndexNow submission failed: ' . $e->getMessage(), [
                'submitted' => count($submitted),
            ]);
            return $result(false, 500, $submitted);
        }
    }

    /**
     * @return array<int, string>
     */
    public function collectRecentUrls(): array
    {
        // Yalnız indekslenebilir adresler bildirilir. 17 Eylül 2026'da
        // ölçüldü: yayındaki 988 tarifin 674'ü şablon olduğu için noindex
        // taşıyordu ve bu liste hepsini Bing'e gönderiyordu. Arama motoruna
        // "bu sayfayı hemen tara" deyip sayfada "indeksleme" yazmak, tarama
        // bütçesini harcamaktan başka bir şey yapmaz.
        $indekslenebilir = static fn ($sorgu) => $sorgu->whereDoesntHave(
            'seoMetadata',
            fn ($q) => $q->where('robots', 'like', '%noindex%')
        );

        $recipes = $indekslenebilir(Recipe::query()
            ->where('status', 'published')
            ->where('verification_status', 'verified')
            ->whereNotNull('published_at')
            ->where('published_at', '<=', now()))
            ->latest('updated_at')
            ->limit(150)
            ->get();

        $blogs = $indekslenebilir(BlogPost::query()
            ->publiclyVisible()
            ->where('originality_status', '!=', 'duplicate'))
            ->latest('updated_at')
            ->limit(50)
            ->get();

        $urls = [
            route('home'),
            route('recipes.index'),
            route('ingredients.explore'),
            route('blog.index'),
            route('cuisines.index'),
            route('categories.index'),
            route('cuisines.show', 'kibris-mutfagi'),
            route('cuisines.show', 'turk-mutfagi'),
            route('cuisines.show', 'akdeniz-mutfagi'),
            route('categories.show', 'corba'),
            route('categories.show', 'ana-yemek'),
            route('categories.show', 'zeytinyagli-sebze'),
            route('categories.show', 'salata-meze'),
            route('categories.show', 'hamur-isi'),
            route('categories.show', 'tatli'),
            url('/sure/20-dakika'),
            url('/zorluk/kolay'),
            route('llms.txt'),
            route('llms.full'),
            route('llms.txt.en'),
            route('llms.full.en'),
            route('feed'),
            route('feed.en'),
            route('opensearch'),
            route('opensearch.en'),
            url('/manifest.webmanifest'),
            route('manifest.en'),
            url('/sitemap.xml'),
            url('/sitemap-recipes.xml'),
            url('/sitemap-blog.xml'),
            url('/sitemap-cuisines.xml'),
            url('/sitemap-categories.xml'),
            url('/sitemap-ingredients.xml'),
        ];

        foreach ($recipes as $recipe) {
            $urls[] = route('recipes.show', $recipe);
        }

        foreach ($blogs as $blog) {
            $urls[] = route('blog.show', $blog);
        }

        return array_values(array_unique(array_merge($urls, $this->cevrilmisAdresler())));
    }

    /**
     * Varsayılan dil dışındaki dillerin adresleri.
     *
     * Bu olmadan IndexNow yalnız Türkçe adres bildiriyordu: bir tarif
     * İngilizceye çevrilip yayına girdiğinde arama motoruna haber giden tek
     * adres Türkçe sürümüydü ve /en/ sayfası kendiliğinden taranmayı
     * bekliyordu. Çeviri emeğinin karşılığını geciktiren nokta tam burasıydı.
     *
     * Yalnız o dilde TAM çevirisi olan kayıt bildirilir. Çevirisi olmayan
     * kaydın dil adresi zaten 404 verir; 404'ü IndexNow'a bildirmek, arama
     * motoruna olmayan sayfa vaat etmektir ve güveni düşürür.
     *
     * @return list<string>
     */
    private function cevrilmisAdresler(): array
    {
        $diller = app(LocaleUrlService::class);
        $varsayilan = $diller->defaultLocale();
        $adresler = [];
        $ceviriKapisi = null;

        foreach (array_keys($diller->enabledLocales()) as $dil) {
            if ($dil === $varsayilan) {
                continue;
            }

            $ceviriKapisi ??= app(RecipeTranslationGate::class);

            // Listeleme sayfaları ve AI keşif korpusları her açık dilde vardır; içerik kapısına
            // takılmazlar çünkü kendi metinleri dil dosyalarından veya çeviri motorundan gelir.
            foreach (['/', '/tarifler', '/blog', '/mutfaklar', '/kategoriler', '/llms.txt', '/llms-full.txt'] as $yol) {
                $adresler[] = $diller->urlFor($dil, $yol);
            }

            Recipe::query()
                ->where('status', 'published')
                ->where('verification_status', 'verified')
                ->translatedInto($dil)
                ->latest('updated_at')
                ->limit(150)
                ->get()
                ->each(function (Recipe $tarif) use (&$adresler, $diller, $dil, $ceviriKapisi): void {
                    // Adımları kalıp çeviri olan tarif o dilde noindex; arama
                    // motoruna "hemen tara" demek çelişki olur.
                    if ($ceviriKapisi->hazir($tarif, $dil)) {
                        $adresler[] = $diller->urlFor($dil, '/tarifler/'.$tarif->slug);
                    }
                });

            BlogPost::query()
                ->publiclyVisible()
                ->translatedInto($dil)
                ->latest('updated_at')
                ->limit(50)
                ->get()
                ->each(function (BlogPost $yazi) use (&$adresler, $diller, $dil): void {
                    $adresler[] = $diller->urlFor($dil, '/blog/'.$yazi->slug);
                });
        }

        return $adresler;
    }
}
