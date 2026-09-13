<?php

declare(strict_types=1);

namespace App\Services\Seo;

use App\Models\BlogPost;
use App\Models\Recipe;
use App\Services\Seo\LocaleUrlService;
use Illuminate\Support\Facades\Http;
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
     * @return array{success: bool, status: int, count: int, urls: array<int, string>}
     */
    public function submit(?array $urls = null): array
    {
        if ($urls === null || empty($urls)) {
            $urls = $this->collectRecentUrls();
        }

        if (empty($urls)) {
            return [
                'success' => true,
                'status' => 200,
                'count' => 0,
                'urls' => [],
            ];
        }

        $key = $this->getKey();
        $host = (string) parse_url(config('app.url', 'https://housechefcyprus.com'), PHP_URL_HOST);
        if ($host === '') {
            $host = 'housechefcyprus.com';
        }

        $payload = [
            'host' => $host,
            'key' => $key,
            'keyLocation' => "https://{$host}/{$key}.txt",
            'urlList' => array_values(array_unique($urls)),
        ];

        try {
            $response = Http::timeout(10)
                ->withHeaders(['Content-Type' => 'application/json; charset=utf-8'])
                ->post(self::ENDPOINT, $payload);

            $status = $response->status();
            $success = $response->successful();

            Log::info('IndexNow submission completed', [
                'status' => $status,
                'count' => count($urls),
            ]);

            return [
                'success' => $success,
                'status' => $status,
                'count' => count($urls),
                'urls' => $urls,
            ];
        } catch (\Throwable $e) {
            Log::warning('IndexNow submission failed: ' . $e->getMessage(), [
                'count' => count($urls),
            ]);

            return [
                'success' => false,
                'status' => 500,
                'count' => count($urls),
                'urls' => $urls,
            ];
        }
    }

    /**
     * @return array<int, string>
     */
    public function collectRecentUrls(): array
    {
        $recipes = Recipe::query()
            ->where('status', 'published')
            ->where('verification_status', 'verified')
            ->latest('updated_at')
            ->limit(150)
            ->get();

        $blogs = BlogPost::query()
            ->publiclyVisible()
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

        foreach (array_keys($diller->enabledLocales()) as $dil) {
            if ($dil === $varsayilan) {
                continue;
            }

            // Listeleme sayfaları her açık dilde vardır; içerik kapısına
            // takılmazlar çünkü kendi metinleri dil dosyalarından gelir.
            foreach (['/', '/tarifler', '/blog', '/mutfaklar', '/kategoriler'] as $yol) {
                $adresler[] = $diller->urlFor($dil, $yol);
            }

            Recipe::query()
                ->where('status', 'published')
                ->where('verification_status', 'verified')
                ->translatedInto($dil)
                ->latest('updated_at')
                ->limit(150)
                ->get()
                ->each(function (Recipe $tarif) use (&$adresler, $diller, $dil): void {
                    $adresler[] = $diller->urlFor($dil, '/tarifler/'.$tarif->slug);
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
