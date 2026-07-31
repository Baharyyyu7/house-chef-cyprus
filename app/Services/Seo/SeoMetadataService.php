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
    public function for(Recipe|BlogPost $entity, string $fallbackTitle, string $fallbackDescription, string $canonical, string $defaultRobots = 'index,follow'): array
    {
        /** @var SeoMetadata|null $metadata */
        $metadata = $entity->relationLoaded('seoMetadata') ? $entity->getRelation('seoMetadata') : $entity->seoMetadata()->first();

        return [
            'title' => Str::limit((string) ($metadata?->meta_title ?: $fallbackTitle), 70, ''),
            'description' => Str::limit((string) ($metadata?->meta_description ?: $fallbackDescription), 160, ''),
            'canonical' => $metadata?->canonical_override ?: $canonical,
            'robots' => $metadata?->robots ?: $defaultRobots,
        ];
    }

    /** @param array<string, mixed> $attributes */
    public function update(Recipe|BlogPost $entity, array $attributes): SeoMetadata
    {
        /** @var SeoMetadata $metadata */
        $metadata = $entity->seoMetadata()->updateOrCreate([], $attributes);

        return $metadata;
    }
}
