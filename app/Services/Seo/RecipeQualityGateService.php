<?php

declare(strict_types=1);

namespace App\Services\Seo;

use App\Models\Recipe;
use Illuminate\Support\Collection;

/**
 * Yayındaki tariflerde şablon metni ve eksik tarifi ölçer.
 *
 * Neden var: 16 Eylül 2026'da 815 tarif tek günde eklendi. Açıklamaları
 * "X Mutfağı ev mutfağından ilham alan anlaşılır bir tarif" kalıbıyla
 * üretilmişti, bir kısmının hiç adımı yoktu. Aynı gün indeksleme kapısı
 * "onaylı görseli olmalı" kuralından "mutfağı ve kategorisi olsun" kuralına
 * gevşetildi; bu içerik indekse girdi. Site 16 Ağustos 2026'da ölçekli
 * makine içeriği yüzünden bir kez zarar gördü.
 *
 * Ölçüt kasten basit: normalize edilmiş, 60 karakterden uzun cümlenin başka
 * bir yayındaki tarifte birebir geçmesi. Cümlenin sahibi ilk yayınlanan
 * tariftir; tekrar sayılan sonrakilerdir.
 */
final class RecipeQualityGateService
{
    public const ASGARI_CUMLE = 60;

    public const TEKRAR_ESIGI = 0.5;

    public const ASGARI_ADIM = 2;

    public const ASGARI_MALZEME = 3;

    /** Aynı malzeme kümesini bu kadar tarif paylaşıyorsa küme şablondur. */
    public const ORTAK_KUME_ESIGI = 3;

    /** Başlıkta geçen kelime → malzeme listesinde bulunması gereken desen. */
    private const BASLIK_MALZEME = [
        'kolokoti' => 'kaba[kğ]',
        'flaouna' => 'peynir|hellim',
        'pilavuna' => 'peynir|hellim',
        'revani' => 'irmi[kğ]',
        'etli' => '(?<![\\p{L}])et(?![\\p{L}])|kıyma|kuşbaşı|dana|kuzu|tavuk',
        'kıymalı' => 'kıyma|kuşbaşı|dana|kuzu',
        'tavuklu' => 'tavu[kğ]',
        'kuzu' => 'kuzu',
        'hellimli' => 'hellim',
        'peynirli' => 'peynir|hellim|lor|kaşar',
        'mercimek' => 'mercime[kğ]',
        'nohut' => 'nohut',
        'patlıcan' => 'patlıcan',
        'ıspanak' => 'ıspana[kğ]',
        'sucuk' => 'sucu[kğ]',
        'karides' => 'karides',
        'kolokas' => 'kolokas',
        'molohiya' => 'molohiya|molehiya',
        'antep fıstık' => 'fıstı[kğ]',
        'gaziantep katmer' => 'fıstı[kğ]',
    ];

    /** Adım↔malzeme karşılaştırmasında sayılmayan ölçü ve tarif kelimeleri. */
    private const OLCU_KELIMELERI = ['adet', 'gram', 'bardağı', 'yemek', 'kaşığı', 'tatlı', 'tutam', 'demet', 'paket', 'dilim', 'yarım', 'mililitre', 'litre', 'kilogram', 'ince', 'kıyılmış', 'doğranmış', 'küçük', 'büyük', 'orta', 'taze', 'veya', 'dondurulmuş', 'için', 'eritilmiş', 'ılık', 'sıcak', 'soğuk', 'isteğe', 'bağlı', 'servis'];

    /** @var array<string, list<int>>|null Tek koşuda bir kez kurulan küme sayacı. */
    private ?array $kumeSayaci = null;

    /**
     * @param  Collection<int, Recipe>|null  $tarifler
     * @return array<int, array{units: int, repeated: int, share: float}>
     */
    public function olc(?Collection $tarifler = null): array
    {
        $tarifler ??= Recipe::query()
            ->where('status', 'published')
            ->with('steps:id,recipe_id,instruction')
            ->get(['id', 'title', 'description', 'excerpt', 'published_at']);

        $tarifler = $tarifler
            ->sortBy(static fn (Recipe $t): array => [$t->published_at?->getTimestamp() ?? PHP_INT_MAX, $t->getKey() ?? PHP_INT_MAX])
            ->values();

        $sahip = [];
        $izleri = [];

        foreach ($tarifler as $tarif) {
            $izler = array_values(array_unique($this->parmakIzleri($tarif)));
            $izleri[$tarif->id] = $izler;

            foreach ($izler as $iz) {
                $sahip[$iz] ??= $tarif->id;
            }
        }

        $sonuc = [];

        foreach ($izleri as $id => $izler) {
            $tekrar = count(array_filter($izler, static fn (string $iz): bool => $sahip[$iz] !== $id));
            $sonuc[$id] = [
                'units' => count($izler),
                'repeated' => $tekrar,
                'share' => $izler === [] ? 0.0 : round($tekrar / count($izler), 3),
            ];
        }

        return $sonuc;
    }

