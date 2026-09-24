<?php

declare(strict_types=1);

namespace App\Services\Coni;

use App\Models\BlogPost;
use App\Models\ConiAction;
use App\Models\Recipe;
use App\Services\Seo\BlogIndexability;
use App\Services\Seo\LocaleUrlService;
use App\Services\Seo\RankedUrlGuardService;
use App\Services\Seo\RecipeTranslationGate;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Cache;

/**
 * Odaktaki sayfalar ve onlara sitenin kendi içinden bağlantı verilmesi.
 *
 * Odak sayfası, aynı dildeki konu komşularının "ilgili" kutularında öne
 * alınır. Bağlantı yalnız konu gerçekten örtüşüyorsa verilir: aynı
 * kategori, aynı mutfak ya da adında ortak bir kelime kökü. Her sayfaya
 * aynı bağlantıyı basmak okuyucuya bir şey kazandırmaz ve arama motoru
 * gözünde yapay bir bağlantı ağı gibi durur.
 */
final class ConiOdakKumesi
{
    /** Konu karşılaştırmasında anlam taşımayan adres kelimeleri. */
    private const BOS_KELIMELER = ['tarifi', 'tarif', 'nasil', 'yapilir', 'yapimi', 'evde', 'kolay', 'pratik', 'usulu', 'rehberi', 'rehber', 'icin', 'puf', 'noktalari', 'hazirlanir', 'lezzetli', 'kibris', 'kibrista', 'nedir', 'hangi', 'olur', 'gibi', 'tadinda', 'sosu', 'sirlari', 'yontemleri', 'teknikleri', 'kacinci', 'dakika', 'dakikada', 'derece', 'ipuclari'];

    /** @var array<string, array{dil: string, tur: string, model: BlogPost|Recipe, adres: string}|null> */
    private array $cozulen = [];

    public function __construct(
        private readonly LocaleUrlService $diller,
        private readonly RankedUrlGuardService $koruma
    ) {}

    /**
     * Adresin arkasındaki kayıt: hangi dil, yazı mı tarif mi, standart adresi.
     *
     * Search Console aynı sayfayı eski ve yeni yazımıyla ayrı satır olarak
     * getiriyor (/en/tarifler/x ve /en/recipes/x). İkisi aynı kayda çözülür
     * ve tek standart adrese toplanır; yoksa bir sayfanın gösterimi ikiye
     * bölünür ve aday sıralaması yanlış olur.
     *
     * @return array{dil: string, tur: string, model: BlogPost|Recipe, adres: string}|null
     */
    public function coz(string $adres): ?array
    {
        if (array_key_exists($adres, $this->cozulen)) {
            return $this->cozulen[$adres];
        }

        return $this->cozulen[$adres] = $this->cozumle($adres);
    }

    /** @return array{dil: string, tur: string, model: BlogPost|Recipe, adres: string}|null */
    private function cozumle(string $adres): ?array
    {
        $parcalar = array_values(array_filter(explode('/', trim((string) parse_url($adres, PHP_URL_PATH), '/')), 'strlen'));
        $desteklenen = array_keys((array) config('locales.supported', []));
        $dil = $this->diller->defaultLocale();

        if (count($parcalar) > 2 && in_array($parcalar[0], $desteklenen, true)) {
            $dil = (string) array_shift($parcalar);
        }

        if (count($parcalar) !== 2 || ! array_key_exists($dil, $this->diller->enabledLocales())) {
            return null;
        }

        [$bolum, $slug] = $parcalar;
        $slug = rawurldecode($slug);

        if ($bolum === 'blog') {
            $yazi = BlogPost::query()->where('slug', $slug)->first();

            return $yazi === null ? null : ['dil' => $dil, 'tur' => 'blog', 'model' => $yazi, 'adres' => $this->diller->urlFor($dil, '/blog/'.$yazi->slug)];
        }

        if (in_array($bolum, $this->tarifBolumleri(), true)) {
            $tarif = Recipe::query()->where('slug', $slug)->first();

            return $tarif === null ? null : ['dil' => $dil, 'tur' => 'recipe', 'model' => $tarif, 'adres' => $this->diller->urlFor($dil, '/tarifler/'.$tarif->slug)];
        }

        return null;
    }

