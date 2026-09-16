<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Models\Cuisine;
use App\Models\Recipe;
use App\Services\Media\HubCoverPicker;
use App\Services\Media\PublicMediaEligibilityService;
use App\Services\Seo\SeoIndexabilityService;
use App\Services\Seo\SeoSchemaService;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Http\Request;
use Illuminate\View\View;

/**
 * Mutfak sayfaları: "Kıbrıs mutfağı tarifleri".
 *
 * Sitenin kendi arama kaydı bunu ölçtü: "kıbrıs mutfağı" 24 kez arandı,
 * "kolay tarifler" 19, "20 dakikada" 26. Buna karşılık indekslenebilir tek
 * bir mutfak sayfası yoktu; `/tarifler?cuisine=...` görünümü `noindex` ve
 * canonical'ı `/tarifler`. Yani sitenin en ayırt edici konusu olan Kıbrıs
 * mutfağı için arama motorunun sıralayabileceği hiçbir adres yoktu.
 *
 * Eşik burada da belirleyici. 13 mutfak kaydı var ama dördünün hiç yayındaki
 * tarifi yok, dördünün de bir ya da iki tane. Hepsine sayfa açmak dokuz ince
 * sayfa üretmek olurdu - sitenin daha önce 3.649 makine sayfasıyla düştüğü
 * tuzağın aynısı. Zayıf sayfalar güçlü sayfaların sıralamasını da düşürür.
 *
 * Sayfa, arkasında yeterli tarif varsa var olur. Yoksa 404.
 */
final class CuisineHubController extends Controller
{
    /** Bir mutfak sayfasının var olması için gereken en az yayındaki tarif sayısı. */
    private const ASGARI_TARIF = 4;

    /**
     * Bu mutfağın sayfası var mı?
     *
     * Eşik tek yerde durmalı: katalogdaki kartlar da bu cevaba göre ya mutfak
     * sayfasına ya filtreli görünüme bağlanıyor. Şablonda ayrı bir sayı
     * yazılsaydı eşik değiştiğinde kartlar 404'e bağlanırdı.
     */
    public static function sayfasiVar(?int $yayindakiTarifSayisi): bool
    {
        return (int) $yayindakiTarifSayisi >= self::ASGARI_TARIF;
    }

    public function index(Request $request, SeoIndexabilityService $indexability): View
    {
        $mutfaklar = $this->yeterliMutfaklar();

        return view('cuisines.index', [
            'mutfaklar' => $mutfaklar,
            // Kart gorseli: yeni gorsel uretilmiyor, var olan onaylilardan
            // seciliyor. Gorseli olmayan mutfak metin karti olarak kaliyor.
            'kapaklar' => app(HubCoverPicker::class)->sutunaGore('cuisine_id', $mutfaklar->pluck('id')->all()),
            'seoIndexability' => $indexability->forRequest($request, route('cuisines.index')),
        ]);
    }

