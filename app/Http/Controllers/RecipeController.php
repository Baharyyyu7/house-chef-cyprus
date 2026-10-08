<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Contracts\Search\RecipeSearchInterface;
use App\Models\Allergen;
use App\Models\BlogPost;
use App\Models\Category;
use App\Models\Cuisine;
use App\Models\DietaryTag;
use App\Models\Ingredient;
use App\Models\Recipe;
use App\Services\AllergenAnalysisService;
use App\Services\DietaryCompatibilityService;
use App\Services\IngredientMatchingService;
use App\Services\Measurements\RecipeIngredientDisplayService;
use App\Services\Media\HubCoverPicker;
use App\Services\Media\PublicMediaEligibilityService;
use App\Services\NutritionService;
use App\Services\Coni\ConiOdakKumesi;
use App\Services\Seo\InternalLinkService;
use App\Services\Seo\RecipeTranslationGate;
use App\Services\Seo\SeoIndexabilityService;
use App\Services\Seo\SeoMetadataService;
use App\Services\Seo\SeoSchemaService;
use App\Services\Seo\ZeroResultSearchService;
use Illuminate\Http\Request;

final class RecipeController extends Controller
{
    public function index(Request $request, RecipeSearchInterface $search, ZeroResultSearchService $zeroResults, SeoIndexabilityService $indexability, SeoSchemaService $schemaService)
    {
        if ($request->routeIs('home')) {
            return $this->home($request, $indexability, $schemaService);
        }

        $filters = $request->only([
            'q', 'cuisine', 'category', 'difficulty', 'dietary_tag', 'exclude_allergens',
            'max_minutes', 'min_protein', 'max_calories', 'sort', 'page',
        ]);

        $recipes = $search->search($filters);
        if ($request->filled('q')) {
            $zeroResults->record($request->string('q')->toString(), $recipes->total());
        }

        return view('recipes.index', [
            'recipes' => $recipes,
            'cuisines' => $this->mutfaklar(),
            'categories' => Category::orderBy('name')->get(),
            'dietaryTags' => DietaryTag::orderBy('name')->get(),
            'allergens' => Allergen::orderBy('name')->get(),
            'seoIndexability' => $indexability->forRequest($request, route('recipes.index')),
        ]);
    }

