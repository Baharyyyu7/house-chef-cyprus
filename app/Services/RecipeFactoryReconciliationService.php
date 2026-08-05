<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\ContentFactoryReview;
use App\Models\Recipe;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;

/**
 * Produces explainable, non-destructive catalogue evidence before a recipe can
 * enter any generation or publication workflow. It never merges, archives,
 * verifies, approves, or publishes recipes.
 */
final class RecipeFactoryReconciliationService
{
    /** @var array<string, list<string>> */
    private const IDENTITY_TERMS = [
        'tavuk' => ['tavuk'],
        'balık' => ['balık'],
        'nohut' => ['nohut'],
        'mercimek' => ['mercimek'],
        'patates' => ['patates'],
        'makarna' => ['makarna'],
        'pilav' => ['pirinç', 'bulgur'],
        'yumurta' => ['yumurta'],
        'mantar' => ['mantar'],
    ];

    /**
     * @return array{total:int,audited:int,exact_duplicate_groups:int,exact_duplicates:int,near_duplicate_families:int,identity_mismatches:int,incomplete:int,unsafe_or_fact_check_required:int,verified_unique:int,legitimate_recipe_count:int,new_recipes_required:int,records:list<array<string,mixed>>,write:bool,auto_publish:bool,human_approval_required:bool}
     */
    public function reconcile(bool $write = false): array
    {
        /** @var Collection<int, Recipe> $recipes */
        $recipes = Recipe::query()
            ->with(['cuisine', 'category', 'recipeIngredients.ingredient', 'steps'])
            ->orderBy('id')
            ->get();

        $records = $recipes->map(fn (Recipe $recipe): array => $this->record($recipe))->values();
        $exactGroups = $records
            ->filter(fn (array $record): bool => $record['instruction_fingerprint'] !== null && $record['ingredient_fingerprint'] !== null)
            ->groupBy(fn (array $record): string => $record['ingredient_fingerprint'].'|'.$record['instruction_fingerprint'])
            ->filter(fn (Collection $group): bool => $group->count() > 1);
        $nearFamilies = $records
            ->groupBy('identity_family')
            ->filter(fn (Collection $group): bool => $group->count() > 1);

        $exactDuplicateIds = $exactGroups->flatten(1)->pluck('public_id')->flip();
        $nearDuplicateIds = $nearFamilies->flatten(1)
            ->filter(fn (array $record): bool => ! $exactDuplicateIds->has($record['public_id']))
            ->pluck('public_id')
            ->flip();

        $records = $records->map(function (array $record) use ($exactDuplicateIds, $nearDuplicateIds): array {
            $duplicateState = $exactDuplicateIds->has($record['public_id'])
                ? 'EXACT_DUPLICATE'
                : ($nearDuplicateIds->has($record['public_id']) ? 'POSSIBLE_NEAR_DUPLICATE' : 'DISTINCT');
            $classification = $record['incomplete']
                ? 'INCOMPLETE'
                : ($record['identity_mismatch'] ? 'IDENTITY_MISMATCH' : ($duplicateState === 'EXACT_DUPLICATE' ? 'DUPLICATE' : ($duplicateState === 'POSSIBLE_NEAR_DUPLICATE' ? 'NEAR_DUPLICATE' : 'KEEP')));
            $legitimate = $record['publication_state'] === 'published'
                && $record['verification_state'] === 'verified'
                && ! $record['incomplete']
                && ! $record['identity_mismatch']
                && $duplicateState === 'DISTINCT';

            return [...$record, 'duplicate_state' => $duplicateState, 'classification' => $classification, 'legitimate' => $legitimate];
        })->values();

        if ($write) {
            $records->each(fn (array $record): ContentFactoryReview => $this->persist($record));
        }

        $legitimate = $records->where('legitimate', true)->count();

        return [
            'total' => $records->count(),
            'audited' => $records->count(),
            'exact_duplicate_groups' => $exactGroups->count(),
            'exact_duplicates' => $records->where('duplicate_state', 'EXACT_DUPLICATE')->count(),
            'near_duplicate_families' => $nearFamilies->count(),
            'identity_mismatches' => $records->where('identity_mismatch', true)->count(),
            'incomplete' => $records->where('incomplete', true)->count(),
            'unsafe_or_fact_check_required' => $records->where('food_safety_state', 'REQUIRED')->count(),
            'verified_unique' => $records->where('verification_state', 'verified')->where('duplicate_state', 'DISTINCT')->count(),
            'legitimate_recipe_count' => $legitimate,
            'new_recipes_required' => max(0, 1000 - $legitimate),
            'records' => $records->all(),
            'write' => $write,
            'auto_publish' => false,
            'human_approval_required' => true,
        ];
    }

