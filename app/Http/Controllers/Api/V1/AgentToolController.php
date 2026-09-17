<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\V1;

use App\Contracts\Search\RecipeSearchInterface;
use App\Http\Controllers\Controller;
use App\Models\Ingredient;
use App\Models\Recipe;
use App\Services\IngredientMatchingService;
use App\Services\Measurements\RecipeIngredientDisplayService;
use App\Services\NutritionService;
use App\Services\Seo\LocaleUrlService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

final class AgentToolController extends Controller
{
    /**
     * Return capability registry and tool schemas for AI Agents / WebMCP.
     */
    public function tools(): JsonResponse
    {
        return response()->json([
            'protocol' => 'WebMCP/1.0',
            'website' => 'HouseChef Cyprus',
            'base_url' => url('/'),
            'tools' => [
                [
                    'name' => 'search_recipes',
                    'description' => 'Search HouseChef Cyprus recipes with multi-constraint filters (query, cuisine, max cooking time, difficulty, dietary preferences).',
                    'parameters' => [
                        'type' => 'object',
                        'properties' => [
                            'query' => ['type' => 'string', 'description' => 'Keyword to search in recipe titles and descriptions (e.g. "hellim", "tavuk").'],
                            'cuisine' => ['type' => 'string', 'description' => 'Cuisine slug (e.g. "kibris-mutfagi", "turk-mutfagi", "akdeniz-mutfagi").'],
                            'category' => ['type' => 'string', 'description' => 'Category slug (e.g. "ana-yemek", "tatli", "corba", "salata-meze").'],
                            'max_minutes' => ['type' => 'integer', 'description' => 'Maximum total time (prep + cook) in minutes (e.g. 30, 45, 60).'],
                            'difficulty' => ['type' => 'string', 'enum' => ['kolay', 'orta', 'zor'], 'description' => 'Difficulty level.'],
                            'dietary_tag' => ['type' => 'string', 'description' => 'Dietary preference code (e.g. "vejetaryen", "vegan", "glutensiz").'],
                            'min_protein' => ['type' => 'number', 'description' => 'Minimum protein in grams per serving.'],
                            'max_calories' => ['type' => 'number', 'description' => 'Maximum calories in kcal per serving.'],
                            'locale' => ['type' => 'string', 'enum' => ['tr', 'en'], 'description' => 'Content language (default "tr").'],
                        ],
                    ],
                    'risk_level' => 'read_only',
                ],
                [
                    'name' => 'find_recipes_by_ingredients',
                    'description' => 'Match and rank recipes based on available pantry ingredients. Solves "What can I cook with ingredient X, Y, Z?".',
                    'parameters' => [
                        'type' => 'object',
                        'required' => ['ingredients'],
                        'properties' => [
                            'ingredients' => ['type' => 'array', 'items' => ['type' => 'string'], 'description' => 'List of ingredient names or public IDs (e.g. ["hellim", "domates", "patates"]).'],
                            'excluded' => ['type' => 'array', 'items' => ['type' => 'string'], 'description' => 'List of ingredients to exclude.'],
                            'max_missing' => ['type' => 'integer', 'description' => 'Maximum number of missing required ingredients allowed (default 2).'],
                            'max_minutes' => ['type' => 'integer', 'description' => 'Maximum total time in minutes.'],
                            'exact_only' => ['type' => 'boolean', 'description' => 'If true, returns only recipes where 100% of required ingredients are available.'],
                        ],
                    ],
                    'risk_level' => 'read_only',
                ],
                [
                    'name' => 'get_recipe',
                    'description' => 'Retrieve complete structured details for a recipe, including ingredients with scaled quantities, ordered preparation steps, timings, equipment, nutrition facts, and allergen warnings.',
                    'parameters' => [
                        'type' => 'object',
                        'required' => ['slug'],
                        'properties' => [
                            'slug' => ['type' => 'string', 'description' => 'Recipe slug or public ID.'],
                            'servings' => ['type' => 'integer', 'description' => 'Desired portion count to calculate scaled ingredient quantities.'],
                            'locale' => ['type' => 'string', 'enum' => ['tr', 'en'], 'description' => 'Content language.'],
                        ],
                    ],
                    'risk_level' => 'read_only',
                ],
                [
                    'name' => 'adjust_servings',
                    'description' => 'Calculate scaled ingredient amounts and nutrition values for a recipe according to target serving count.',
                    'parameters' => [
                        'type' => 'object',
                        'required' => ['slug', 'servings'],
                        'properties' => [
                            'slug' => ['type' => 'string', 'description' => 'Recipe slug or public ID.'],
                            'servings' => ['type' => 'integer', 'minimum' => 1, 'maximum' => 99, 'description' => 'Target number of servings.'],
                        ],
                    ],
                    'risk_level' => 'read_only',
                ],
                [
                    'name' => 'get_cooking_step',
                    'description' => 'Get a specific step instruction, timing, duration, equipment, and chef tips for interactive cooking.',
                    'parameters' => [
                        'type' => 'object',
                        'required' => ['slug', 'step'],
                        'properties' => [
                            'slug' => ['type' => 'string', 'description' => 'Recipe slug or public ID.'],
                            'step' => ['type' => 'integer', 'minimum' => 1, 'description' => 'Step number.'],
                        ],
                    ],
                    'risk_level' => 'read_only',
                ],
                [
                    'name' => 'switch_language',
                    'description' => 'Get the localized canonical destination URL to view content in another language (e.g. switch to English or Turkish).',
                    'parameters' => [
                        'type' => 'object',
                        'required' => ['locale'],
                        'properties' => [
                            'locale' => ['type' => 'string', 'enum' => ['tr', 'en'], 'description' => 'Target language code.'],
                            'path' => ['type' => 'string', 'description' => 'Path to translate (e.g. "/tarifler/kibris-usulu-seftali-kebab"). Defaults to current page.'],
                        ],
                    ],
                    'risk_level' => 'read_only',
                ],
                [
                    'name' => 'save_recipe',
                    'description' => 'Save or favorite a recipe to the user profile. Requires authenticated session with explicit human confirmation.',
                    'parameters' => [
                        'type' => 'object',
                        'required' => ['slug'],
                        'properties' => [
                            'slug' => ['type' => 'string', 'description' => 'Recipe slug to favorite.'],
                        ],
                    ],
                    'risk_level' => 'write',
                    'requires_human_confirmation' => true,
                ],
            ],
        ]);
    }