    /** @param array{units: int, repeated: int, share: float}|null $olcum */
    public function sablonMu(?array $olcum): bool
    {
        return $olcum !== null && $olcum['units'] > 0 && $olcum['share'] >= self::TEKRAR_ESIGI;
    }

    /** Adımı ya da malzemesi yetersiz tarif tariften sayılmaz. */
    public function eksikMi(Recipe $tarif): bool
    {
        return $tarif->steps()->count() < self::ASGARI_ADIM
            || $tarif->recipeIngredients()->count() < self::ASGARI_MALZEME
            || $this->yerTutucuMalzemeVar($tarif)
            || $this->hicOlcuYok($tarif)
            || $this->ortakMalzemeKumesi($tarif)
            || $this->tutarlilikSorunlari($tarif) !== [];
    }

    /**
     * Başlığın vaat ettiği malzeme listede yoksa ya da adımlar hiçbir
     * malzemeden söz etmiyorsa tarif kendisiyle çelişir.
     *
     * 8 Ekim 2026'da canlıdaki 128 tarifte ölçüldü: kolokotide balkabağı,
     * flaounada peynir, sütlü revanide irmik, etli kabak dolmasında et yoktu;
     * sucuklu yumurtanın yedi adımı "Malzemeleri yıkayın ve hazırlayın"
     * kalıbındaydı. Hepsi "Doğrulandı" rozetiyle indeksteydi. Sözlük kasten
     * dar: yalnız yemeğin kimliği olan malzemeler.
     *
     * @return list<string>
     */
    public function tutarlilikSorunlari(Recipe $tarif): array
    {
        $baslik = self::kucult((string) $tarif->getRawOriginal('title'));
        $malzemeler = self::kucult($tarif->ingredients()->pluck('name')->implode(' | '));
        $sorunlar = [];

        foreach (self::BASLIK_MALZEME as $kelime => $desen) {
            if (preg_match('/(?<![\p{L}])'.$kelime.'/u', $baslik) === 1 && preg_match('/'.$desen.'/u', $malzemeler) !== 1) {
                $sorunlar[] = 'baslik_malzeme:'.$kelime;
            }
        }

        $adimlar = $tarif->steps()->pluck('instruction')->map(static fn ($a): string => self::kucult((string) $a))->filter()->values();
        $kokler = collect(preg_split('/[^\p{L}]+/u', $malzemeler) ?: [])
            ->filter(static fn (string $k): bool => mb_strlen($k) >= 4 && ! in_array($k, self::OLCU_KELIMELERI, true))
            ->map(static fn (string $k): string => mb_substr($k, 0, mb_strlen($k) > 5 ? 5 : mb_strlen($k) - 1))
            ->unique();

        if ($adimlar->count() >= 3 && $kokler->isNotEmpty() && $adimlar->every(static fn (string $a): bool => ! $kokler->contains(static fn (string $k): bool => str_contains($a, $k)))) {
            $sorunlar[] = 'genel_adimlar';
        }

        return $sorunlar;
    }

    /** mb_strtolower("İrmik") "i̇rmik" (birleşik noktalı) verir; Türkçe kurala göre küçültür. */
    private static function kucult(string $metin): string
    {
        return mb_strtolower(str_replace(['İ', 'I'], ['i', 'ı'], $metin), 'UTF-8');
    }

    /**
     * "Ana Malzeme" bir malzeme değil, doldurulmamış bir alandır.
     *
     * 17 Eylül 2026'da yayındaki 74 tarifin malzeme listesi bu yer tutucuyla
     * başlıyordu; bir tatlı tarifinin malzemesinde soğan ve sarımsak vardı.
     */
    public function yerTutucuMalzemeVar(Recipe $tarif): bool
    {
        // "Sebze Karışımı" da aynı türden bir yer tutucu: 20 Eylül 2026'da
        // 28 tarifin tek ayırt edici malzemesi buydu ve tarifler mantar
        // risottosundan börülce yemeğine kadar değişiyordu.
        // Koşullar parantez içinde tutulur: gruplanmamış bir orWhere ilişki
        // kısıtının dışına taşar ve sorgu bütün tarifler için doğru döner.
        return $tarif->ingredients()
            ->where(fn ($q) => $q->whereIn('slug', ['ana-malzeme', 'sebze-karisimi'])
                ->orWhereIn('name', ['Ana Malzeme', 'Sebze Karışımı']))
            ->exists();
    }

