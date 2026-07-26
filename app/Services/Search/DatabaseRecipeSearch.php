<?php

declare(strict_types=1);

namespace App\Services\Search;

use App\Contracts\Search\RecipeSearchInterface;
use App\Models\Recipe;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

final class DatabaseRecipeSearch implements RecipeSearchInterface
{
    public function search(array $filters): LengthAwarePaginator
    {
        return Recipe::query()->with(['cuisine', 'category', 'media'])
            ->where('status', 'published')
            ->when($filters['q'] ?? null, fn ($query, string $term) => $query->where(fn ($nested) => $nested->where('title', 'like', "%{$term}%")->orWhereHas('ingredients', fn ($ingredients) => $ingredients->where('name', 'like', "%{$term}%"))))
            ->when($filters['cuisine'] ?? null, fn ($query, string $slug) => $query->whereHas('cuisine', fn ($cuisines) => $cuisines->where('slug', $slug)))
            ->when($filters['max_minutes'] ?? null, fn ($query, int $minutes) => $query->whereRaw('(prep_minutes + cook_minutes + rest_minutes) <= ?', [$minutes]))
            ->when($filters['diet'] ?? null, fn ($query, string $diet) => $query->whereJsonContains('dietary_tags', $diet))
            ->orderByDesc('is_featured')->latest()->paginate(12)->withQueryString();
    }
}
