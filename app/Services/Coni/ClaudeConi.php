<?php

declare(strict_types=1);

namespace App\Services\Coni;

use App\Models\BlogPost;
use App\Models\ConiAction;
use App\Models\ContentTranslation;
use App\Models\IntegrityReport;
use App\Models\Recipe;
use App\Models\RecipeStep;
use App\Services\Integrity\IntegrityReportLog;
use App\Services\Seo\IndexNowService;
use App\Services\Seo\LocaleUrlService;
use App\Services\Seo\RecipeTranslationGate;
use App\Services\Seo\SearchConsoleMeasurementService;
use App\Services\Seo\SeoMetadataService;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use InvalidArgumentException;
use Throwable;

/**
 * Claude Coni: sitenin SEO, GEO ve backlink çalışanı.
 *
 * Her gün aynı döngü, her açık dil için ayrı:
 *
 *   1. Ölç: Search Console sayfa satırları, dile göre bölünmüş.
 *   2. Değerlendir: süresi dolan her değişikliğin önceki iki haftası ile
 *      sonraki iki haftası yan yana konur; tıklama arttıysa kazanç,
 *      düştüyse kayıp, aradaysa nötr. Sonuç deftere yazılır.
 *   3. Seç: odağı olmayan her dilde, sıra birkaç basamak yükselirse en çok
 *      tıklama getirecek tek sayfa seçilir.
 *   4. Uygula: odak sayfası aynı dildeki konu komşularının "ilgili"
 *      kutularında öne alınır. İç bağlantı otomatik, geri alınabilir ve
 *      ölçülür.
 *   5. Yaz: her şey defterde (coni_actions) ve günlük raporda
 *      (claude_coni) durur.
 *
 * Başlık ve açıklama yazmak yargı işi; kalıpla üretilmez. O iş için
 * serp() var: yazıyı Claude oturumu dilin kendisinde yazar, bu servis
 * uygular, eski değeri saklar ve aynı ölçüye bağlar.
 *
 * Dokunmadığı şeyler: içerik yayınlamaz, toplu sayfa üretmez, dışarıya
 * e-posta göndermez, bağlantı satın almaz. Bu site 16 Ağustos 2026'da
 * ölçekli makine içeriği yüzünden bir kez zarar gördü.
 */
final class ClaudeConi
{
    /** Odak dönemi. Tek değişikliğin etkisi bundan kısa sürede okunmaz. */
    public const ODAK_GUNU = 14;

    /** Search Console verisi bu kadar gün geriden gelir. */
    public const GSC_GECIKMESI = 3;

    /** Aynı sayfa bu süre dolmadan yeniden odak olmaz; kazanç hariç. */
    public const TEKRAR_BEKLEME = 42;

    /** Ölçülemeyen iş bu kadar gün sonra "ölçülemedi" diye kapanır. */
    public const OLCUM_ZAMAN_ASIMI = 30;

    public const RAPOR = 'claude_coni';

    /** @var array<string, array<string, array{page: string, impressions: int, clicks: int, engel: string}>> */
    private array $ceviriBekleyen = [];

    public function __construct(
        private readonly SearchConsoleMeasurementService $gsc,
        private readonly LocaleUrlService $diller,
        private readonly ConiOdakSecici $secici,
        private readonly ConiDegerlendirici $degerlendirici,
        private readonly ConiOdakKumesi $kume,
        private readonly IntegrityReportLog $log
    ) {}

