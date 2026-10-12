<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Models\Cuisine;
use App\Models\Recipe;
use App\Services\Media\HubCoverPicker;
use App\Services\Media\PublicMediaEligibilityService;
use App\Services\Seo\LocalePageAvailability;
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
        abort_unless(LocalePageAvailability::taksonomiAdiVar('cuisine', $mutfak->slug), 404);

        $tarifler = $this->tarifleriIcin($mutfak);

        // Eşiğin altındaki mutfağın sayfası yok: iki tarifle açılmış bir sayfa
        // ziyaretçiye de arama motoruna da bir şey vermiyor.
        abort_if($tarifler->count() < self::ASGARI_TARIF, 404);

        $canonical = route('cuisines.show', $mutfak->slug);
        $isKibris = $mutfak->slug === 'kibris-mutfagi' && app()->getLocale() === 'tr';
        // EN money-hub: İngilizce SERP "Cypriot/Cyprus" terimleriyle arıyor;
        // Türkçe taksonomi adıyla üretilen "Kıbrıs Mutfağı Recipes" başlığı
        // ne kullanıcıyla ne sorguyla eşleşiyordu. TR dalına dokunulmaz.
        $isKibrisEn = $mutfak->slug === 'kibris-mutfagi' && app()->getLocale() === 'en';
        $title = $isKibris ? 'Kıbrıs Yemekleri ve Yemek Tarifleri | House Chef Cyprus' : ($isKibrisEn ? __('cuisine.kibris_en_title') : __('cuisine.page_title', ['name' => taxonomy_name($mutfak), 'count' => $tarifler->count()]));
        $description = $isKibris
            ? 'Kıbrıs yemeklerini keşfedin: hellim, şeftali kebabı, molehiya ve kolokas. '.$tarifler->count().' tarifin malzemelerini, ölçülerini ve pişirme adımlarını inceleyin.'
            : ($isKibrisEn ? __('cuisine.kibris_en_desc') : __('cuisine.meta_description', ['name' => taxonomy_name($mutfak), 'count' => $tarifler->count()]));
        $h1 = $isKibris ? 'Kıbrıs Yemekleri ve Yemek Tarifleri' : ($isKibrisEn ? __('cuisine.kibris_en_h1') : __('cuisine.h1', ['name' => taxonomy_name($mutfak)]));
        $faq = $isKibris ? [
            ['question' => 'Kıbrıs Yemekleri nelerdir?', 'answer' => 'Hellim, şeftali kebabı, molehiya, kolokas, magarina bulli, pirohu ve pilavuna Kıbrıs mutfağında karşılaşabileceğiniz lezzetler arasındadır. Malzemeler ve hazırlama yöntemleri aileden aileye değişebilir.'],
            ['question' => 'Kıbrıs Yemek Tarifi nereden bulunur?', 'answer' => 'Bu sayfadaki '.$tarifler->count().' tarifin her birinde malzemeler gram ve ev ölçüsüyle, hazırlık adımları süreleriyle birlikte yer alır. Evdeki malzemelerle yapılabilecek Kıbrıs yemeklerini malzemeye göre tarif bulma aracıyla da arayabilirsiniz.'],
            ['question' => 'Şeftali kebabında şeftali var mı?', 'answer' => 'Şeftali kebabı adını meyveden alan bir tatlı değildir. Kıyma, soğan, maydanoz ve baharatlarla hazırlanan harç, gömlek yağına sarılarak pişirilir.'],
        ] : ($isKibrisEn ? [
            ['question' => __('cuisine.kibris_en_faq_1_q'), 'answer' => __('cuisine.kibris_en_faq_1_a')],
            ['question' => __('cuisine.kibris_en_faq_2_q'), 'answer' => __('cuisine.kibris_en_faq_2_a')],
            ['question' => __('cuisine.kibris_en_faq_3_q'), 'answer' => __('cuisine.kibris_en_faq_3_a')],
        ] : []);
        $seoSchema = $schema->collectionPage($h1, $description, $canonical, $tarifler, [
            ['name' => __('nav.home'), 'url' => route('home')],
            ['name' => __('cuisine.breadcrumb'), 'url' => route('cuisines.index')],
            ['name' => taxonomy_name($mutfak), 'url' => $canonical],
        ]);
        if ($faq !== []) {
            $seoSchema['@graph'][] = [
                '@type' => 'FAQPage',
                'mainEntity' => array_map(static fn (array $item): array => [
                    '@type' => 'Question', 'name' => $item['question'],
                    'acceptedAnswer' => ['@type' => 'Answer', 'text' => $item['answer']],
                ], $faq),
            ];
        }

        return view('cuisines.show', [
            'mutfak' => $mutfak,
            'kardesler' => self::sayfasiOlanlar()
                ->map(fn (Cuisine $m): array => ['title' => taxonomy_name($m), 'url' => route('cuisines.show', $m->slug)])
                ->all(),
            'tarifler' => $tarifler,
            'mediaEligibility' => $gate,
            'seoTitle' => $title,
            'cuisineFaq' => $faq,
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
        //
        // Sayım o dile çevrilmiş tariflerle yapılır (show() ile aynı tanım).
        // /en/cuisines eskiden Türkçe sayımla liste basıyordu ve kartların
        // bir kısmı İngilizcede 404 veren sayfalara gidiyordu.
        $sayilar = self::ceviriSayilari();

        return Cuisine::query()
            ->whereIn('slug', array_keys($sayilar))
            ->withMax(['recipes as latest_published_at' => fn ($q) => $q->where('status', 'published')->where('verification_status', 'verified')], 'published_at')
            ->get()
            ->each(fn (Cuisine $m) => $m->setAttribute('recipes_count', $sayilar[$m->slug] ?? 0))
            ->filter(fn (Cuisine $m): bool => self::sayfasiVar((int) $m->recipes_count))
            ->sortByDesc('recipes_count')
            ->values();
    }

    /** @return Collection<int, Recipe> */
    /**
     * Bu dilde çevrilmiş yayındaki tarif sayısı, slug'a göre.
     *
     * Ana sayfa hub'lara sabit bağlantı veriyordu; tarifler o dile
     * çevrilmemişse hub 404 veriyor ve bağlantı kırılıyordu (27 Eylül 2026:
     * /en/kategoriler/corba, /en/mutfaklar/akdeniz-mutfagi). Sayım
     * tarifleriIcin() ile aynı tanımı kullanır.
     *
     * @return array<string, int>
     */
    public static function ceviriSayilari(): array
    {
        return cache()->remember('hub-ceviri-sayilari:mutfak:'.app()->getLocale(), now()->addMinutes(10), static function (): array {
            $sayilar = Recipe::query()->where('status', 'published')->where('verification_status', 'verified')
                ->translatedInto()->whereNotNull('cuisine_id')->pluck('cuisine_id')->countBy();

            return Cuisine::query()->whereIn('id', $sayilar->keys())->pluck('slug', 'id')
                ->mapWithKeys(static fn (string $slug, int $id): array => [$slug => (int) $sayilar[$id]])
                // Adı o dile çevrilmemiş mutfak o dilde yok: sayfası Türkçe
                // başlıkla açılıyordu. Sayım sıfır sayılır ki her bağlantı
                // basan yer aynı cevabı alsın.
                ->filter(static fn (int $adet, string $slug): bool => LocalePageAvailability::taksonomiAdiVar('cuisine', $slug))
                ->all();
        });
    }

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