    public function show(Request $request, string $slug, SeoIndexabilityService $indexability, PublicMediaEligibilityService $gate, SeoSchemaService $schema): View
    {
        $mutfak = Cuisine::query()->where('slug', $slug)->first();

        abort_if($mutfak === null, 404);

        $tarifler = $this->tarifleriIcin($mutfak);

        // Eşiğin altındaki mutfağın sayfası yok: iki tarifle açılmış bir sayfa
        // ziyaretçiye de arama motoruna da bir şey vermiyor.
        abort_if($tarifler->count() < self::ASGARI_TARIF, 404);

        $canonical = route('cuisines.show', $mutfak->slug);
        $isKibris = $mutfak->slug === 'kibris-mutfagi';

        // Kıbrıs için 5 varyantı tek sayfada topla: "Kıbrıs", "Kıbrıs Yemek", "Kıbrıs Yemek Tarif", "Yemek Kıbrıs", "Kıbrıs Yemekleri"
        // Ayrı 5 sayfa = %100 kanibalizasyon. Tek güçlü hub, 5 niyeti de karşılar.
        if ($isKibris) {
            $title = 'Kıbrıs Yemekleri — 65 Doğrulanmış Kıbrıs Mutfağı Tarifi | House Chef Cyprus';
            $description = 'Kıbrıs Yemekleri, Kıbrıs Mutfağı ve Kıbrıs Yemek Tarifleri: hellim, şeftali kebabı, molehiya, kolokas ve 61 ada lezzeti daha. 65 doğrulanmış tarif, ölçülü ve adımlı.';
            $seoTitle = $title;
            $h1 = 'Kıbrıs Yemekleri — Kıbrıs Mutfağı Tarifleri';
        } else {
            $title = $mutfak->name.' Tarifleri';
            $description = $mutfak->name.' mutfağından '.$tarifler->count().' doğrulanmış tarif: ölçüler, süreler ve adım adım anlatım.';
            $seoTitle = $mutfak->name.' Tarifleri · '.$tarifler->count().' Doğrulanmış Tarif · House Chef Cyprus';
            $h1 = $mutfak->name.' Tarifleri';
        }

        $collectionSchema = $schema->collectionPage(
            $isKibris ? 'Kıbrıs Yemekleri' : $mutfak->name.' Tarifleri',
            $description,
            $canonical,
            $tarifler,
            [
                ['name' => 'Ana Sayfa', 'url' => route('home')],
                ['name' => 'Mutfaklar', 'url' => route('cuisines.index')],
                ['name' => $mutfak->name, 'url' => $canonical],
            ],
        );

        // Kıbrıs için FAQPage + WebSite SearchAction'ı aynı graph'a ekle — GEO ve PAA kazanır.
        $seoSchema = $collectionSchema;
        if ($isKibris) {
            $seoSchema['@graph'][] = [
                '@type' => 'FAQPage',
                'mainEntity' => [
                    [
                        '@type' => 'Question',
                        'name' => 'Kıbrıs Yemekleri nelerdir?',
                        'acceptedAnswer' => ['@type' => 'Answer', 'text' => 'Kıbrıs Yemekleri; hellim, şeftali kebabı, molehiya, kolokas, magarina bulli, pirohu, pilavuna ve 58 ada lezzeti daha olmak üzere toplam 65 doğrulanmış Kıbrıs Mutfağı tarifini kapsar. House Chef Cyprus arşivi her tarifi gramajlı, süreli ve adımlı olarak belgeler.'],
                    ],
                    [
                        '@type' => 'Question',
                        'name' => 'Kıbrıs Yemek Tarifi nereden bulunur?',
                        'acceptedAnswer' => ['@type' => 'Answer', 'text' => 'Doğrulanmış 65 Kıbrıs Yemek Tarifi bu sayfada listelenir. “Kıbrıs Usulü” etiketli her tarif mutfak ekibince ölçülmüş, denenmiş ve besin değerleriyle birlikte yayınlanır. Aradığınız Kıbrıs Yemek Tarifini filtreleyip adımlarıyla pişirebilirsiniz.'],
                    ],
                    [
                        '@type' => 'Question',
                        'name' => 'Kıbrıs Mutfağı ile Kıbrıs Yemekleri aynı mı?',
                        'acceptedAnswer' => ['@type' => 'Answer', 'text' => 'Evet. “Kıbrıs Mutfağı”, “Kıbrıs Yemekleri” ve “Yemek Kıbrıs” aramaları aynı ada mutfağını ifade eder. Bu sayfa 5 varyantın tamamı için kanonik kaynaktır ve aynı 65 tarif koleksiyonunu sunar — ayrı 5 sayfa kanibalizasyon yaratır.'],
                    ],
                    [
                        '@type' => 'Question',
                        'name' => 'En meşhur Kıbrıs Yemekleri hangileri?',
                        'acceptedAnswer' => ['@type' => 'Answer', 'text' => 'Hellim kızartması, Şeftali Kebabı (gömlek içinde), Molehiya (kurutulmuş yapraklı güveç), Kolokas (taro kökü), Magarina Bulli (tavuklu makarna), Pirohu (norlu mantı) ve Pilavuna (hellimli Paskalya çöreği) en çok aranan Kıbrıs Yemekleri arasındadır.'],
                    ],
                    [
                        '@type' => 'Question',
                        'name' => 'Kıbrıs Yemekleri kaç tariftir?',
                        'acceptedAnswer' => ['@type' => 'Answer', 'text' => 'House Chef Cyprus arşivinde 65 doğrulanmış Kıbrıs Yemekleri tarifi vardır. Tamamı “Kıbrıs Mutfağı” mutfağına aittir ve her biri görseli, ölçüsü ve pişirme süresiyle birlikte belgelenmiştir.'],
                    ],
                ],
            ];
        }

        return view('cuisines.show', [
            'mutfak' => $mutfak,
            'kardesler' => self::sayfasiOlanlar()
                ->map(fn (Cuisine $m): array => ['title' => $m->name, 'url' => route('cuisines.show', $m->slug)])
                ->all(),
            'tarifler' => $tarifler,
            'mediaEligibility' => $gate,
            'seoTitle' => $seoTitle ?? null,
            'seoDescription' => $description,
            'seoH1' => $h1 ?? null,
            'isKibrisHub' => $isKibris,
            'seoIndexability' => $indexability->forRequest($request, $canonical),
            'seoSchema' => $seoSchema,
        ]);
    }

    /**
     * Sayfası olan mutfaklar. Sitemap de bu listeyi kullanır; liste ile sitemap
     * ayrışırsa sitemap var olmayan adresleri ilan eder.
     *
     * @return Collection<int, Cuisine>
     */
    public static function sayfasiOlanlar(): Collection
    {
        return (new self)->yeterliMutfaklar();
    }

    /** @return Collection<int, Cuisine> */
    private function yeterliMutfaklar(): Collection
    {
        // Süzme SQL'de değil koleksiyonda: SQLite toplama içermeyen bir
        // sorguda HAVING kabul etmiyor ve testler SQLite üzerinde koşuyor.
        // Mutfak sayısı iki haneli, bunun bir maliyeti yok.
        return Cuisine::query()
            ->withCount(['recipes' => fn ($q) => $q->where('status', 'published')->where('verification_status', 'verified')])
            ->withMax(['recipes as latest_published_at' => fn ($q) => $q->where('status', 'published')->where('verification_status', 'verified')], 'published_at')
            ->orderByDesc('recipes_count')
            ->get()
            ->filter(fn (Cuisine $m): bool => (int) $m->recipes_count >= self::ASGARI_TARIF)
            ->values();
    }

    /** @return Collection<int, Recipe> */
    private function tarifleriIcin(Cuisine $mutfak): Collection
    {
        return Recipe::query()
            ->where('status', 'published')
            ->where('verification_status', 'verified')
            ->translatedInto()
            ->where('cuisine_id', $mutfak->id)
            ->with(['media' => fn ($q) => $q->where('visibility', 'public'), 'category'])
            ->orderByDesc('published_at')
            ->get();
    }
}
