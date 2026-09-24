<?php

declare(strict_types=1);

namespace App\Services\AI;

use App\Models\AiConversation;
use App\Models\AiInteraction;
use App\Models\AiMealPlan;
use App\Models\AiShoppingList;
use App\Models\AuditLog;
use App\Models\Ingredient;
use App\Models\Recipe;
use App\Models\User;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Arr;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Str;

/**
 * Database-grounded AI kitchen orchestration. It never changes publishing or verification state.
 */
final class AiKitchenService
{
    private const PROMPT_VERSION = 'ai-kitchen-v1';

    public function __construct(
        private readonly AiKitchenProviderRegistry $providers,
        private readonly AiSafetyService $safety,
    ) {}

    /** @param array<string, mixed> $input @return array<string, mixed> */
    public function assistant(User $user, string $question, array $input = []): array
    {
        return $this->execute($user, 'assistant', $question, $input + ['recipes' => $this->recipeCards($this->publicRecipes()->limit(3)->get())]);
    }

    /** @return array<string, mixed> */
    public function pantry(User $user): array
    {
        $pantry = $user->pantryItems()->where('is_available', true)->with('ingredient')->get()
            ->filter(fn ($item): bool => $item->expires_at === null || Carbon::parse($item->expires_at)->isFuture() || Carbon::parse($item->expires_at)->isToday());
        $ids = $pantry->pluck('ingredient_id')->all();
        $recipes = $ids === [] ? collect() : $this->publicRecipes()->with('ingredients')
            ->whereHas('ingredients', fn ($query) => $query->whereIn('ingredients.id', $ids))
            ->limit(12)->get()->sortByDesc(fn (Recipe $recipe): int => $recipe->ingredients->pluck('id')->intersect($ids)->count())->values();

        return $this->execute($user, 'pantry', 'Dolabıma göre tarif öner.', [
            'pantry' => $pantry->map(fn ($item): array => ['public_id' => $item->ingredient->public_id, 'name' => $item->ingredient->name])->values()->all(),
            'recipes' => $this->recipeCards($recipes),
            'missing_policy' => 'Eksik malzemeler tarif kartında açıkça belirtilir; AI bunları varmış gibi varsaymaz.',
        ]);
    }

    /** @param array<string, mixed> $constraints @return array<string, mixed> */
    public function mealPlan(User $user, int $days, array $constraints = []): array
    {
        $days = min(30, max(7, $days));
        $recipes = $this->publicRecipes()->with('ingredients')->orderBy('published_at')->limit(max($days, 14))->get();
        $plan = collect(range(1, $days))->map(function (int $day) use ($recipes): array {
            $recipe = $recipes->get(($day - 1) % max(1, $recipes->count()));

            return ['day' => $day, 'recipe' => $recipe ? $this->recipeCard($recipe) : null];
        })->all();
        $result = $this->execute($user, 'meal_plan', "{$days} günlük menü planı", ['days' => $days, 'constraints' => $this->safeConstraints($constraints), 'plan' => $plan]);
        $mealPlan = AiMealPlan::create(['user_id' => $user->id, 'ai_interaction_id' => $result['audit_id'] ? AiInteraction::query()->where('public_id', $result['audit_id'])->value('id') : null, 'days' => $days, 'constraints' => $this->safeConstraints($constraints), 'plan' => $plan, 'status' => 'draft']);
        $result['meal_plan_id'] = $mealPlan->public_id;
        $result['plan'] = $plan;

        return $result;
    }

    /** @return array<string, mixed> */
    public function shopping(User $user, AiMealPlan $plan): array
    {
        abort_unless($plan->user_id === $user->id, 403);
        $recipePublicIds = collect($plan->plan)->pluck('recipe.public_id')->filter()->all();
        $items = Recipe::query()->whereIn('public_id', $recipePublicIds)->with('recipeIngredients.ingredient')->get()
            ->flatMap(fn (Recipe $recipe) => $recipe->recipeIngredients)
            ->groupBy('ingredient_id')
            ->map(fn ($lines): array => ['ingredient' => $lines->first()->ingredient->name, 'ingredient_public_id' => $lines->first()->ingredient->public_id, 'count' => $lines->count(), 'category' => 'Malzemeler'])
            ->values()->all();
        $result = $this->execute($user, 'shopping', 'Menü planı için alışveriş listesi', ['meal_plan_id' => $plan->public_id, 'items' => $items]);
        $list = AiShoppingList::create(['user_id' => $user->id, 'ai_meal_plan_id' => $plan->id, 'ai_interaction_id' => AiInteraction::query()->where('public_id', $result['audit_id'])->value('id'), 'items' => $items, 'status' => 'draft']);
        $result['shopping_list_id'] = $list->public_id;
        $result['items'] = $items;

        return $result;
    }

