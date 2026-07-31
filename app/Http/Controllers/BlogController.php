<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Models\BlogCategory;
use App\Models\BlogPost;
use App\Models\BlogTag;
use App\Services\Seo\SeoMetadataService;
use Illuminate\Http\Request;
use Illuminate\View\View;

final class BlogController extends Controller
{
    public function index(Request $request): View
    {
        $posts = BlogPost::query()->publiclyVisible()->with(['category', 'tags', 'heroMedia'])
            ->when($request->filled('q'), fn ($query) => $query->where(fn ($nested) => $nested->where('title', 'like', '%'.$request->string('q')->toString().'%')->orWhere('excerpt', 'like', '%'.$request->string('q')->toString().'%')))
            ->latest('published_at')->paginate(12)->withQueryString();

        return view('blog.index', ['posts' => $posts, 'title' => 'Blog · House Chef Cyprus']);
    }

    public function show(BlogPost $post, SeoMetadataService $seo): View
    {
        abort_unless(BlogPost::query()->publiclyVisible()->whereKey($post->id)->exists(), 404);
        $post->load(['category', 'tags', 'heroMedia', 'author', 'recipes.media']);

        $schema = ['@context' => 'https://schema.org', '@type' => 'Article', 'headline' => $post->title, 'description' => $post->excerpt, 'datePublished' => $post->published_at?->toAtomString(), 'dateModified' => $post->updated_at?->toAtomString(), 'mainEntityOfPage' => route('blog.show', $post), 'author' => ['@type' => 'Organization', 'name' => 'House Chef Cyprus']];
        if ($post->heroMedia?->publicUrl()) {
            $schema['image'] = [$post->heroMedia->publicUrl()];
        }

        $metadata = $seo->for($post, $post->meta_title ?: $post->title.' · House Chef Cyprus', $post->meta_description ?: $post->excerpt, route('blog.show', $post));

        return view('blog.show', compact('post', 'schema', 'metadata'));
    }

    public function category(BlogCategory $category): View
    {
        return view('blog.index', ['posts' => $category->posts()->publiclyVisible()->with(['category', 'tags', 'heroMedia'])->latest('published_at')->paginate(12), 'title' => $category->name.' · Blog']);
    }

    public function tag(BlogTag $tag): View
    {
        return view('blog.index', ['posts' => $tag->posts()->publiclyVisible()->with(['category', 'tags', 'heroMedia'])->latest('published_at')->paginate(12), 'title' => '#'.$tag->name.' · Blog']);
    }
}
