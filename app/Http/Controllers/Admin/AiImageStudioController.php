<?php

declare(strict_types=1);

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AiImageGeneration;
use App\Models\AuditLog;
use App\Models\Media;
use App\Models\Recipe;
use App\Models\RecipeMediaCandidate;
use App\Services\Media\AiImagePilotService;
use App\Services\Media\AiImageReviewService;
use App\Services\Media\FallbackImageComparisonService;
use App\Services\Media\ImageProviderRegistry;
use App\Services\Media\PexelsCandidateMediaService;
use App\Services\Media\PexelsEditorialReviewService;
use App\Services\Media\PexelsRecipeImageProvider;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\Response;

final class AiImageStudioController extends Controller
{
    public function index(Request $request, AiImagePilotService $pilot, ImageProviderRegistry $providers, FallbackImageComparisonService $comparison, PexelsRecipeImageProvider $pexels): View
    {
        $this->admin();
        $pilot->synchronize();

        $comparisonGenerations = AiImageGeneration::query()
            ->with(['recipe.cuisine', 'recipe.category', 'media'])
            ->where('generation_scope', 'fallback_provider_comparison')
            ->whereHas('recipe', fn ($query) => $query->where('slug', 'domates-corbasi'))
            ->orderBy('provider')
            ->get();

        $selectedRecipe = $request->filled('recipe')
            ? Recipe::query()->where('public_id', $request->string('recipe')->toString())->first()
            : null;

        $pexelsSearch = $selectedRecipe === null ? null : $pexels->search($selectedRecipe);
        $candidateBatch = max(1, min(5, (int) $request->integer('review_batch', 1)));
        $rankedCandidates = RecipeMediaCandidate::query()
            ->with(['recipe.cuisine', 'recipe.category', 'recipe.ingredients'])
            ->where('review_batch', $candidateBatch)
            ->whereIn('status', ['pending_review', 'selected_for_review', 'auto_rejected', 'fallback_kept'])
            ->orderBy('recipe_id')->orderBy('rank')->get()->groupBy('recipe_id');
        $requestedRecipe = $request->string('review_recipe')->toString();
        $quickCandidates = $requestedRecipe === ''
            ? $rankedCandidates->first()
            : $rankedCandidates->first(fn ($candidates) => $candidates->first()?->recipe?->public_id === $requestedRecipe);
        $quickRecipe = $quickCandidates?->first()?->recipe;
        $reviewCounts = RecipeMediaCandidate::query()
            ->where('review_batch', $candidateBatch)
            ->selectRaw("sum(case when status = 'selected_for_review' then 1 else 0 end) as selected")
            ->selectRaw("sum(case when status = 'rejected' then 1 else 0 end) as rejected")
            ->selectRaw("sum(case when status = 'pending_review' then 1 else 0 end) as remaining")
            ->first();

        return view('admin.ai-image-studio.index', [
            'generations' => AiImageGeneration::query()->with(['recipe.cuisine', 'recipe.category'])->latest()->paginate(30),
            'providers' => $providers->health(),
            'cost' => $pilot->costPreview(),
            'comparison' => [
                'primary' => (string) config('ai_images.primary_provider'),
                'fallback' => (string) config('ai_images.fallback_provider'),
                'request_limit' => (int) config('ai_images.fallback_provider_comparison.max_total_requests'),
                'budget_cents' => (int) config('ai_images.fallback_provider_comparison.budget_cents'),
                'records' => $comparisonGenerations,
                'dry_run' => $comparison->dryRun(),
            ],
            'recipes' => Recipe::query()->orderBy('title')->get(['id', 'public_id', 'title']),
            'selectedRecipe' => $selectedRecipe,
            'pexelsCandidates' => $pexelsSearch['candidates'] ?? [],
            'pexelsSearch' => $pexelsSearch,
            'pexelsReviewMedia' => Media::query()->with('mediable')->where('source_name', 'Pexels')->latest()->paginate(12, ['*'], 'pexels_page'),
            'candidateBatch' => $candidateBatch,
            'rankedCandidates' => $rankedCandidates,
            'quickCandidates' => $quickCandidates,
            'quickRecipe' => $quickRecipe,
            'reviewCounts' => $reviewCounts,
        ]);
    }

    public function show(AiImageGeneration $generation): View
    {
        $this->admin();

        return view('admin.ai-image-studio.show', ['generation' => $generation->load(['recipe.cuisine', 'recipe.category', 'recipe.ingredients', 'media'])]);
    }