    /** @return array<string,mixed> */
    private function record(Recipe $recipe): array
    {
        $ingredients = $recipe->recipeIngredients
            ->map(fn ($item): string => $this->normalize((string) (($item->ingredient ? $item->ingredient->name : null) ?? '')).':'.(string) ($item->canonical_quantity ?? $item->quantity ?? '').':'.$this->normalize((string) ($item->canonical_unit ?? $item->unit ?? '')))
            ->filter()
            ->sort()
            ->values();
        $ingredientNames = $recipe->recipeIngredients
            ->map(fn ($item): string => $this->normalize((string) (($item->ingredient ? $item->ingredient->name : null) ?? '')))
            ->filter()
            ->values();
        $instructions = $recipe->steps
            ->sortBy('position')
            ->map(fn ($step): string => $this->normalize((string) $step->instruction))
            ->filter()
            ->values();
        $title = $this->normalize((string) $recipe->title);
        $identityTerms = collect(self::IDENTITY_TERMS)
            ->filter(fn (array $expected, string $term): bool => str_contains($title, $term))
            ->keys();
        $missingIdentityTerms = $identityTerms
            ->filter(fn (string $term): bool => collect(self::IDENTITY_TERMS[$term])->doesntContain(fn (string $ingredient): bool => $ingredientNames->contains(fn (string $name): bool => str_contains($name, $ingredient))))
            ->values()
            ->all();
        $method = $this->method($title, $instructions->implode(' '));
        $primaryIngredient = $ingredientNames->first() ?? 'unknown';
        $highRisk = collect(['tavuk', 'balık', 'deniz', 'kıyma', 'yumurta', 'pirinç'])->contains(fn (string $term): bool => str_contains($title.' '.$ingredientNames->implode(' '), $term));

        return [
            'database_id' => $recipe->id,
            'public_id' => $recipe->public_id,
            'title' => $recipe->title,
            'slug' => $recipe->slug,
            'publication_state' => $recipe->status,
            'verification_state' => $recipe->verification_status,
            'cuisine' => $recipe->cuisine?->name,
            'category' => $recipe->category?->name,
            'identity_fingerprint' => hash('sha256', implode('|', [$title, $this->normalize((string) $recipe->cuisine?->name), $this->normalize((string) $recipe->category?->name), $method, $primaryIngredient])),
            'ingredient_fingerprint' => $ingredients->isEmpty() ? null : hash('sha256', $ingredients->implode('|')),
            'instruction_fingerprint' => $instructions->isEmpty() ? null : hash('sha256', $instructions->implode('|')),
            'identity_family' => implode('|', [$this->normalize((string) $recipe->cuisine?->name), $this->normalize((string) $recipe->category?->name), $method, $primaryIngredient]),
            'identity_mismatch' => $missingIdentityTerms !== [],
            'missing_identity_terms' => $missingIdentityTerms,
            'incomplete' => $title === '' || $ingredients->isEmpty() || $instructions->isEmpty(),
            'food_safety_state' => $highRisk || $recipe->fact_check_required ? 'REQUIRED' : 'NOT_REQUIRED',
            'measurement_state' => $recipe->recipeIngredients->isNotEmpty() && $recipe->recipeIngredients->every(fn ($item): bool => $item->canonical_quantity !== null && $item->canonical_unit !== null) ? 'READY' : 'CONVERSION_MISSING',
            'media_state' => $recipe->image_status,
        ];
    }

    /** @param array<string,mixed> $record */
    private function persist(array $record): ContentFactoryReview
    {
        $review = ContentFactoryReview::query()->firstOrNew([
            'entity_type' => Recipe::class,
            'entity_public_id' => $record['public_id'],
            'stage' => 'recipe_factory_reconciliation',
        ]);
        if (! $review->exists) {
            $review->public_id = (string) Str::ulid();
        }
        $review->fill([
            'content_type' => 'recipe',
            'status' => 'pending',
            'fact_check_required' => $record['food_safety_state'] === 'REQUIRED',
            'findings' => [
                'identity_fingerprint' => $record['identity_fingerprint'],
                'ingredient_fingerprint' => $record['ingredient_fingerprint'],
                'instruction_fingerprint' => $record['instruction_fingerprint'],
                'identity_state' => $record['identity_mismatch'] ? 'IDENTITY_REVIEW' : 'AUDITED',
                'duplicate_state' => $record['duplicate_state'],
                'classification' => $record['classification'],
                'measurement_state' => $record['measurement_state'],
                'food_safety_state' => $record['food_safety_state'],
                'auto_publish' => false,
                'human_approval_required' => true,
            ],
        ]);
        $review->save();

        return $review;
    }

    /** @param Collection<int, string>|string $instructions */
    private function method(string $title, Collection|string $instructions): string
    {
        $instructionText = $instructions instanceof Collection ? $instructions->implode(' ') : $instructions;
        $content = $title.' '.$instructionText;

        return match (true) {
            str_contains($content, 'fırın'), str_contains($content, 'tepsi') => 'oven',
            str_contains($content, 'kızart') => 'frying',
            str_contains($content, 'ızgara') => 'grilling',
            str_contains($content, 'haşla') => 'boiling',
            str_contains($content, 'pişir') => 'stovetop',
            default => 'unknown',
        };
    }

    private function normalize(string $value): string
    {
        $ascii = Str::lower(Str::ascii($value));
        $normalized = preg_replace('/[^a-z0-9]+/', ' ', $ascii) ?? '';

        return trim(preg_replace('/\s+/', ' ', $normalized) ?? '');
    }
}