    /** @return array<string, mixed> */
    public function calis(): array
    {
        return Cache::lock('coni:calis', 900)->block(5, function (): array {
            try {
                $satirlar = $this->gsc->pageRows(28);
            } catch (Throwable) {
                $satirlar = [];
            }

            if ($satirlar === []) {
                $this->log->record(self::RAPOR, 'attention', 0, ['olcum' => 'yok', 'not' => 'Search Console verisi alınamadı; seçim ve değerlendirme yapılmadı.']);

                return ['olcum' => 'yok'];
            }

            $degerlendirilen = $this->degerlendir();
            $diller = $this->dileGoreBirlestir($satirlar);
            $rapor = ['olcum' => 'var', 'pencere_gun' => 28, 'degerlendirilen' => $degerlendirilen, 'diller' => []];
            $yeniOdak = 0;

            foreach (array_keys($this->diller->enabledLocales()) as $dil) {
                $sayfalar = $diller[$dil] ?? [];
                $adaylar = $this->zenginlestir($this->secici->adaylar(array_values($sayfalar), $this->dislanan($dil)), $sayfalar);
                $aktif = ConiAction::query()->aktif()->where('kind', ConiAction::ODAK)->where('locale', $dil)->first();

                if ($aktif === null && ($aday = $this->ilkKazandiran($adaylar)) !== null) {
                    $aktif = $this->odakBaslat($dil, $aday);
                    $yeniOdak++;
                }

                $toplam = $this->toplam(array_values($sayfalar));
                $rapor['diller'][$dil] = [
                    'olcum' => $toplam,
                    'odak' => $aktif === null ? null : ['id' => $aktif->id, 'page' => $aktif->page, 'baslangic' => $aktif->applied_at->toDateString(), 'degerlendirme' => $aktif->evaluate_after->toDateString(), 'baseline' => $aktif->baseline],
                    'adaylar' => array_slice($adaylar, 0, 5),
                    'tiklanmayan' => $this->tiklanmayan(array_values($sayfalar)),
                    'nushalar' => array_slice($this->secici->nushalar(array_values($sayfalar)), 0, 5),
                    // Aramada görünen ama adımları kalıp çeviri olduğu için
                    // dizine kapalı tarifler: gerçek çevirisi ilk yazılacaklar.
                    'ceviri_bekleyen' => $this->ceviriSirasi($dil),
                ];
            }

            $this->kume->unut();
            $this->log->record(self::RAPOR, 'ok', $yeniOdak, $rapor);

            return $rapor;
        });
    }

    /**
     * Arama sonucunda görünen başlık ve açıklamayı tek dilde değiştirir.
     *
     * Varsayılan dilde seo_metadata, diğer dillerde content_translations
     * (seo_title, seo_description) kullanılır; sayfanın H1'i ve gövdesi
     * değişmez. Eski değer saklanır, geriAl() aynen geri koyar.
     *
     * @return array{id: int, page: string, dil: string, onceki: array<string, string|null>, yeni: array<string, string|null>}
     */
    public function serp(string $adres, ?string $baslik, ?string $aciklama, string $neden, bool $zorla = false): array
    {
        $baslik = $baslik !== null ? trim($baslik) : null;
        $aciklama = $aciklama !== null ? trim($aciklama) : null;

        if (($baslik === null || $baslik === '') && ($aciklama === null || $aciklama === '')) {
            throw new InvalidArgumentException('Başlık ya da açıklamadan en az biri gerekli.');
        }

        if ($baslik !== null && $baslik !== '' && (mb_strlen($baslik) < 15 || mb_strlen($baslik) > 65)) {
            throw new InvalidArgumentException('Başlık 15-65 karakter olmalı; marka eki ayrıca ekleniyor.');
        }

        if ($aciklama !== null && $aciklama !== '' && (mb_strlen($aciklama) < 70 || mb_strlen($aciklama) > 160)) {
            throw new InvalidArgumentException('Açıklama 70-160 karakter olmalı.');
        }

        if (trim($neden) === '') {
            throw new InvalidArgumentException('Neden boş olamaz: üç hafta sonra bu değişikliği okuyacak olan onu arayacak.');
        }

        $kayit = $this->kume->coz($adres);

        if ($kayit === null || ! $this->kume->acik($kayit)) {
            throw new InvalidArgumentException('Adres yayında ve dizine açık bir yazı ya da tarife çözülmedi: '.$adres);
        }

        $sayfa = $kayit['adres'];
        $dil = $kayit['dil'];
        $baska = ConiAction::query()->aktif()->where('page_hash', ConiAction::hash($sayfa))->where('kind', ConiAction::SERP)->exists();

        if ($baska && ! $zorla) {
            throw new InvalidArgumentException('Bu sayfada ölçülmeyi bekleyen bir SERP değişikliği var. Aynı anda iki değişiklik ölçülemez.');
        }

        $onceki = $this->serpDegerleri($kayit['model'], $dil);
        $yeni = [
            'title' => $baslik !== null && $baslik !== '' ? $baslik : $onceki['title'],
            'description' => $aciklama !== null && $aciklama !== '' ? $aciklama : $onceki['description'],
        ];

        $is = DB::transaction(function () use ($kayit, $dil, $sayfa, $onceki, $yeni, $neden): ConiAction {
            $this->serpYaz($kayit['model'], $dil, $yeni);

            return ConiAction::query()->create([
                'locale' => $dil,
                'page' => $sayfa,
                'page_hash' => ConiAction::hash($sayfa),
                'kind' => ConiAction::SERP,
                'status' => ConiAction::UYGULANDI,
                'reason' => trim($neden),
                'evidence' => ['tur' => $kayit['tur'], 'id' => $kayit['model']->id, 'slug' => $kayit['model']->slug],
                'before' => $onceki,
                'after' => $yeni,
                'baseline' => $this->sonOlcum($sayfa),
                'applied_at' => now(),
                'evaluate_after' => $this->degerlendirmeGunu(now()),
            ]);
        });

        $this->bildir([$sayfa]);

        return ['id' => $is->id, 'page' => $sayfa, 'dil' => $dil, 'onceki' => $onceki, 'yeni' => $yeni];
    }

