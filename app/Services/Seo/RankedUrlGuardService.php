<?php

declare(strict_types=1);

namespace App\Services\Seo;

use App\Models\BlogPost;
use App\Models\Recipe;

/**
 * Google'ın gösterdiği adreslerin hâlâ açık olduğunu denetler.
 *
 * 18 Eylül 2026'da ölçüldü: son 28 günün 1.856 gösteriminin dörtte biri,
 * bir gün önce toplu olarak taslağa çekilmiş 10 yazıya aitti. Sayfalar 404
 * veriyordu ve kimse fark etmemişti; grafik bu yüzden düşüyordu.
 *
 * Bu servis Search Console satırlarını alır ve gösterim alan her adresin
 * yayında ve indekslenebilir olup olmadığına bakar. Kapanmış olanları
 * raporlar; içeriği kendiliğinden geri açmaz, çünkü kapatılma sebebi içerik
 * kalitesi olabilir. Karar insana ya da içerik düzeltmesine kalır.
 */
final class RankedUrlGuardService
{
    public const ASGARI_GOSTERIM = 5;

    /**
     * @param  list<array{page: string, impressions: int|float, clicks?: int|float, position?: int|float}>  $satirlar
     * @return list<array{page: string, impressions: int, reason: string}>
     */
    public function kapananlar(array $satirlar): array
    {
        $sonuc = [];

        foreach ($satirlar as $satir) {
            if ((int) ($satir['impressions'] ?? 0) < self::ASGARI_GOSTERIM) {
                continue;
            }

            $sebep = $this->sebep((string) $satir['page']);

            if ($sebep !== null) {
                $sonuc[] = ['page' => (string) $satir['page'], 'impressions' => (int) $satir['impressions'], 'reason' => $sebep];
            }
        }

        usort($sonuc, static fn (array $a, array $b): int => $b['impressions'] <=> $a['impressions']);

        return $sonuc;
    }

    private function sebep(string $adres): ?string
    {
        $yol = trim((string) parse_url($adres, PHP_URL_PATH), '/');
        $parcalar = explode('/', $yol);

        // Dil öneki soyulur: /en/blog/x ile /blog/x aynı kayda bakar.
        if (isset($parcalar[0]) && strlen($parcalar[0]) === 2 && count($parcalar) > 2) {
            array_shift($parcalar);
        }

        [$bolum, $slug] = [$parcalar[0] ?? '', $parcalar[1] ?? ''];

        if ($slug === '') {
            return null;
        }

        if ($bolum === 'blog') {
            $yazi = BlogPost::query()->where('slug', $slug)->with('seoMetadata')->first();

            if ($yazi === null) {
                return 'kayit_yok';
            }

            if ($yazi->status !== 'published' || $yazi->published_at === null || $yazi->published_at->isFuture()) {
                return 'yayinda_degil';
            }

            return str_contains(app(BlogIndexability::class)->robots($yazi), 'noindex') ? 'noindex' : null;
        }

        if (in_array($bolum, ['tarifler', 'recipes', 'rezepte', 'recepty', 'przepisy', 'receitas'], true)) {
            $tarif = Recipe::query()->where('slug', $slug)->with('seoMetadata')->first();

            if ($tarif === null) {
                return 'kayit_yok';
            }

            if ($tarif->status !== 'published') {
                return 'yayinda_degil';
            }

            return str_contains((string) $tarif->seoMetadata?->robots, 'noindex') ? 'noindex' : null;
        }

        return null;
    }
}
