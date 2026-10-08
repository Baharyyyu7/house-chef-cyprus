<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Models\BlogPost;
use App\Models\Recipe;
use App\Services\Localization\CulinaryTranslationService;
use App\Services\Seo\IndexableContent;
use App\Services\Seo\LocaleUrlService;
use Illuminate\Http\Response;

final class RssFeedController extends Controller
{
    public function __construct(
        private readonly CulinaryTranslationService $translator
    ) {
    }

    public function index(): Response
    {
        return $this->renderFeed('tr');
    }

    public function en(): Response
    {
        return $this->renderFeed('en');
    }

    private function renderFeed(string $locale): Response
    {
        $isEn = $locale === 'en';

        // Site haritasıyla aynı süzgeç (8 Ekim 2026 denetimi): besleme
        // noindex bir nüsha tarifi ve 404 adresleri yayıyordu; İngilizce
        // besleme çevirisi olmayan tarifi Türkçe adresle veriyordu.
        $indeks = app(IndexableContent::class);
        $adresler = app(LocaleUrlService::class);
        $adres = fn (string $rota, Recipe|BlogPost $kayit): string => $adresler->urlFor($locale, route($rota, $kayit, false));

        $recipes = $indeks->tarifler($locale)
            ->with(['cuisine', 'category', 'media' => fn ($q) => $q->where('visibility', 'public')->orderBy('sort_order')])
            ->latest('published_at')
            ->limit(30)
            ->get()
            ->filter(fn (Recipe $r): bool => $indeks->izinVerir($r, $locale));

        $blogs = $indeks->yazilar($locale)
            ->with(['category', 'heroMedia'])
            ->latest('published_at')
            ->limit(15)
            ->get()
            ->filter(fn (BlogPost $b): bool => $indeks->izinVerir($b, $locale));

        $items = collect()
            ->concat($recipes->map(fn (Recipe $r): array => [
                'title' => $this->translator->translateRecipeTitle($r, $locale),
                'link' => $adres('recipes.show', $r),
                'guid' => $adres('recipes.show', $r),
                'description' => $this->translator->translateRecipeDescription($r, $locale),
                'pubDate' => $r->published_at?->toRssString() ?? now()->toRssString(),
                'category' => $isEn
                    ? $this->translator->translateCategory($r->category?->slug, 'en')
                    : ($r->cuisine?->name ?? $r->category?->name ?? 'Tarif'),
                'image' => $r->media->first()?->publicUrl(),
            ]))
            ->concat($blogs->map(fn (BlogPost $b): array => [
                'title' => $this->translator->translateBlogTitle($b, $locale),
                'link' => $adres('blog.show', $b),
                'guid' => $adres('blog.show', $b),
                'description' => $this->translator->translateBlogDescription($b, $locale),
                'pubDate' => $b->published_at?->toRssString() ?? now()->toRssString(),
                'category' => $isEn ? 'Gastronomy & Culinary Guides' : ($b->category?->name ?? 'Blog'),
                'image' => $b->heroMedia?->publicUrl(),
            ]))
            ->sortByDesc('pubDate')
            ->values();

        $lastBuildDate = $items->first()['pubDate'] ?? now()->toRssString();

        $channelTitle = $isEn
            ? 'House Chef Cyprus - Mediterranean & Cypriot Recipes'
            : 'House Chef Cyprus';

        $channelDescription = $isEn
            ? 'Verified Mediterranean and Cypriot home recipes, traditional cooking techniques, and gastronomy guides.'
            : 'Doğrulanmış Kıbrıs ve Akdeniz yemek tarifleri, mutfak rehberleri ve gastronomi yazıları.';

        $language = $isEn ? 'en-US' : 'tr-CY';
        $feedUrl = $isEn ? route('feed.en') : route('feed');

        $xml = view('feed', compact('items', 'lastBuildDate', 'channelTitle', 'channelDescription', 'language', 'feedUrl'))->render();

        return response($xml, 200, [
            'Content-Type' => 'application/rss+xml; charset=UTF-8',
            'Cache-Control' => 'public, max-age=3600',
        ]);
    }
}
