<?php

declare(strict_types=1);

namespace App\Services\Personalization;

use App\Models\AiMealPlan;
use App\Models\AiShoppingList;
use App\Models\AuditLog;
use App\Models\Recipe;
use App\Models\User;
use Illuminate\Support\Collection;

/**
 * Deterministic plan/list operations. This service deliberately has no provider or model dependency.
 */
final class PersonalizedPlanningService
{
    public function __construct(private readonly PersonalizationService $personalization) {}

    /** @param array<string, mixed> $constraints */
    public function mealPlan(User $user, int $days, array $constraints = []): AiMealPlan
    {
        $days = min(30, max(7, $days));
        $profile = $this->personalization->profile($user);
        $candidates = $this->personalization->recommendations($user, 100)
            ->map(fn ($recommendation): Recipe => $recommendation->recipe)
            ->filter(fn (Recipe $recipe): bool => $this->matchesPlanConstraints($recipe, $constraints))
            ->values();

        $seen = [];
        $plan = collect(range(1, $days))->map(function (int $day) use ($candidates, &$seen): array {
            $recipe = $candidates->first(fn (Recipe $candidate): bool => ($seen[$candidate->id] ?? 0) < 1)
                ?? $candidates->first(fn (Recipe $candidate): bool => ($seen[$candidate->id] ?? 0) < 2);
            if ($recipe !== null) {
                $seen[$recipe->id] = ($seen[$recipe->id] ?? 0) + 1;
            }

            return ['day' => $day, 'meal_type' => 'main', 'recipe' => $recipe === null ? null : [
                'public_id' => $recipe->public_id,
                'title' => $recipe->title,
                'slug' => $recipe->slug,
                'servings' => $recipe->servings,
                'minutes' => $recipe->totalMinutes(),
            ]];
        })->all();

        $safe = collect($constraints)->only(['budget', 'dietary_tag', 'exclude_allergens', 'max_minutes', 'household_size', 'pantry_priority', 'meal_prep_days', 'leftovers'])->all();
        $safe['household_size'] ??= $profile->household_size;
        $result = AiMealPlan::create(['user_id' => $user->id, 'days' => $days, 'constraints' => $safe, 'plan' => $plan, 'status' => 'draft']);
        $this->audit($user, 'personalization.meal_plan_created', $result, ['plan_public_id' => $result->public_id, 'days' => $days, 'coverage' => $candidates->count()]);

        return $result;
    }

    public function shopping(User $user, AiMealPlan $plan): AiShoppingList
    {
        abort_unless($plan->user_id === $user->id, 403);
        $recipeIds = collect($plan->plan)->pluck('recipe.public_id')->filter()->all();
        $pantry = $user->pantryItems()->where('is_available', true)->where(fn ($query) => $query->whereNull('expires_at')->orWhereDate('expires_at', '>=', today()))->get()->keyBy('ingredient_id');
        $items = Recipe::query()->whereIn('public_id', $recipeIds)->with('recipeIngredients.ingredient')->get()
            ->flatMap(fn (Recipe $recipe) => $recipe->recipeIngredients)
            ->groupBy('ingredient_id')
            ->map(function (Collection $lines) use ($pantry): array {
                $line = $lines->first();
                $required = $lines->sum(fn ($item): float => (float) ($item->canonical_quantity ?? $item->quantity ?? 0));
                $pantryItem = $pantry->get($line->ingredient_id);
                $available = $pantryItem === null ? 0.0 : (float) ($pantryItem->canonical_quantity ?? 0);
                $remaining = max(0, $required - $available);

                return [
                    'ingredient_public_id' => $line->ingredient->public_id,
                    'ingredient' => $line->ingredient->name,
                    'quantity' => $remaining > 0 ? round($remaining, 3) : null,
                    'unit' => $line->canonical_unit ?? $line->unit,
                    'quantity_known' => $remaining > 0,
                    'optional' => $lines->every(fn ($item): bool => (bool) $item->is_optional),
                    'purchased_at' => null,
                    'sources' => [],
                ];
            })->values()->all();
        $list = AiShoppingList::create(['user_id' => $user->id, 'ai_meal_plan_id' => $plan->id, 'items' => $items, 'status' => 'draft']);
        $this->audit($user, 'personalization.shopping_list_created', $list, ['shopping_list_public_id' => $list->public_id, 'item_count' => count($items)]);

        return $list;
    }

    public function togglePurchased(User $user, AiShoppingList $list, int $index): AiShoppingList
    {
        abort_unless($list->user_id === $user->id, 403);
        $items = $list->items;
        abort_unless(array_key_exists($index, $items), 404);
        $item = $items[$index];
        $item['purchased_at'] = ($item['purchased_at'] ?? null) === null ? now()->toIso8601String() : null;
        $items[$index] = $item;
        $list->update(['items' => $items]);
        $this->audit($user, 'personalization.shopping_item_toggled', $list, ['shopping_list_public_id' => $list->public_id]);

        return $list->fresh();
    }

    /** @param array<string, mixed> $constraints */
    private function matchesPlanConstraints(Recipe $recipe, array $constraints): bool
    {
        $max = $constraints['max_minutes'] ?? null;

        return ! is_numeric($max) || $recipe->totalMinutes() <= (int) $max;
    }

    /** @param array<string, mixed> $metadata */
    private function audit(User $user, string $action, object $subject, array $metadata): void
    {
        AuditLog::create(['user_id' => $user->id, 'action' => $action, 'auditable_type' => $subject::class, 'auditable_id' => $subject->id, 'metadata' => $metadata]);
    }
}
