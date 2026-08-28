<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Contracts\Search\RecipeSearchInterface;
use App\Models\Allergen;
use App\Models\BlogPost;
use App\Models\Category;
use App\Models\Cuisine;
use App\Models\DietaryTag;
use App\Models\Recipe;
use App\Services\AllergenAnalysisService;
use App\Services\DietaryCompatibilityService;
use App\Services\IngredientMatchingService;
use App\Services\Measurements\RecipeIngredientDisplayService;
use App\Services\Media\PublicMediaEligibilityService;
use App\Services\NutritionService;
use App\Services\Seo\SeoIndexabilityService;
use App\Services\Seo\SeoMetadataService;
use App\Services\Seo\SeoSchemaService;
use App\Services\Seo\ZeroResultSearchService;
use Illuminate\Http\Request;

final class RecipeController extends Controller
{
    public function index(Request $request, RecipeSearchInterface $search, ZeroResultSearchService $zeroResults, SeoIndexabilityService $indexability)
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

            $usedRecipeIds = [];
            $select = function ($matches) use (&$usedRecipeIds) {
                $selection = $matches->reject(fn (Recipe $recipe): bool => in_array($recipe->id, $usedRecipeIds, true))->take(4)->values();
                $usedRecipeIds = [...$usedRecipeIds, ...$selection->pluck('id')->all()];

                return $selection;
            };
            $viewData['homeSections'] = [
                'quick' => $select($catalogue->filter(fn (Recipe $recipe): bool => $recipe->totalMinutes() <= 30)),
                'cyprus' => $select($catalogue->filter(fn (Recipe $recipe): bool => str_contains(mb_strtolower((string) $recipe->cuisine?->name), 'kıbrıs') || str_contains(mb_strtolower((string) $recipe->cuisine?->name), 'akdeniz'))),
                'breakfast' => $select($catalogue->filter(fn (Recipe $recipe): bool => str_contains(mb_strtolower((string) $recipe->category?->name), 'kahvaltı'))),
                'mains' => $select($catalogue->filter(fn (Recipe $recipe): bool => str_contains(mb_strtolower((string) $recipe->category?->name), 'ana'))),
                'desserts' => $select($catalogue->filter(fn (Recipe $recipe): bool => str_contains(mb_strtolower((string) $recipe->category?->name), 'tatlı'))),
            ];
            $viewData['latestPosts'] = BlogPost::query()->publiclyVisible()->with(['category', 'heroMedia', 'media'])->latest('published_at')->take(3)->get();
        }

        return view('recipes.index', $viewData);
    }

    public function show(Request $request, Recipe $recipe, NutritionService $nutrition, IngredientMatchingService $matching, AllergenAnalysisService $allergens, DietaryCompatibilityService $dietary, RecipeIngredientDisplayService $ingredientDisplay, PublicMediaEligibilityService $mediaEligibility, SeoMetadataService $seo, SeoSchemaService $schemaService)
    {
        abort_unless(($recipe->status === 'published' && $recipe->verification_status === 'verified') || $request->user()?->isAdmin(), 404);
        // Çevirisi olmayan tarifin o dildeki adresi yoktur. Sessizce Türkçe
        // içerik servis etmek, hreflang'in gösterdiği adresin başka dilde
        // içerik döndürmesi ve aynı içeriğin ikinci kopyası sayılması olurdu.
        abort_unless($recipe->hasTranslation() || $request->user()?->isAdmin(), 404);
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

        // Şema görseli de paylaşım varyantını kullanır; Google zengin sonuç
        // için bu adresi okuyor ve ham kaynak bir tarifte 2,77 MB'tı.
        $schema = $schemaService->recipe($recipe, collect($ingredientDisplay->forRecipe($recipe->recipeIngredients, (int) $recipe->servings, $servings))->pluck('formatted')->values()->all(), $sosyalGorsel['url'] ?? '');

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
            'metadata' => $seo->for($recipe, $recipe->title.' · House Chef Cyprus', $recipe->description ?: $recipe->excerpt, route('recipes.show', $recipe), $request->query() === [] ? 'index,follow' : 'noindex,follow'),
            // Paylaşım görseli ham kaynağı gösteriyordu; bir tarifte
            // 2,77 MB'lık PNG. Hattın ürettiği 1200x630 varyantı bu iş için.
            'seoImage' => $sosyalGorsel['url'] ?? null,
            'seoImageWidth' => $sosyalGorsel['width'] ?? null,
            'seoImageHeight' => $sosyalGorsel['height'] ?? null,
            'seoImageAlt' => $heroMedia?->alt_text,
        ]);
    }
}