    public function reject(Request $request, AiImageGeneration $generation): RedirectResponse
    {
        $this->admin();
        $data = $request->validate(['review_notes' => ['nullable', 'string', 'max:1000']]);
        abort_unless($generation->status === 'ready_for_review', 422, 'Bu taslak reddedilemez.');
        $history = (array) $generation->status_transitions;
        $history[] = ['status' => 'rejected', 'at' => now()->toIso8601String()];
        $generation->update(['status' => 'rejected', 'review_notes' => $data['review_notes'] ?? null, 'reviewed_at' => now(), 'reviewed_by_user_id' => auth()->id(), 'status_transitions' => $history]);
        AuditLog::create(['user_id' => auth()->id(), 'action' => 'ai_image_generation.reject', 'auditable_type' => $generation->getMorphClass(), 'auditable_id' => $generation->getKey(), 'metadata' => ['generation_public_id' => $generation->public_id]]);

        return to_route('admin.ai-image-studio.show', $generation)->with('status', 'Taslak reddedildi; public alana aktarılmadı.');
    }

    public function preview(AiImageGeneration $generation): Response
    {
        $this->admin();
        $media = $generation->media;
        abort_unless($media !== null && $media->visibility === 'private' && is_string($media->path) && Storage::disk($media->disk)->exists($media->path), 404);

        return response(Storage::disk($media->disk)->get($media->path), 200, ['Content-Type' => $media->mime_type ?: 'image/png', 'Cache-Control' => 'private, no-store']);
    }

    public function approve(Request $request, AiImageGeneration $generation, AiImageReviewService $review): RedirectResponse
    {
        $this->admin();
        $data = $request->validate(['review_notes' => ['required', 'string', 'min:10', 'max:1000']]);
        $review->approve($generation->load('media', 'recipe.media'), $request->user(), $data['review_notes']);

        return to_route('admin.ai-image-studio.show', $generation)->with('status', 'Görsel manuel checklist onayıyla public medya hattına aktarıldı.');
    }

    public function importPexels(Request $request, PexelsCandidateMediaService $pexels): RedirectResponse
    {
        $this->admin();
        $data = $request->validate(['recipe_public_id' => ['required', 'string', 'size:26'], 'external_photo_id' => ['required', 'string', 'max:64']]);
        $recipe = Recipe::query()->where('public_id', $data['recipe_public_id'])->firstOrFail();
        $media = $pexels->import($recipe, $data['external_photo_id']);

        return to_route('admin.ai-image-studio.index', ['recipe' => $recipe->public_id])->with('status', 'Pexels adayı private review alanına aktarıldı: '.$media->public_id);
    }

    public function approvePexels(Request $request, Media $media, PexelsCandidateMediaService $pexels): RedirectResponse
    {
        $this->admin();
        $data = $request->validate(['alt_text' => ['required', 'string', 'min:8', 'max:255'], 'review_notes' => ['required', 'string', 'min:10', 'max:1000']]);
        $pexels->approve($media->load('mediable'), $request->user(), $data['alt_text'], $data['review_notes']);

        return back()->with('status', 'Pexels görseli insan incelemesiyle public medya hattına alındı.');
    }

    public function rejectPexels(Request $request, Media $media, PexelsCandidateMediaService $pexels): RedirectResponse
    {
        $this->admin();
        $data = $request->validate(['review_notes' => ['nullable', 'string', 'max:1000']]);
        $pexels->reject($media, $request->user(), $data['review_notes'] ?? null);

        return back()->with('status', 'Pexels adayı reddedildi; public alana aktarılmadı.');
    }

    public function selectCandidate(Request $request, RecipeMediaCandidate $candidate, PexelsEditorialReviewService $review): RedirectResponse
    {
        $this->admin();
        $review->select($candidate, $request->user());

        return back()->with('status', 'Aday seçildi. Private import yapılmadan public alana aktarılmaz.');
    }

    public function rejectCandidate(Request $request, RecipeMediaCandidate $candidate, PexelsEditorialReviewService $review): RedirectResponse
    {
        $this->admin();
        $review->reject($candidate, $request->user());

        return back()->with('status', 'Aday reddedildi; fallback korunuyor.');
    }

    public function rejectRecipeCandidates(Request $request, Recipe $recipe, PexelsEditorialReviewService $review): RedirectResponse
    {
        $this->admin();
        $review->rejectAll($recipe, $request->user());

        return back()->with('status', 'Tarifin bütün adayları reddedildi; branded fallback korunuyor.');
    }

    public function importSelectedCandidate(Request $request, RecipeMediaCandidate $candidate, PexelsCandidateMediaService $pexels): RedirectResponse
    {
        $this->admin();
        abort_unless($candidate->status === 'selected_for_review' && $candidate->recipe !== null, 422, 'Bu aday private import için seçilmemiş.');
        $media = $pexels->importStoredCandidate($candidate->load('recipe'));
        $candidate->update(['status' => 'private_imported', 'media_id' => $media->id]);

        return back()->with('status', 'Aday private review alanına indirildi. Bu işlem public yayın değildir.');
    }

    public function keepPexelsFallback(Request $request, Recipe $recipe, PexelsEditorialReviewService $review): RedirectResponse
    {
        $this->admin();
        $review->keepFallback($recipe, $request->user());

        return back()->with('status', 'Bu tarif için branded fallback korunuyor; durum needs_real_media olarak işaretlendi.');
    }

    private function admin(): void
    {
        abort_unless(auth()->user()?->isAdmin(), 403);
    }
}