    public function geriAl(int $id): ConiAction
    {
        $is = ConiAction::query()->findOrFail($id);

        if ($is->status === ConiAction::GERI_ALINDI) {
            return $is;
        }

        if ($is->kind === ConiAction::CEVIRI) {
            $tarif = Recipe::query()->find($is->evidence['id'] ?? 0);

            if ($tarif !== null) {
                foreach ((array) ($is->before['recipe'] ?? []) as $alan => $deger) {
                    $this->ceviriYaz($tarif, $is->locale, (string) $alan, $deger);
                }

                foreach (['steps' => 'instruction', 'step_titles' => 'title'] as $anahtar => $alan) {
                    foreach ((array) ($is->before[$anahtar] ?? []) as $adimId => $deger) {
                        $adim = RecipeStep::query()->where('recipe_id', $tarif->id)->find((int) $adimId);

                        if ($adim !== null && ($anahtar === 'steps' || ! empty($is->after['step_titles']))) {
                            $this->ceviriYaz($adim, $is->locale, $alan, $deger);
                        }
                    }
                }
            }

            $this->bildir([$is->page]);
        }

        if ($is->kind === ConiAction::SERP) {
            $model = ($is->evidence['tur'] ?? '') === 'recipe'
                ? Recipe::query()->find($is->evidence['id'] ?? 0)
                : BlogPost::query()->find($is->evidence['id'] ?? 0);

            if ($model !== null) {
                $this->serpYaz($model, $is->locale, (array) $is->before);
            }

            $this->bildir([$is->page]);
        }

        $is->update(['status' => ConiAction::GERI_ALINDI, 'evaluated_at' => now()]);
        $this->kume->unut();

        return $is;
    }

    /**
     * Son koşunun raporu ve defterin özeti; Claude oturumunun girdisi.
     *
     * @return array<string, mixed>
     */
    public function durum(): array
    {
        $rapor = IntegrityReport::query()->where('guard', self::RAPOR)->latest('id')->first();

        return [
            'rapor_zamani' => $rapor?->created_at?->toIso8601String(),
            'rapor' => $rapor?->payload,
            'aktif' => ConiAction::query()->aktif()->orderBy('locale')->get()
                ->map(static fn (ConiAction $is): array => ['id' => $is->id, 'kind' => $is->kind, 'locale' => $is->locale, 'page' => $is->page, 'baslangic' => $is->applied_at->toDateString(), 'degerlendirme' => $is->evaluate_after->toDateString(), 'neden' => $is->reason, 'after' => $is->after])
                ->all(),
            'sonuclanan' => ConiAction::query()->whereNotIn('status', [ConiAction::UYGULANDI])->latest('evaluated_at')->limit(20)->get()
                ->map(static fn (ConiAction $is): array => ['id' => $is->id, 'kind' => $is->kind, 'locale' => $is->locale, 'page' => $is->page, 'status' => $is->status, 'result' => $is->result])
                ->all(),
            'karne' => ConiAction::query()->select('status', DB::raw('count(*) as n'))->groupBy('status')->pluck('n', 'status')->all(),
        ];
    }