    /**
     * Search recipes tool.
     */
    public function search(Request $request, RecipeSearchInterface $search): JsonResponse
    {
        $validated = $request->validate([
            'query' => ['nullable', 'string', 'max:100'],
            'q' => ['nullable', 'string', 'max:100'],
            'cuisine' => ['nullable', 'string', 'max:50'],
            'category' => ['nullable', 'string', 'max:50'],
            'difficulty' => ['nullable', 'string', 'max:20'],
            'dietary_tag' => ['nullable', 'string', 'max:50'],
            'max_minutes' => ['nullable', 'integer', 'min:1', 'max:1440'],
            'min_protein' => ['nullable', 'numeric', 'min:0'],
            'max_calories' => ['nullable', 'numeric', 'min:0'],
            'page' => ['nullable', 'integer', 'min:1'],
            'per_page' => ['nullable', 'integer', 'min:1', 'max:50'],
        ]);

        $filters = [
            'q' => $validated['query'] ?? $validated['q'] ?? null,
            'cuisine' => $validated['cuisine'] ?? null,
            'category' => $validated['category'] ?? null,
            'difficulty' => $validated['difficulty'] ?? null,
            'dietary_tag' => $validated['dietary_tag'] ?? null,
            'max_minutes' => $validated['max_minutes'] ?? null,
            'min_protein' => $validated['min_protein'] ?? null,
            'max_calories' => $validated['max_calories'] ?? null,
            'page' => $validated['page'] ?? 1,
            'per_page' => min(50, (int) ($validated['per_page'] ?? 15)),
        ];

        $paginator = $search->search(array_filter($filters, fn ($v) => $v !== null));

        $results = collect($paginator->items())->map(function (Recipe $recipe): array {
            return [
                'title' => $recipe->translated('title'),
                'slug' => $recipe->slug,
                'url' => route('recipes.show', $recipe->slug),
                'excerpt' => (string) ($recipe->translated('excerpt') ?: $recipe->excerpt),
                'cuisine' => $recipe->cuisine?->name,
                'category' => $recipe->category?->name,
                'prep_minutes' => $recipe->prep_minutes,
                'cook_minutes' => $recipe->cook_minutes,
                'total_minutes' => $recipe->totalMinutes(),
                'servings' => $recipe->servings,
                'difficulty' => $recipe->difficulty,
                'difficulty_label' => $recipe->zorlukEtiketi(),
            ];
        });

        return response()->json([
            'count' => $results->count(),
            'total' => $paginator->total(),
            'current_page' => $paginator->currentPage(),
            'last_page' => $paginator->lastPage(),
            'results' => $results,
        ]);
    }

