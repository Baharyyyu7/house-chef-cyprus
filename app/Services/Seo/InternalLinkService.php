<?php

declare(strict_types=1);

namespace App\Services\Seo;

use App\Models\BlogPost;
use App\Models\Recipe;

final class InternalLinkService
{
    /**
     * @return list<array{label: string, url: string, kind: string, relevance_score?: int, reason?: string}>
     */
    public function suggestionsFor(BlogPost $post, ?string $locale = null): array
    {
        $targetLocale = $locale ?? app()->getLocale();
        $linkedIds = $post->recipes()->pluck('recipes.id')->all();
        $limit = (int) config('seo.audit.max_internal_links_per_document', 8);

        $query = Recipe::query()
            ->where('status', 'published')
            ->where('verification_status', 'verified')
            ->translatedInto($targetLocale)
            ->when($linkedIds !== [], fn ($q) => $q->whereNotIn('id', $linkedIds));

        $recipes = $query->orderByDesc('is_featured')
            ->limit($limit)
            ->get();

        return $recipes->map(function (Recipe $recipe) use ($targetLocale): array {
            $label = $recipe->translated('title', $targetLocale) ?: (string) $recipe->getRawOriginal('title');
            $url = app(LocaleUrlService::class)->urlFor($targetLocale, '/tarifler/'.$recipe->slug);

            return [
                'label' => $label,
                'url' => $url,
                'kind' => 'recipe',
                'relevance_score' => $recipe->is_featured ? 90 : 75,
                'reason' => 'Doğrulanmış gastronomi ve tarif içeriği',
            ];
        })->all();
    }

    /**
     * Suggests complementary recipes and related editorial guides for a recipe.
     *
     * @return list<array{label: string, url: string, kind: string, relevance_score: int, reason: string}>
     */
    public function recipeSuggestionsFor(Recipe $recipe, ?string $locale = null): array
    {
        $targetLocale = $locale ?? app()->getLocale();
        $limit = (int) config('seo.audit.max_internal_links_per_document', 6);

        $relatedRecipes = Recipe::query()
            ->where('status', 'published')
            ->where('verification_status', 'verified')
            ->where('id', '!=', $recipe->id)
            ->translatedInto($targetLocale)
            ->when($recipe->cuisine_id, fn ($q) => $q->where('cuisine_id', $recipe->cuisine_id))
            ->limit($limit)
            ->get();

        $results = [];

        foreach ($relatedRecipes as $rel) {
            $label = $rel->translated('title', $targetLocale) ?: (string) $rel->getRawOriginal('title');
            $results[] = [
                'label' => $label,
                'url' => app(LocaleUrlService::class)->urlFor($targetLocale, '/tarifler/'.$rel->slug),
                'kind' => 'recipe',
                'relevance_score' => 85,
                'reason' => 'Aynı mutfak ve teruar eşleşmesi',
            ];
        }

        return $results;
    }
}