    /** @return array{clicks: int, impressions: int, position: float|null}|null */
    private function sonOlcum(string $sayfa): ?array
    {
        try {
            $satirlar = $this->gsc->pageRows(28);
        } catch (Throwable) {
            return null;
        }

        $olcum = $this->sayfaOlcumu($satirlar, $sayfa);

        return $olcum === null ? null : [...$olcum, 'pencere_gun' => 28, 'olculdu' => now()->toIso8601String()];
    }

    /** @return list<array<string, mixed>> */
    private function degerlendir(): array
    {
        $sonuc = [];
        $pencereler = [];

        $isler = ConiAction::query()->aktif()->whereDate('evaluate_after', '<=', today())->orderBy('applied_at')->limit(50)->get();

        foreach ($isler as $is) {
            $gun = $is->applied_at->copy()->startOfDay();
            $onceAraligi = [$gun->copy()->subDays(self::ODAK_GUNU)->toDateString(), $gun->copy()->subDay()->toDateString()];
            $sonraAraligi = [$gun->copy()->addDays(self::GSC_GECIKMESI)->toDateString(), $gun->copy()->addDays(self::GSC_GECIKMESI + self::ODAK_GUNU - 1)->toDateString()];

            try {
                foreach ([$onceAraligi, $sonraAraligi] as $aralik) {
                    $anahtar = implode('/', $aralik);
                    $pencereler[$anahtar] ??= $this->gsc->pageRowsBetween($aralik[0], $aralik[1]);
                }
            } catch (Throwable) {
                continue;
            }

            $once = $this->sayfaOlcumu($pencereler[implode('/', $onceAraligi)], $is->page) ?? ['clicks' => 0, 'impressions' => 0, 'position' => null];
            $sonra = $this->sayfaOlcumu($pencereler[implode('/', $sonraAraligi)], $is->page) ?? ['clicks' => 0, 'impressions' => 0, 'position' => null];

            if ($pencereler[implode('/', $onceAraligi)] === [] && $pencereler[implode('/', $sonraAraligi)] === []) {
                if ($is->evaluate_after->copy()->addDays(self::OLCUM_ZAMAN_ASIMI)->isPast()) {
                    $is->update(['status' => ConiAction::OLCULEMEDI, 'evaluated_at' => now()]);
                }

                continue;
            }

            $fark = $this->degerlendirici->sonuc($once, $sonra);
            $is->update([
                'status' => $fark['status'],
                'result' => ['once' => [...$once, 'aralik' => $onceAraligi], 'sonra' => [...$sonra, 'aralik' => $sonraAraligi], 'fark' => $fark],
                'evaluated_at' => now(),
            ]);

            $sonuc[] = ['id' => $is->id, 'kind' => $is->kind, 'locale' => $is->locale, 'page' => $is->page, 'status' => $fark['status'], 'fark' => $fark];
        }

        return $sonuc;
    }

    /**
     * @param  array{page: string, impressions: int, clicks: int, position: float, ctr: float, potansiyel: float, tur: string, id: int, slug: string, kazandirir: bool}  $aday
     */
    private function odakBaslat(string $dil, array $aday): ConiAction
    {
        return ConiAction::query()->create([
            'locale' => $dil,
            'page' => $aday['page'],
            'page_hash' => ConiAction::hash($aday['page']),
            'kind' => ConiAction::ODAK,
            'status' => ConiAction::UYGULANDI,
            'reason' => sprintf(
                '28 günde %d gösterim, %d tıklama, ortalama sıra %.1f. İlk üçe çıkarsa ayda yaklaşık %d ek tıklama. Konu komşularının ilgili kutularında öne alındı.',
                $aday['impressions'], $aday['clicks'], $aday['position'], (int) round($aday['potansiyel'])
            ),
            'evidence' => ['tur' => $aday['tur'], 'id' => $aday['id'], 'slug' => $aday['slug'], 'kaynak' => 'search_console', 'kaldirac' => 'ic_baglanti', 'reklam_tasir' => $aday['kazandirir']],
            'baseline' => ['clicks' => $aday['clicks'], 'impressions' => $aday['impressions'], 'position' => $aday['position'], 'ctr' => $aday['ctr'], 'pencere_gun' => 28, 'olculdu' => now()->toIso8601String()],
            'applied_at' => now(),
            'evaluate_after' => $this->degerlendirmeGunu(now()),
        ]);
    }