    /**
     * Find recipes by ingredients tool.
     */
    public function findByIngredients(Request $request, IngredientMatchingService $matching): JsonResponse
    {
        $validated = $request->validate([
            'ingredients' => ['required', 'array', 'min:1', 'max:50'],
            'ingredients.*' => ['string', 'max:100'],
            'excluded' => ['nullable', 'array', 'max:50'],
            'excluded.*' => ['string', 'max:100'],
            'max_missing' => ['nullable', 'integer', 'min:0', 'max:10'],
            'max_minutes' => ['nullable', 'integer', 'min:1', 'max:1440'],
            'exact_only' => ['nullable', 'boolean'],
            'include_substitutions' => ['nullable', 'boolean'],
        ]);

        $resolvedIngredients = $this->resolveIngredientPublicIds($validated['ingredients']);
        $resolvedExcluded = ! empty($validated['excluded']) ? $this->resolveIngredientPublicIds($validated['excluded']) : [];

        if (empty($resolvedIngredients)) {
            return response()->json([
                'count' => 0,
                'message' => 'None of the provided ingredients matched our verified ingredients dictionary.',
                'results' => [],
            ]);
        }

        $matches = $matching->findRecipes(
            $resolvedIngredients,
            $resolvedExcluded,
            (bool) ($validated['exact_only'] ?? false),
            (bool) ($validated['include_substitutions'] ?? true),
            (int) ($validated['max_missing'] ?? 2)
        );

        if (! empty($validated['max_minutes'])) {
            $maxM = (int) $validated['max_minutes'];
            $matches = $matches->filter(fn (array $m) => $m['recipe']->totalMinutes() <= $maxM);
        }

        $results = $matches->take(20)->map(function (array $match): array {
            /** @var Recipe $recipe */
            $recipe = $match['recipe'];

            return [
                'title' => $recipe->translated('title'),
                'slug' => $recipe->slug,
                'url' => route('recipes.show', $recipe->slug),
                'match_percentage' => $match['effective_match_percentage'] ?? 0,
                'status' => $match['status'] ?? 'Kısmi uyum',
                'matched_count' => $match['matched_required_count'] ?? 0,
                'missing_count' => $match['missing_required_count'] ?? 0,
                'missing_ingredients' => collect($match['missing_required_ingredients'] ?? [])->pluck('name')->all(),
                'available_substitutions' => collect($match['available_substitutions'] ?? [])->map(fn ($s) => [
                    'original' => $s['ingredient'] ?? '',
                    'substitute' => $s['substitute'] ?? '',
                ])->all(),
                'total_minutes' => $recipe->totalMinutes(),
                'cuisine' => $recipe->cuisine?->name,
                'category' => $recipe->category?->name,
            ];
        })->values();

        return response()->json([
            'count' => $results->count(),
            'results' => $results,
        ]);
    }

