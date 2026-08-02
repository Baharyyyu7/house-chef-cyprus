<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\AiMealPlan;
use App\Models\AiShoppingList;
use App\Models\Recipe;
use App\Services\Personalization\PersonalizationService;
use App\Services\Personalization\PersonalizedPlanningService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

final class PersonalizationController extends Controller
{
    public function profile(Request $request, PersonalizationService $service): JsonResponse
    {
        return $this->single($request, $this->profileData($service->profile($request->user())->load('preferences')));
    }

    public function updateProfile(Request $request, PersonalizationService $service): JsonResponse
    {
        $data = $this->profileInput($request);
        $profile = $service->updateProfile($request->user(), $data['attributes'], $data['preferences']);

        return $this->single($request, $this->profileData($profile));
    }

    public function tasteProfile(Request $request, PersonalizationService $service): JsonResponse
    {
        $profile = $service->profile($request->user())->load('preferences');

        return $this->single($request, [
            'public_id' => $profile->public_id,
            'explicit_preferences' => $profile->preferences->where('source', 'explicitly_selected')->values()->map(fn ($item): array => $this->preferenceData($item))->all(),
            'inferred_preferences' => $profile->preferences->where('source', '!=', 'explicitly_selected')->values()->map(fn ($item): array => $this->preferenceData($item))->all(),
        ]);
    }

    public function recommendations(Request $request, PersonalizationService $service): JsonResponse
    {
        $limit = $request->integer('per_page', 12);
        abort_unless($limit >= 1 && $limit <= 30, 422);
        $items = $service->recommendations($request->user(), $limit)->map(fn ($item): array => [
            'public_id' => $item->public_id,
            'recipe' => ['public_id' => $item->recipe->public_id, 'title' => $item->recipe->title, 'slug' => $item->recipe->slug],
            'reasons' => $this->reasons($item->reason_codes),
        ])->values();

        return response()->json(['data' => $items, 'meta' => ['per_page' => $limit, 'count' => $items->count()], 'links' => [], 'request_id' => $this->requestId($request)]);
    }

    public function feedback(Request $request, Recipe $recipe, PersonalizationService $service): JsonResponse
    {
        abort_unless($recipe->status === 'published' && $recipe->verification_status === 'verified', 404);
        $data = $request->validate(['type' => ['required', 'in:like,not_interested,hide_recipe,too_long,too_difficult,budget_mismatch,dietary_mismatch,allergen_concern,more_like_this'], 'note' => ['nullable', 'string', 'max:500'], 'recommendation_public_id' => ['nullable', 'string', 'max:26']]);
        $feedback = $service->feedback($request->user(), $recipe, $data['type'], $data);

        return $this->single($request, ['public_id' => $feedback->public_id, 'type' => $feedback->type]);
    }

    public function unhide(Request $request, Recipe $recipe, PersonalizationService $service): JsonResponse
    {
        $updated = $request->user()->personalizationFeedback()
            ->where('recipe_id', $recipe->id)
            ->where('type', 'hide_recipe')
            ->whereNull('withdrawn_at')
            ->update(['withdrawn_at' => now()]);
        $service->clearRecommendationHistory($request->user());

        return $this->single($request, ['recipe_public_id' => $recipe->public_id, 'restored' => $updated > 0]);
    }

    public function mealPlan(Request $request, PersonalizedPlanningService $planning): JsonResponse
    {
        $data = $request->validate(['days' => ['required', 'integer', 'in:7,14,30'], 'max_minutes' => ['nullable', 'integer', 'min:10', 'max:360'], 'pantry_priority' => ['nullable', 'boolean'], 'leftovers' => ['nullable', 'boolean']]);
        $plan = $planning->mealPlan($request->user(), (int) $data['days'], $data);

        return $this->single($request, ['public_id' => $plan->public_id, 'days' => $plan->days, 'plan' => $plan->plan, 'status' => $plan->status]);
    }

    public function shopping(Request $request, AiMealPlan $plan, PersonalizedPlanningService $planning): JsonResponse
    {
        $list = $planning->shopping($request->user(), $plan);

        return $this->single($request, ['public_id' => $list->public_id, 'items' => $list->items, 'status' => $list->status]);
    }

    public function addShoppingItem(Request $request, AiShoppingList $list, PersonalizedPlanningService $planning): JsonResponse
    {
        $data = $request->validate(['name' => ['required', 'string', 'max:120'], 'quantity' => ['nullable', 'numeric', 'min:0'], 'unit' => ['nullable', 'string', 'max:32']]);
        $list = $planning->addManualItem($request->user(), $list, $data['name'], isset($data['quantity']) ? (float) $data['quantity'] : null, $data['unit'] ?? null);

        return $this->single($request, ['public_id' => $list->public_id, 'items' => $list->items]);
    }