    private function degerlendirmeGunu(Carbon $uygulama): Carbon
    {
        // Sonraki pencere uygulamadan üç gün sonra başlar, iki hafta sürer;
        // verinin kesinleşmesi için üç gün daha beklenir.
        return $uygulama->copy()->startOfDay()->addDays(self::GSC_GECIKMESI + self::ODAK_GUNU + self::GSC_GECIKMESI);
    }

    /**
     * Search Console satırlarını dile ve standart adrese göre toplar.
     *
     * @param  list<array{page: string, impressions: int, clicks: int, position: float}>  $satirlar
     * @return array<string, array<string, array{page: string, impressions: int, clicks: int, position: float, tur: string, id: int, slug: string, kazandirir: bool}>>
     */
    private function dileGoreBirlestir(array $satirlar): array
    {
        $sonuc = [];
        $cozulen = [];

        foreach ($satirlar as $satir) {
            $adres = (string) $satir['page'];
            $cozulen[$adres] ??= $this->kume->coz($adres) ?? false;
            $kayit = $cozulen[$adres];

            if ($kayit === false) {
                continue;
            }

            $dil = $kayit['dil'];
            $standart = $kayit['adres'];
            $mevcut = $sonuc[$dil][$standart] ?? null;

            if ($mevcut === null) {
                if (! $this->kume->acik($kayit)) {
                    $this->ceviriBekliyorsaKaydet($kayit, $satir);

                    continue;
                }

                $mevcut = ['page' => $standart, 'impressions' => 0, 'clicks' => 0, 'position' => 0.0, 'tur' => $kayit['tur'], 'id' => (int) $kayit['model']->id, 'slug' => (string) $kayit['model']->slug, 'kazandirir' => $this->kume->kazandirir($kayit), 'siraAgirlik' => 0.0];
            }

            $mevcut['impressions'] += (int) $satir['impressions'];
            $mevcut['clicks'] += (int) $satir['clicks'];
            $mevcut['siraAgirlik'] += (float) $satir['position'] * (int) $satir['impressions'];
            $mevcut['position'] = $mevcut['impressions'] > 0 ? round($mevcut['siraAgirlik'] / $mevcut['impressions'], 2) : 0.0;
            $sonuc[$dil][$standart] = $mevcut;
        }

        foreach ($sonuc as $dil => $sayfalar) {
            foreach ($sayfalar as $adres => $sayfa) {
                unset($sonuc[$dil][$adres]['siraAgirlik']);
            }
        }

        return $sonuc;
    }

    /**
     * @param  list<array{page: string, impressions: int, clicks: int, position: float, ctr: float, potansiyel: float}>  $adaylar
     * @param  array<string, array{tur: string, id: int, slug: string, kazandirir: bool}>  $sayfalar
     * @return list<array{page: string, impressions: int, clicks: int, position: float, ctr: float, potansiyel: float, tur: string, id: int, slug: string, kazandirir: bool}>
     */
    private function zenginlestir(array $adaylar, array $sayfalar): array
    {
        return array_map(static fn (array $a): array => [
            ...$a,
            'tur' => $sayfalar[$a['page']]['tur'],
            'id' => $sayfalar[$a['page']]['id'],
            'slug' => $sayfalar[$a['page']]['slug'],
            'kazandirir' => $sayfalar[$a['page']]['kazandirir'],
        ], $adaylar);
    }

    /**
     * Reklam taşıyan ilk aday. Reklamsız ince bir yazıyı yükseltmek grafiği
     * güzelleştirir ama gelire dönmez; yalnız başka aday yoksa seçilmez.
     *
     * @param  list<array<string, mixed>>  $adaylar
     * @return array<string, mixed>|null
     */
    private function ilkKazandiran(array $adaylar): ?array
    {
        foreach ($adaylar as $aday) {
            if ($aday['kazandirir'] === true) {
                return $aday;
            }
        }

        return null;
    }