    /** @param list<string> $leftovers @return array<string, mixed> */
    public function leftovers(User $user, array $leftovers): array
    {
        $names = collect($leftovers)->filter('is_string')->map(fn (string $value): string => Str::squish($value))->filter()->take(20)->values();
        $ingredients = Ingredient::query()->where('is_active', true)->whereIn('name', $names)->get();
        $recipes = $ingredients->isEmpty() ? collect() : $this->publicRecipes()->with('ingredients')->whereHas('ingredients', fn ($q) => $q->whereIn('ingredients.id', $ingredients->pluck('id')))->limit(12)->get();

        return $this->execute($user, 'leftover', 'Artan malzemeleri değerlendir.', ['leftovers' => $names->all(), 'recipes' => $this->recipeCards($recipes)]);
    }

    /** @return array<string, mixed> */
    public function portion(User $user, Recipe $recipe, int $servings): array
    {
        $servings = min(30, max(1, $servings));
        $base = max(1, (int) $recipe->servings);
        $ratio = round($servings / $base, 4);
        $lines = $recipe->recipeIngredients()->with('ingredient')->get()->map(fn ($line): array => ['ingredient' => $line->ingredient->name, 'quantity' => $line->quantity === null ? null : round((float) $line->quantity * $ratio, 3), 'unit' => $line->unit])->all();

        return $this->execute($user, 'portion', 'Porsiyon dönüşümü', ['recipe' => $this->recipeCard($recipe), 'from_servings' => $base, 'to_servings' => $servings, 'ratio' => $ratio, 'ingredients' => $lines]);
    }

    /** @return array<string, mixed> */
    public function nutrition(User $user, Recipe $recipe): array
    {
        $snapshot = $recipe->nutritionSnapshots()->whereNull('invalidated_at')->latest('calculated_at')->first();

        return $this->execute($user, 'nutrition', 'Tarifin beslenme özetini açıkla.', ['recipe' => $this->recipeCard($recipe), 'nutrition' => $snapshot?->payload, 'confidence' => $snapshot?->confidence_score, 'completeness' => $snapshot?->completeness_percentage]);
    }

    /** @return array<string, mixed> */
    public function explain(User $user, Recipe $recipe): array
    {
        return $this->execute($user, 'recipe_explainer', 'Tarif adımlarını açıkla.', ['recipe' => $this->recipeCard($recipe), 'steps' => $recipe->steps()->get(['position', 'instruction'])->map(fn ($step): array => ['position' => $step->position, 'instruction' => $step->instruction])->all()]);
    }

    /** @return array<string, mixed> */
    public function stats(): array
    {
        $base = AiInteraction::query();

        return ['requests' => (clone $base)->count(), 'failures' => (clone $base)->where('status', 'failed')->count(), 'cache_hits' => (clone $base)->where('cache_hit', true)->count(), 'fallbacks' => (clone $base)->where('fallback_used', true)->count(), 'cost_cents' => (clone $base)->sum('cost_cents'), 'average_latency_ms' => (int) round((float) (clone $base)->avg('latency_ms')), 'safety_events' => (clone $base)->whereNotNull('safety_events')->count(), 'providers' => $this->providers->health()];
    }

