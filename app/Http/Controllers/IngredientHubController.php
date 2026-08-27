<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Models\Ingredient;
use App\Models\Recipe;
use App\Services\Media\PublicMediaEligibilityService;
use App\Services\Seo\SeoIndexabilityService;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Http\Request;
use Illuminate\View\View;

/**
 * Malzeme sayfaları: "Hellim ile ne yapılır?"
 *
 * Bu gerçek bir arama niyeti ve cevabı sitenin kendi verisinde duruyor: 2.962
 * tarif-malzeme bağı. Sayfa üretilmiyor, var olan veriden derleniyor.
 *
 * Eşik burada belirleyici. 180 malzeme yayındaki tariflerde geçiyor ama
 * yalnız 39'unun beşten fazla tarifi var. Hepsine sayfa açmak 102 tane tek
 * tarifli ince sayfa üretmek olurdu - sitenin daha önce 3.649 sayfayla
 * düştüğü tuzağın aynısı, yeni biçimde. Yardımcı içerik sistemi site geneli
 * çalışıyor: zayıf sayfalar güçlü sayfaların sıralamasını da aşağı çeker.
 *
 * Bu yüzden sayfa, arkasında yeterli içerik varsa var olur. Yoksa 404.
 */
final class IngredientHubController extends Controller
{
    /** Bir malzeme sayfasının var olması için gereken en az tarif sayısı. */
    private const ASGARI_TARIF = 5;

    public function index(Request $request, SeoIndexabilityService $indexability): View
    {
        $malzemeler = $this->yeterliMalzemeler();

        return view('ingredients.hub-index', [
            'malzemeler' => $malzemeler,
            'seoIndexability' => $indexability->forRequest($request, route('ingredients.hub.index')),
        ]);
    }

    public function show(Request $request, string $slug, SeoIndexabilityService $indexability, PublicMediaEligibilityService $gate): View
    {
        $malzeme = Ingredient::query()->where('slug', $slug)->first();

        abort_if($malzeme === null, 404);

        $tarifler = $this->tarifleriIcin($malzeme);

        // Eşiğin altındaki malzemenin sayfası yok. Az tarifle açılmış bir
        // sayfa ziyaretçiye de arama motoruna da bir şey vermiyor.
        abort_if($tarifler->count() < self::ASGARI_TARIF, 404);

        return view('ingredients.hub-show', [
            'malzeme' => $malzeme,
            'tarifler' => $tarifler,
            'mediaEligibility' => $gate,
            'seoIndexability' => $indexability->forRequest($request, route('ingredients.hub.show', $malzeme->slug)),
        ]);
    }

    /**
     * Sayfası olan malzemeler. Sitemap de bu listeyi kullanır; liste ile
     * sitemap ayrışırsa sitemap var olmayan adresleri ilan eder.
     *
     * @return Collection<int, Ingredient>
     */
    public static function sayfasiOlanlar(): Collection
    {
        return (new self)->yeterliMalzemeler();
    }

    /** @return Collection<int, Ingredient> */
    private function yeterliMalzemeler(): Collection
    {
        $sayilar = $this->yayindakiTarifSayilari();

        return Ingredient::query()
            ->whereIn('id', $sayilar->filter(fn (int $adet): bool => $adet >= self::ASGARI_TARIF)->keys())
            ->orderBy('name')
            ->get()
            ->each(fn (Ingredient $m) => $m->setAttribute('tarif_sayisi', $sayilar[$m->id] ?? 0));
    }

    /** @return \Illuminate\Support\Collection<int, int> */
    private function yayindakiTarifSayilari(): \Illuminate\Support\Collection
    {
        return \Illuminate\Support\Facades\DB::table('recipe_ingredients')
            ->whereIn('recipe_id', $this->yayindakiTarifIdleri())
            ->selectRaw('ingredient_id, count(distinct recipe_id) as adet')
            ->groupBy('ingredient_id')
            ->pluck('adet', 'ingredient_id')
            ->map(fn ($v): int => (int) $v);
    }

    /** @return \Illuminate\Support\Collection<int, int> */
    private function yayindakiTarifIdleri(): \Illuminate\Support\Collection
    {
        return Recipe::query()
            ->where('status', 'published')
            ->where('verification_status', 'verified')
            ->translatedInto()
            ->pluck('id');
    }

    /** @return Collection<int, Recipe> */
    private function tarifleriIcin(Ingredient $malzeme): Collection
    {
        return Recipe::query()
            ->where('status', 'published')
            ->where('verification_status', 'verified')
            ->translatedInto()
            ->whereHas('recipeIngredients', fn ($q) => $q->where('ingredient_id', $malzeme->id))
            ->with(['media' => fn ($q) => $q->where('visibility', 'public'), 'category'])
            ->orderByDesc('published_at')
            ->get();
    }
}
