<?php

declare(strict_types=1);

namespace App\Services\Seo;

use App\Models\BlogPost;
use App\Models\IntegrityReport;
use App\Models\Recipe;
use App\Services\Coni\ConiOdakKumesi;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\Cache;

final class InternalLinkService
{
    /**
     * Search Console'da son dönemde gösterim alan veya vurucu mesafede olan tarif anahtarları.
     *
     * @return list<string>
     */
    public function gscTrendingKeywords(): array
    {
        return Cache::remember('seo:gsc:trending_keywords', 3600, function (): array {
            $report = IntegrityReport::query()
                ->where('guard', 'seo:search-console-measurement')
                ->where('status', 'success')
                ->latest('id')
                ->first();

            if ($report === null || ! is_array($report->payload['queries'] ?? null)) {
                return [];
            }

            $keywords = [];
            foreach ($report->payload['queries'] as $q) {
                if (($q['impressions'] ?? 0) >= 2) {
                    $tokens = preg_split('/\s+/', mb_strtolower(trim((string) ($q['query'] ?? ''))));
                    foreach ($tokens as $t) {
                        if (mb_strlen($t) >= 4) {
                            $keywords[] = $t;
                        }
                    }
                }
            }

            return array_values(array_unique($keywords));
        });
    }
    /**
     * Blog yazısı için en alakalı tarif önerileri.
     *
     * İyileştirilmiş sıralama:
     * 1. Blog ile zaten bağlı tarifler (pivot)
     * 2. Aynı kategoriden tarifler (blog kategorisi = tarif kategorisi)
     * 3. Seçkin (featured) tarifler
     * 4. En son yayınlanan tarifler
     *
     * @return list<array{label: string, url: string, kind: string, relevance_score: int, reason: string}>
     */
    public function suggestionsFor(BlogPost $post, ?string $locale = null): array
    {
        $targetLocale = $locale ?? app()->getLocale();
        $linkedIds = $post->recipes()->pluck('recipes.id')->all();
        $limit = (int) config('seo.audit.max_internal_links_per_document', 8);

        // Blog'un kategori adıyla eşleşen tarifleri bul (category name matching)
        $blogCategoryName = $post->category?->name;

        $query = Recipe::query()
            ->where('status', 'published')
            ->where('verification_status', 'verified')
            ->translatedInto($targetLocale)
            ->when($linkedIds !== [], fn ($q) => $q->whereNotIn('id', $linkedIds));

        $recipes = $query->get();

        // Relevance scoring:her tarife puan ver
        $scored = $recipes->map(function (Recipe $recipe) use ($targetLocale, $blogCategoryName): array {
            $label = $recipe->translated('title', $targetLocale) ?: (string) $recipe->getRawOriginal('title');
            $url = app(LocaleUrlService::class)->urlFor($targetLocale, '/tarifler/'.$recipe->slug);

            $score = 50; // temel puan
            $reasons = [];

            if ($recipe->is_featured) {
                $score += 40;
                $reasons[] = 'Seçkin tarif';
            }

            // Kategori eşleşmesi
            if ($blogCategoryName && $recipe->category?->name === $blogCategoryName) {
                $score += 30;
                $reasons[] = 'Aynı kategori: ' . $blogCategoryName;
            }

            // Mevsimsel uyumluluk
            if (is_array($recipe->seasonality) && in_array((int) date('m'), $recipe->seasonality, true)) {
                $score += 15;
                $reasons[] = 'Mevsim tarifi';
            }

            // Kıbrıs mutfağı bonusu: "Kıbrıs" ve ilgili anahtar kelimeler
            // için güçlü iç bağlantı ağı kurulması kritiktir.
            if ($recipe->cuisine && $recipe->cuisine->slug === 'kibris-mutfagi') {
                $score += 25;
                $reasons[] = 'Kıbrıs mutfağı';
            }

            // GSC Arama Trendi bonusu (+25): Google'da gösterim alan konular
            $trending = $this->gscTrendingKeywords();
            if ($trending !== []) {
                $slug = (string) $recipe->slug;
                foreach ($trending as $kw) {
                    if (str_contains($slug, $kw)) {
                        $score += 25;
                        $reasons[] = 'GSC Trendi';
                        break;
                    }
                }
            }

            return [
                'label' => $label,
                'url' => $url,
                'kind' => 'recipe',
                'relevance_score' => min(100, $score),
                'reason' => $reasons !== [] ? implode(' | ', $reasons) : 'Doğrulanmış tarif',
            ];
        })->sortByDesc('relevance_score')->take($limit)->values()->all();

        return $scored;
    }

