<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Models\BlogCategory;
use App\Models\BlogPost;
use App\Models\BlogTag;
use App\Services\Media\PublicMediaEligibilityService;
use App\Services\Seo\BlogIndexability;
use App\Services\Coni\ConiOdakKumesi;
use App\Services\Seo\InternalLinkService;
use App\Services\Seo\SeoIndexabilityService;
use App\Services\Seo\SeoMetadataService;
use App\Services\Seo\SeoSchemaService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

final class BlogController extends Controller
{
    public function index(Request $request, SeoIndexabilityService $indexability, SeoSchemaService $schema): View
    {
        // Çevirisi olmayan yazı o dilin sitesinde yoktur. Listelenseydi
        // /en/blog altında Türkçe başlıklar çıkardı; Google sayfanın dilini
        // görünen içerikten belirler ve bu aynı içeriğin ikinci kopyası
        // olurdu. Liste, sitemap ve hreflang aynı tanımı paylaşmak zorunda.
        $posts = BlogPost::query()->publiclyVisible()->translatedInto()->with(['category', 'tags', 'heroMedia', 'media'])
            ->when($request->filled('q'), fn ($query) => $query->where(fn ($nested) => $nested->where('title', 'like', '%'.$request->string('q')->toString().'%')->orWhere('excerpt', 'like', '%'.$request->string('q')->toString().'%')))
            ->latest('published_at')->paginate(12)->withPath(route('blog.index'))->withQueryString();
        abort_if($posts->currentPage() > $posts->lastPage(), 404);

        $description = __('blog.index.meta_description');

        return view('blog.index', [
            'posts' => $posts,
            'title' => 'Blog · House Chef Cyprus',
            'seoIndexability' => $indexability->forRequest($request, route('blog.index')),
            'seoSchema' => $schema->blogCollectionPage(__('blog.index.title'), $description, route('blog.index'), $posts->items(), [
                ['name' => __('nav.home'), 'url' => route('home')],
                ['name' => 'Blog', 'url' => route('blog.index')],
            ]),
        ]);
    }