    /** @return list<string> */
    private function dislanan(string $dil): array
    {
        return ConiAction::query()->where('locale', $dil)
            ->where(function ($q): void {
                $q->where('status', ConiAction::UYGULANDI)
                    ->orWhere(fn ($r) => $r->where('kind', ConiAction::ODAK)->where('status', '!=', ConiAction::KAZANC)->where('applied_at', '>=', now()->subDays(self::TEKRAR_BEKLEME)));
            })
            ->pluck('page')->all();
    }

    /**
     * @param  list<array{page: string, impressions: int, clicks: int, position: float}>  $satirlar
     * @return array{clicks: int, impressions: int, position: float|null}|null
     */
    private function sayfaOlcumu(array $satirlar, string $sayfa): ?array
    {
        $hedef = rtrim($sayfa, '/');
        $tiklama = 0;
        $gosterim = 0;
        $agirlik = 0.0;
        $bulundu = false;

        foreach ($satirlar as $satir) {
            $adres = (string) $satir['page'];

            if (rtrim($adres, '/') !== $hedef) {
                $kayit = $this->kume->coz($adres);

                if ($kayit === null || rtrim($kayit['adres'], '/') !== $hedef) {
                    continue;
                }
            }

            $bulundu = true;
            $tiklama += (int) $satir['clicks'];
            $gosterim += (int) $satir['impressions'];
            $agirlik += (float) $satir['position'] * (int) $satir['impressions'];
        }

        return $bulundu ? ['clicks' => $tiklama, 'impressions' => $gosterim, 'position' => $gosterim > 0 ? round($agirlik / $gosterim, 2) : null] : null;
    }

    /**
     * @param  list<array{page: string, impressions: int, clicks: int, position: float}>  $sayfalar
     * @return array{clicks: int, impressions: int, pages: int, position: float|null}
     */
    private function toplam(array $sayfalar): array
    {
        $tiklama = array_sum(array_column($sayfalar, 'clicks'));
        $gosterim = array_sum(array_column($sayfalar, 'impressions'));
        $agirlik = array_sum(array_map(static fn (array $s): float => (float) $s['position'] * (int) $s['impressions'], $sayfalar));

        return ['clicks' => (int) $tiklama, 'impressions' => (int) $gosterim, 'pages' => count($sayfalar), 'position' => $gosterim > 0 ? round($agirlik / $gosterim, 2) : null];
    }

    /**
     * İlk yirmide görünüp hiç tıklanmayan sayfalar: SERP işi.
     *
     * @param  list<array{page: string, impressions: int, clicks: int, position: float}>  $sayfalar
     * @return list<array{page: string, impressions: int, position: float}>
     */
    private function tiklanmayan(array $sayfalar): array
    {
        $sonuc = array_values(array_filter($sayfalar, static fn (array $s): bool => $s['clicks'] === 0 && $s['impressions'] >= 20 && $s['position'] > 0 && $s['position'] <= 20));
        usort($sonuc, static fn (array $a, array $b): int => $b['impressions'] <=> $a['impressions']);

        return array_map(static fn (array $s): array => ['page' => $s['page'], 'impressions' => $s['impressions'], 'position' => $s['position']], array_slice($sonuc, 0, 5));
    }

    /**
     * @param  array{dil: string, tur: string, model: BlogPost|Recipe, adres: string}  $kayit
     * @param  array{page: string, impressions: int, clicks: int, position: float}  $satir
     */
    private function ceviriBekliyorsaKaydet(array $kayit, array $satir): void
    {
        if (! $kayit['model'] instanceof Recipe || $kayit['model']->status !== 'published') {
            return;
        }

        $engel = app(RecipeTranslationGate::class)->engel($kayit['model'], $kayit['dil']);

        if ($engel === null) {
            return;
        }

        $mevcut = $this->ceviriBekleyen[$kayit['dil']][$kayit['adres']] ?? ['page' => $kayit['adres'], 'slug' => (string) $kayit['model']->slug, 'impressions' => 0, 'clicks' => 0, 'engel' => $engel];
        $mevcut['impressions'] += (int) $satir['impressions'];
        $mevcut['clicks'] += (int) $satir['clicks'];
        $this->ceviriBekleyen[$kayit['dil']][$kayit['adres']] = $mevcut;
    }