    /** @param array<string, mixed> $payload @return array<string, mixed> */
    private function execute(User $user, string $purpose, string $question, array $payload): array
    {
        abort_unless((bool) config('ai_kitchen.enabled'), 503, 'AI mutfak asistanı geçici olarak kullanılamıyor.');
        $key = 'ai-kitchen:'.$user->public_id.':'.hash('sha256', $purpose.'|'.json_encode($this->cacheable($payload), JSON_THROW_ON_ERROR));
        $hit = Cache::has($key);
        if (RateLimiter::tooManyAttempts('ai-kitchen:'.$user->id, (int) config('ai_kitchen.rate_limit_per_hour'))) {
            abort(429, 'AI mutfak asistanı için saatlik istek sınırına ulaştınız.');
        }
        RateLimiter::hit('ai-kitchen:'.$user->id, 3600);
        $started = hrtime(true);
        $safety = $this->safety->events($question, $purpose);
        $cached = Cache::get($key);
        $fallback = false;
        $providerName = 'cache';
        $answer = is_array($cached) ? $cached : null;
        if ($answer === null) {
            foreach ($this->providers->chain() as $provider) {
                $candidate = $provider->respond(['purpose' => $purpose, 'question' => $question, 'payload' => $payload, 'prompt_version' => self::PROMPT_VERSION]);
                if ($candidate['status'] === 'completed') {
                    $answer = $candidate;
                    $providerName = $provider->name();
                    break;
                }
                $fallback = true;
            }
            $answer ??= ['status' => 'failed', 'text' => 'Şu anda güvenli bir AI yanıtı üretilemedi. Tarif kataloğunu doğrudan inceleyebilirsiniz.', 'input_tokens' => 0, 'output_tokens' => 0, 'cost_cents' => 0, 'safe_error_code' => 'AI_NO_PROVIDER_AVAILABLE'];
            Cache::put($key, $answer, now()->addSeconds((int) config('ai_kitchen.cache_ttl_seconds')));
        }
        $conversation = $this->conversation($user, $purpose, $question);
        $interaction = AiInteraction::create(['user_id' => $user->id, 'ai_conversation_id' => $conversation->id, 'purpose' => $purpose, 'provider' => $providerName, 'model' => is_string($answer['model'] ?? null) ? $answer['model'] : ($providerName === 'mock' ? (string) config('ai_kitchen.providers.mock.model') : null), 'prompt_version' => self::PROMPT_VERSION, 'request_fingerprint' => hash('sha256', $key), 'safe_request_summary' => Str::limit($question, 280), 'safe_response_summary' => Str::limit((string) $answer['text'], 500), 'response_payload' => $this->cacheable($payload), 'input_tokens' => (int) $answer['input_tokens'], 'output_tokens' => (int) $answer['output_tokens'], 'cost_cents' => (int) $answer['cost_cents'], 'latency_ms' => (int) ((hrtime(true) - $started) / 1_000_000), 'cache_hit' => $hit, 'fallback_used' => $fallback, 'status' => $answer['status'], 'safe_error_code' => $answer['safe_error_code'], 'safety_events' => $safety === [] ? null : $safety, 'completed_at' => now()]);
        AuditLog::create(['user_id' => $user->id, 'action' => 'ai_kitchen.'.$purpose, 'auditable_type' => AiInteraction::class, 'auditable_id' => $interaction->id, 'metadata' => ['public_id' => $interaction->public_id, 'cache_hit' => $hit, 'provider' => $providerName]]);

        return ['audit_id' => $interaction->public_id, 'answer' => $answer['text'], 'provider' => $providerName, 'cache_hit' => $hit, 'safety_events' => $safety, 'data' => $payload, 'status' => $answer['status']];
    }

    private function conversation(User $user, string $purpose, string $question): AiConversation
    {
        $conversation = AiConversation::firstOrCreate(['user_id' => $user->id, 'purpose' => $purpose], ['memory' => []]);
        $memory = Arr::wrap($conversation->memory);
        $memory[] = Str::limit($question, 280);
        $conversation->update(['memory' => array_slice($memory, -10), 'last_interacted_at' => now()]);

        return $conversation;
    }

    /** @return Builder<Recipe> */
    private function publicRecipes(): Builder
    {
        return Recipe::query()->where('status', 'published')->where('verification_status', 'verified');
    }

    /** @param iterable<Recipe> $recipes @return list<array<string, mixed>> */
    private function recipeCards(iterable $recipes): array
    {
        return collect($recipes)->map(fn (Recipe $recipe): array => $this->recipeCard($recipe))->values()->all();
    }

    /** @return array<string, mixed> */
    private function recipeCard(Recipe $recipe): array
    {
        return ['public_id' => $recipe->public_id, 'title' => $recipe->title, 'slug' => $recipe->slug, 'minutes' => $recipe->totalMinutes(), 'servings' => $recipe->servings];
    }

    /** @param array<string, mixed> $constraints @return array<string, scalar|array|null> */
    private function safeConstraints(array $constraints): array
    {
        return collect($constraints)->only(['budget', 'dietary_tag', 'exclude_allergens', 'max_calories', 'min_protein', 'vegetarian', 'vegan', 'children'])->all();
    }

    /** @param array<string, mixed> $data @return array<string, mixed> */
    private function cacheable(array $data): array
    {
        return Arr::except($data, ['email', 'phone', 'address', 'notes']);
    }
}