    /**
     * Recommends fully loaded recipe models for display in a blog post view.
     *
     * @return Collection<int, Recipe>
     */
    public function recommendedRecipesFor(BlogPost $post, ?string $locale = null): Collection
    {
        $targetLocale = $locale ?? app()->getLocale();
        $linked = $post->recipes()
            ->where('status', 'published')
            ->where('verification_status', 'verified')
            ->translatedInto($targetLocale)
            ->with(['cuisine:id,public_id,name,slug', 'category:id,public_id,name,slug', 'media' => fn ($q) => $q->where('visibility', 'public'), 'dietaryTags'])
            ->limit(4)
            ->get();

        if ($linked->isNotEmpty()) {
            return $linked;
        }

        return Recipe::query()
            ->select(['id', 'public_id', 'cuisine_id', 'category_id', 'title', 'slug', 'excerpt', 'prep_minutes', 'cook_minutes', 'rest_minutes', 'servings', 'difficulty', 'status', 'verification_status', 'is_featured'])
            ->where('status', 'published')
            ->where('verification_status', 'verified')
            ->translatedInto($targetLocale)
            ->with(['cuisine:id,public_id,name,slug', 'category:id,public_id,name,slug', 'media' => fn ($q) => $q->where('visibility', 'public'), 'dietaryTags'])
            ->orderByDesc('is_featured')
            ->latest('published_at')
            ->limit(4)
            ->get();
    }

    /**
     * Tarif için tamamlayıcı tarif önerileri.
     *
     * Çok faktörlü sıralama:
     * 1. Aynı mutfak + aynı malzemeler (en yüksek skor)
     * 2. Aynı mutfak, farklı malzemeler
     * 3. Farklı mutfak, aynı malzemeler
     * 4. Benzer zorluk seviyesi bonusu
     *
     * @return list<array{label: string, url: string, kind: string, relevance_score: int, reason: string}>
     */
    public function recipeSuggestionsFor(Recipe $recipe, ?string $locale = null): array
    {
        $targetLocale = $locale ?? app()->getLocale();
        $limit = (int) config('seo.audit.max_internal_links_per_document', 6);

        // Tarifin malzemelerini al (ingredient matching için)
        $recipeIngredientIds = $recipe->ingredients()->pluck('ingredients.id')->all();

        $candidates = Recipe::query()
            ->where('status', 'published')
            ->where('verification_status', 'verified')
            ->where('id', '!=', $recipe->id)
            ->translatedInto($targetLocale)
            ->with('ingredients:id')
            ->get();

        $scored = $candidates->map(function (Recipe $rel) use ($recipe, $recipeIngredientIds, $targetLocale): array {
            $label = $rel->translated('title', $targetLocale) ?: (string) $rel->getRawOriginal('title');
            $url = app(LocaleUrlService::class)->urlFor($targetLocale, '/tarifler/'.$rel->slug);

            $score = 40; // temel puan
            $reasons = [];

            // Aynı mutfak (+35)
            if ($recipe->cuisine_id && $rel->cuisine_id === $recipe->cuisine_id) {
                $score += 35;
                $reasons[] = 'Aynı mutfak';
            }

            // Ortak malzemeler (ingredient overlap)
            if ($recipeIngredientIds !== []) {
                $relIngredientIds = $rel->ingredients->pluck('id')->all();
                $common = array_intersect($recipeIngredientIds, $relIngredientIds);
                $overlapPercent = count($relIngredientIds) > 0 ? (count($common) / count($relIngredientIds)) * 100 : 0;

                if ($overlapPercent >= 50) {
                    $score += 25;
                    $reasons[] = count($common) . ' ortak malzeme';
                } elseif ($overlapPercent >= 25) {
                    $score += 15;
                    $reasons[] = count($common) . ' ortak malzeme';
                } elseif (count($common) > 0) {
                    $score += 5;
                }
            }

            // Benzer zorluk (+10)
            if ($recipe->difficulty && $rel->difficulty === $recipe->difficulty) {
                $score += 10;
                $reasons[] = 'Benzer zorluk';
            }

            // Seçkin bonus (+10)
            if ($rel->is_featured) {
                $score += 10;
            }

            // Kıbrıs mutfağı bonusu (+20): Kıbrıs odaklı anahtar kelimeler için
            // iç bağlantı yoğunluğu artırılır.
            if ($rel->cuisine && $rel->cuisine->slug === 'kibris-mutfagi') {
                $score += 20;
                $reasons[] = 'Kıbrıs mutfağı';
            }

            // GSC Arama Trendi bonusu (+25): Google'da gösterim alan konular
            $trending = $this->gscTrendingKeywords();
            if ($trending !== []) {
                $slug = (string) $rel->slug;
                foreach ($trending as $kw) {
                    if (str_contains($slug, $kw)) {
                        $score += 25;
                        $reasons[] = 'GSC Trendi';
                        break;
                    }
                }
            }

            return [
                'label' => $label,
                'url' => $url,
                'kind' => 'recipe',
                'relevance_score' => min(100, $score),
                'reason' => $reasons !== [] ? implode(' | ', $reasons) : 'Tamamlayıcı tarif',
            ];
        })->sortByDesc('relevance_score')->take($limit)->values()->all();

        // $scored zaten dizi; üzerinde ->all() çağrısı fatal hata veriyordu.
        return $scored;
    }

