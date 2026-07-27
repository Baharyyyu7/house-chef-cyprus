<?php

declare(strict_types=1);

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AuditLog;
use App\Models\Category;
use App\Models\Cuisine;
use App\Models\Recipe;
use App\Services\RecipeCompletenessService;
use App\Services\RecipePublicationService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

final class RecipeVerificationController extends Controller
{
    public function index(Request $request, RecipeCompletenessService $completeness): View
    {
        $query = Recipe::query()->with(['cuisine', 'category'])->withCount(['recipeIngredients', 'steps', 'provenances']);
        foreach (['status', 'verification_status', 'image_status', 'completeness_status'] as $filter) {
            if ($request->filled($filter)) {
                $query->where($filter, $request->string($filter));
            }
        }
        if ($request->filled('cuisine')) {
            $query->where('cuisine_id', $request->integer('cuisine'));
        }
        if ($request->filled('category')) {
            $query->where('category_id', $request->integer('category'));
        }

        $recipes = $query->orderBy('completeness_score')->paginate(20)->withQueryString();
        $recipes->getCollection()->each(fn (Recipe $recipe) => $completeness->refresh($recipe));

        return view('admin.recipe-verification.index', [
            'recipes' => $recipes,
            'cuisines' => Cuisine::query()->orderBy('name')->get(),
            'categories' => Category::query()->orderBy('name')->get(),
        ]);
    }

    public function show(Recipe $recipe, RecipeCompletenessService $completeness): View
    {
        return view('admin.recipe-verification.show', ['recipe' => $recipe->load(['cuisine', 'category', 'recipeIngredients.group', 'steps', 'provenances.contentSource', 'nutritionSnapshots', 'media']), 'report' => $completeness->refresh($recipe)]);
    }

    public function approve(Recipe $recipe, RecipeCompletenessService $completeness): RedirectResponse
    {
        $recipe->update(['editorial_status' => 'editorial_review', 'verification_status' => 'partially_verified', 'editorial_reviewed_at' => now(), 'editorial_reviewed_by_user_id' => auth()->id()]);
        $completeness->refresh($recipe);
        $this->audit('recipe_verification.approved', $recipe);

        return back()->with('status', 'Tarif editoryal incelemeye alındı.');
    }

    public function requestChanges(Recipe $recipe, RecipeCompletenessService $completeness): RedirectResponse
    {
        $recipe->update(['editorial_status' => 'needs_update', 'verification_status' => 'needs_review']);
        $completeness->refresh($recipe);
        $this->audit('recipe_verification.changes_requested', $recipe);

        return back()->with('status', 'Tarif düzeltme için işaretlendi.');
    }

    public function reject(Recipe $recipe): RedirectResponse
    {
        $recipe->update(['status' => 'draft', 'editorial_status' => 'deprecated', 'verification_status' => 'rejected']);
        $this->audit('recipe_verification.rejected', $recipe);

        return back()->with('status', 'Tarif yayından kaldırıldı.');
    }

    public function publish(Recipe $recipe, RecipePublicationService $publication): RedirectResponse
    {
        $publication->publish($recipe, auth()->user());
        $this->audit('recipe_verification.published', $recipe);

        return back()->with('status', 'Tarif yayınlandı.');
    }

    public function unpublish(Recipe $recipe, RecipePublicationService $publication): RedirectResponse
    {
        $publication->unpublish($recipe);
        $this->audit('recipe_verification.unpublished', $recipe);

        return back()->with('status', 'Tarif yayından kaldırıldı.');
    }

    private function audit(string $action, Recipe $recipe): void
    {
        AuditLog::query()->create(['user_id' => auth()->id(), 'action' => $action, 'auditable_type' => Recipe::class, 'auditable_id' => $recipe->id, 'metadata' => ['recipe_public_id' => $recipe->public_id]]);
    }
}
