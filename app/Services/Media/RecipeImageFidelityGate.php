<?php

declare(strict_types=1);

namespace App\Services\Media;

use App\Models\Recipe;

final class RecipeImageFidelityGate
{
    /** @var array<string, list<string>> */
    private const REQUIRED_INGREDIENTS = [
        'kibris-usulu-firin-tavuk' => ['tavuk'],
        'domates-corbasi' => ['domates'],
        'limonlu-akdeniz-makarnasi' => ['limon', 'makarna'],
        'sebzeli-teriyaki-tavuk' => ['tavuk'],
        'firin-sebzeli-taco-kasesi' => ['fasulye'],
        'nohutlu-akdeniz-kasesi' => ['nohut'],
        'peynirli-borek' => ['peynir'],
        'baklava-dilimi' => ['yufka'],
        'zeytinyagli-nohutlu-pilav' => ['nohut', 'pirinç'],
        'cacik' => ['yoğurt'],
    ];

    /** @return array{ready:bool,missing:list<string>,declared:list<string>} */
    public function assess(Recipe $recipe): array
    {
        $declared = $recipe->ingredients->map(fn ($ingredient): string => mb_strtolower((string) ($ingredient->canonical_name ?: $ingredient->name)))->filter()->values()->all();
        $required = self::REQUIRED_INGREDIENTS[$recipe->slug] ?? [];
        $missing = array_values(array_filter($required, fn (string $term): bool => ! collect($declared)->contains(fn (string $name): bool => str_contains($name, $term))));

        return ['ready' => $missing === [], 'missing' => $missing, 'declared' => $declared];
    }
}
