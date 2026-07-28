<?php

declare(strict_types=1);

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AiImageGeneration;
use App\Models\AuditLog;
use App\Services\Media\AiImagePilotService;
use App\Services\Media\AiImageReviewService;
use App\Services\Media\ImageProviderRegistry;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\Response;

final class AiImageStudioController extends Controller
{
    public function index(AiImagePilotService $pilot, ImageProviderRegistry $providers): View
    {
        $this->admin();
        $pilot->synchronize();

        return view('admin.ai-image-studio.index', ['generations' => AiImageGeneration::query()->with(['recipe.cuisine', 'recipe.category'])->latest()->paginate(30), 'providers' => $providers->health(), 'cost' => $pilot->costPreview()]);
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

    private function admin(): void
    {
        abort_unless(auth()->user()?->isAdmin(), 403);
    }
}