    /**
     * Ana sayfa.
     *
     * Eskiden arşivle aynı yoldan geçiyordu: ana sayfanın hiç göstermediği
     * arama sonucu, kategori, diyet etiketi ve alerjen listeleri her ziyarette
     * sorgulanıyor, yalnız sayısı ve ilk dört görselli tarifi gereken bütün
     * yayın kataloğu (ilişkileri ve medyasıyla) belleğe alınıyordu.
     */
    private function home(Request $request, SeoIndexabilityService $indexability, SeoSchemaService $schemaService)
    {
        $locale = app()->getLocale();

        // Çevirisi olmayan tarif o dilin sitesinde yoktur; listede
        // görünseydi /en/ altında Türkçe başlıklar çıkardı.
        $yayinda = fn () => Recipe::query()
            ->where('status', 'published')
            ->where('verification_status', 'verified')
            ->translatedInto();

        // Never cache approval decisions: a revoked image leaves the showcase immediately.
        // Bu yüzden vitrin önbelleğe alınmaz; yalnız gerektiği kadar tarif
        // parça parça okunur.
        $mediaEligibility = app(PublicMediaEligibilityService::class);
        $highlights = $yayinda()
            ->select(['id', 'public_id', 'cuisine_id', 'category_id', 'title', 'slug', 'excerpt', 'prep_minutes', 'cook_minutes', 'rest_minutes', 'servings', 'difficulty', 'status', 'verification_status', 'published_at'])
            ->with(['cuisine:id,public_id,name,slug', 'category:id,public_id,name,slug', 'media' => fn ($query) => $query->where('visibility', 'public'), 'dietaryTags:id,code,name'])
            ->orderByDesc('published_at')
            ->orderByDesc('id')
            ->lazy(24)
            ->filter(fn (Recipe $recipe): bool => $mediaEligibility->firstEligible($recipe->media) !== null)
            ->take(4)
            ->collect();

        return view('recipes.index', [
            'cuisines' => $this->mutfaklar(),
            // Anasayfadaki mutfak seckisi, /mutfaklar sayfasindaki kartla
            // ayni gorunmeli: ayni icerik iki sayfada farkli gorunuyordu.
            'mutfakKapaklari' => app(HubCoverPicker::class)
                ->sutunaGore('cuisine_id', Cuisine::query()->pluck('id')->all()),
            'seoIndexability' => $indexability->forRequest($request, route('home')),
            // Hero alt metnindeki sayı. Eskiden "1.000'den fazla" sabit yazıyordu,
            // yayında 534 tarif varken; o dilde gerçekten görünen sayı basılır.
            'homeRecipeCount' => cache()->remember('home-recipe-count-v1:'.$locale, now()->addMinutes(5), fn (): int => $yayinda()->count()),
            'homeHighlights' => $highlights,
            'homeIngredients' => cache()->remember('home-ingredients-v3:'.$locale, now()->addMinutes(5), fn () => Ingredient::dileGoreSirala(Ingredient::query()->yayindaKullanilan()->get(['id', 'public_id', 'name']))
                ->map(fn (Ingredient $malzeme): array => ['public_id' => $malzeme->public_id, 'name' => $malzeme->name])->all()),
            // Hero'daki eşleştirme gösterisi. Temiz senaryo çıkmayan dilde
            // boş döner ve panel hiç basılmaz.
            'homeMatchShowcase' => cache()->remember('home-match-showcase-v1:'.$locale, now()->addMinutes(30), fn () => app(\App\Services\HomeMatchShowcaseService::class)->senaryolar(3)),
            // Anasayfa daha önce WebSite şemasız basılıyordu: webSite()
            // tanımlıydı ama hiçbir çağrısı yoktu. Google site adı + site
            // içi arama kutusu zengin sonucunu yalnız bu şema açar.
            'homeSchema' => $schemaService->webSite(),
            // Blog-hub şeridi: anasayfadan bloga giden tek bağlantı üyelik
            // kartındaki metin linkti. Son 3 yayında yazı, hub desteğini
            // kapatır ve Kıbrıs rehber içeriklerini yüzeye taşır.
            'homeBlogPosts' => BlogPost::query()->publiclyVisible()->translatedInto()->with(['category', 'heroMedia', 'media'])->latest('published_at')->take(3)->get(),
        ]);
    }

    /**
     * Sayım ana sayfadaki mutfak kutularının içini dolduruyor: boş bir kutu
     * yerine o mutfakta kaç yayında tarif olduğu görünür. Filtre açılır
     * listesi aynı koleksiyonu kullanmaya devam eder. Sayım dil başına beş
     * dakika önbellekte: her ziyarette mutfak başına alt sorgu koşuyordu.
     */
    private function mutfaklar()
    {
        return cache()->remember('recipe-index-cuisines-v1:'.app()->getLocale(), now()->addMinutes(5), fn () => Cuisine::withCount(['recipes' => fn ($q) => $q
            ->where('status', 'published')
            ->where('verification_status', 'verified')
            ->translatedInto()])
            ->orderBy('name')
            ->get()
            ->filter(fn (Cuisine $cuisine): bool => (int) $cuisine->recipes_count >= 10)
            ->values());
    }

