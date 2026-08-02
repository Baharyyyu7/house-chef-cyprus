<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Models\Recipe;
use App\Services\Personalization\PersonalizationService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

final class PersonalizationController extends Controller
{
    public function index(Request $request, PersonalizationService $personalization): View
    {
        $profile = $personalization->profile($request->user());

        return view('personalization.index', [
            'profile' => $profile->load('preferences'),
            'recommendations' => $personalization->recommendations($request->user()),
            'reasonLabels' => [
                'pantry_match' => 'Dolabınızdaki malzemelerle eşleşiyor.',
                'favorite_cuisine' => 'Sevdiğiniz mutfaklardan biri.',
                'saved_recipe' => 'Daha önce kaydettiğiniz tariflere benziyor.',
                'quick_recipe' => 'Kısa sürede hazırlanabilir.',
                'published_verified' => 'Editoryal olarak doğrulanmış ve yayında.',
            ],
        ]);
    }

    public function update(Request $request, PersonalizationService $personalization): RedirectResponse
    {
        $data = $request->validate([
            'household_size' => ['nullable', 'integer', 'min:1', 'max:20'],
            'default_portions' => ['nullable', 'integer', 'min:1', 'max:20'],
            'weekly_cooking_days' => ['nullable', 'integer', 'min:1', 'max:7'],
            'preferred_duration_max' => ['nullable', 'integer', 'min:10', 'max:360'],
            'preferred_difficulty' => ['nullable', 'in:easy,medium,hard'],
            'preferred_budget_level' => ['nullable', 'in:low,medium,high'],
            'favorite_cuisines' => ['array', 'max:20'],
            'favorite_cuisines.*' => ['string', 'max:64'],
            'disliked_cuisines' => ['array', 'max:20'],
            'disliked_cuisines.*' => ['string', 'max:64'],
            'excluded_ingredients' => ['array', 'max:50'],
            'excluded_ingredients.*' => ['string', 'max:64'],
            'allergens' => ['array', 'max:30'],
            'allergens.*' => ['string', 'max:64'],
        ]);

        $personalization->updateProfile($request->user(), collect($data)->only(['household_size', 'default_portions', 'weekly_cooking_days', 'preferred_duration_max', 'preferred_difficulty', 'preferred_budget_level'])->all(), [
            'favorite_cuisine' => $data['favorite_cuisines'] ?? [],
            'disliked_cuisine' => $data['disliked_cuisines'] ?? [],
            'excluded_ingredient' => $data['excluded_ingredients'] ?? [],
            'allergen' => $data['allergens'] ?? [],
        ]);

        return back()->with('status', 'Kişiselleştirme tercihleri güncellendi.');
    }

    public function feedback(Request $request, Recipe $recipe, PersonalizationService $personalization): RedirectResponse
    {
        abort_unless($recipe->status === 'published' && $recipe->verification_status === 'verified', 404);
        $data = $request->validate([
            'type' => ['required', 'in:like,not_interested,hide_recipe,too_long,too_difficult,budget_mismatch,dietary_mismatch,allergen_concern,more_like_this'],
            'note' => ['nullable', 'string', 'max:500'],
            'recommendation_public_id' => ['nullable', 'string', 'max:26'],
        ]);
        $personalization->feedback($request->user(), $recipe, $data['type'], $data);

        return back()->with('status', 'Geri bildiriminiz kaydedildi.');
    }

    public function disable(Request $request, PersonalizationService $personalization): RedirectResponse
    {
        $personalization->disable($request->user());

        return back()->with('status', 'Kişiselleştirme kapatıldı. Genel öneriler gösterilmeye devam eder.');
    }

    public function clear(Request $request, PersonalizationService $personalization): RedirectResponse
    {
        $personalization->clearLearnedData($request->user());

        return back()->with('status', 'Öğrenilmiş tercihler ve öneri geçmişi silindi.');
    }
}