    /** @return list<array{page: string, impressions: int, clicks: int, engel: string}> */
    private function ceviriSirasi(string $dil): array
    {
        $liste = array_values($this->ceviriBekleyen[$dil] ?? []);
        usort($liste, static fn (array $a, array $b): int => $b['impressions'] <=> $a['impressions']);

        return array_slice($liste, 0, 10);
    }

    /**
     * Bir tarifin tek dildeki çevirisini yazar: başlık, özet, açıklama ve
     * her adımın talimatı. Kalıp çeviriyi gerçeğiyle değiştirmek içindir;
     * metni Claude oturumu Türkçe asıldan o dilde yazar. Eski değerler
     * saklanır, geriAl() aynen geri koyar.
     *
     * @param  array{title?: string, excerpt?: string, description?: string, steps: list<string>, step_titles?: list<string>}  $ceviri
     * @return array{id: int, page: string, engel_once: string|null, engel_sonra: string|null}
     */
    public function ceviri(string $slug, string $dil, array $ceviri, string $neden): array
    {
        if ($dil === $this->diller->defaultLocale() || ! array_key_exists($dil, $this->diller->enabledLocales())) {
            throw new InvalidArgumentException('Çeviri yalnız açık bir yabancı dile yazılır: '.$dil);
        }

        if (trim($neden) === '') {
            throw new InvalidArgumentException('Neden boş olamaz.');
        }

        $tarif = Recipe::query()->where('slug', $slug)->first();

        if ($tarif === null || $tarif->status !== 'published' || $tarif->verification_status !== 'verified') {
            throw new InvalidArgumentException('Yayında ve doğrulanmış tarif bulunamadı: '.$slug);
        }

        $adimlar = $tarif->steps()->orderBy('position')->get();
        $yeniAdimlar = array_values(array_map(static fn ($a): string => trim((string) $a), (array) ($ceviri['steps'] ?? [])));

        if (count($yeniAdimlar) !== $adimlar->count() || in_array('', $yeniAdimlar, true)) {
            throw new InvalidArgumentException(sprintf('Tarifin %d adımı var; %d dolu adım çevirisi gerekli.', $adimlar->count(), $adimlar->count()));
        }

        $kapi = app(RecipeTranslationGate::class);

        if ($kapi->kalipMi($yeniAdimlar)) {
            throw new InvalidArgumentException('Yeni adımlar da aynı açılışla başlıyor; bu çeviri değil kalıp.');
        }

        // Adım başlıkları da kalıptı: "Step 1: Adım 1 (Execution & Cooking)".
        $yeniBasliklar = array_values(array_map(static fn ($a): string => trim((string) $a), (array) ($ceviri['step_titles'] ?? [])));

        if ($yeniBasliklar !== [] && (count($yeniBasliklar) !== $adimlar->count() || in_array('', $yeniBasliklar, true))) {
            throw new InvalidArgumentException(sprintf('Adım başlığı verilecekse %d dolu başlık gerekli.', $adimlar->count()));
        }

        $alanlar = array_filter([
            'title' => isset($ceviri['title']) ? trim((string) $ceviri['title']) : null,
            'excerpt' => isset($ceviri['excerpt']) ? trim((string) $ceviri['excerpt']) : null,
            'description' => isset($ceviri['description']) ? trim((string) $ceviri['description']) : null,
        ], static fn (?string $d): bool => $d !== null && $d !== '');

        $engelOnce = $kapi->engel($tarif, $dil);
        $once = ['recipe' => [], 'steps' => []];

        foreach (array_keys($alanlar) as $alan) {
            $once['recipe'][$alan] = $tarif->translated($alan, $dil);
        }

        foreach ($adimlar as $adim) {
            $once['steps'][(string) $adim->id] = $adim->translated('instruction', $dil);
            $once['step_titles'][(string) $adim->id] = $adim->translated('title', $dil);
        }

        $sayfa = $this->diller->urlFor($dil, '/tarifler/'.$tarif->slug);

        $is = DB::transaction(function () use ($tarif, $adimlar, $alanlar, $yeniAdimlar, $yeniBasliklar, $dil, $once, $sayfa, $neden, $engelOnce): ConiAction {
            foreach ($alanlar as $alan => $deger) {
                $this->ceviriYaz($tarif, $dil, $alan, $deger);
            }

            foreach ($adimlar->values() as $sira => $adim) {
                $this->ceviriYaz($adim, $dil, 'instruction', $yeniAdimlar[$sira]);

                if ($yeniBasliklar !== []) {
                    $this->ceviriYaz($adim, $dil, 'title', $yeniBasliklar[$sira]);
                }
            }

            return ConiAction::query()->create([
                'locale' => $dil,
                'page' => $sayfa,
                'page_hash' => ConiAction::hash($sayfa),
                'kind' => ConiAction::CEVIRI,
                'status' => ConiAction::UYGULANDI,
                'reason' => trim($neden),
                'evidence' => ['tur' => 'recipe', 'id' => $tarif->id, 'slug' => $tarif->slug, 'engel_once' => $engelOnce],
                'before' => $once,
                'after' => ['recipe' => $alanlar, 'steps' => array_combine($adimlar->pluck('id')->map(static fn ($id): string => (string) $id)->all(), $yeniAdimlar), 'step_titles' => $yeniBasliklar],
                'applied_at' => now(),
                'evaluate_after' => $this->degerlendirmeGunu(now()),
            ]);
        });

        $engelSonra = (new RecipeTranslationGate)->engel($tarif->fresh(), $dil);

        if ($engelSonra === null) {
            $this->bildir([$sayfa]);
        }

        return ['id' => $is->id, 'page' => $sayfa, 'engel_once' => $engelOnce, 'engel_sonra' => $engelSonra];
    }