    public function expiryInsights(Request $request, PersonalizedPlanningService $planning): JsonResponse
    {
        return $this->single($request, ['items' => $planning->expiryInsights($request->user())]);
    }

    public function toggleShopping(Request $request, AiShoppingList $list, PersonalizedPlanningService $planning): JsonResponse
    {
        $data = $request->validate(['index' => ['required', 'integer', 'min:0', 'max:1000']]);
        $list = $planning->togglePurchased($request->user(), $list, $data['index']);

        return $this->single($request, ['public_id' => $list->public_id, 'items' => $list->items]);
    }

    public function status(Request $request, PersonalizationService $service): JsonResponse
    {
        $profile = $service->profile($request->user());

        return $this->single($request, ['enabled' => $profile->personalization_enabled, 'onboarding_completed' => $profile->onboarding_completed]);
    }

    public function disable(Request $request, PersonalizationService $service): JsonResponse
    {
        $profile = $service->disable($request->user());

        return $this->single($request, ['enabled' => $profile->personalization_enabled]);
    }

    public function clear(Request $request, PersonalizationService $service): JsonResponse
    {
        $service->clearLearnedData($request->user());

        return $this->single($request, ['cleared' => true]);
    }

    public function requestExport(Request $request, PersonalizationService $service): JsonResponse
    {
        $export = $service->requestExport($request->user());

        return $this->single($request, ['public_id' => $export->public_id, 'status' => $export->status]);
    }

    /** @return array{attributes:array<string,mixed>,preferences:array<string,list<string>>} */
    private function profileInput(Request $request): array
    {
        $data = $request->validate([
            'household_size' => ['nullable', 'integer', 'min:1', 'max:20'], 'default_portions' => ['nullable', 'integer', 'min:1', 'max:20'], 'weekly_cooking_days' => ['nullable', 'integer', 'min:1', 'max:7'], 'preferred_duration_max' => ['nullable', 'integer', 'min:10', 'max:360'], 'preferred_difficulty' => ['nullable', 'in:easy,medium,hard'], 'preferred_budget_level' => ['nullable', 'in:low,medium,high'],
            'favorite_cuisines' => ['array', 'max:20'], 'favorite_cuisines.*' => ['string', 'max:64'], 'disliked_cuisines' => ['array', 'max:20'], 'disliked_cuisines.*' => ['string', 'max:64'], 'excluded_ingredients' => ['array', 'max:50'], 'excluded_ingredients.*' => ['string', 'max:64'], 'allergens' => ['array', 'max:30'], 'allergens.*' => ['string', 'max:64'],
        ]);

        return ['attributes' => collect($data)->only(['household_size', 'default_portions', 'weekly_cooking_days', 'preferred_duration_max', 'preferred_difficulty', 'preferred_budget_level'])->all(), 'preferences' => ['favorite_cuisine' => $data['favorite_cuisines'] ?? [], 'disliked_cuisine' => $data['disliked_cuisines'] ?? [], 'excluded_ingredient' => $data['excluded_ingredients'] ?? [], 'allergen' => $data['allergens'] ?? []]];
    }

    /** @return array<string,mixed> */
    private function profileData(object $profile): array
    {
        return ['public_id' => $profile->public_id, 'enabled' => $profile->personalization_enabled, 'onboarding_completed' => $profile->onboarding_completed, 'household_size' => $profile->household_size, 'default_portions' => $profile->default_portions, 'settings' => $profile->settings, 'preferences' => $profile->preferences->map(fn ($item): array => $this->preferenceData($item))->values()];
    }

    /** @return array{public_id:string,type:string,value:string,source:string} */
    private function preferenceData(object $item): array
    {
        return ['public_id' => $item->public_id, 'type' => $item->type, 'value' => $item->value, 'source' => $item->source];
    }

    /** @param array<int, string> $codes @return list<array{code:string,label:string}> */
    private function reasons(array $codes): array
    {
        $labels = ['pantry_match' => 'Dolabınızdaki malzemelerle eşleşiyor.', 'favorite_cuisine' => 'Sevdiğiniz mutfaklardan biri.', 'saved_recipe' => 'Kaydettiğiniz tariflere benziyor.', 'quick_recipe' => 'Kısa sürede hazırlanabilir.', 'published_verified' => 'Editoryal olarak doğrulandı.'];

        return collect($codes)->map(fn (string $code): array => ['code' => $code, 'label' => $labels[$code] ?? 'Güvenli yayın kurallarını karşılıyor.'])->all();
    }

    private function single(Request $request, array $data): JsonResponse
    {
        return response()->json(['data' => $data, 'request_id' => $this->requestId($request)]);
    }

    private function requestId(Request $request): string
    {
        return (string) $request->attributes->get('request_id');
    }
}
