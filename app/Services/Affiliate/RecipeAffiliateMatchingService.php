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
            $matchedSlugs[] = 'isiya-dayanikli-silikon-spatula-seti';
        }

        // 2. Balık & Deniz Ürünleri
        elseif (str_contains($categorySlug, 'balik') || str_contains($categorySlug, 'deniz') || str_contains($titleLower, 'levrek') || str_contains($titleLower, 'çipura') || str_contains($titleLower, 'cipura') || str_contains($titleLower, 'balık') || str_contains($titleLower, 'balik') || str_contains($titleLower, 'karides') || str_contains($titleLower, 'kalamar') || str_contains($titleLower, 'ahtapot') || str_contains($titleLower, 'somon') || str_contains($titleLower, 'hamsi') || str_contains($titleLower, 'sardalya')) {
            if (str_contains($titleLower, 'güveç') || str_contains($titleLower, 'guvec') || str_contains($titleLower, 'saganaki')) {
                $matchedSlugs[] = 'toprak-guvec-firin-seti';
            } else {
                $matchedSlugs[] = 'balik-fileto-bicagi';
            }
            $matchedSlugs[] = 'dokum-izgara-tava-lava';
            $matchedSlugs[] = 'zeytinyagi-spreyi-ve-yagdanlik';
        }

        // 3. Tandır, Kleftiko, Güveç, Fırın Et, Kuzu
        elseif (str_contains($titleLower, 'tandır') || str_contains($titleLower, 'tandir') || str_contains($titleLower, 'güveç') || str_contains($titleLower, 'guvec') || str_contains($titleLower, 'yahni') || str_contains($titleLower, 'kuzu') || str_contains($titleLower, 'incik') || str_contains($titleLower, 'kleftiko')) {
            $matchedSlugs[] = 'dokum-tencere-lava-28cm';
            $matchedSlugs[] = 'anlik-dijital-et-termometresi';
            $matchedSlugs[] = 'toprak-guvec-firin-seti';
            $matchedSlugs[] = 'pirge-ecco-sef-bicagi';
        }

        // 4. Izgara, Kebap, Şeftali Kebabı, Köfte, Hellim
        elseif (str_contains($titleLower, 'kebap') || str_contains($titleLower, 'kofte') || str_contains($titleLower, 'köfte') || str_contains($titleLower, 'ızgara') || str_contains($titleLower, 'izgara') || str_contains($titleLower, 'hellim') || str_contains($titleLower, 'antrikot') || str_contains($titleLower, 'biftek') || str_contains($titleLower, 'pirzola')) {
            $matchedSlugs[] = 'dokum-izgara-tava-lava';
            $matchedSlugs[] = 'paslanmaz-celik-masa-ve-spatula';
            $matchedSlugs[] = 'anlik-dijital-et-termometresi';
            $matchedSlugs[] = 'pirge-ecco-sef-bicagi';
        }

        // 5. Bakliyat (Nohut, Fasulye, Barbunya, Mercimek)
        elseif (str_contains($titleLower, 'nohut') || str_contains($titleLower, 'fasulye') || str_contains($titleLower, 'barbunya') || (str_contains($titleLower, 'bakla') && ! str_contains($titleLower, 'baklava')) || str_contains($titleLower, 'börülce')) {
            $matchedSlugs[] = 'paslanmaz-celik-duduklu-tencere';
            $matchedSlugs[] = 'dokum-tencere-lava-28cm';
            $matchedSlugs[] = 'pirge-santoku-bicagi';
        }

        // 6. Lahmacun, Pide, Pizza
        elseif (str_contains($titleLower, 'lahmacun') || str_contains($titleLower, 'pide') || str_contains($titleLower, 'pizza')) {
            $matchedSlugs[] = 'firin-pizza-tasi-ve-kuregi';
            $matchedSlugs[] = 'silikon-hamur-mat-ve-merdane';
            $matchedSlugs[] = 'dijital-hassas-mutfak-tartisi';
        }

        // 7. Börek, Ekmek, Çörek, Hamur İşi
        elseif (str_contains($categorySlug, 'hamur') || str_contains($titleLower, 'börek') || str_contains($titleLower, 'borek') || str_contains($titleLower, 'çörek') || str_contains($titleLower, 'corek') || str_contains($titleLower, 'ekmek') || str_contains($titleLower, 'poğaça') || str_contains($titleLower, 'pogaca') || str_contains($titleLower, 'gözleme') || str_contains($titleLower, 'gozleme')) {
            $matchedSlugs[] = 'dijital-hassas-mutfak-tartisi';
            $matchedSlugs[] = 'stand-mikser-hamur-yogurma';
            $matchedSlugs[] = 'baklava-ve-borek-tepsisi';
            $matchedSlugs[] = 'pirge-ekmek-ve-borek-bicagi';
            $matchedSlugs[] = 'sicak-hava-fritozu-airfryer';
        }

        // 8. Makarna, Erişte, Mantı
        elseif (str_contains($titleLower, 'makarna') || str_contains($titleLower, 'mantı') || str_contains($titleLower, 'manti') || str_contains($titleLower, 'erişte') || str_contains($titleLower, 'eriste') || str_contains($titleLower, 'pastitsio')) {
            $matchedSlugs[] = 'celik-makarna-haslama-tenceresi';
            $matchedSlugs[] = 'silikon-hamur-mat-ve-merdane';
            $matchedSlugs[] = 'dijital-hassas-mutfak-tartisi';
        }

        // 9. Şerbetli Tatlılar (Baklava, Kadayıf, Künefe, Revani, Şambali)
        elseif (str_contains($titleLower, 'baklava') || str_contains($titleLower, 'kadayıf') || str_contains($titleLower, 'kadayif') || str_contains($titleLower, 'künefe') || str_contains($titleLower, 'kunefe') || str_contains($titleLower, 'revani') || str_contains($titleLower, 'şambali') || str_contains($titleLower, 'sambali') || str_contains($titleLower, 'helva')) {
            $matchedSlugs[] = 'baklava-ve-borek-tepsisi';
            $matchedSlugs[] = 'dijital-hassas-mutfak-tartisi';
            $matchedSlugs[] = 'isiya-dayanikli-silikon-spatula-seti';
        }

        // 10. Kek, Tart, Sütlü Tatlı (Sütlaç, Muhallebi, Kazandibi, Trileçe)
        elseif (str_contains($categorySlug, 'tatli') || str_contains($titleLower, 'kek') || str_contains($titleLower, 'tatlı') || str_contains($titleLower, 'tatli') || str_contains($titleLower, 'sütlaç') || str_contains($titleLower, 'sutlac') || str_contains($titleLower, 'muhallebi') || str_contains($titleLower, 'kazandibi') || str_contains($titleLower, 'trileçe')) {
            $matchedSlugs[] = 'dokum-kek-kalibi';
            $matchedSlugs[] = 'paslanmaz-celik-cirpici-seti';
            $matchedSlugs[] = 'dijital-hassas-mutfak-tartisi';
        }

        // 11. Salata & Meze (Humus, Cacık, Ezme, Tarator)
        elseif (str_contains($categorySlug, 'salata') || str_contains($categorySlug, 'meze') || str_contains($titleLower, 'salata') || str_contains($titleLower, 'ezme') || str_contains($titleLower, 'humus') || str_contains($titleLower, 'cacık') || str_contains($titleLower, 'cacik') || str_contains($titleLower, 'haydari') || str_contains($titleLower, 'fava')) {
            if (str_contains($titleLower, 'humus') || str_contains($titleLower, 'ezme')) {
                $matchedSlugs[] = 'braun-el-blender-seti';
                $matchedSlugs[] = 'granit-havan-ve-tokmak';
            } else {
                $matchedSlugs[] = 'salata-kurutucu-santrifuj';
                $matchedSlugs[] = 'zeytinyagi-spreyi-ve-yagdanlik';
            }
            $matchedSlugs[] = 'paslanmaz-celik-sarimsak-ezici';
            $matchedSlugs[] = 'ahsap-bambu-kesme-tahtasi';
            $matchedSlugs[] = 'pirge-santoku-bicagi';
        }

        // 12. Kahvaltı, Menemen, Yumurta, Tost
        elseif (str_contains($categorySlug, 'kahvalti') || str_contains($titleLower, 'omlet') || str_contains($titleLower, 'menemen') || str_contains($titleLower, 'yumurta') || str_contains($titleLower, 'tost') || str_contains($titleLower, 'çılbır') || str_contains($titleLower, 'cilbir')) {
            if (str_contains($titleLower, 'tost')) {
                $matchedSlugs[] = 'granit-tost-ve-panini-makinesi';
            } else {
                $matchedSlugs[] = 'dokum-sahan-seti';
            }
            $matchedSlugs[] = 'dokum-izgara-tava-lava';
            $matchedSlugs[] = 'pirge-ecco-sef-bicagi';
        }

        // 13. Kızartma & Çıtır Atıştırmalık
        elseif (str_contains($titleLower, 'çıtır') || str_contains($titleLower, 'kızartma') || str_contains($titleLower, 'patates') || str_contains($titleLower, 'mücver') || str_contains($titleLower, 'mucver')) {
            $matchedSlugs[] = 'sicak-hava-fritozu-airfryer';
            $matchedSlugs[] = 'mandolin-sebze-dograyici';
            $matchedSlugs[] = 'dijital-hassas-mutfak-tartisi';
        }

        // 14. Genel Sebze, Zeytinyağlı & Kolokas/Enginar
        elseif (str_contains($categorySlug, 'zeytinyagli') || str_contains($categorySlug, 'sebze') || str_contains($categorySlug, 'vegan') || str_contains($titleLower, 'enginar') || str_contains($titleLower, 'kolokas') || str_contains($titleLower, 'molohiya') || str_contains($titleLower, 'türlü') || str_contains($titleLower, 'turlu')) {
            $matchedSlugs[] = 'zeytinyagi-spreyi-ve-yagdanlik';
            $matchedSlugs[] = 'dokum-tencere-lava-28cm';
            $matchedSlugs[] = 'pirge-santoku-bicagi';
            $matchedSlugs[] = 'ahsap-bambu-kesme-tahtasi';
        }

        // 15. Et & Tavuk Sote / Kavurma
        elseif (str_contains($titleLower, 'sote') || str_contains($titleLower, 'kavurma') || str_contains($titleLower, 'tavuk') || str_contains($titleLower, 'tantuni')) {
            $matchedSlugs[] = 'granit-wok-tava-28cm';
            $matchedSlugs[] = 'pirge-ecco-sef-bicagi';
            $matchedSlugs[] = 'anlik-dijital-et-termometresi';
        }

        // Güçlü varsayılan eşleşmeler
        $matchedSlugs[] = 'pirge-ecco-sef-bicagi';
        $matchedSlugs[] = 'dijital-hassas-mutfak-tartisi';
        $matchedSlugs[] = 'ahsap-bambu-kesme-tahtasi';
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
