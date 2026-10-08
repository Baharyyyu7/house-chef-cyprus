<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Models\Ingredient;
use App\Models\Recipe;
use App\Services\Media\HubCoverPicker;
use App\Services\Media\PublicMediaEligibilityService;
use App\Services\Seo\SeoIndexabilityService;
use App\Services\Seo\SeoSchemaService;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
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

    /**
     * Sayfası olmayacak malzemeler.
     *
     * Tarif sayısı eşiği veri hacmini filtreliyor ama arama niyetini
     * filtrelemiyor - ve sinyal burada ters işliyor. Tuz 73 tarifte, zeytinyağı
     * 92 tarifte geçiyor; tam da her yemekte oldukları için. Kimse "tuz ile ne
     * yapılır" diye aramaz. Bu sayfalar açılırsa yüksek tarif sayılarına rağmen
     * niyetsiz kalır ve zayıf sayfa olarak site geneli kalite sinyalini düşürür.
     *
     * "Ana Malzeme" ise malzeme bile değil, veri tarafında kalmış bir yer
     * tutucu etiket.
     *
     * Bu liste algoritmayla türetilemiyor: hangi malzemenin konu, hangisinin
     * baharat olduğu editoryal bir karar.
     */
    private const KONU_OLMAYANLAR = [
        'ana-malzeme',
        'tuz', 'karabiber', 'seker', 'sivi-yag', 'zeytinyagi', 'su', 'un',
        'kuru-sogan', 'sogan', 'sarimsak', 'tereyagi', 'limon-suyu', 'ceviz-ici',
        'biber-salcasi', 'domates-salcasi', 'sut', 'yumurta',
    ];

    public function index(Request $request, SeoIndexabilityService $indexability): View
    {
        $malzemeler = $this->yeterliMalzemeler();

        return view('ingredients.hub-index', [
            'malzemeler' => $malzemeler,
            'kapaklar' => app(HubCoverPicker::class)->malzemeyeGore($malzemeler->pluck('id')->all()),
            'seoIndexability' => $indexability->forRequest($request, route('ingredients.hub.index')),
        ]);
    }

    public function show(Request $request, string $slug, SeoIndexabilityService $indexability, PublicMediaEligibilityService $gate, SeoSchemaService $schema): View
    {
        $malzeme = Ingredient::query()->where('slug', $slug)->first();

        abort_if($malzeme === null, 404);
        abort_if(in_array($malzeme->slug, self::KONU_OLMAYANLAR, true), 404);

        $tarifler = $this->tarifleriIcin($malzeme);

        // Eşiğin altındaki malzemenin sayfası yok. Az tarifle açılmış bir
        // sayfa ziyaretçiye de arama motoruna da bir şey vermiyor.
        abort_if($tarifler->count() < self::ASGARI_TARIF, 404);

        $canonical = route('ingredients.hub.show', $malzeme->slug);
        // Metinler dil dosyasında: "tomatoes ile Ne Yapılır?" gibi yarı
        // Türkçe başlıklar altı dilde indekse açıktı (8 Ekim 2026 denetimi).
        $description = __('ingredient_hub.meta_description', ['name' => $malzeme->name, 'count' => $tarifler->count()]);

        return view('ingredients.hub-show', [
            'malzeme' => $malzeme,
            'tarifler' => $tarifler,
            'mediaEligibility' => $gate,
            'seoIndexability' => $indexability->forRequest($request, $canonical),
            'seoSchema' => $schema->collectionPage(
                __('ingredient_hub.h1', ['name' => $malzeme->name]), $description, $canonical, $tarifler,
                [
                    ['name' => __('nav.home'), 'url' => route('home')],
                    ['name' => __('ingredient_hub.breadcrumb'), 'url' => route('ingredients.hub.index')],
                    ['name' => $malzeme->name, 'url' => $canonical],
                ],
            ),
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
        $sonYayinlar = DB::table('recipe_ingredients')
            ->join('recipes', 'recipes.id', '=', 'recipe_ingredients.recipe_id')
            ->where('recipes.status', 'published')
            ->where('recipes.verification_status', 'verified')
            ->selectRaw('recipe_ingredients.ingredient_id, max(recipes.published_at) as latest_published_at')
            ->groupBy('recipe_ingredients.ingredient_id')
            ->pluck('latest_published_at', 'ingredient_id');

        return Ingredient::query()
            ->whereIn('id', $sayilar->filter(fn (int $adet): bool => $adet >= self::ASGARI_TARIF)->keys())
            ->whereNotIn('slug', self::KONU_OLMAYANLAR)
            ->orderBy('name')
            ->get()
            ->each(function (Ingredient $m) use ($sayilar, $sonYayinlar): void {
                $m->setAttribute('tarif_sayisi', $sayilar[$m->id] ?? 0);
                $m->setAttribute('latest_published_at', $sonYayinlar[$m->id] ?? null);
            });
    }

    /** @return \Illuminate\Support\Collection<int, int> */
    private function yayindakiTarifSayilari(): \Illuminate\Support\Collection
    {
        return DB::table('recipe_ingredients')
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
    /**
     * Bu malzemenin bu dilde sayfası var mı?
     *
     * 23 Eylül 2026'da ölçüldü: alt bilgideki "Hellim" bağlantısı sitenin
     * her sayfasında duruyordu ve İngilizcede 404 veriyordu. Eşik dile göre
     * değişiyor, çünkü tarif sayımı o dile çevrilmiş tarifler üzerinden
     * yapılıyor; bağlantıyı basan yer de aynı ölçüte bakmak zorunda.
     */
    public static function sayfasiVarMi(string $slug): bool
    {
        if (in_array($slug, self::KONU_OLMAYANLAR, true)) {
            return false;
        }

        return Cache::remember(
            'ingredient-hub-var:'.app()->getLocale().':'.$slug,
            now()->addMinutes(10),
            static function () use ($slug): bool {
                $malzeme = Ingredient::query()->where('slug', $slug)->first();

                return $malzeme !== null && (new self)->tarifleriIcin($malzeme)->count() >= self::ASGARI_TARIF;
            }
        );
    }

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
