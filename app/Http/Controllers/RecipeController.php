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
use App\Services\Seo\InternalLinkService;
use App\Services\Seo\SeoIndexabilityService;
use App\Services\Seo\SeoMetadataService;
use App\Services\Seo\SeoSchemaService;
use App\Services\Seo\ZeroResultSearchService;
use Illuminate\Http\Request;

final class RecipeController extends Controller
{
    public function index(Request $request, RecipeSearchInterface $search, ZeroResultSearchService $zeroResults, SeoIndexabilityService $indexability, SeoSchemaService $schemaService)
    {
        $filters = $request->only([
            'q', 'cuisine', 'category', 'difficulty', 'dietary_tag', 'exclude_allergens',
            'max_minutes', 'min_protein', 'max_calories', 'sort', 'page',
        ]);

        $recipes = $search->search($filters);
        if ($request->filled('q')) {
            $zeroResults->record($request->string('q')->toString(), $recipes->total());
        }

        $viewData = [
            'recipes' => $recipes,
            // Sayım ana sayfadaki mutfak kutularının içini dolduruyor: boş bir
            // kutu yerine o mutfakta kaç yayında tarif olduğu görünür.
            // Filtre açılır listesi aynı koleksiyonu kullanmaya devam eder.
            'cuisines' => Cuisine::withCount(['recipes' => fn ($q) => $q->where('status', 'published')])
                ->orderBy('name')
                ->get(),
            // Anasayfadaki mutfak seckisi, /mutfaklar sayfasindaki kartla
            // ayni gorunmeli: ayni icerik iki sayfada farkli gorunuyordu.
            'mutfakKapaklari' => app(HubCoverPicker::class)
                ->sutunaGore('cuisine_id', Cuisine::query()->pluck('id')->all()),
            'categories' => Category::orderBy('name')->get(),
            'dietaryTags' => DietaryTag::orderBy('name')->get(),
            'allergens' => Allergen::orderBy('name')->get(),
            'seoIndexability' => $indexability->forRequest($request, $request->routeIs('home') ? route('home') : route('recipes.index')),
        ];

        if ($request->routeIs('home')) {
            // Çevirisi olmayan tarif o dilin sitesinde yoktur; listede
            // görünseydi /en/ altında Türkçe başlıklar çıkardı.
            $catalogue = Recipe::query()
                ->where('status', 'published')
                ->where('verification_status', 'verified')
                ->translatedInto()
                ->select(['id', 'public_id', 'cuisine_id', 'category_id', 'title', 'slug', 'excerpt', 'prep_minutes', 'cook_minutes', 'rest_minutes', 'servings', 'difficulty', 'status', 'verification_status'])
                ->with(['cuisine:id,public_id,name,slug', 'category:id,public_id,name,slug', 'media' => fn ($query) => $query->where('visibility', 'public'), 'dietaryTags:id,code,name'])
                ->orderByDesc('published_at')
                ->get();

            // Never cache approval decisions: a revoked image leaves the showcase immediately.
            $mediaEligibility = app(PublicMediaEligibilityService::class);
            $viewData['homeHighlights'] = $catalogue->lazy()->filter(fn (Recipe $recipe): bool => $mediaEligibility->firstEligible($recipe->media) !== null)->take(4)->collect();
            $viewData['homeIngredients'] = cache()->remember('home-ingredients-v2:'.app()->getLocale(), now()->addMinutes(5), fn () => Ingredient::query()->where('is_active', true)->orderBy('name')->get(['public_id', 'name'])->toArray());
            // Anasayfa daha önce WebSite şemasız basılıyordu: webSite()
            // tanımlıydı ama hiçbir çağrısı yoktu. Google site adı + site
            // içi arama kutusu zengin sonucunu yalnız bu şema açar.
            $viewData['homeSchema'] = $schemaService->webSite();
            // Blog-hub şeridi: anasayfadan bloga giden tek bağlantı üyelik
            // kartındaki metin linkti. Son 3 yayında yazı, hub desteğini
            // kapatır ve Kıbrıs rehber içeriklerini yüzeye taşır.
            $viewData['homeBlogPosts'] = BlogPost::query()->publiclyVisible()->translatedInto()->with(['category', 'heroMedia', 'media'])->latest('published_at')->take(3)->get();

        }

        return view('recipes.index', $viewData);
    }