    /**
     * Get complete recipe details tool.
     */
    public function recipe(Request $request, RecipeIngredientDisplayService $ingredientDisplay, NutritionService $nutritionService): JsonResponse
    {
        $validated = $request->validate([
            'slug' => ['required', 'string'],
            'servings' => ['nullable', 'integer', 'min:1', 'max:99'],
        ]);

        $recipe = Recipe::query()
            ->where('slug', $validated['slug'])
            ->orWhere('public_id', $validated['slug'])
            ->with(['cuisine', 'category', 'steps', 'recipeIngredients.ingredient', 'recipeIngredients.group'])
            ->firstOrFail();

        abort_unless($recipe->status === 'published' && $recipe->verification_status === 'verified', 404);

        $servings = (int) ($validated['servings'] ?? $recipe->servings);
        $ingredients = $ingredientDisplay->forRecipe($recipe->recipeIngredients, $recipe->servings, $servings);
        $nutrition = $nutritionService->forRecipe($recipe, $servings);

        return response()->json([
            'title' => $recipe->translated('title'),
            'slug' => $recipe->slug,
            'url' => route('recipes.show', $recipe->slug),
            'description' => (string) ($recipe->translated('description') ?: $recipe->description),
            'base_servings' => $recipe->servings,
            'active_servings' => $servings,
            'prep_minutes' => $recipe->prep_minutes,
            'cook_minutes' => $recipe->cook_minutes,
            'total_minutes' => $recipe->totalMinutes(),
            'difficulty' => $recipe->difficulty,
            'difficulty_label' => $recipe->zorlukEtiketi(),
            'cuisine' => $recipe->cuisine?->name,
            'category' => $recipe->category?->name,
            'ingredients' => collect($ingredients)->map(fn (array $item) => [
                'group' => $item['group'],
                'name' => $item['line']->ingredient?->name,
                'formatted' => $item['formatted'],
                'quantity' => $item['display_quantity'],
                'unit' => $item['canonical_unit'],
                'is_optional' => (bool) $item['line']->is_optional,
            ]),
            'steps' => $recipe->steps->sortBy('position')->values()->map(fn ($s) => [
                'position' => $s->position,
                'title' => $s->title,
                'instruction' => $s->instruction,
                'duration_seconds' => $s->duration_seconds,
                'equipment' => $s->equipment,
                'chef_tip' => $s->chef_tip,
                'safety_note' => $s->safety_note,
            ]),
            'nutrition_per_serving' => [
                'calories' => $nutrition['nutrients']['energy_kcal']['per_serving'] ?? null,
                'protein_g' => $nutrition['nutrients']['protein']['per_serving'] ?? null,
                'carbs_g' => $nutrition['nutrients']['carbohydrate']['per_serving'] ?? null,
                'fat_g' => $nutrition['nutrients']['fat']['per_serving'] ?? null,
            ],
        ]);
    }

    /**
     * Adjust recipe servings tool.
     */
    public function adjustServings(Request $request, RecipeIngredientDisplayService $ingredientDisplay): JsonResponse
    {
        $validated = $request->validate([
            'slug' => ['required', 'string'],
            'servings' => ['required', 'integer', 'min:1', 'max:99'],
        ]);

        $recipe = Recipe::query()
            ->where('slug', $validated['slug'])
            ->orWhere('public_id', $validated['slug'])
            ->with(['recipeIngredients.ingredient', 'recipeIngredients.group'])
            ->firstOrFail();

        $targetServings = (int) $validated['servings'];
        $scalingFactor = round($targetServings / max(1, $recipe->servings), 2);
        $ingredients = $ingredientDisplay->forRecipe($recipe->recipeIngredients, $recipe->servings, $targetServings);

        return response()->json([
            'recipe' => $recipe->translated('title'),
            'slug' => $recipe->slug,
            'url' => route('recipes.show', $recipe->slug),
            'base_servings' => $recipe->servings,
            'target_servings' => $targetServings,
            'scaling_factor' => $scalingFactor,
            'ingredients' => collect($ingredients)->map(fn (array $item) => [
                'group' => $item['group'],
                'name' => $item['line']->ingredient?->name,
                'formatted' => $item['formatted'],
                'quantity' => $item['display_quantity'],
                'unit' => $item['canonical_unit'],
            ]),
        ]);
    }