    public function show(BlogPost $post, SeoMetadataService $seo, SeoSchemaService $schemaService, PublicMediaEligibilityService $mediaEligibility, BlogIndexability $indekslenebilirlik, InternalLinkService $internalLinks): View|RedirectResponse
    {
        if (! BlogPost::query()->publiclyVisible()->whereKey($post->id)->exists() && ! request()->user()?->isAdmin()) {
            $slugStr = (string) $post->slug;
            $redirect = \App\Models\SlugRedirect::query()
                ->where(function ($q) use ($slugStr) {
                    $q->where('old_slug', $slugStr)
                        ->orWhere('old_slug', rawurldecode($slugStr));
                })
                ->first();

            if ($redirect !== null) {
                if ($redirect->model_type === \App\Models\Recipe::class) {
                    $recipeTarget = \App\Models\Recipe::query()->where('status', 'published')->where('verification_status', 'verified')->find($redirect->model_id);
                    if ($recipeTarget !== null) {
                        return redirect()->to(route('recipes.show', $recipeTarget->slug), 301);
                    }
                } else {
                    $target = BlogPost::query()->publiclyVisible()->find($redirect->model_id);
                    if ($target !== null) {
                        return redirect()->to(route('blog.show', $target), 301);
                    }
                }
            }

            $rootSlug = (string) preg_replace('/-(?:taslak-inceleme-)?[A-Za-z0-9]{5}$/', '', $slugStr);
            if ($rootSlug !== '' && $rootSlug !== $slugStr) {
                $twin = BlogPost::query()->publiclyVisible()
                    ->where(fn ($q) => $q->where('slug', $rootSlug)->orWhere('slug', 'like', $rootSlug . '-%'))
                    ->first();
                if ($twin !== null) {
                    return redirect()->to(route('blog.show', $twin), 301);
                }
            }

            $twin = BlogPost::query()->publiclyVisible()
                ->where('title', $post->title)
                ->where('id', '!=', $post->id)
                ->first();

            if ($twin !== null) {
                return redirect()->to(route('blog.show', $twin), 301);
            }

            $rootSlug = (string) preg_replace('/-(?:taslak-inceleme-)?[A-Za-z0-9]{5}$/', '', $slugStr);
            if ($rootSlug !== '' && $rootSlug !== $slugStr) {
                $candidate = BlogPost::query()->publiclyVisible()
                    ->where(fn ($q) => $q->where('slug', $rootSlug)->orWhere('slug', 'like', $rootSlug.'-%'))
                    ->first();

                if ($candidate !== null) {
                    return redirect()->to(route('blog.show', $candidate), 301);
                }
            }

            abort(404);
        }
        // Aynı kural detayda da geçerli: çevirisi olmayan yazının o dildeki
        // adresi yoktur. Sessizce Türkçe gövde servis etmek, hreflang'in
        // gösterdiği adresin başka dilde içerik döndürmesi demekti.
        if (! $post->hasTranslation() && ! request()->user()?->isAdmin()) {
            $slugStr = (string) $post->slug;
            $redirect = \App\Models\SlugRedirect::query()
                ->where(function ($q) use ($slugStr) {
                    $q->where('old_slug', $slugStr)
                        ->orWhere('old_slug', rawurldecode($slugStr));
                })
                ->first();

            if ($redirect !== null) {
                if ($redirect->model_type === \App\Models\Recipe::class) {
                    $recipeTarget = \App\Models\Recipe::query()->where('status', 'published')->where('verification_status', 'verified')->find($redirect->model_id);
                    if ($recipeTarget !== null) {
                        return redirect()->to(route('recipes.show', $recipeTarget->slug), 301);
                    }
                } else {
                    $target = BlogPost::query()->publiclyVisible()->find($redirect->model_id);
                    if ($target !== null) {
                        return redirect()->to(route('blog.show', $target), 301);
                    }
                }
            }

            abort(404);
        }
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
                ->translatedInto()
                ->with(['media' => fn ($media) => $media->where('visibility', 'public')]),
        ]);
        $relatedPosts = BlogPost::query()->publiclyVisible()->translatedInto()
            ->whereKeyNot($post->id)
            ->when($post->blog_category_id !== null, fn ($query) => $query->where('blog_category_id', $post->blog_category_id))
            ->with(['category', 'heroMedia', 'media'])
            ->latest('published_at')
            ->limit(3)
            ->get();
        // Claude Coni'nin bu dildeki odak yazısı konu komşusuysa öne alınır.
        $relatedPosts = app(ConiOdakKumesi::class)->yazilaraEkle($relatedPosts, $post, app()->getLocale(), 3, ['category', 'heroMedia', 'media']);

        $heroMedia = $mediaEligibility->heroOrFirstEligible($post->heroMedia, $post->media);
        $sosyalGorsel = $heroMedia?->socialImage();
        // Paylaşım ve şema görseli 1200x630 varyantını kullanır, ham kaynağı değil.
        $schema = $schemaService->article($post, $sosyalGorsel['url'] ?? '');

        $metadata = $seo->for($post, $post->meta_title ?: $post->title.' · House Chef Cyprus', $post->meta_description ?: $post->excerpt, route('blog.show', $post));

        // Editoryal kapılar kayıtta vardı ama indekslemeye etkisi yoktu:
        // olgu denetimi ve özgünlüğü "pending" olan bir yazı da index,follow
        // olarak dizine giriyordu. Yazı yayında kalıyor, yalnız kendi
        // kapılarından geçene kadar aranabilir olmuyor.
        $metadata['robots'] = $indekslenebilirlik->robots($post);

        $suggestedRecipes = $post->recipes->isNotEmpty()
            ? $post->recipes
            : $internalLinks->recommendedRecipesFor($post, app()->getLocale());

        return view('blog.show', compact('post', 'schema', 'metadata', 'relatedPosts', 'heroMedia', 'suggestedRecipes'));
    }

    public function category(BlogCategory $category): View
    {
        $posts = $category->posts()->publiclyVisible()->translatedInto()->with(['category', 'tags', 'heroMedia', 'media'])->latest('published_at')->paginate(12)->withPath(route('blog.category', $category));
        abort_if($posts->currentPage() > $posts->lastPage(), 404);

        return view('blog.index', ['posts' => $posts, 'title' => $category->name.' · Blog', 'seoIndexability' => ['canonical' => route('blog.category', $category), 'robots' => request()->query() === [] ? 'index,follow' : 'noindex,follow']]);
    }

    public function tag(BlogTag $tag): View
    {
        $posts = $tag->posts()->publiclyVisible()->translatedInto()->with(['category', 'tags', 'heroMedia', 'media'])->latest('published_at')->paginate(12)->withPath(route('blog.tag', $tag));
        abort_if($posts->currentPage() > $posts->lastPage(), 404);

        return view('blog.index', ['posts' => $posts, 'title' => '#'.$tag->name.' · Blog', 'seoIndexability' => ['canonical' => route('blog.tag', $tag), 'robots' => request()->query() === [] ? 'index,follow' : 'noindex,follow']]);
    }
}
