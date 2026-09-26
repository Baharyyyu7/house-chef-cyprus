<?php

declare(strict_types=1);

namespace App\Services\Seo;

use App\Models\BlogPost;
use App\Models\Recipe;
use App\Models\SeoMetadata;
use Illuminate\Support\Str;

final class SeoMetadataService
{
    /** @return array{title:string,description:string,canonical:string,robots:string} */
    public function for(Recipe|BlogPost $entity, string $fallbackTitle, ?string $fallbackDescription, string $canonical, string $defaultRobots = 'index,follow'): array
    {
        /** @var SeoMetadata|null $metadata */
        $metadata = $entity->relationLoaded('seoMetadata') ? $entity->getRelation('seoMetadata') : $entity->seoMetadata()->first();

        $candidateCanonical = is_string($metadata?->canonical_override) ? $metadata->canonical_override : null;
        $robots = $this->robots($metadata?->robots, $defaultRobots);

        // Reklam uygunluğu indekslenebilirlikten ayrı ölçülür. Aramada
        // görünmeye değen bir yazı reklam taşımaya uygun olmayabilir ve
        // 2026-09-24'te yayındaki 156 yazının 100'ü tam olarak bu
        // durumdaydı: indekste, reklamlı ve 700 kelimenin altında.
        // Bayrak burada kurulur çünkü reklam yuvası düzenden önce
        // işleniyor; düzene yazmak ona geç kalıyor.
        if ($entity instanceof BlogPost && app()->bound('request')) {
            request()->attributes->set(
                'hc_page_ads_allowed',
                app(BlogIndexability::class)->reklamTasiyabilir($entity)
            );
        }

        if (app()->getLocale() !== config('locales.default', 'tr')) {
            $locale = app()->getLocale();
            $translatedTitle = (string) $entity->translated('title');
            $rawTitle = $translatedTitle !== '' ? $translatedTitle : $fallbackTitle;
            $title = $this->cleanBrandSuffix($rawTitle);

            if ($entity instanceof Recipe && $title !== '') {
                $lower = mb_strtolower($title);
                $title = match ($locale) {
                    'en' => ! str_contains($lower, 'recipe') ? $title.' Recipe' : $title,
                    'de' => ! str_contains($lower, 'rezept') ? $title.' Rezept' : $title,
                    'pl' => ! str_contains($lower, 'przepis') ? $title.' Przepis' : $title,
                    'pt' => ! str_contains($lower, 'receita') ? $title.' Receita' : $title,
                    'ru' => ! str_contains($lower, 'рецепт') ? $title.' Рецепт' : $title,
                    'fa' => (! str_contains($title, 'طرز تهیه') && ! str_contains($title, 'دستور')) ? $title.' - طرز تهیه' : $title,
                    default => $title,
                };
            }

            $title = $this->formatTitleWithBrand($title);

            $desc = (string) ($entity instanceof Recipe ? ($entity->translated('description') ?: $entity->translated('excerpt')) : $entity->translated('excerpt'));
            if ($entity instanceof Recipe && $desc !== '' && ($metadata?->meta_description === null || trim((string) $metadata->meta_description) === '')) {
                $prefix = $this->timeAndServingsPrefix($entity, $locale);
                if ($prefix !== '' && ! str_contains($desc, '⏱')) {
                    $desc = $prefix.$desc;
                }
            }

            // Arama sonucuna elle yazılmış dile özgü başlık ve açıklama
            // (Claude Coni'nin coni:serp işi). Sayfanın H1'i ve gövdesi aynı
            // kalır; yalnız arama sonucunda görünen metin değişir. Elle
            // yazıldığı için "Recipe" eki ve süre öneki eklenmez.
            $serpTitle = trim((string) $entity->translated('seo_title'));
            $serpDescription = trim((string) $entity->translated('seo_description'));

            if ($serpTitle !== '') {
                $title = $this->formatTitleWithBrand($serpTitle);
            }

            if ($serpDescription !== '') {
                $desc = $serpDescription;
            }

            return [
                'title' => $title,
                'description' => Str::limit($desc, 160, ''),
                'canonical' => app(LocaleUrlService::class)->urlFor(app()->getLocale(), $canonical),
                'robots' => $robots,
            ];
        }

        // Kıbrıs mutfağı tariflerinde başlığa "Kıbrıs Yemek Tarifi" ekle:
        // "Hellim Kızartması" → "Hellim Kızartması — Kıbrıs Yemek Tarifi · House Chef Cyprus"
        // "Kıbrıs Usulü Kleftiko" → "Kıbrıs Usulü Kleftiko · House Chef Cyprus"
        $rawTitle = (string) ($metadata?->meta_title ?: $fallbackTitle);
        $title = $this->cleanBrandSuffix($rawTitle);

        if ($entity instanceof Recipe) {
            if ($entity->cuisine && $entity->cuisine->slug === 'kibris-mutfagi') {
                if ($title !== '' && ! str_contains(mb_strtolower($title), 'kibris')) {
                    $title = $title.' — Kıbrıs Yemek Tarifi';
                } elseif ($title !== '' && ! str_contains(mb_strtolower($title), 'tarif')) {
                    $title = $title.' Tarifi';
                }
            } elseif ($title !== '' && ! str_contains(mb_strtolower($title), 'tarif')) {
                $title = $title.' Tarifi';
            }
        }

        $title = $this->formatTitleWithBrand($title);

        $description = (string) ($metadata?->meta_description ?: $fallbackDescription ?: '');
        if ($entity instanceof Recipe && ($metadata?->meta_description === null || trim((string) $metadata->meta_description) === '')) {
            $prefix = $this->timeAndServingsPrefix($entity, 'tr');
            if ($prefix !== '' && $description !== '' && ! str_contains($description, '⏱')) {
                $description = $prefix.$description;
            }
        }

        return [
            'title' => $title,
            'description' => Str::limit($description, 160, ''),
            'canonical' => $this->safeCanonical($candidateCanonical, $canonical),
            'robots' => $robots,
        ];
    }

