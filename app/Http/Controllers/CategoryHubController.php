<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\Recipe;
use App\Services\Media\HubCoverPicker;
use App\Services\Media\PublicMediaEligibilityService;
use App\Services\Seo\LocalePageAvailability;
use App\Services\Seo\SeoIndexabilityService;
use App\Services\Seo\SeoSchemaService;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Http\Request;
use Illuminate\View\View;

final class CategoryHubController extends Controller
{
    private const ASGARI_TARIF = 4;

    public function index(Request $request, SeoIndexabilityService $indexability): View
    {
        $kategoriler = self::sayfasiOlanlar();

        return view('categories.index', [
            'kategoriler' => $kategoriler,
            'kapaklar' => app(HubCoverPicker::class)->sutunaGore('category_id', $kategoriler->pluck('id')->all()),
            'seoIndexability' => $indexability->forRequest($request, route('categories.index')),
        ]);
    }

    public function show(Request $request, string $slug, SeoIndexabilityService $indexability, PublicMediaEligibilityService $gate, SeoSchemaService $schema): View
    {
        $kategori = Category::query()->where('slug', $slug)->first();
        abort_if($kategori === null, 404);
        abort_unless(LocalePageAvailability::taksonomiAdiVar('category', $kategori->slug), 404);

        $tarifler = $this->tarifleriIcin($kategori);
        abort_if($tarifler->count() < self::ASGARI_TARIF, 404);

        $canonical = route('categories.show', $kategori->slug);
        $description = __('category.meta_description', ['name' => taxonomy_name($kategori), 'count' => $tarifler->count()]);

        return view('categories.show', [
            'kategori' => $kategori,
            'kardesler' => self::sayfasiOlanlar()
                ->map(fn ($k): array => ['title' => taxonomy_name($k), 'url' => route('categories.show', $k->slug)])
                ->all(), 'tarifler' => $tarifler, 'mediaEligibility' => $gate,
            'seoIndexability' => $indexability->forRequest($request, $canonical),
            'seoSchema' => $schema->collectionPage(__('category.h1', ['name' => taxonomy_name($kategori)]), $description, $canonical, $tarifler, [
                ['name' => __('nav.home'), 'url' => route('home')],
                ['name' => __('category.breadcrumb'), 'url' => route('categories.index')],
                ['name' => taxonomy_name($kategori), 'url' => $canonical],
            ]),
        ]);
    }

    /** @return Collection<int, Category> */
    public static function sayfasiOlanlar(): Collection
    {
        // Sayım o dile çevrilmiş tariflerle yapılır (show() ile aynı tanım);
        // Türkçe sayım yabancı listede 404 veren kartlar basıyordu.
        $sayilar = self::ceviriSayilari();

        return Category::query()
            ->whereIn('slug', array_keys($sayilar))
            ->withMax(['recipes as latest_published_at' => fn ($q) => $q->where('status', 'published')->where('verification_status', 'verified')], 'published_at')
            ->get()
            ->each(fn (Category $k) => $k->setAttribute('recipes_count', $sayilar[$k->slug] ?? 0))
            ->filter(fn (Category $k): bool => self::sayfasiVar((int) $k->recipes_count))
            ->sortByDesc('recipes_count')
            ->values();
    }

    /** @return Collection<int, Recipe> */
    public static function sayfasiVar(?int $tarifSayisi): bool
    {
        return (int) $tarifSayisi >= self::ASGARI_TARIF;
    }

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
        return cache()->remember('hub-ceviri-sayilari:kategori:'.app()->getLocale(), now()->addMinutes(10), static function (): array {
            $sayilar = Recipe::query()->where('status', 'published')->where('verification_status', 'verified')
                ->translatedInto()->whereNotNull('category_id')->pluck('category_id')->countBy();

            return Category::query()->whereIn('id', $sayilar->keys())->pluck('slug', 'id')
                ->mapWithKeys(static fn (string $slug, int $id): array => [$slug => (int) $sayilar[$id]])
                // Adı o dile çevrilmemiş kategori o dilde yok: sayfası Türkçe
                // başlıkla açılıyordu. Sayım sıfır sayılır ki her bağlantı
                // basan yer aynı cevabı alsın.
                ->filter(static fn (int $adet, string $slug): bool => LocalePageAvailability::taksonomiAdiVar('category', $slug))
                ->all();
        });
    }

    private function tarifleriIcin(Category $category): Collection
    {
        return Recipe::query()->where('status', 'published')->where('verification_status', 'verified')
            ->translatedInto()->where('category_id', $category->id)
            ->with(['media' => fn ($q) => $q->where('visibility', 'public'), 'category'])
            ->orderByDesc('published_at')->get();
    }
}