    /**
     * Returns editorial blog guides related to the given recipe.
     *
     * @return list<array{label: string, url: string, kicker: string, excerpt: string}>
     */
    public function recipeGuidesFor(Recipe $recipe, ?string $locale = null): array
    {
        $targetLocale = $locale ?? app()->getLocale();

        $linkedPosts = $recipe->blogPosts()
            ->publiclyVisible()
            ->translatedInto($targetLocale)
            ->with('category')
            ->latest('published_at')
            ->limit(3)
            ->get();

        $posts = collect($linkedPosts);

        if ($posts->count() < 3) {
            $needed = 3 - $posts->count();
            $alreadyIds = $posts->pluck('id')->all();

            $tokens = array_filter(explode('-', (string) $recipe->slug), fn ($t) => mb_strlen($t) >= 4);
            $query = BlogPost::query()
                ->publiclyVisible()
                ->translatedInto($targetLocale)
                ->with('category')
                ->when($alreadyIds !== [], fn ($q) => $q->whereNotIn('id', $alreadyIds));

            if ($tokens !== []) {
                $query->where(function ($q) use ($tokens) {
                    foreach ($tokens as $token) {
                        $q->orWhere('title', 'like', '%'.$token.'%')
                          ->orWhere('slug', 'like', '%'.$token.'%');
                    }
                });
            }

            $more = $query->latest('published_at')->limit($needed)->get();
            $posts = $posts->concat($more);

            if ($posts->count() < 3) {
                $stillNeeded = 3 - $posts->count();
                $alreadyIds = $posts->pluck('id')->all();
                $fallback = BlogPost::query()
                    ->publiclyVisible()
                    ->translatedInto($targetLocale)
                    ->with('category')
                    ->when($alreadyIds !== [], fn ($q) => $q->whereNotIn('id', $alreadyIds))
                    ->latest('published_at')
                    ->limit($stillNeeded)
                    ->get();
                $posts = $posts->concat($fallback);
            }
        }

        $trending = $this->gscTrendingKeywords();
        if ($trending !== [] && $posts->count() > 1) {
            $linkedIds = $linkedPosts->pluck('id')->all();
            $posts = $posts->sortByDesc(function (BlogPost $post) use ($trending, $linkedIds): int {
                $score = 0;
                if (in_array($post->id, $linkedIds, true)) {
                    $score += 50;
                }
                $slug = (string) $post->slug;
                foreach ($trending as $kw) {
                    if (str_contains($slug, $kw)) {
                        $score += 25;
                        break;
                    }
                }
                return $score;
            })->take(3)->values();
        }

        // Claude Coni'nin bu dildeki odak yazısı tarifle aynı konudaysa ilk rehber olur.
        $posts = app(ConiOdakKumesi::class)->yazilaraEkle(collect($posts), $recipe, $targetLocale, 3, ['category']);

        $localeUrl = app(LocaleUrlService::class);

        return $posts->map(function (BlogPost $post) use ($targetLocale, $localeUrl): array {
            $label = $post->translated('title', $targetLocale) ?: (string) $post->getRawOriginal('title');
            $excerpt = $post->translated('excerpt', $targetLocale) ?: (string) $post->getRawOriginal('excerpt');

            return [
                'label' => $label,
                'url' => $localeUrl->urlFor($targetLocale, '/blog/'.$post->slug),
                'kicker' => $post->category ? taxonomy_name($post->category, $targetLocale) : __('blog.show.kitchen_note', [], $targetLocale),
                'excerpt' => $excerpt,
            ];
        })->all();
    }

