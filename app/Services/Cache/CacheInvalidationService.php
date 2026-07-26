<?php

declare(strict_types=1);

namespace App\Services\Cache;

use App\Models\Recipe;

final class CacheInvalidationService
{
    public function recipe(Recipe $recipe): void
    {
        $key = 'cache:recipe:'.$recipe->public_id.':version';
        cache()->forever($key, (int) cache()->get($key, 1) + 1);
    }
}