    /**
     * Açıklama cümleleri ve adım yönergeleri birlikte ölçülür.
     *
     * 111 tarifin ilk adımı birebir "Tüm malzemeleri tarif için hazırlayın ve
     * temizleyin." idi. Açıklamada yemeğin adı geçtiği için açıklama tek
     * başına ayırt edemiyordu; yönergeler ediyor.
     *
     * @return list<string>
     */
    private function parmakIzleri(Recipe $tarif): array
    {
        $metin = trim(strip_tags((string) $tarif->getRawOriginal('description').' '.$tarif->getRawOriginal('excerpt')));
        $izler = [];

        $parcalar = preg_split('/(?<=[.!?])\s+/u', $metin) ?: [];

        if ($tarif->relationLoaded('steps') || $tarif->exists) {
            foreach ($tarif->steps as $adim) {
                $parcalar[] = (string) $adim->getRawOriginal('instruction');
            }
        }

        foreach ($parcalar as $cumle) {
            $sade = mb_strtolower(trim($cumle));
            $sade = preg_replace('/[^\p{L}\p{N} ]+/u', ' ', $sade) ?? $sade;
            $sade = trim(preg_replace('/\s+/u', ' ', $sade) ?? $sade);

            if (mb_strlen($sade) >= self::ASGARI_CUMLE) {
                $izler[] = hash('sha256', $sade);
            }
        }

        return $izler;
    }

    /**
     * Hiçbir malzemesinde ölçü olmayan tarif alışveriş listesidir.
     *
     * 20 Eylül 2026'da ölçüldü: yayında ve indekse açık 223 tarifin malzeme
     * listesi "Tavuk, Patates, Domates" gibiydi; tek bir miktar ya da
     * hazırlık notu yoktu. Bu sayfalar aranan soruyu yanıtlamıyor.
     *
     * Ölçüt kasten katı: tek bir malzemesinde bile miktar ya da hazırlık
     * notu bulunan tarif geçer. Tuzu ölçüsüz yazılmış gerçek bir tarif
     * cezalandırılmaz; ölçüsü hiç olmayan liste geçmez.
     */
    public function hicOlcuYok(Recipe $tarif): bool
    {
        $malzemeler = $tarif->recipeIngredients()->get(['quantity', 'preparation']);

        if ($malzemeler->isEmpty()) {
            return true;
        }

        return $malzemeler->every(static fn ($malzeme): bool => blank($malzeme->quantity) && blank($malzeme->preparation));
    }

    /**
     * Malzeme kümesi birbirinin aynı olan tarifler şablondur.
     *
     * 20 Eylül 2026'da ölçüldü: 49 tarifin malzemesi harfi harfine "un,
     * şeker, tereyağı, süt, yumurta, vanilya" idi ve aralarında Kıbrıs
     * cevizli macunu da vardı. 28 tarifinki "zeytinyağı, soğan, sarımsak,
     * tuz, karabiber, limon suyu, sebze karışımı" idi; mantar risottosu ile
     * börülce yemeği aynı listeyi paylaşıyordu.
     *
     * İki tarifin aynı listeyi paylaşması olabilir (sade omlet ile acılı
     * omlet), bu yüzden eşik üçtür.
     */
    public function ortakMalzemeKumesi(Recipe $tarif): bool
    {
        $kume = $this->malzemeKumesi($tarif);

        if ($kume === '') {
            return false;
        }

        $sayac = $this->kumeSayaci();

        if (count($sayac[$kume] ?? []) < self::ORTAK_KUME_ESIGI) {
            return false;
        }

        // Kümenin sahibi ilk yayınlanan tariftir; şablon sayılan sonrakilerdir.
        // Aksi halde makine kopyaları yüzünden özgün tarif de kapanırdı.
        return $sayac[$kume][0] !== $tarif->getKey();
    }

    /** @return array<string, list<int>> Küme → yayın sırasına göre tarif kimlikleri. */
    private function kumeSayaci(): array
    {
        if ($this->kumeSayaci !== null) {
            return $this->kumeSayaci;
        }

        $sayac = [];

        $tarifler = Recipe::query()->where('status', 'published')->with('ingredients:id,slug')->get(['id', 'published_at'])
            ->sortBy(static fn (Recipe $t): array => [$t->published_at?->getTimestamp() ?? PHP_INT_MAX, $t->getKey() ?? PHP_INT_MAX])
            ->values();

        foreach ($tarifler as $tarif) {
            $kume = $this->malzemeKumesi($tarif);

            if ($kume !== '') {
                $sayac[$kume][] = $tarif->getKey();
            }
        }

        return $this->kumeSayaci = $sayac;
    }

    private function malzemeKumesi(Recipe $tarif): string
    {
        $sluglar = $tarif->ingredients->pluck('slug')->filter()->unique()->sort()->values()->all();

        return $sluglar === [] ? '' : implode('|', $sluglar);
    }
}
