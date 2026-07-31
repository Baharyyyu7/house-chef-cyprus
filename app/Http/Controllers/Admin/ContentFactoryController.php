<?php

declare(strict_types=1);

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AuditLog;
use App\Models\BlogPost;
use App\Models\ContentFactoryBatch;
use App\Models\ContentFactoryReview;
use App\Models\ContentTopicCluster;
use App\Models\Recipe;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

final class ContentFactoryController extends Controller
{
    public function index(Request $request): View
    {
        $this->admin();
        $reviews = ContentFactoryReview::query()->with('batch')->when($request->filled('type'), fn ($query) => $query->where('content_type', $request->string('type')->toString()))->when($request->filled('stage'), fn ($query) => $query->where('stage', $request->string('stage')->toString()))->when($request->filled('status'), fn ($query) => $query->where('status', $request->string('status')->toString()))->latest()->paginate(25)->withQueryString();

        return view('admin.content-factory.index', [
            'recipeDrafts' => Recipe::query()->where('content_origin', 'content_factory_draft')->count(),
            'verifiedRecipes' => Recipe::query()->where('status', 'published')->where('verification_status', 'verified')->count(),
            'blogDrafts' => BlogPost::query()->whereNotNull('content_factory_batch_id')->where('status', 'draft')->count(),
            'publishedBlogs' => BlogPost::query()->publiclyVisible()->count(),
            'recipeBatches' => ContentFactoryBatch::query()->where('content_type', 'recipe')->count(),
            'blogBatches' => ContentFactoryBatch::query()->where('content_type', 'blog')->count(),
            'clusters' => ContentTopicCluster::query()->count(),
            'waiting' => ContentFactoryReview::query()->where('status', 'pending')->count(),
            'failed' => ContentFactoryReview::query()->whereIn('status', ['failed', 'rejected'])->count(),
            'reviews' => $reviews,
        ]);
    }

    public function approve(ContentFactoryReview $review): RedirectResponse
    {
        $this->admin();
        abort_unless($review->stage === 'human_review', 422, 'Bu aşama insan onayı için uygun değildir.');
        $entity = $this->entity($review);
        if ($entity === null) {
            return back()->with('error', 'İlgili taslak bulunamadı; güvenli olarak onaylanmadı.');
        }
        if ($review->status !== 'human_approved') {
            $review->update(['status' => 'human_approved', 'reviewed_by_user_id' => auth()->id(), 'reviewed_at' => now()]);
            $entity->forceFill(['content_factory_state' => 'human_approved', 'human_approved_by_user_id' => auth()->id(), 'human_approved_at' => now()])->save();
            AuditLog::query()->create(['user_id' => auth()->id(), 'action' => 'content_factory.human_approved', 'auditable_type' => $review->entity_type, 'auditable_id' => $entity->id, 'metadata' => ['review_public_id' => $review->public_id, 'entity_public_id' => $review->entity_public_id, 'auto_publish' => false]]);
        }

        return back()->with('status', 'İnsan onayı kaydedildi. İçerik hâlâ taslak durumundadır; yayınlanmadı.');
    }

    public function reject(ContentFactoryReview $review): RedirectResponse
    {
        $this->admin();
        abort_unless($review->stage === 'human_review', 422, 'Bu aşama insan reddi için uygun değildir.');
        $review->update(['status' => 'rejected', 'reviewed_by_user_id' => auth()->id(), 'reviewed_at' => now()]);
        if ($entity = $this->entity($review)) {
            $entity->forceFill(['content_factory_state' => 'rejected', 'editorial_status' => 'editorial_failed'])->save();
            AuditLog::query()->create(['user_id' => auth()->id(), 'action' => 'content_factory.rejected', 'auditable_type' => $review->entity_type, 'auditable_id' => $entity->id, 'metadata' => ['review_public_id' => $review->public_id, 'entity_public_id' => $review->entity_public_id]]);
        }

        return back()->with('status', 'Taslak reddedildi; otomatik yayın yapılmadı.');
    }

    private function entity(ContentFactoryReview $review): Recipe|BlogPost|null
    {
        return match ($review->entity_type) {
            Recipe::class => Recipe::query()->where('public_id', $review->entity_public_id)->first(),
            BlogPost::class => BlogPost::query()->where('public_id', $review->entity_public_id)->first(),
            default => null,
        };
    }

    private function admin(): void
    {
        abort_unless(auth()->user()?->isAdmin(), 403);
    }
}
