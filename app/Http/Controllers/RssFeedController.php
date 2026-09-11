<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Models\BlogPost;
use App\Models\Recipe;
use Illuminate\Http\Response;

final class RssFeedController extends Controller
{
    public function index(): Response
    {
        $recipes = Recipe::query()
            ->where('status', 'published')
            ->where('verification_status', 'verified')
            ->with(['cuisine', 'category', 'media' => fn ($q) => $q->where('visibility', 'public')->orderBy('sort_order')])
            ->latest('published_at')
            ->limit(25)
            ->get();

        $blogs = BlogPost::query()
            ->publiclyVisible()
            ->with(['category', 'heroMedia'])
            ->latest('published_at')
            ->limit(15)
            ->get();

        $items = collect()
            ->concat($recipes->map(fn (Recipe $r): array => [
                'title' => $r->title,
                'link' => route('recipes.show', $r),
                'guid' => route('recipes.show', $r),
                'description' => (string) ($r->description ?: $r->excerpt),
                'pubDate' => $r->published_at?->toRssString() ?? now()->toRssString(),
                'category' => $r->cuisine?->name ?? $r->category?->name ?? 'Tarif',
                'image' => $r->media->first()?->publicUrl(),
            ]))
            ->concat($blogs->map(fn (BlogPost $b): array => [
                'title' => $b->title,
                'link' => route('blog.show', $b),
                'guid' => route('blog.show', $b),
                'description' => (string) ($b->excerpt ?: substr(strip_tags((string) $b->body), 0, 250).'...'),
                'pubDate' => $b->published_at?->toRssString() ?? now()->toRssString(),
                'category' => $b->category?->name ?? 'Blog',
                'image' => $b->heroMedia?->publicUrl(),
            ]))
            ->sortByDesc('pubDate')
            ->values();

        $lastBuildDate = $items->first()['pubDate'] ?? now()->toRssString();

        $xml = view('feed', compact('items', 'lastBuildDate'))->render();

        return response($xml, 200, [
            'Content-Type' => 'application/rss+xml; charset=UTF-8',
            'Cache-Control' => 'public, max-age=3600',
        ]);
    }
}
