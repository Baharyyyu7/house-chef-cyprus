<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Models\AiMealPlan;
use App\Models\Recipe;
use App\Services\AI\AiKitchenService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

final class AiKitchenController extends Controller
{
    public function index(Request $request): View
    {
        return view('ai-kitchen.index', ['plans' => $request->user()->aiMealPlans()->latest()->limit(5)->get(), 'recent' => $request->user()->aiInteractions()->latest()->limit(8)->get()]);
    }

    public function assistant(Request $request, AiKitchenService $kitchen): RedirectResponse
    {
        $data = $request->validate(['question' => ['required', 'string', 'max:1000']]);

        return $this->back($kitchen->assistant($request->user(), $data['question']));
    }

    public function pantry(Request $request, AiKitchenService $kitchen): RedirectResponse
    {
        return $this->back($kitchen->pantry($request->user()));
    }

    public function leftovers(Request $request, AiKitchenService $kitchen): RedirectResponse
    {
        $data = $request->validate(['leftovers' => ['required', 'array', 'min:1', 'max:20'], 'leftovers.*' => ['string', 'max:80']]);

        return $this->back($kitchen->leftovers($request->user(), $data['leftovers']));
    }

    public function mealPlan(Request $request, AiKitchenService $kitchen): RedirectResponse
    {
        $data = $request->validate(['days' => ['required', 'integer', 'in:7,14,30'], 'max_calories' => ['nullable', 'numeric', 'min:1'], 'min_protein' => ['nullable', 'numeric', 'min:0'], 'dietary_tag' => ['nullable', 'string', 'max:64']]);

        return $this->back($kitchen->mealPlan($request->user(), $data['days'], $data));
    }

    public function shopping(Request $request, AiMealPlan $plan, AiKitchenService $kitchen): RedirectResponse
    {
        return $this->back($kitchen->shopping($request->user(), $plan));
    }

    public function portion(Request $request, Recipe $recipe, AiKitchenService $kitchen): RedirectResponse
    {
        $data = $request->validate(['servings' => ['required', 'integer', 'min:1', 'max:30']]);
        abort_unless($recipe->status === 'published' && $recipe->verification_status === 'verified', 404);

        return $this->back($kitchen->portion($request->user(), $recipe, $data['servings']));
    }

    public function nutrition(Request $request, Recipe $recipe, AiKitchenService $kitchen): RedirectResponse
    {
        abort_unless($recipe->status === 'published' && $recipe->verification_status === 'verified', 404);

        return $this->back($kitchen->nutrition($request->user(), $recipe));
    }

    private function back(array $result): RedirectResponse
    {
        return back()->with('ai_kitchen_result', $result);
    }
}
