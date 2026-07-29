<?php

declare(strict_types=1);

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\BlogCategory;
use App\Models\BlogPost;
use App\Models\BlogTag;
use App\Models\Recipe;
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

    public function store(Request $request): RedirectResponse
    {
        $this->admin();
        $post = BlogPost::query()->create(array_merge($this->validated($request), ['author_id' => $request->user()->id]));
        $this->syncRelations($post, $request);

        return to_route('admin.blog.edit', $post)->with('status', 'Blog taslağı kaydedildi.');
    }

    public function edit(BlogPost $post): View
    {
        $this->admin();

        return view('admin.blog.form', $this->formData($post->load(['tags', 'recipes'])));
    }

    public function update(Request $request, BlogPost $post): RedirectResponse
    {
        $this->admin();
        $post->update($this->validated($request, $post));
        $this->syncRelations($post, $request);

        return back()->with('status', 'Blog yazısı güncellendi.');
    }

    public function publish(BlogPost $post): RedirectResponse
    {
        $this->admin();
        abort_if($post->excerpt === '' || $post->body === '', 422, 'Yayın için özet ve içerik gereklidir.');
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
        return ['post' => $post, 'categories' => BlogCategory::query()->orderBy('name')->get(), 'tags' => BlogTag::query()->orderBy('name')->get(), 'recipes' => Recipe::query()->where('status', 'published')->orderBy('title')->get(['id', 'title'])];
    }

    /** @return array<string,mixed> */
    private function validated(Request $request, ?BlogPost $post = null): array
    {
        $data = $request->validate([
            'title' => ['required', 'string', 'max:255'], 'slug' => ['nullable', 'string', 'max:255', Rule::unique('blog_posts', 'slug')->ignore($post?->id)],
            'excerpt' => ['required', 'string', 'max:1000'], 'body' => ['required', 'string', 'max:30000'], 'blog_category_id' => ['nullable', 'exists:blog_categories,id'],
            'status' => ['required', Rule::in(['draft', 'in_review', 'scheduled'])], 'scheduled_at' => ['nullable', 'date'],
            'meta_title' => ['nullable', 'string', 'max:70'], 'meta_description' => ['nullable', 'string', 'max:160'], 'canonical_url' => ['nullable', 'url', 'max:2048'],
        ]);
        $data['slug'] = filled($data['slug'] ?? null) ? Str::slug((string) $data['slug']) : Str::slug((string) $data['title']);
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

    private function admin(): void
    {
        abort_unless(auth()->user()?->isAdmin(), 403);
    }
}