    /**
     * Sayfa yayında, o dilde çevrilmiş ve dizine açık mı.
     *
     * Kapanma tanımı sıralanan adres muhafızıyla ortak: taslağa çekilmiş,
     * noindex ya da başka bir nüshaya kanonikle bağlanmış sayfa odak olamaz.
     * Kapalı sayfaya bağlantı taşımak emeği boşa harcamaktır.
     *
     * @param  array{dil: string, tur: string, model: BlogPost|Recipe, adres: string}  $kayit
     */
    public function acik(array $kayit): bool
    {
        $model = $kayit['model'];

        if (! $model->hasTranslation($kayit['dil'])) {
            return false;
        }

        if ($model instanceof Recipe && ($model->status !== 'published' || $model->verification_status !== 'verified')) {
            return false;
        }

        if ($model instanceof Recipe && ! app(RecipeTranslationGate::class)->hazir($model, $kayit['dil'])) {
            return false;
        }

        // Yazının dizine açıklığı o dildeki gövdeye göre ölçülüyor; sayfa
        // da o dilde servis edilirken aynı ölçüyle karar veriliyor.
        $onceki = app()->getLocale();
        app()->setLocale($kayit['dil']);

        try {
            return $this->koruma->kapananlar([['page' => $kayit['adres'], 'impressions' => PHP_INT_MAX]]) === [];
        } finally {
            app()->setLocale($onceki);
        }
    }

    /**
     * Reklam taşıyabilen sayfa mı. Bu sitede gelir reklamdan geliyor;
     * reklam basmayan ince bir yazıya gelen tıklama para etmiyor.
     *
     * @param  array{dil: string, tur: string, model: BlogPost|Recipe, adres: string}  $kayit
     */
    public function kazandirir(array $kayit): bool
    {
        if (! $kayit['model'] instanceof BlogPost) {
            return true;
        }

        $onceki = app()->getLocale();
        app()->setLocale($kayit['dil']);

        try {
            return app(BlogIndexability::class)->reklamTasiyabilir($kayit['model']);
        } finally {
            app()->setLocale($onceki);
        }
    }

    /**
     * Dilin şu anki odak sayfaları.
     *
     * @return list<array{tur: string, id: int, slug: string, page: string}>
     */
    public function odaklar(string $dil): array
    {
        return Cache::remember('coni:odak:'.$dil, 600, static function () use ($dil): array {
            return ConiAction::query()->aktif()->where('kind', ConiAction::ODAK)->where('locale', $dil)->get()
                ->map(static fn (ConiAction $is): array => [
                    'tur' => (string) ($is->evidence['tur'] ?? ''),
                    'id' => (int) ($is->evidence['id'] ?? 0),
                    'slug' => (string) ($is->evidence['slug'] ?? ''),
                    'page' => $is->page,
                ])
                ->filter(static fn (array $o): bool => $o['id'] > 0)
                ->values()
                ->all();
        });
    }

    public function unut(): void
    {
        foreach (array_keys((array) config('locales.supported', [])) as $dil) {
            Cache::forget('coni:odak:'.$dil);
        }
    }