    /** @param array<string, mixed> $attributes */
    public function update(Recipe|BlogPost $entity, array $attributes): SeoMetadata
    {
        /** @var SeoMetadata $metadata */
        $metadata = $entity->seoMetadata()->updateOrCreate([], $attributes);

        return $metadata;
    }

    public function timeAndServingsPrefix(Recipe $recipe, string $locale): string
    {
        $totalMinutes = $recipe->totalMinutes();
        $servings = (int) $recipe->servings;

        if ($totalMinutes <= 0 && $servings <= 0) {
            return '';
        }

        $minStr = match ($locale) {
            'tr' => "⏱ {$totalMinutes} dk",
            'de' => "⏱ {$totalMinutes} Min.",
            'ru' => "⏱ {$totalMinutes} мин",
            'fa' => "⏱ {$totalMinutes} دقیقه",
            default => "⏱ {$totalMinutes} min",
        };

        $srvStr = match ($locale) {
            'tr' => "{$servings} Kişilik",
            'de' => "{$servings} Portionen",
            'pl' => "{$servings} porcje",
            'pt' => "{$servings} porções",
            'ru' => "{$servings} порций",
            'fa' => "{$servings} نفره",
            default => "{$servings} Servings",
        };

        $parts = [];
        if ($totalMinutes > 0) {
            $parts[] = $minStr;
        }
        if ($servings > 0) {
            $parts[] = $srvStr;
        }

        return $parts !== [] ? implode(' • ', $parts).' • ' : '';
    }

    private function safeCanonical(?string $candidate, string $fallback): string
    {
        if ($candidate === null || $candidate === '') {
            return $fallback;
        }
        $candidateHost = parse_url($candidate, PHP_URL_HOST);
        $applicationHost = parse_url(url('/'), PHP_URL_HOST);

        return in_array(parse_url($candidate, PHP_URL_SCHEME), ['http', 'https'], true)
            && parse_url($candidate, PHP_URL_USER) === null
            && parse_url($candidate, PHP_URL_PASS) === null
            && parse_url($candidate, PHP_URL_FRAGMENT) === null
            && is_string($candidateHost) && is_string($applicationHost)
            && strcasecmp($applicationHost, $candidateHost) === 0 ? $candidate : $fallback;
    }

    public function robots(?string $override, string $defaults = 'index,follow'): string
    {
        if (! config('seo.indexing_enabled')) {
            return 'noindex,nofollow';
        }

        $directives = array_unique(array_filter(array_map('trim', explode(',', strtolower(($override ?: '').','.$defaults)))));
        if (in_array('none', $directives, true)) {
            $directives = array_merge($directives, ['noindex', 'nofollow']);
        }

        $sonuc = implode(',', array_merge([
            in_array('noindex', $directives, true) ? 'noindex' : 'index',
            in_array('nofollow', $directives, true) ? 'nofollow' : 'follow',
        ], array_values(array_diff($directives, ['all', 'none', 'index', 'noindex', 'follow', 'nofollow']))));

        // Sayfanın kapalı olduğu, görünüm katmanının okuyabileceği tek yere
        // yazılır. Düzen bir Blade bileşeni olduğu için gövdesi ondan önce
        // işleniyor; bayrağı düzende kurmak reklam yuvası için geç kalıyordu.
        if (str_contains($sonuc, 'noindex') && app()->bound('request')) {
            request()->attributes->set('hc_page_is_noindex', true);
        }

        return $sonuc;
    }

    public function cleanBrandSuffix(string $title): string
    {
        $title = trim($title);

        return trim(preg_replace('/\s*(\·|\||\-|\—)\s*House Chef Cyprus$/ui', '', $title) ?? $title);
    }

    public function formatTitleWithBrand(string $rawTitle, string $brand = 'House Chef Cyprus', int $maxTotalLength = 68): string
    {
        $coreTitle = $this->cleanBrandSuffix($rawTitle);
        if ($coreTitle === '') {
            return $brand;
        }

        $suffix = ' · '.$brand;
        $suffixLen = mb_strlen($suffix);
        $maxCoreLength = $maxTotalLength - $suffixLen;

        // Başlık marka ekiyle birlikte sığmıyorsa önce ek bırakılır. Asıl
        // başlığı ortasından kesip "…" koymak, arama sonucunda sorguyu
        // karşılayan kelimeleri siliyordu: "Fırın Kaç Dakikada Isınır? Ön
        // Isıtma Süreleri… · House Chef Cyprus". Marka zaten adreste ve
        // site adında görünüyor.
        if (mb_strlen($coreTitle) > $maxCoreLength && mb_strlen($coreTitle) <= $maxTotalLength) {
            return $coreTitle;
        }

        if (mb_strlen($coreTitle) > $maxTotalLength) {
            $maxCoreLength = $maxTotalLength;
            $suffix = '';
        }

        if (mb_strlen($coreTitle) > $maxCoreLength) {
            $truncated = mb_substr($coreTitle, 0, $maxCoreLength - 1);
            $lastSpace = mb_strrpos($truncated, ' ');
            if ($lastSpace !== false && $lastSpace > (int) ($maxCoreLength * 0.5)) {
                $coreTitle = mb_substr($truncated, 0, $lastSpace).'…';
            } else {
                $coreTitle = $truncated.'…';
            }
        }

        return $coreTitle.$suffix;
    }
}
