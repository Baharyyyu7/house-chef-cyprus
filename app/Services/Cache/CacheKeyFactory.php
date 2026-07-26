<?php

declare(strict_types=1);

namespace App\Services\Cache;

use App\Models\Recipe;

final class CacheKeyFactory
{
    public function nutrition(Recipe $recipe, int $servings, bool $includeOptional): string
    {
        return sprintf('nutrition:recipe:%s:v%d:servings:%d:optional:%d', $recipe->public_id, $this->recipeVersion($recipe), $servings, $includeOptional ? 1 : 0);
    }

    public function recipeVersion(Recipe $recipe): int
    {
        return (int) cache()->get('cache:recipe:'.$recipe->public_id.':version', 1);
    }

    public function warmupMarker(string $section): string
    {
        return 'cache:warmup:'.$section;
    }
}