    private function ceviriYaz(Model $model, string $dil, string $alan, ?string $deger): void
    {
        $kosul = ['translatable_type' => $model->getMorphClass(), 'translatable_id' => $model->getKey(), 'locale' => $dil, 'field' => $alan];

        if ($deger === null || trim($deger) === '') {
            ContentTranslation::query()->where($kosul)->delete();

            return;
        }

        ContentTranslation::query()->updateOrCreate($kosul, ['value' => $deger]);
    }

    /** @return array{title: string|null, description: string|null} */
    private function serpDegerleri(BlogPost|Recipe $model, string $dil): array
    {
        if ($dil === $this->diller->defaultLocale()) {
            $meta = $model->seoMetadata()->first();

            return ['title' => $meta?->meta_title, 'description' => $meta?->meta_description];
        }

        return ['title' => $model->translated('seo_title', $dil), 'description' => $model->translated('seo_description', $dil)];
    }

    /** @param array<string, string|null> $degerler */
    private function serpYaz(BlogPost|Recipe $model, string $dil, array $degerler): void
    {
        if ($dil === $this->diller->defaultLocale()) {
            app(SeoMetadataService::class)->update($model, ['meta_title' => $degerler['title'] ?? null, 'meta_description' => $degerler['description'] ?? null]);

            return;
        }

        foreach (['seo_title' => $degerler['title'] ?? null, 'seo_description' => $degerler['description'] ?? null] as $alan => $deger) {
            $kosul = ['translatable_type' => $model->getMorphClass(), 'translatable_id' => $model->getKey(), 'locale' => $dil, 'field' => $alan];

            if ($deger === null || trim($deger) === '') {
                ContentTranslation::query()->where($kosul)->delete();

                continue;
            }

            ContentTranslation::query()->updateOrCreate($kosul, ['value' => $deger]);
        }
    }

    /** @param list<string> $adresler */
    private function bildir(array $adresler): void
    {
        // Yalnız gerçekten değişen adres bildirilir. IndexNow değişmeyen
        // sayfanın tekrar tekrar bildirilmesi için değil.
        if (! config('seo.indexnow.enabled', false)) {
            return;
        }

        try {
            app(IndexNowService::class)->submit($adresler);
        } catch (Throwable) {
            // Bildirim başarısızsa arama motoru sayfayı kendi taramasında görür.
        }
    }
}
