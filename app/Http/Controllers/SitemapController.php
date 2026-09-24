<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Models\BlogPost;
use App\Models\BlogPostTranslation;
use App\Models\Media;
use App\Models\Recipe;
use App\Models\RecipeTranslation;
use Illuminate\Http\Response;

final class SitemapController extends Controller
{
    public function index(): Response
    {
        $sitemaps = collect([
            // Static pages have no trustworthy per-page update timestamp.
            // Omitting lastmod is preferable to publishing a false daily change.
            ['loc' => route('sitemap.pages'), 'lastmod' => null],
            ['loc' => route('sitemap.recipes'), 'lastmod' => $this->date(Recipe::query()->where('status', 'published')->where('verification_status', 'verified')->max('updated_at'))],
            ['loc' => route('sitemap.blog'), 'lastmod' => $this->date(BlogPost::query()->publiclyVisible()->max('updated_at'))],
            ['loc' => route('sitemap.images'), 'lastmod' => $this->date(Media::query()->where('visibility', 'public')->max('updated_at'))],
            ['loc' => route('sitemap.en'), 'lastmod' => $this->date(collect([RecipeTranslation::query()->publiclyVisible('en')->max('updated_at'), BlogPostTranslation::query()->publiclyVisible('en')->max('updated_at')])->filter()->max())],
        ]);

        return response()->view('sitemap-index', compact('sitemaps'))->header('Content-Type', 'application/xml; charset=UTF-8');
    }

    public function pages(): Response
    {
        $staticPaths = ['', 'tarifler', 'blog', 'hakkimizda', 'nasil-calisir', 'sss', 'editorial-ilkeler', 'tarif-dogrulama-politikasi', 'besin-degeri-aciklamasi', 'alerjen-uyarisi', 'gorsel-ve-medya-politikasi', 'icerik-duzeltme-politikasi', 'hata-bildir', 'yasal-bilgilendirme', 'iletisim'];
        $english = ['' => route('en.home'), 'tarifler' => route('en.recipes.index'), 'blog' => route('en.blog.index'), 'hakkimizda' => route('en.about')];
        $urls = collect($staticPaths)->map(fn (string $path): array => ['loc' => url($path), 'lastmod' => null, 'alternates' => isset($english[$path]) ? $this->alternates(url($path), $english[$path]) : []]);

        return $this->urls($urls);
    }

    public function recipes(): Response
    {
        $english = RecipeTranslation::query()->publiclyVisible('en')->pluck('slug', 'recipe_id');
        $urls = Recipe::query()->where('status', 'published')->where('verification_status', 'verified')->orderBy('updated_at')->get(['id', 'slug', 'updated_at'])
            ->map(fn (Recipe $recipe): array => ['loc' => route('recipes.show', $recipe->slug), 'lastmod' => $recipe->updated_at?->toDateString(), 'alternates' => isset($english[$recipe->id]) ? $this->alternates(route('recipes.show', $recipe->slug), route('en.recipes.show', $english[$recipe->id])) : []]);

        return $this->urls($urls);
    }

    public function blog(): Response
    {
        $english = BlogPostTranslation::query()->publiclyVisible('en')->pluck('slug', 'blog_post_id');
        $urls = BlogPost::query()->publiclyVisible()->orderBy('updated_at')->get(['id', 'slug', 'updated_at'])
            ->map(fn (BlogPost $post): array => ['loc' => route('blog.show', $post), 'lastmod' => $post->updated_at?->toDateString(), 'alternates' => isset($english[$post->id]) ? $this->alternates(route('blog.show', $post), route('en.blog.show', $english[$post->id])) : []]);

        return $this->urls($urls);
    }

    public function images(): Response
    {
        $urls = Recipe::query()->where('status', 'published')->where('verification_status', 'verified')
            ->with(['media' => fn ($query) => $query->where('visibility', 'public')->where('verification_status', 'verified')->where('media_status', 'approved_real_media')->where('license_review_status', 'approved')->where('attribution_review_status', 'approved')->orderBy('sort_order')])
            ->get(['id', 'slug', 'updated_at'])
            ->filter(fn (Recipe $recipe): bool => $recipe->media->isNotEmpty())
            ->map(fn (Recipe $recipe): array => ['loc' => route('recipes.show', $recipe->slug), 'lastmod' => $recipe->updated_at?->toDateString(), 'images' => $recipe->media->map(fn (Media $media): array => ['loc' => $media->publicUrl(), 'title' => $media->alt_text ?: $recipe->slug])->filter(fn (array $image): bool => is_string($image['loc']) && $image['loc'] !== '')->values()->all()]);

        $blogUrls = BlogPost::query()->publiclyVisible()->with('heroMedia')->get()
            ->filter(fn (BlogPost $post): bool => $post->heroMedia?->isApprovedRealMedia() && $post->heroMedia->publicUrl() !== null)
            ->map(fn (BlogPost $post): array => ['loc' => route('blog.show', $post), 'lastmod' => $post->updated_at?->toDateString(), 'images' => [['loc' => $post->heroMedia->responsiveUrl(1200) ?: $post->heroMedia->publicUrl(), 'title' => $post->heroMedia->alt_text ?: $post->title]]]);
        $urls = $urls->concat($blogUrls)->values();

        return response()->view('image-sitemap', compact('urls'))->header('Content-Type', 'application/xml; charset=UTF-8');
    }

    /** English edition: every URL carries reciprocal hreflang links to its Turkish original. */
    public function english(): Response
    {
        $pages = collect([
            [route('en.home'), url('/')],
            [route('en.recipes.index'), route('recipes.index')],
            [route('en.blog.index'), route('blog.index')],
            [route('en.about'), route('information.show', 'hakkimizda')],
        ])->map(fn (array $pair): array => ['loc' => $pair[0], 'lastmod' => null, 'alternates' => $this->alternates($pair[1], $pair[0])]);

        $recipes = RecipeTranslation::query()->publiclyVisible('en')->with('recipe:id,slug')->orderBy('updated_at')->get()
            ->map(fn (RecipeTranslation $translation): array => ['loc' => route('en.recipes.show', $translation->slug), 'lastmod' => $translation->updated_at?->toDateString(), 'alternates' => $this->alternates(route('recipes.show', (string) $translation->recipe?->slug), route('en.recipes.show', $translation->slug))]);

        $posts = BlogPostTranslation::query()->publiclyVisible('en')->with('post:id,slug')->orderBy('updated_at')->get()
            ->map(fn (BlogPostTranslation $translation): array => ['loc' => route('en.blog.show', $translation->slug), 'lastmod' => $translation->updated_at?->toDateString(), 'alternates' => $this->alternates(route('blog.show', (string) $translation->post?->slug), route('en.blog.show', $translation->slug))]);

        return $this->urls($pages->concat($recipes)->concat($posts)->values());
    }

    /** @return array<string,string> */
    private function alternates(string $turkish, string $english): array
    {
        return ['tr' => $turkish, 'en' => $english, 'x-default' => config('localization.x_default') === 'tr' ? $turkish : $english];
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
