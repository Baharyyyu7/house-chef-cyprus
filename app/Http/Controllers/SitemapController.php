<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Models\BlogPost;
use App\Models\Media;
use App\Models\Recipe;
use App\Services\Media\PublicMediaEligibilityService;
use App\Services\Seo\BlogIndexability;
use App\Services\Seo\LocaleUrlService;
use Illuminate\Database\Eloquent\Builder;
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
        ]);

        return response()->view('sitemap-index', compact('sitemaps'))->header('Content-Type', 'application/xml; charset=UTF-8');
    }

    public function pages(): Response
    {
        $ingredients = IngredientHubController::sayfasiOlanlar();
        $cuisines = CuisineHubController::sayfasiOlanlar();
        $categories = CategoryHubController::sayfasiOlanlar();
        $latestRecipe = $this->date(Recipe::query()->where('status', 'published')->where('verification_status', 'verified')->max('published_at'));
        $hubLastmods = [
            '' => $latestRecipe,
            'tarifler' => $latestRecipe,
            'blog' => $this->date(BlogPost::query()->publiclyVisible()->max('published_at')),
            'malzemeler' => $this->date($ingredients->max('latest_published_at')),
            'mutfaklar' => $this->date($cuisines->max('latest_published_at')),
            'kategoriler' => $this->date($categories->max('latest_published_at')),
        ];

        // ne-pisirebilirim üyelik duvarının arkasından çıkarıldı; indekslenebilir bir araç sayfasıdır.
        $staticPaths = ['', 'tarifler', 'blog', 'malzemeler', 'mutfaklar', 'kategoriler', 'ne-pisirebilirim', 'hakkimizda', 'nasil-calisir', 'sss', 'editorial-ilkeler', 'tarif-dogrulama-politikasi', 'besin-degeri-aciklamasi', 'alerjen-uyarisi', 'gorsel-ve-medya-politikasi', 'icerik-duzeltme-politikasi', 'hata-bildir', 'yasal-bilgilendirme', 'iletisim'];
        $urls = collect($staticPaths)->map(fn (string $path): array => ['loc' => url($path), 'lastmod' => $hubLastmods[$path] ?? null]);

        // Malzeme sayfaları. Liste ile sitemap aynı eşiği paylaşmak zorunda:
        // ayrışırsa sitemap 404 veren adresleri ilan eder ve tarama bütçesi
        // boşa gider.
        $urls = $urls->concat(
            $ingredients
                ->map(fn ($malzeme): array => [
                    'loc' => route('ingredients.hub.show', $malzeme->slug),
                    'lastmod' => $this->date($malzeme->latest_published_at),
                ])
        );

        // Mutfak sayfalari ayni esigi paylasiyor.
        $urls = $urls->concat(
            $cuisines
                ->map(fn ($mutfak): array => [
                    'loc' => route('cuisines.show', $mutfak->slug),
                    'lastmod' => $this->date($mutfak->latest_published_at),
                ])
        );

        $urls = $urls->concat(
            $categories->map(fn ($kategori): array => [
                'loc' => route('categories.show', $kategori->slug),
                'lastmod' => $this->date($kategori->latest_published_at),
            ])
        );

        $koleksiyonlar = RecipeCollectionLandingController::sayfasiOlanlar();

        $urls = $urls->concat(
            $koleksiyonlar->map(fn (array $koleksiyon): array => [
                'loc' => $koleksiyon['url'],
                'lastmod' => $this->date($koleksiyon['lastmod'] ?? null),
            ])
        );

        // Ust sayfalar yalniz alt sayfasi olan tur icin var; bos ust sayfa
        // yayinlanmiyor, dolayisiyla haritada da yer almamali.
        $urls = $urls->concat(
            collect(['sure', 'zorluk'])
                ->filter(fn (string $tur): bool => $koleksiyonlar->where('type', $tur)->isNotEmpty())
                ->map(fn (string $tur): array => [
                    'loc' => route('collections.'.$tur.'.index'),
                    'lastmod' => $this->date($koleksiyonlar->where('type', $tur)->max('lastmod')),
                ])
                ->values()
        );

        return $this->urls($urls);
    }

    public function recipes(): Response
    {
        $mediaEligibility = app(PublicMediaEligibilityService::class);
        $urls = $this->indexable(Recipe::query()->where('status', 'published')->where('verification_status', 'verified'))
            ->with(['media' => fn ($query) => $query->where('visibility', 'public')->orderBy('sort_order')])
            ->orderBy('updated_at')->get(['id', 'slug', 'updated_at'])
            ->filter(fn (Recipe $recipe): bool => $mediaEligibility->firstEligible($recipe->media) !== null)
            ->map(fn (Recipe $recipe): array => [
                'loc' => route('recipes.show', $recipe->slug),
                'lastmod' => $recipe->updated_at?->toDateString(),
                'alternates' => $this->alternates('/tarifler/'.$recipe->slug, $recipe),
            ]);

        return $this->urls($urls);
    }

    public function blog(): Response
    {
        // Dizine girmeyen yazı site haritasında durmamalı: harita "bunları
        // indeksle" demek, sayfanın kendisi "indeksleme" diyor. İkisi
        // çelişince tarayıcıya karışık sinyal gidiyor.
        $indekslenebilirlik = app(BlogIndexability::class);

        $urls = $this->indexable(BlogPost::query()->publiclyVisible())
            ->orderBy('updated_at')->get(['id', 'slug', 'updated_at', 'body', 'fact_check_status', 'originality_status'])
            ->filter(fn (BlogPost $post): bool => $indekslenebilirlik->indekslenebilir($post))
            ->map(fn (BlogPost $post): array => [
                'loc' => route('blog.show', $post),
                'lastmod' => $post->updated_at?->toDateString(),
                'alternates' => $this->alternates('/blog/'.$post->slug, $post),
            ]);

        return $this->urls($urls);
    }

    /**
     * Bir kaydın dil alternatifleri.
     *
     * Kaydın çevirisi olmayan dil kümeye girmez. Girseydi sitemap var olmayan
     * bir adresi ilan ederdi; hedefi 404 olan hreflang çiftini Google atar ve
     * kümenin tamamı geçersiz olur. Sitemap, listeleme ve HTML head'deki
     * hreflang aynı "bu dilde var mı" tanımını paylaşmak zorunda.
     *
     * @return list<array{hreflang: string, href: string}>
     */
    private function alternates(string $path, object $kayit): array
    {
        $servis = app(LocaleUrlService::class);
        $diller = array_filter(
            $servis->enabledLocales(),
            static fn (array $dil, string $kod): bool => $kayit->hasTranslation($kod),
            ARRAY_FILTER_USE_BOTH
        );

        if (count($diller) < 2) {
            return [];
        }

        $kume = [];

        foreach ($diller as $kod => $dil) {
            $kume[] = ['hreflang' => (string) ($dil['html_locale'] ?? $kod), 'href' => $servis->urlFor($kod, $path)];
        }

        $kume[] = ['hreflang' => 'x-default', 'href' => $servis->urlFor($servis->defaultLocale(), $path)];

        return $kume;
    }

    /**
     * Sitemap "bu sayfayı indeksle" demektir. Yayındaki 678 tarifin 450'si
     * seoMetadata.robots alanında noindex taşıyor -- inceleme beklediği için,
     * ki bu doğru -- ama hepsi sitemap'e giriyordu. Google'a aynı sayfa için
     * iki karşıt talimat vermek Search Console'da "gönderilen URL noindex
     * işaretli" hatası üretir ve sitenin geri kalanına olan güveni düşürür.
     *
     * @template TModel of \Illuminate\Database\Eloquent\Model
     *
     * @param  Builder<TModel>  $query
     * @return Builder<TModel>
     */
    private function indexable($query)
    {
        return $query->whereDoesntHave('seoMetadata', fn ($q) => $q->where('robots', 'like', '%noindex%'));
    }

    public function images(PublicMediaEligibilityService $mediaEligibility): Response
    {
        $urls = $this->indexable(Recipe::query()->where('status', 'published')->where('verification_status', 'verified'))
            ->with(['media' => fn ($query) => $query->where('visibility', 'public')->where('verification_status', 'verified')->where('media_status', 'approved_real_media')->where('license_review_status', 'approved')->where('attribution_review_status', 'approved')->orderBy('sort_order')])
            ->get(['id', 'slug', 'updated_at'])
            ->map(function (Recipe $recipe) use ($mediaEligibility): ?array {
                $images = $recipe->media->filter(fn (Media $media): bool => $mediaEligibility->isEligible($media))
                    ->map(fn (Media $media): array => ['loc' => $media->publicUrl(), 'title' => $media->alt_text ?: $recipe->slug])
                    ->filter(fn (array $image): bool => is_string($image['loc']) && $image['loc'] !== '')->values()->all();

                return $images === [] ? null : ['loc' => route('recipes.show', $recipe->slug), 'lastmod' => $recipe->updated_at?->toDateString(), 'images' => $images];
            })->filter();

        $blogUrls = $this->indexable(BlogPost::query()->publiclyVisible())->with('heroMedia')->get()
            ->filter(fn (BlogPost $post): bool => $mediaEligibility->isEligible($post->heroMedia) && $post->heroMedia?->publicUrl() !== null)
            ->map(fn (BlogPost $post): array => ['loc' => route('blog.show', $post), 'lastmod' => $post->updated_at?->toDateString(), 'images' => [['loc' => $post->heroMedia->responsiveUrl(1200) ?: $post->heroMedia->publicUrl(), 'title' => $post->heroMedia->alt_text ?: $post->title]]]);
        $urls = $urls->concat($blogUrls)->values();

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
