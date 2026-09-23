<?php

declare(strict_types=1);

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\BlogCategory;
use App\Models\BlogPost;
use App\Models\BlogTag;
use App\Models\ContentRevision;
use App\Models\Recipe;
use App\Services\Seo\ContentQualityService;
use App\Services\Seo\ContentSimilarityService;
use App\Services\Seo\BlogPublicationReadinessService;
use App\Services\Seo\SeoMetadataService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

final class BlogPostController extends Controller
{
    public function index(Request $request): View
    {
        $this->admin();
        $posts = BlogPost::query()->with('category')
            ->withExists(['revisions as completion_revision_exists' => fn ($query) => $query->where('reason', 'published-blog-completion-v1')])
            ->when($request->filled('q'), fn ($query) => $query->where('title', 'like', '%'.$request->string('q')->toString().'%'))
            ->when($request->filled('status'), fn ($query) => $query->where('status', $request->string('status')->toString()))
            ->latest()->paginate(20)->withQueryString();

        return view('admin.blog.index', compact('posts'));
    }

    public function create(): View
    {
        $this->admin();

        return view('admin.blog.form', $this->formData(new BlogPost));
    }

    public function store(Request $request, SeoMetadataService $seoMetadata): RedirectResponse
    {
        $this->admin();
        $post = BlogPost::query()->create(array_merge($this->validated($request), ['author_id' => $request->user()->id]));
        $this->syncRelations($post, $request);
        $this->syncSeoMetadata($post, $seoMetadata);

        return to_route('admin.blog.edit', $post)->with('status', 'Blog taslağı kaydedildi.');
    }

    public function edit(BlogPost $post): View
    {
        $this->admin();

        return view('admin.blog.form', $this->formData($post->load(['tags', 'recipes'])));
    }

    public function review(BlogPost $post): View
    {
        $this->admin();
        $revision = ContentRevision::query()->where('blog_post_id', $post->id)
            ->where('reason', 'published-blog-completion-v1')->latest('revision_number')->firstOrFail();

        return view('admin.blog.review', compact('post', 'revision'));
    }

    public function update(Request $request, BlogPost $post, SeoMetadataService $seoMetadata): RedirectResponse
    {
        $this->admin();
        $post->update($this->validated($request, $post));
        $this->syncRelations($post, $request);
        $this->syncSeoMetadata($post, $seoMetadata);

        return back()->with('status', 'Blog yazısı güncellendi.');
    }

    public function publish(BlogPost $post, BlogPublicationReadinessService $readiness, ContentSimilarityService $similarity): RedirectResponse
    {
        $this->admin();
        $assessment = $readiness->evaluate($post);
        $similarityResult = $similarity->inspect($post);
        abort_unless($assessment['passed'], 422, implode(' ', $assessment['issues']));
        abort_unless($similarityResult['status'] === 'passed', 422, 'İçerik benzerliği editoryal inceleme gerektiriyor.');
        $post->update(['status' => 'published', 'published_at' => now(), 'scheduled_at' => null]);

        return back()->with('status', 'Yazı yayınlandı.');
    }

    public function archive(BlogPost $post): RedirectResponse
    {
        $this->admin();
        $post->update(['status' => 'archived']);

        return back()->with('status', 'Yazı arşivlendi ve public listeden kaldırıldı.');
    }

    /** @return array<string,mixed> */
    private function formData(BlogPost $post): array
    {
        return ['post' => $post, 'categories' => BlogCategory::query()->orderBy('name')->get(), 'tags' => BlogTag::query()->orderBy('name')->get(), 'recipes' => Recipe::query()->where('status', 'published')->where('verification_status', 'verified')->orderBy('title')->get(['id', 'title'])];
    }

    /** @return array<string,mixed> */
    private function validated(Request $request, ?BlogPost $post = null): array
    {
        $data = $request->validate([
            'title' => ['required', 'string', 'max:255'], 'slug' => ['nullable', 'string', 'max:255', Rule::unique('blog_posts', 'slug')->ignore($post?->id)],
            'excerpt' => ['required', 'string', 'max:1000'], 'body' => ['required', 'string', 'max:30000'], 'blog_category_id' => ['nullable', 'exists:blog_categories,id'],
            'status' => ['required', Rule::in(['idea', 'research', 'researched', 'brief_ready', 'draft', 'fact_check', 'editorial_review', 'seo_review', 'media_review', 'approved', 'scheduled'])], 'scheduled_at' => ['nullable', 'date'],
            'meta_title' => ['nullable', 'string', 'max:70'], 'meta_description' => ['nullable', 'string', 'max:160'], 'canonical_url' => ['nullable', 'url', 'max:2048'],
            'content_purpose' => ['nullable', 'string', 'max:120'], 'search_intent' => ['nullable', Rule::in(['informational', 'navigational', 'commercial', 'transactional'])], 'primary_keyword' => ['nullable', 'string', 'max:160'],
            'fact_check_status' => ['nullable', Rule::in(['pending', 'passed', 'needs_review'])], 'editorial_status' => ['nullable', Rule::in(['draft', 'in_review', 'approved', 'rejected'])], 'originality_status' => ['nullable', Rule::in(['pending', 'passed', 'needs_review'])], 'source_notes_text' => ['nullable', 'string', 'max:5000'],
        ]);
        $data['slug'] = filled($data['slug'] ?? null) ? Str::slug((string) $data['slug']) : Str::slug((string) $data['title']);
        $data['source_notes'] = filled($data['source_notes_text'] ?? null) ? ['editorial_note' => $data['source_notes_text']] : null;
        unset($data['source_notes_text']);
        if ($data['status'] === 'scheduled') {
            abort_unless(filled($data['scheduled_at'] ?? null), 422, 'Planlanmış yazı için tarih gereklidir.');
        }

        return $data;
    }

    private function syncRelations(BlogPost $post, Request $request): void
    {
        $post->tags()->sync($request->input('tag_ids', []));
        $post->recipes()->sync($request->input('recipe_ids', []));
    }

    private function syncSeoMetadata(BlogPost $post, SeoMetadataService $service): void
    {
        $service->update($post, [
            'meta_title' => $post->meta_title,
            'meta_description' => $post->meta_description,
            'canonical_override' => $post->canonical_url,
            'focus_topic' => $post->primary_keyword,
            'primary_query' => $post->primary_keyword,
            'search_intent' => $post->search_intent,
            'quality_score' => app(ContentQualityService::class)->evaluate($post)['score'],
            'last_audited_at' => now(),
        ]);
    }

    private function admin(): void
    {
        $this->authorizeAdminOperations();
    }
}
