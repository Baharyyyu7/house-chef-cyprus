<?php

declare(strict_types=1);

namespace App\Http\Controllers\Localized;

use App\Http\Controllers\Controller;
use App\Models\BlogPostTranslation;
use App\Models\RecipeTranslation;
use App\Services\Localization\LanguageAlternates;
use App\Services\Seo\SeoSchemaService;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\View\View;

final class LocalizedBlogController extends Controller
{
    public function index(Request $request, LanguageAlternates $alternates): View
    {
        $locale = app()->getLocale();
        $posts = BlogPostTranslation::query()->publiclyVisible($locale)
            ->with(['post' => fn ($query) => $query->with('heroMedia')])
            ->join('blog_posts', 'blog_posts.id', '=', 'blog_post_translations.blog_post_id')
            ->orderByDesc('blog_posts.published_at')
            ->select('blog_post_translations.*')
            ->paginate(12)
            ->withQueryString();

        return view('localized.'.$locale.'.blog.index', [
            'posts' => $posts,
            'metadata' => $this->metadata($request, 'Cyprus Food & Kitchen Guides · House Chef Cyprus Blog', 'Guides to Cypriot and Mediterranean home cooking: halloumi, olive oil, meze, pantry planning, food safety and seasonal eating, written for English-speaking cooks.', route($locale.'.blog.index')),
            'alternates' => $request->query() === [] ? $alternates->forRequest($request) : [],
        ]);
    }

    public function show(Request $request, string $slug, LanguageAlternates $alternates, SeoSchemaService $schemaService): View
    {
        $locale = app()->getLocale();
        $translation = BlogPostTranslation::query()->publiclyVisible($locale)->where('slug', $slug)
            ->with(['post' => fn ($query) => $query->with(['heroMedia', 'recipes' => fn ($recipes) => $recipes->where('status', 'published')->where('verification_status', 'verified')])])
            ->firstOrFail();
        $post = $translation->post;
        abort_if($post === null, 404);

        $hero = $post->heroMedia?->isApprovedRealMedia() ? $post->heroMedia : null;
        $relatedRecipes = RecipeTranslation::query()->publiclyVisible($locale)
            ->whereIn('recipe_id', $post->recipes->pluck('id'))
            ->with(['recipe' => fn ($query) => $query->with(['media' => fn ($media) => $media->where('visibility', 'public')])])
            ->get();
        $relatedPosts = BlogPostTranslation::query()->publiclyVisible($locale)->whereKeyNot($translation->id)
            ->when($translation->category_label !== null, fn ($query) => $query->orderByRaw('case when category_label = ? then 0 else 1 end', [(string) $translation->category_label]))
            ->limit(3)->get();

        return view('localized.'.$locale.'.blog.show', [
            'translation' => $translation,
            'post' => $post,
            'hero' => $hero,
            'relatedRecipes' => $relatedRecipes,
            'relatedPosts' => $relatedPosts,
            'schema' => $schemaService->localizedArticle($translation, $hero ? ($hero->responsiveUrl(1200) ?: ($hero->publicUrl() ?: '')) : ''),
            'metadata' => $this->metadata($request, $translation->meta_title ?: $translation->title.' · House Chef Cyprus', $translation->meta_description ?: $translation->excerpt, route($locale.'.blog.show', $translation->slug)),
            'alternates' => $alternates->forPostTranslation($translation),
        ]);
    }

    /** @return array{title:string,description:string,canonical:string,robots:string} */
    private function metadata(Request $request, string $title, ?string $description, string $canonical): array
    {
        return [
            'title' => Str::limit($title, 70, ''),
            'description' => Str::limit((string) $description, 160, ''),
            'canonical' => $canonical,
            'robots' => ! config('seo.indexing_enabled') ? 'noindex,nofollow' : ($request->query() === [] ? 'index,follow' : 'noindex,follow'),
        ];
    }
}
