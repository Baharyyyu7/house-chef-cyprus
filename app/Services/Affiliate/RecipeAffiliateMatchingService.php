<?php

declare(strict_types=1);

namespace App\Services\Affiliate;

use App\Models\AffiliateLink;
use App\Models\Recipe;
use Illuminate\Support\Collection;

final class RecipeAffiliateMatchingService
{
    /** @var Collection<string, AffiliateLink>|null */
    private static ?Collection $cachedLinks = null;

    public static function clearCache(): void
    {
        self::$cachedLinks = null;
    }

    /**
     * @return Collection<int, AffiliateLink>
     */
    public function forRecipe(Recipe $recipe, int $limit = 2): Collection
    {
        if (! config('integrations.affiliate.enabled', true)) {
            return collect();
        }

        if (self::$cachedLinks === null || app()->runningUnitTests()) {
            $links = AffiliateLink::query()
                ->where('is_active', true)
                ->get()
                ->keyBy('slug');

            if (! app()->runningUnitTests()) {
                self::$cachedLinks = $links;
            }

            $allLinks = $links;
        } else {
            $allLinks = self::$cachedLinks;
        }

        if ($allLinks->isEmpty()) {
            return collect();
        }

        $categorySlug = (string) ($recipe->category?->slug ?? '');
        $titleLower = mb_strtolower((string) $recipe->title);

        $matchedSlugs = [];

        // 1. Çorba & Püre
        if (str_contains($categorySlug, 'corba') || str_contains($titleLower, 'çorba') || str_contains($titleLower, 'corba')) {
            $matchedSlugs[] = 'braun-el-blender-seti';
            $matchedSlugs[] = 'paslanmaz-celik-duduklu-tencere';
        }

        // 2. Bakliyat (Nohut, Fasulye, Mercimek)
        elseif (str_contains($titleLower, 'nohut') || str_contains($titleLower, 'fasulye') || str_contains($titleLower, 'barbunya')) {
            $matchedSlugs[] = 'paslanmaz-celik-duduklu-tencere';
            $matchedSlugs[] = 'dokum-tencere-lava-28cm';
        }

        // 3. Et, Tandır, Güveç, Yahni
        elseif (str_contains($titleLower, 'tandır') || str_contains($titleLower, 'tandir') || str_contains($titleLower, 'güveç') || str_contains($titleLower, 'yahni') || str_contains($titleLower, 'kuzu') || str_contains($titleLower, 'incik') || str_contains($titleLower, 'kleftiko')) {
            $matchedSlugs[] = 'dokum-tencere-lava-28cm';
            $matchedSlugs[] = 'pirge-ecco-sef-bicagi';
        }

        // 4. Izgara, Kebap, Köfte, Hellim
        elseif (str_contains($titleLower, 'kebap') || str_contains($titleLower, 'kofte') || str_contains($titleLower, 'köfte') || str_contains($titleLower, 'ızgara') || str_contains($titleLower, 'hellim') || str_contains($titleLower, 'antrikot')) {
            $matchedSlugs[] = 'dokum-izgara-tava-lava';
            $matchedSlugs[] = 'pirge-ecco-sef-bicagi';
        }

        // 5. Hamur İşi & Ekmek & Börek
        elseif (str_contains($categorySlug, 'hamur') || str_contains($titleLower, 'börek') || str_contains($titleLower, 'borek') || str_contains($titleLower, 'pide') || str_contains($titleLower, 'ekmek') || str_contains($titleLower, 'lahmacun')) {
            $matchedSlugs[] = 'dijital-hassas-mutfak-tartisi';
            $matchedSlugs[] = 'sicak-hava-fritozu-airfryer';
        }

        // 6. Tatlı & Kek
        elseif (str_contains($categorySlug, 'tatli') || str_contains($titleLower, 'kek') || str_contains($titleLower, 'tatlı') || str_contains($titleLower, 'revani') || str_contains($titleLower, 'helva') || str_contains($titleLower, 'sütlaç') || str_contains($titleLower, 'sutlac')) {
            $matchedSlugs[] = 'dokum-kek-kalibi';
            $matchedSlugs[] = 'dijital-hassas-mutfak-tartisi';
        }

        // 7. Salata & Meze
        elseif (str_contains($categorySlug, 'salata') || str_contains($categorySlug, 'meze') || str_contains($titleLower, 'salata') || str_contains($titleLower, 'ezme') || str_contains($titleLower, 'humus')) {
            $matchedSlugs[] = 'pirge-ecco-sef-bicagi';
            $matchedSlugs[] = str_contains($titleLower, 'humus') || str_contains($titleLower, 'ezme')
                ? 'braun-el-blender-seti'
                : 'ahsap-bambu-kesme-tahtasi';
        }

        // 8. Kızartma & Çıtır atıştırmalık
        elseif (str_contains($titleLower, 'çıtır') || str_contains($titleLower, 'kızartma') || str_contains($titleLower, 'patates')) {
            $matchedSlugs[] = 'sicak-hava-fritozu-airfryer';
            $matchedSlugs[] = 'dijital-hassas-mutfak-tartisi';
        }

        // 9. Genel Sebze & Zeytinyağlı
        elseif (str_contains($categorySlug, 'zeytinyagli') || str_contains($categorySlug, 'sebze')) {
            $matchedSlugs[] = 'pirge-ecco-sef-bicagi';
            $matchedSlugs[] = 'dokum-tencere-lava-28cm';
        }

        // Varsayılan eşleşmeler
        $matchedSlugs[] = 'dijital-hassas-mutfak-tartisi';
        $matchedSlugs[] = 'pirge-ecco-sef-bicagi';
        $matchedSlugs[] = 'kilitli-cam-saklama-kabi-seti';

        $matchedSlugs = array_values(array_unique($matchedSlugs));

        $result = collect();
        foreach ($matchedSlugs as $itemSlug) {
            if ($result->count() >= $limit) {
                break;
            }
            if ($allLinks->has($itemSlug)) {
                $item = clone $allLinks->get($itemSlug);
                $this->applyAffiliateTags($item);
                $result->push($item);
            }
        }

        return $result;
    }

    private function applyAffiliateTags(AffiliateLink $link): void
    {
        $amazonTag = config('integrations.affiliate.amazon.tag', 'housechefcyprus-21');

        if ($link->provider === 'amazon' && ! empty($amazonTag)) {
            $parsed = parse_url($link->target_url);
            if (isset($parsed['query'])) {
                parse_str($parsed['query'], $queryParams);
                $queryParams['tag'] = $amazonTag;
                $scheme = $parsed['scheme'] ?? 'https';
                $host = $parsed['host'] ?? 'www.amazon.com.tr';
                $path = $parsed['path'] ?? '';
                $link->target_url = "{$scheme}://{$host}{$path}?" . http_build_query($queryParams);
            }
        }
    }
}
