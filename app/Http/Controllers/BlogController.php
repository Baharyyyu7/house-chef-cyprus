<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Models\BlogCategory;
use App\Models\BlogPost;
use App\Models\BlogTag;
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

    public function show(BlogPost $post): View
    {
        abort_unless(BlogPost::query()->publiclyVisible()->whereKey($post->id)->exists(), 404);
        $post->load(['category', 'tags', 'heroMedia', 'author', 'recipes.media']);

        return view('blog.show', compact('post'));
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
