<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Models\BlogCategory;
use App\Models\BlogPost;
use App\Models\BlogTag;
use App\Services\Media\PublicMediaEligibilityService;
use App\Services\Seo\SeoIndexabilityService;
use App\Services\Seo\SeoMetadataService;
use App\Services\Seo\SeoSchemaService;
use Illuminate\Http\Request;
use Illuminate\View\View;

final class BlogController extends Controller
{
    public function index(Request $request, SeoIndexabilityService $indexability): View
    {
        $posts = BlogPost::query()->publiclyVisible()->with(['category', 'tags', 'heroMedia', 'media'])
            ->when($request->filled('q'), fn ($query) => $query->where(fn ($nested) => $nested->where('title', 'like', '%'.$request->string('q')->toString().'%')->orWhere('excerpt', 'like', '%'.$request->string('q')->toString().'%')))
            ->latest('published_at')->paginate(12)->withQueryString();

        return view('blog.index', ['posts' => $posts, 'title' => 'Blog · House Chef Cyprus', 'seoIndexability' => $indexability->forRequest($request, route('blog.index'))]);
    }

    public function show(BlogPost $post, SeoMetadataService $seo, SeoSchemaService $schemaService, PublicMediaEligibilityService $mediaEligibility): View
    {
        abort_unless(BlogPost::query()->publiclyVisible()->whereKey($post->id)->exists(), 404);
        // A blog relation can be prepared before a recipe is editorially ready.
        // Never expose that private relation as a public link: the recipe route
        // deliberately returns 404 for anything other than published + verified.
        $post->load([
            'category',
            'tags',
            'heroMedia',
            'author',
            'recipes' => fn ($query) => $query
                ->where('status', 'published')
                ->where('verification_status', 'verified')
                ->with(['media' => fn ($media) => $media->where('visibility', 'public')]),
        ]);
        $relatedPosts = BlogPost::query()->publiclyVisible()
            ->whereKeyNot($post->id)
            ->when($post->blog_category_id !== null, fn ($query) => $query->where('blog_category_id', $post->blog_category_id))
            ->with(['category', 'heroMedia', 'media'])
            ->latest('published_at')
            ->limit(3)
            ->get();

        $heroMedia = $mediaEligibility->heroOrFirstEligible($post->heroMedia, $post->media);
        $schema = $schemaService->article($post, $heroMedia?->publicUrl() ?: '');

        $metadata = $seo->for($post, $post->meta_title ?: $post->title.' · House Chef Cyprus', $post->meta_description ?: $post->excerpt, route('blog.show', $post));

        return view('blog.show', compact('post', 'schema', 'metadata', 'relatedPosts', 'heroMedia'));
    }

    public function category(BlogCategory $category): View
    {
        return view('blog.index', ['posts' => $category->posts()->publiclyVisible()->with(['category', 'tags', 'heroMedia', 'media'])->latest('published_at')->paginate(12), 'title' => $category->name.' · Blog', 'seoIndexability' => ['canonical' => route('blog.category', $category), 'robots' => request()->query() === [] ? 'index,follow' : 'noindex,follow']]);
    }

    public function tag(BlogTag $tag): View
    {
        return view('blog.index', ['posts' => $tag->posts()->publiclyVisible()->with(['category', 'tags', 'heroMedia', 'media'])->latest('published_at')->paginate(12), 'title' => '#'.$tag->name.' · Blog', 'seoIndexability' => ['canonical' => route('blog.tag', $tag), 'robots' => request()->query() === [] ? 'index,follow' : 'noindex,follow']]);
    }
}
