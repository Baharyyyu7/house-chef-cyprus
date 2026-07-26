<?php

declare(strict_types=1);

namespace App\Contracts\AI;

use App\Models\Recipe;

interface AiProviderInterface
{
    /** @param array<string, mixed> $context */
    public function answer(string $question, ?Recipe $recipe = null, array $context = []): string;
}