    /**
     * Get cooking step tool.
     */
    public function cookingStep(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'slug' => ['required', 'string'],
            'step' => ['nullable', 'integer', 'min:1'],
        ]);

        $recipe = Recipe::query()
            ->where('slug', $validated['slug'])
            ->orWhere('public_id', $validated['slug'])
            ->with(['steps'])
            ->firstOrFail();

        $steps = $recipe->steps->sortBy('position')->values();
        $totalSteps = $steps->count();
        $targetStepNum = (int) ($validated['step'] ?? 1);

        $step = $steps->firstWhere('position', $targetStepNum) ?? $steps->first();

        if (! $step) {
            return response()->json(['error' => 'No steps recorded for this recipe.'], 404);
        }

        return response()->json([
            'recipe' => $recipe->translated('title'),
            'slug' => $recipe->slug,
            'current_step' => $step->position,
            'total_steps' => $totalSteps,
            'has_next' => $step->position < $totalSteps,
            'next_step' => $step->position < $totalSteps ? $step->position + 1 : null,
            'title' => $step->title,
            'instruction' => $step->instruction,
            'duration_seconds' => $step->duration_seconds,
            'timer_label' => $step->duration_seconds ? sprintf('%d dk', intdiv($step->duration_seconds, 60)) : null,
            'equipment' => $step->equipment,
            'chef_tip' => $step->chef_tip,
            'safety_note' => $step->safety_note,
        ]);
    }

    /**
     * Switch language tool.
     */
    public function switchLanguage(Request $request, LocaleUrlService $localeUrlService): JsonResponse
    {
        $validated = $request->validate([
            'locale' => ['required', 'string', 'in:tr,en'],
            'path' => ['nullable', 'string', 'max:2048'],
        ]);

        $targetLocale = $validated['locale'];
        $path = $validated['path'] ?? '/';

        $localizedUrl = $localeUrlService->urlFor($targetLocale, $path);

        return response()->json([
            'locale' => $targetLocale,
            'target_url' => $localizedUrl,
            'is_supported' => true,
        ]);
    }

    /**
     * Save recipe tool (with strict human confirmation / auth safeguard).
     */
    public function saveRecipe(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'slug' => ['required', 'string'],
        ]);

        $recipe = Recipe::query()
            ->where('slug', $validated['slug'])
            ->orWhere('public_id', $validated['slug'])
            ->firstOrFail();

        $user = $request->user();

        if (! $user) {
            return response()->json([
                'success' => false,
                'authenticated' => false,
                'requires_human_confirmation' => true,
                'message' => 'Authentication required: User must be signed in to save recipes to their favorites.',
                'login_url' => route('login'),
                'recipe' => $recipe->title,
            ], 401);
        }

        $user->favoriteRecipes()->toggle($recipe->id);
        $isFavorited = $user->favoriteRecipes()->where('recipes.id', $recipe->id)->exists();

        return response()->json([
            'success' => true,
            'authenticated' => true,
            'is_favorited' => $isFavorited,
            'message' => $isFavorited ? 'Recipe saved to favorites.' : 'Recipe removed from favorites.',
            'recipe' => $recipe->title,
        ]);
    }

    /**
     * Resolve ingredient strings (names or public IDs) to array of public IDs.
     *
     * @param  list<string>  $inputs
     * @return list<string>
     */
    private function resolveIngredientPublicIds(array $inputs): array
    {
        $cleanInputs = array_values(array_unique(array_filter(array_map('trim', $inputs))));
        if (empty($cleanInputs)) {
            return [];
        }

        // Direct public IDs
        $directMatches = Ingredient::query()
            ->whereIn('public_id', $cleanInputs)
            ->pluck('public_id')
            ->all();

        // Name matches
        $nameMatches = Ingredient::query()
            ->where(function ($query) use ($cleanInputs) {
                foreach ($cleanInputs as $input) {
                    $query->orWhere('name', 'like', '%'.$input.'%');
                }
            })
            ->pluck('public_id')
            ->all();

        return array_values(array_unique(array_merge($directMatches, $nameMatches)));
    }
}
