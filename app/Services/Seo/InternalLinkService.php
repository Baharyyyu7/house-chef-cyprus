<?php

declare(strict_types=1);

namespace App\Services\Seo;

use App\Models\BlogPost;
use App\Models\Recipe;

final class InternalLinkService
{
    /** @return list<array{label:string,url:string,kind:string}> */
    public function suggestionsFor(BlogPost $post): array
    {
        $linkedIds = $post->recipes()->pluck('recipes.id')->all();

        return Recipe::query()->where('status', 'published')->where('verification_status', 'verified')->translatedInto()->when($linkedIds !== [], fn ($query) => $query->whereNotIn('id', $linkedIds))->orderByDesc('is_featured')->limit((int) config('seo.audit.max_internal_links_per_document', 8))->get(['id', 'title', 'slug'])->map(fn (Recipe $recipe): array => ['label' => $recipe->title, 'url' => route('recipes.show', $recipe), 'kind' => 'recipe'])->all();
    }
}
