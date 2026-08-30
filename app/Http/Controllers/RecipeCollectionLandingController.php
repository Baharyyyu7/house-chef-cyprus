<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Models\Recipe;
use App\Services\Media\PublicMediaEligibilityService;
use App\Services\Seo\SeoIndexabilityService;
use App\Services\Seo\SeoSchemaService;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\View\View;

final class RecipeCollectionLandingController extends Controller
{
    private const ASGARI_TARIF = 4;

    /** @var array<string, array{title:string,description:string,max_minutes:int}> */
    private const SURELER = [
        '20-dakika' => ['title' => '20 Dakikada Hazır Tarifler', 'description' => 'Toplam 20 dakika veya daha kısa sürede hazırlanan doğrulanmış tarifler.', 'max_minutes' => 20],
        '30-dakika' => ['title' => '30 Dakikada Hazır Tarifler', 'description' => 'Toplam 30 dakika veya daha kısa sürede hazırlanan doğrulanmış tarifler.', 'max_minutes' => 30],
    ];

    /** @var array<string, array{title:string,description:string,difficulty:string}> */
    private const ZORLUKLAR = [
        'kolay' => ['title' => 'Kolay Tarifler', 'description' => 'Mutfakta güvenle başlayabileceğiniz doğrulanmış kolay tarifler.', 'difficulty' => 'easy'],
        'orta' => ['title' => 'Orta Zorlukta Tarifler', 'description' => 'Biraz daha fazla hazırlık isteyen doğrulanmış tarifler.', 'difficulty' => 'medium'],
        'zor' => ['title' => 'Zor Tarifler', 'description' => 'Deneyim ve dikkat isteyen doğrulanmış tarifler.', 'difficulty' => 'hard'],
    ];

    public function sure(Request $request, string $slug, SeoIndexabilityService $indexability, PublicMediaEligibilityService $gate, SeoSchemaService $schema): View
    {
        return $this->goster($request, 'sure', $slug, $indexability, $gate, $schema);
    }

    public function zorluk(Request $request, string $slug, SeoIndexabilityService $indexability, PublicMediaEligibilityService $gate, SeoSchemaService $schema): View
    {
        return $this->goster($request, 'zorluk', $slug, $indexability, $gate, $schema);
    }

    /** @return Collection<int, array{type:string,slug:string,title:string,url:string,lastmod:?string}> */
    public static function sayfasiOlanlar(): Collection
    {
        $controller = new self;

        return collect(['sure' => self::SURELER, 'zorluk' => self::ZORLUKLAR])
            ->flatMap(function (array $tanimlar, string $tur) use ($controller): array {
                return collect($tanimlar)->map(function (array $tanim, string $slug) use ($controller, $tur): ?array {
                    if (! self::sayfasiVar($controller->sorgu($tur, $tanim)->count())) {
                        return null;
                    }

                    return [
                        'type' => $tur,
                        'slug' => $slug,
                        'title' => $tanim['title'],
                        'url' => route('collections.'.$tur, $slug),
                        'lastmod' => $controller->sonYayinTarihi($tur, $tanim),
                    ];
                })->filter()->values()->all();
            })->values();
    }

    public static function sayfasiVar(?int $tarifSayisi): bool
    {
        return (int) $tarifSayisi >= self::ASGARI_TARIF;
    }

    /** @param array<string,mixed> $plan */
    public static function yapisalSorguAdresi(array $plan): ?string
    {
        $controller = new self;

        foreach (self::SURELER as $slug => $tanim) {
            if (($plan['max_minutes'] ?? null) === $tanim['max_minutes']
                && self::sayfasiVar($controller->sorgu('sure', $tanim)->count())) {
                return route('collections.sure', $slug);
            }
        }

        foreach (self::ZORLUKLAR as $slug => $tanim) {
            if (($plan['difficulty'] ?? null) === $tanim['difficulty']
                && self::sayfasiVar($controller->sorgu('zorluk', $tanim)->count())) {
                return route('collections.zorluk', $slug);
            }
        }

        return null;
    }

    private function goster(Request $request, string $tur, string $slug, SeoIndexabilityService $indexability, PublicMediaEligibilityService $gate, SeoSchemaService $schema): View
    {
        $tanim = ($tur === 'sure' ? self::SURELER : self::ZORLUKLAR)[$slug] ?? null;
        abort_if($tanim === null, 404);

        $tarifler = $this->sorgu($tur, $tanim)
            ->with(['media' => fn ($query) => $query->where('visibility', 'public'), 'category', 'cuisine'])
            ->orderByDesc('published_at')
            ->get();

        abort_unless(self::sayfasiVar($tarifler->count()), 404);

        $canonical = route('collections.'.$tur, $slug);

        return view('recipe-collections.show', [
            'koleksiyon' => $tanim,
            'tarifler' => $tarifler,
            'mediaEligibility' => $gate,
            'seoIndexability' => $indexability->forRequest($request, $canonical),
            'seoSchema' => $schema->collectionPage(
                $tanim['title'],
                $tanim['description'],
                $canonical,
                $tarifler,
                [
                    ['name' => 'Ana Sayfa', 'url' => route('home')],
                    ['name' => 'Tarifler', 'url' => route('recipes.index')],
                    ['name' => $tanim['title'], 'url' => $canonical],
                ],
            ),
        ]);
    }

    /** @param array<string, int|string> $tanim */
    private function sorgu(string $tur, array $tanim): Builder
    {
        return Recipe::query()
            ->where('status', 'published')
            ->where('verification_status', 'verified')
            ->when(
                $tur === 'sure',
                // COALESCE: sürelerden biri NULL ise toplam da NULL olur ve
                // tarif karşılaştırmadan sessizce düşer. Bugün üretimde NULL
                // yok, ama eksik süreyle eklenecek bir tarif listeden görünmez
                // biçimde kaybolurdu.
                fn (Builder $query): Builder => $query->whereRaw('(COALESCE(prep_minutes,0) + COALESCE(cook_minutes,0) + COALESCE(rest_minutes,0)) <= ?', [(int) $tanim['max_minutes']]),
                fn (Builder $query): Builder => $query->where('difficulty', (string) $tanim['difficulty'])
            );
    }

    /** @param array<string,int|string> $tanim */
    private function sonYayinTarihi(string $tur, array $tanim): ?string
    {
        $tarih = $this->sorgu($tur, $tanim)->max('published_at');

        return is_string($tarih) && $tarih !== '' ? $tarih : null;
    }
}