    /**
     * Yazı sayfasının "ilgili yazılar" kutusu.
     *
     * @param  Collection<int, BlogPost>  $yazilar
     * @param  list<string>  $iliskiler
     * @return Collection<int, BlogPost>
     */
    public function yazilaraEkle(Collection $yazilar, BlogPost|Recipe $simdiki, string $dil, int $adet, array $iliskiler = []): Collection
    {
        foreach ($this->odaklar($dil) as $odak) {
            if ($odak['tur'] !== 'blog' || ($simdiki instanceof BlogPost && $simdiki->id === $odak['id'])) {
                continue;
            }

            $mevcut = $yazilar->firstWhere('id', $odak['id']);
            $ayniKategori = $simdiki instanceof BlogPost && $mevcut === null
                && BlogPost::query()->whereKey($odak['id'])->where('blog_category_id', $simdiki->blog_category_id)->exists();

            if ($mevcut === null && ! $ayniKategori && ! $this->ilgili((string) $simdiki->slug, $odak['slug'])) {
                continue;
            }

            $yazi = $mevcut ?? BlogPost::query()->publiclyVisible()->translatedInto($dil)->with($iliskiler)->find($odak['id']);

            if ($yazi !== null) {
                $yazilar = $yazilar->reject(static fn (BlogPost $y): bool => $y->id === $yazi->id)->prepend($yazi);
            }
        }

        return $yazilar->take($adet)->values();
    }

    /**
     * Tarif sayfasının "ilgili tarifler" kutusu.
     *
     * @param  Collection<int, Recipe>  $tarifler
     * @return Collection<int, Recipe>
     */
    public function tariflereEkle(Collection $tarifler, Recipe $simdiki, string $dil, int $adet): Collection
    {
        foreach ($this->odaklar($dil) as $odak) {
            if ($odak['tur'] !== 'recipe' || $simdiki->id === $odak['id']) {
                continue;
            }

            $tarif = $tarifler->firstWhere('id', $odak['id']) ?? Recipe::query()
                ->where('status', 'published')
                ->where('verification_status', 'verified')
                ->translatedInto($dil)
                ->with(['cuisine:id,public_id,name,slug', 'category:id,public_id,name,slug', 'media' => fn ($q) => $q->where('visibility', 'public'), 'dietaryTags:id,code,name'])
                ->find($odak['id']);

            if ($tarif === null) {
                continue;
            }

            $komsu = ($simdiki->category_id !== null && $tarif->category_id === $simdiki->category_id)
                || ($simdiki->cuisine_id !== null && $tarif->cuisine_id === $simdiki->cuisine_id)
                || $this->ilgili((string) $simdiki->slug, (string) $tarif->slug);

            if ($komsu) {
                $tarifler = $tarifler->reject(static fn (Recipe $t): bool => $t->id === $tarif->id)->prepend($tarif);
            }
        }

        return $tarifler->take($adet)->values();
    }

    /**
     * İki adresin konusu örtüşüyor mu.
     *
     * Türkçe ekler kelimeyi uzatıyor ("fırın", "fırında", "fırınlanmış");
     * kelimenin ilk beş harfi kök yerine geçiyor. Kaba ama bu iş için
     * yeterli: yanlış pozitifin bedeli ilgisiz bir kutuya bir bağlantı,
     * ölçülür ve odak iki hafta sonra biter.
     */
    public function ilgili(string $a, string $b): bool
    {
        return array_intersect($this->kokler($a), $this->kokler($b)) !== [];
    }

    /** @return list<string> */
    public function kokler(string $slug): array
    {
        // Kopya nüshaları ayıran "-B253B" gibi ek konu taşımaz.
        $slug = (string) preg_replace('/-[A-Z0-9]{5}$/', '', $slug);
        $kokler = [];

        foreach (explode('-', mb_strtolower($slug)) as $kelime) {
            if (mb_strlen($kelime) < 4 || in_array($kelime, self::BOS_KELIMELER, true) || ctype_digit($kelime)) {
                continue;
            }

            $kokler[] = mb_substr($kelime, 0, 5);
        }

        return array_values(array_unique($kokler));
    }

    /** @return list<string> */
    private function tarifBolumleri(): array
    {
        $bolumler = ['tarifler'];

        foreach ((array) config('locales.path_aliases', []) as $harita) {
            if (isset($harita['tarifler'])) {
                $bolumler[] = (string) $harita['tarifler'];
            }
        }

        return array_values(array_unique($bolumler));
    }
}
