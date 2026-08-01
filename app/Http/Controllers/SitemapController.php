<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Models\BlogPost;
use App\Models\Media;
use App\Models\Recipe;
use Illuminate\Http\Response;

final class SitemapController extends Controller
{
    public function index(): Response
    {
        $sitemaps = collect([
            ['loc' => route('sitemap.pages'), 'lastmod' => now()->toDateString()],
            ['loc' => route('sitemap.recipes'), 'lastmod' => $this->date(Recipe::query()->where('status', 'published')->where('verification_status', 'verified')->max('updated_at'))],
            ['loc' => route('sitemap.blog'), 'lastmod' => $this->date(BlogPost::query()->publiclyVisible()->max('updated_at'))],
            ['loc' => route('sitemap.images'), 'lastmod' => $this->date(Media::query()->where('visibility', 'public')->max('updated_at'))],
        ]);

        return response()->view('sitemap-index', compact('sitemaps'))->header('Content-Type', 'application/xml; charset=UTF-8');
    }

    public function pages(): Response
    {
        $staticPaths = ['', 'tarifler', 'blog', 'hakkimizda', 'nasil-calisir', 'sss', 'editorial-ilkeler', 'tarif-dogrulama-politikasi', 'kaynak-ve-veri-guven-politikasi', 'besin-degeri-aciklamasi', 'alerjen-uyarisi', 'gorsel-ve-medya-politikasi', 'icerik-duzeltme-politikasi', 'hata-bildir', 'gizlilik', 'kullanim-kosullari', 'cerez-politikasi', 'iletisim'];
        $urls = collect($staticPaths)->map(fn (string $path): array => ['loc' => url($path), 'lastmod' => null]);

        return $this->urls($urls);
    }

    public function recipes(): Response
    {
        $urls = Recipe::query()->where('status', 'published')->where('verification_status', 'verified')->orderBy('updated_at')->get(['slug', 'updated_at'])
            ->map(fn (Recipe $recipe): array => ['loc' => route('recipes.show', $recipe->slug), 'lastmod' => $recipe->updated_at?->toDateString()]);

        return $this->urls($urls);
    }

    public function blog(): Response
    {
        $urls = BlogPost::query()->publiclyVisible()->orderBy('updated_at')->get(['slug', 'updated_at'])
            ->map(fn (BlogPost $post): array => ['loc' => route('blog.show', $post), 'lastmod' => $post->updated_at?->toDateString()]);

        return $this->urls($urls);
    }

    public function images(): Response
    {
        $urls = Recipe::query()->where('status', 'published')->where('verification_status', 'verified')
            ->with(['media' => fn ($query) => $query->where('visibility', 'public')->where('verification_status', 'verified')->orderBy('sort_order')])
            ->get(['id', 'slug', 'updated_at'])
            ->filter(fn (Recipe $recipe): bool => $recipe->media->isNotEmpty())
            ->map(fn (Recipe $recipe): array => ['loc' => route('recipes.show', $recipe->slug), 'lastmod' => $recipe->updated_at?->toDateString(), 'images' => $recipe->media->map(fn (Media $media): array => ['loc' => $media->publicUrl(), 'title' => $media->alt_text ?: $recipe->slug])->filter(fn (array $image): bool => is_string($image['loc']) && $image['loc'] !== '')->values()->all()]);

        return response()->view('image-sitemap', compact('urls'))->header('Content-Type', 'application/xml; charset=UTF-8');
    }

    private function urls(iterable $urls): Response
    {
        return response()->view('sitemap', compact('urls'))->header('Content-Type', 'application/xml; charset=UTF-8');
    }

    private function date(mixed $value): ?string
    {
        return is_string($value) && $value !== '' ? now()->parse($value)->toDateString() : null;
    }
}