    public function show(Request $request, Recipe $recipe, NutritionService $nutrition, IngredientMatchingService $matching, AllergenAnalysisService $allergens, DietaryCompatibilityService $dietary, RecipeIngredientDisplayService $ingredientDisplay, PublicMediaEligibilityService $mediaEligibility, SeoMetadataService $seo, SeoSchemaService $schemaService, InternalLinkService $internalLinks)
    {
        // MySQL harf duyarsız eşleşiyor: "...-FY1DJ" de "...-fy1dj" tarifini
        // 200 ile basıyordu. Slug'lar 20 Eylül sonrası küçük harfe çekildi,
        // 174 kopyanın kanoniği eski yazımda kaldı ve o adres ikinci bir
        // nüsha olarak yanıt veriyordu.
        $istenen = (string) $request->route()?->originalParameter('recipe');
        if ($istenen !== '' && rawurldecode($istenen) !== (string) $recipe->slug) {
            return redirect()->route('recipes.show', $recipe, 301);
        }

        $isPublic = ($recipe->status === 'published' && $recipe->verification_status === 'verified') || $request->user()?->isAdmin();

        if (! $isPublic) {
            $slugStr = (string) $recipe->slug;
            $redirect = \App\Models\SlugRedirect::query()
                ->where(function ($q) use ($slugStr) {
                    $q->where('old_slug', $slugStr)
                        ->orWhere('old_slug', rawurldecode($slugStr));
                })
                ->first();

            if ($redirect !== null) {
                if ($redirect->model_type === BlogPost::class) {
                    $blogTarget = BlogPost::query()->publiclyVisible()->find($redirect->model_id);
                    if ($blogTarget !== null) {
                        return redirect()->to(route('blog.show', $blogTarget), 301);
                    }
                } else {
                    $target = Recipe::query()->where('status', 'published')->where('verification_status', 'verified')->find($redirect->model_id);
                    if ($target !== null) {
                        return redirect()->to(route('recipes.show', $target->slug), 301);
                    }
                }
            }

            $twin = Recipe::query()->where('status', 'published')->where('verification_status', 'verified')
                ->where('title', $recipe->title)
                ->where('id', '!=', $recipe->id)
                ->first();

            if ($twin !== null) {
                return redirect()->to(route('recipes.show', $twin->slug), 301);
            }

            $rootSlug = (string) preg_replace('/-[A-Za-z0-9]{5}$/', '', $slugStr);
            if ($rootSlug !== '' && $rootSlug !== $slugStr) {
                $candidate = Recipe::query()->where('status', 'published')->where('verification_status', 'verified')
                    ->where(fn ($q) => $q->where('slug', $rootSlug)->orWhere('slug', 'like', $rootSlug.'-%'))
                    ->first();

                if ($candidate !== null) {
                    return redirect()->to(route('recipes.show', $candidate->slug), 301);
                }
            }

            abort(404);
        }
        // Çevirisi olmayan tarifin o dildeki adresi yoktur. Sessizce Türkçe
        // içerik servis etmek, hreflang'in gösterdiği adresin başka dilde
        // içerik döndürmesi ve aynı içeriğin ikinci kopyası sayılması olurdu.
        if (! ($recipe->hasTranslation() || $request->user()?->isAdmin())) {
            $slugStr = (string) $recipe->slug;
            $redirect = \App\Models\SlugRedirect::query()
                ->where(function ($q) use ($slugStr) {
                    $q->where('old_slug', $slugStr)
                        ->orWhere('old_slug', rawurldecode($slugStr));
                })
                ->first();

            if ($redirect !== null) {
                if ($redirect->model_type === BlogPost::class) {
                    $blogTarget = BlogPost::query()->publiclyVisible()->find($redirect->model_id);
                    if ($blogTarget !== null) {
                        return redirect()->to(route('blog.show', $blogTarget), 301);
                    }
                } else {
                    $target = Recipe::query()->where('status', 'published')->where('verification_status', 'verified')->find($redirect->model_id);
                    if ($target !== null) {
                        return redirect()->to(route('recipes.show', $target->slug), 301);
                    }
                }
            }

            abort(404);
        }
        $recipe->load(['cuisine', 'category', 'ingredients', 'recipeIngredients.ingredient', 'recipeIngredients.group', 'steps', 'media' => fn ($query) => $query->where('visibility', 'public'), 'provenances.contentSource']);
        // Artır/azalt butonları input ile aynı adı paylaşamaz: ikisi de gönderilir ve
        // DOM sırasına göre biri diğerini ezer. Butonlar yön bildirir, değeri burada uygularız.
        $servings = max(1, (int) $request->integer('servings', $recipe->servings));
        $servingsStep = (int) $request->integer('servings_step', 0);
        if ($servingsStep !== 0) {
            $servings = max(1, min(99, $servings + ($servingsStep > 0 ? 1 : -1)));
        }

        $nutritionResult = $nutrition->snapshot($recipe, $servings);

        $heroMedia = $mediaEligibility->firstEligible($recipe->media);
        $sosyalGorsel = $heroMedia?->socialImage();

        $cuisineUrl = null;
        if ($recipe->cuisine !== null) {
            // Sayım o dildeki hub'ın kendi sayımı: Türkçe sayım yabancı
            // sayfada 404 veren mutfak sayfasına bağlantı veriyordu.
            $dogrulanmisTarifSayisi = CuisineHubController::ceviriSayilari()[$recipe->cuisine->slug] ?? 0;
            $cuisineUrl = CuisineHubController::sayfasiVar($dogrulanmisTarifSayisi)
                ? route('cuisines.show', $recipe->cuisine->slug)
                : route('recipes.index', ['cuisine' => $recipe->cuisine->slug]);
        }

        $displayMedia = app(\App\Services\Media\RecipeDisplayMediaService::class)->resolve($recipe);
        $hasCompletePresentation = $heroMedia !== null || ($recipe->cuisine !== null && $recipe->category !== null);

        // Recipe.image yemeğin kendi fotoğrafı olmak zorunda. Eski kural
        // "mutfağı ve kategorisi varsa yeterli" diyordu ve fotoğrafı olmayan
        // tarifte marka varsayılanını (og-default.jpg) tarifin görseli diye
        // ilan ediyordu. 20 Eylül 2026'da ölçüldü: indekse açık 21 tarif bu
        // durumdaydı. Yanlış görsel bildirmektense Recipe nesnesini hiç
        // basmamak doğru: şema servisi boş adreste zaten breadcrumb'a düşüyor.
        $schemaImageUrl = $sosyalGorsel['url'] ?? (($displayMedia['is_fallback'] ?? true) ? '' : (string) ($displayMedia['hero_url'] ?? ''));

        // Şema görseli de paylaşım varyantını kullanır; Google zengin sonuç
        // için bu adresi okuyor ve ham kaynak bir tarifte 2,77 MB'tı.
        $schema = $schemaService->recipe($recipe, collect($ingredientDisplay->forRecipe($recipe->recipeIngredients, (int) $recipe->servings, $servings))->pluck('formatted')->values()->all(), $schemaImageUrl, $cuisineUrl);
        $metadata = $seo->for($recipe, $recipe->title.' · House Chef Cyprus', $recipe->description ?: $recipe->excerpt, route('recipes.show', $recipe), $request->query() === [] ? 'index,follow' : 'noindex,follow');
        if (! $hasCompletePresentation) {
            // Onayli ana gorseli veya mutfak/kategori kimligi bulunmayan eksik taslaklar
            // kalite kapisini gecene kadar arama motoruna aday gosterilmez.
            $metadata['robots'] = 'noindex,follow';
        }
        // Yabancı dilde adımları kalıp çeviri ya da eksik olan tarif o dilde
        // aranabilir değil; gerçek çeviri gelince kendiliğinden açılır.
        if (app(RecipeTranslationGate::class)->engel($recipe, app()->getLocale()) !== null) {
            $metadata['robots'] = 'noindex,follow';
        }

        return view('recipes.show', [
            'recipe' => $recipe,
            'servings' => $servings,
            'nutrition' => $nutritionResult,
            'allergenAnalysis' => $allergens->analyse($recipe),
            'dietaryCompatibility' => $dietary->assess($recipe, $servings),
            'match' => $request->user() ? $matching->match($recipe, $request->user()) : null,
            'isFavorited' => $request->user()?->favoriteRecipes()->whereKey($recipe->id)->exists() ?? false,
            'recipeCollections' => $request->user()?->recipeCollections()->latest()->limit(5)->get() ?? collect(),
            'ingredientLines' => $ingredientDisplay->forRecipe($recipe->recipeIngredients, (int) $recipe->servings, $servings),
            // Claude Coni'nin bu dildeki odak tarifi aynı kategori ya da mutfaktansa öne alınır.
            'relatedRecipes' => app(ConiOdakKumesi::class)->tariflereEkle(Recipe::query()
                ->select(['id', 'public_id', 'cuisine_id', 'category_id', 'title', 'slug', 'excerpt', 'prep_minutes', 'cook_minutes', 'rest_minutes', 'servings', 'difficulty', 'status', 'verification_status'])
                ->with(['cuisine:id,public_id,name,slug', 'category:id,public_id,name,slug', 'media' => fn ($query) => $query->select(['id', 'mediable_type', 'mediable_id', 'kind', 'disk', 'path', 'source_name', 'source_url', 'provider_metadata', 'alt_text', 'is_placeholder', 'visibility', 'media_status', 'verification_status', 'license_review_status', 'attribution_review_status', 'generated_by_ai', 'responsive_variants', 'sort_order', 'fidelity_status', 'fidelity_confidence', 'checksum_sha256', 'perceptual_hash', 'media_reviewer_user_id', 'media_reviewed_at', 'approved_by_user_id', 'approved_at'])->where('visibility', 'public'), 'dietaryTags:id,code,name'])
                ->where('status', 'published')
                ->where('verification_status', 'verified')
                ->whereKeyNot($recipe->id)
                // Yabancı sayfada çevrilmemiş tarif, kalıp başlığıyla ve o dilde
                // 404 veren bağlantıyla "benzer tarif" olarak basılıyordu.
                ->translatedInto()
                ->where(function ($query) use ($recipe): void {
                    $query->where('category_id', $recipe->category_id)
                        ->orWhere('cuisine_id', $recipe->cuisine_id);
                })
                ->orderByRaw('case when category_id = ? then 0 else 1 end', [$recipe->category_id])
                ->limit(4)
                ->get(), $recipe, app()->getLocale(), 4),
            'seoSchema' => $schema,
            'metadata' => $metadata,
            // Paylaşım görseli: onaylı görsel varsa onun OG varyantı, yoksa yüksek çözünürlüklü gastronomi görseli
            'seoImage' => $sosyalGorsel['url'] ?? $displayMedia['hero_url'] ?? null,
            'seoImageWidth' => $sosyalGorsel['width'] ?? 1600,
            'seoImageHeight' => $sosyalGorsel['height'] ?? 1000,
            'seoImageAlt' => $heroMedia?->alt_text ?? $displayMedia['alt'] ?? null,
            'cuisineUrl' => $cuisineUrl,
            'relatedGuides' => $internalLinks->recipeGuidesFor($recipe, app()->getLocale()),
            'faqs' => $schemaService->generateRecipeFaqs($recipe, app()->getLocale()),
            'ratingSummary' => $recipe->ratingSummary(),
        ]);
    }

    public function embed(Request $request, Recipe $recipe, PublicMediaEligibilityService $mediaEligibility, RecipeIngredientDisplayService $ingredientDisplay)
    {
        abort_unless(($recipe->status === 'published' && $recipe->verification_status === 'verified') || $request->user()?->isAdmin(), 404);

        $recipe->load(['cuisine', 'category', 'media' => fn ($query) => $query->where('visibility', 'public'), 'recipeIngredients.ingredient']);

        $heroMedia = $mediaEligibility->firstEligible($recipe->media);
        $sosyalGorsel = $heroMedia?->socialImage();
        $imageUrl = $sosyalGorsel['url'] ?? null;

        $ingredients = $ingredientDisplay->forRecipe($recipe->recipeIngredients, (int) $recipe->servings, (int) $recipe->servings);

        $locale = $request->query('lang') === 'en' ? 'en' : 'tr';

        return response()
            ->view('recipes.embed', [
                'recipe' => $recipe,
                'imageUrl' => $imageUrl,
                'imageAlt' => $heroMedia?->alt_text ?: $recipe->title,
                'ingredients' => array_slice($ingredients, 0, 5),
                'totalIngredients' => count($ingredients),
                'locale' => $locale,
            ]);
    }
}
