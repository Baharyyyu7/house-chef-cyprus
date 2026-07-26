<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\Ingredient;
use App\Models\IngredientAlias;
use Illuminate\Support\Str;

final class IngredientAliasService
{
    public function normalize(string $alias): string
    {
        return Str::of($alias)->lower()->ascii()->replaceMatches('/[^a-z0-9]+/', ' ')->squish()->value();
    }

    public function resolve(string $term): ?Ingredient
    {
        $normalized = $this->normalize($term);

        return Ingredient::query()->whereRaw('lower(name) = ?', [mb_strtolower($term)])
            ->orWhere('canonical_name', $term)
            ->orWhereHas('aliases', fn ($query) => $query->where('normalized_alias', $normalized)->where('locale', 'tr'))
            ->first();
    }

    public function add(Ingredient $ingredient, string $alias, string $sourceType = 'editorial', float $confidence = 100): IngredientAlias
    {
        $normalized = $this->normalize($alias);

        return IngredientAlias::query()->firstOrCreate(['ingredient_id' => $ingredient->id, 'normalized_alias' => $normalized, 'locale' => 'tr'], ['alias' => $alias, 'source_type' => $sourceType, 'confidence_score' => $confidence]);
    }
}
