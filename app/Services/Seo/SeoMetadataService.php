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

        if (app()->getLocale() !== config('locales.default', 'tr')) {
            $translatedTitle = (string) $entity->translated('title');
            $title = $this->formatTitleWithBrand($translatedTitle !== '' ? $translatedTitle : $fallbackTitle);

            $desc = (string) ($entity instanceof Recipe ? ($entity->translated('description') ?: $entity->translated('excerpt')) : $entity->translated('excerpt'));
            if ($entity instanceof Recipe && $desc !== '' && ($metadata?->meta_description === null || trim((string) $metadata->meta_description) === '')) {
                $totalMinutes = $entity->totalMinutes();
                $servings = (int) $entity->servings;
                $prefix = '';
                if ($totalMinutes > 0) {
                    $prefix .= "⏱ {$totalMinutes} min • ";
                }
                if ($servings > 0) {
                    $prefix .= "{$servings} Servings • ";
                }
                if ($prefix !== '' && ! str_contains($desc, '⏱')) {
                    $desc = $prefix.$desc;
                }
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

        if ($entity instanceof Recipe && $entity->cuisine && $entity->cuisine->slug === 'kibris-mutfagi') {
            if ($title !== '' && ! str_contains(mb_strtolower($title), 'kibris')) {
                $title = $title.' — Kıbrıs Yemek Tarifi';
            }
        }

        $title = $this->formatTitleWithBrand($title);

        $description = (string) ($metadata?->meta_description ?: $fallbackDescription ?: '');
        if ($entity instanceof Recipe && ($metadata?->meta_description === null || trim((string) $metadata->meta_description) === '')) {
            $totalMinutes = $entity->totalMinutes();
            $servings = (int) $entity->servings;
            $prefix = '';
            if ($totalMinutes > 0) {
                $prefix .= "⏱ {$totalMinutes} dk • ";
            }
            if ($servings > 0) {
                $prefix .= "{$servings} Kişilik • ";
            }
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

        return implode(',', array_merge([
            in_array('noindex', $directives, true) ? 'noindex' : 'index',
            in_array('nofollow', $directives, true) ? 'nofollow' : 'follow',
        ], array_values(array_diff($directives, ['all', 'none', 'index', 'noindex', 'follow', 'nofollow']))));
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
