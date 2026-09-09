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

        if (app()->getLocale() !== config('locales.default', 'tr')) {
            return [
                'title' => Str::limit((string) $entity->translated('title').' · House Chef Cyprus', 70, ''),
                'description' => Str::limit((string) $entity->translated('excerpt'), 160, ''),
                'canonical' => app(LocaleUrlService::class)->urlFor(app()->getLocale(), $canonical),
                'robots' => config('seo.indexing_enabled') ? ($metadata?->robots ?: $defaultRobots) : 'noindex,nofollow',
            ];
        }

        return [
            'title' => Str::limit((string) ($metadata?->meta_title ?: $fallbackTitle), 70, ''),
            'description' => Str::limit((string) ($metadata?->meta_description ?: $fallbackDescription ?: ''), 160, ''),
            'canonical' => $this->safeCanonical($candidateCanonical, $canonical),
            'robots' => config('seo.indexing_enabled') ? ($metadata?->robots ?: $defaultRobots) : 'noindex,nofollow',
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

        return is_string($candidateHost) && is_string($applicationHost) && hash_equals($applicationHost, $candidateHost) ? $candidate : $fallback;
    }
}