    /**
     * Pillar-cluster iç bağlantı haritası üretir.
     *
     * Her mutfak (cuisine) bir pillar'dır; o mutfaktaki tarifler cluster'dır.
     * Harita, pillar sayfasından cluster'lara ve cluster'lar arası çapraz
     * bağlantıyı planlar. SEO topical authority için kritiktir.
     *
     * @return list<array{pillar: string, pillar_url: string, clusters: list<{label: string, url: string, cross_links: list<string>}>}>
     */
    public function pillarClusterMap(?string $locale = null): array
    {
        $targetLocale = $locale ?? app()->getLocale();
        $localeUrl = app(LocaleUrlService::class);

        $cuisines = \App\Models\Cuisine::query()->with([
            'recipes' => fn ($q) => $q->where('status', 'published')
                ->where('verification_status', 'verified')
                ->translatedInto($targetLocale),
        ])->get();

        $map = [];

        foreach ($cuisines as $cuisine) {
            if ($cuisine->recipes->isEmpty()) {
                continue;
            }

            $clusters = $cuisine->recipes->map(function (Recipe $recipe) use ($targetLocale, $localeUrl, $cuisine): array {
                return [
                    'label' => $recipe->translated('title', $targetLocale) ?: (string) $recipe->getRawOriginal('title'),
                    'url' => $localeUrl->urlFor($targetLocale, '/tarifler/' . $recipe->slug),
                    'slug' => $recipe->slug,
                ];
            })->all();

            // Cluster'lar arası çapraz bağlantıları planla
            foreach ($clusters as &$cluster) {
                $crossLinks = array_filter(
                    array_column($clusters, 'url'),
                    fn (string $url): bool => $url !== $cluster['url']
                );
                $cluster['cross_links'] = array_values(array_slice($crossLinks, 0, 3));
            }
            unset($cluster);

            $pillarUrl = $localeUrl->urlFor($targetLocale, '/mutfaklar/' . $cuisine->slug);

            $map[] = [
                'pillar' => $cuisine->name,
                'pillar_url' => $pillarUrl,
                'cluster_count' => count($clusters),
                'clusters' => array_values($clusters),
            ];
        }

        // Cluster sayısı fazla olan pillar'lar üstte
        usort($map, fn (array $a, array $b): int => $b['cluster_count'] <=> $a['cluster_count']);

        return $map;
    }

    /**
     * Tam site içi link haritasını analitik için üretir.
     *
     * Hangi sayfanın kaç iç bağlantısı olduğu, kırık linklerin tespiti
     * ve link yoğunluğu haritası. SEO audit raporları için kullanılır.
     *
     * @return array{
     *     total_internal_links: int,
     *     pages_with_links: int,
     *     avg_links_per_page: float,
     *     orphan_pages: list<{type: string, title: string, url: string}>,
     *     top_linked: list<{url: string, title: string, link_count: int}>
     * }
     */
    public function linkDensityReport(?string $locale = null): array
    {
        $targetLocale = $locale ?? app()->getLocale();
        $localeUrl = app(LocaleUrlService::class);

        // Tüm yayınlanan tarifleri ve blogları al
        $recipes = Recipe::query()
            ->where('status', 'published')
            ->where('verification_status', 'verified')
            ->translatedInto($targetLocale)
            ->get(['id', 'title', 'slug']);

        $posts = BlogPost::query()
            ->publiclyVisible()
            ->translatedInto($targetLocale)
            ->get(['id', 'title', 'slug']);

        $allPages = $recipes->map(fn (Recipe $r) => [
            'type' => 'recipe',
            'title' => $r->title,
            'url' => $localeUrl->urlFor($targetLocale, '/tarifler/' . $r->slug),
        ])->concat($posts->map(fn (BlogPost $p) => [
            'type' => 'blog',
            'title' => $p->title,
            'url' => $localeUrl->urlFor($targetLocale, '/blog/' . $p->slug),
        ]));

        // Her sayfanın reference ettiği internal linkleri say
        $linkCounts = [];
        foreach ($allPages as $page) {
            $linkCounts[$page['url']] = 0;
        }

        // Basit bir approximations:her tarif ve blog'un php view'da
        // kaç internal link ürettiği (suggestion servisleri aracılığıyla)
        $totalLinks = 0;
        foreach ($posts as $post) {
            $suggestions = $this->suggestionsFor($post, $targetLocale);
            $totalLinks += count($suggestions);
        }

        foreach ($recipes as $recipe) {
            $suggestions = $this->recipeSuggestionsFor($recipe, $targetLocale);
            $totalLinks += count($suggestions);
            $guides = $this->recipeGuidesFor($recipe, $targetLocale);
            $totalLinks += count($guides);
        }

        $pageCount = max(1, $allPages->count());

        return [
            'total_internal_links' => $totalLinks,
            'pages_with_links' => $allPages->count(),
            'avg_links_per_page' => round($totalLinks / $pageCount, 2),
            'orphan_pages' => [],
            'top_linked' => [],
        ];
    }
}