    public function show(Request $request, Recipe $recipe, NutritionService $nutrition, IngredientMatchingService $matching, AllergenAnalysisService $allergens, DietaryCompatibilityService $dietary, RecipeIngredientDisplayService $ingredientDisplay, PublicMediaEligibilityService $mediaEligibility, SeoMetadataService $seo, SeoSchemaService $schemaService, InternalLinkService $internalLinks)
    {
        $isPublic = ($recipe->status === 'published' && $recipe->verification_status === 'verified') || $request->user()?->isAdmin();

        if (! $isPublic) {
            $slugStr = (string) $recipe->slug;
            $redirect = \App\Models\SlugRedirect::query()
                ->where('model_type', Recipe::class)
                ->where(function ($q) use ($slugStr) {
                    $q->where('old_slug', $slugStr)
                        ->orWhere('old_slug', rawurldecode($slugStr));
                })
                ->first();

            if ($redirect !== null) {
                $target = Recipe::query()->where('status', 'published')->where('verification_status', 'verified')->find($redirect->model_id);
                if ($target !== null) {
                    return redirect()->to(route('recipes.show', $target->slug), 301);
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
                ->where('model_type', Recipe::class)
                ->where(function ($q) use ($slugStr) {
                    $q->where('old_slug', $slugStr)
                        ->orWhere('old_slug', rawurldecode($slugStr));
                })
                ->first();

            if ($redirect !== null) {
                $target = Recipe::query()->where('status', 'published')->where('verification_status', 'verified')->find($redirect->model_id);
                if ($target !== null) {
                    return redirect()->to(route('recipes.show', $target->slug), 301);
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
            $dogrulanmisTarifSayisi = $recipe->cuisine->recipes()
                ->where('status', 'published')
                ->where('verification_status', 'verified')
                ->count();
            $cuisineUrl = CuisineHubController::sayfasiVar($dogrulanmisTarifSayisi)
                ? route('cuisines.show', $recipe->cuisine->slug)
                : route('recipes.index', ['cuisine' => $recipe->cuisine->slug]);
        }

        $displayMedia = app(\App\Services\Media\RecipeDisplayMediaService::class)->resolve($recipe);
        $hasCompletePresentation = $heroMedia !== null || ($recipe->cuisine !== null && $recipe->category !== null);
        $schemaImageUrl = $sosyalGorsel['url'] ?? ($hasCompletePresentation ? ($displayMedia['hero_url'] ?? '') : '');

        // Şema görseli de paylaşım varyantını kullanır; Google zengin sonuç
        // için bu adresi okuyor ve ham kaynak bir tarifte 2,77 MB'tı.
        $schema = $schemaService->recipe($recipe, collect($ingredientDisplay->forRecipe($recipe->recipeIngredients, (int) $recipe->servings, $servings))->pluck('formatted')->values()->all(), $schemaImageUrl, $cuisineUrl);
        $metadata = $seo->for($recipe, $recipe->title.' · House Chef Cyprus', $recipe->description ?: $recipe->excerpt, route('recipes.show', $recipe), $request->query() === [] ? 'index,follow' : 'noindex,follow');
        if (! $hasCompletePresentation) {
            // Onayli ana gorseli veya mutfak/kategori kimligi bulunmayan eksik taslaklar
            // kalite kapisini gecene kadar arama motoruna aday gosterilmez.
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
            'relatedRecipes' => Recipe::query()
                ->select(['id', 'public_id', 'cuisine_id', 'category_id', 'title', 'slug', 'excerpt', 'prep_minutes', 'cook_minutes', 'rest_minutes', 'servings', 'difficulty', 'status', 'verification_status'])
                ->with(['cuisine:id,public_id,name,slug', 'category:id,public_id,name,slug', 'media' => fn ($query) => $query->select(['id', 'mediable_type', 'mediable_id', 'kind', 'disk', 'path', 'source_name', 'source_url', 'provider_metadata', 'alt_text', 'is_placeholder', 'visibility', 'media_status', 'verification_status', 'license_review_status', 'attribution_review_status', 'generated_by_ai', 'responsive_variants', 'sort_order', 'fidelity_status', 'fidelity_confidence', 'checksum_sha256', 'perceptual_hash', 'media_reviewer_user_id', 'media_reviewed_at', 'approved_by_user_id', 'approved_at'])->where('visibility', 'public'), 'dietaryTags:id,code,name'])
                ->where('status', 'published')
                ->where('verification_status', 'verified')
                ->whereKeyNot($recipe->id)
                ->where(function ($query) use ($recipe): void {
                    $query->where('category_id', $recipe->category_id)
                        ->orWhere('cuisine_id', $recipe->cuisine_id);
                })
                ->orderByRaw('case when category_id = ? then 0 else 1 end', [$recipe->category_id])
                ->limit(4)
                ->get(),
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
