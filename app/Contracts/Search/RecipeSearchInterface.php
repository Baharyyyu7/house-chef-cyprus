<?php

declare(strict_types=1);

namespace App\Contracts\Search;

use Illuminate\Contracts\Pagination\LengthAwarePaginator;

interface RecipeSearchInterface
{
    /** @param array<string, mixed> $filters */
    public function search(array $filters): LengthAwarePaginator;
}
