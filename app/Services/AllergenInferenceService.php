<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\Allergen;
use App\Models\Ingredient;
use App\Models\Recipe;
use App\Models\RecipeAllergenOverride;

/**
 * Malzeme adlarından AB'nin 14 zorunlu alerjenini türetir ve doğrulanmış
 * kayıtlarla birleştirir.
 *
 * Neden var: 2026-10 denetiminde 129 tarifin 77'sinde alerjen satırı yoktu ve
 * bunların 39'unda yumurta, un, süt ya da susam vardı. Flaouna süt, yumurta,
 * hellim ve susam içerdiği halde uyarısızdı; sucuklu yumurta "İçerir: Süt"
 * diyor, yumurtayı yazmıyordu. Alerjen tablosu elle doldurulan bir eşleme;
 * eksik olduğunda sayfa "alerjen yok" gibi okunuyordu. Bu sınıf iki şey yapar:
 * (1) adı açıkça alerjen olan malzemeyi her durumda "İçerir"e koyar, (2) bu
 * çıkarım doğrulanmış kayıtlarla örtüşmüyorsa sayfanın bunu söylemesini sağlar.
 *
 * Eşleşme kelime sınırıyla yapılır ("un" "uskumru"yu yakalamaz) ve bilinen
 * yanlış pozitifler önce metinden çıkarılır: hindistan cevizi sert kabuklu
 * yemiş değildir, mısır unu gluten içermez, mürekkep balığı balık değil
 * yumuşakçadır. Çıkarım yalnız uyarı yönünde çalışır; hiçbir zaman "içermez"
 * sonucu üretmez.
 */
final class AllergenInferenceService
{
    /** AB 1169/2011 Ek II sırası; ekrandaki sıra da bu. */
    public const KODLAR = ['gluten', 'crustaceans', 'egg', 'fish', 'peanut', 'soy', 'milk', 'tree_nuts', 'celery', 'mustard', 'sesame', 'sulphites', 'lupin', 'molluscs'];

    /**
     * Kod => [eşleşen kalıplar, önce silinecek yanlış pozitifler].
     * Kalıplar düzenli ifade parçasıdır; Türkçe ek için \p{L}* kullanılır.
     *
     * @var array<string, array{0: list<string>, 1: list<string>}>
     */
    private const KALIPLAR = [
        'gluten' => [[
            'un', 'unu', 'unun', 'buğday\p{L}*', 'yufka\p{L}*', 'ekme[kğ]\p{L}*', 'bulgur\p{L}*', 'irmi[kğ]\p{L}*',
            'makarna\p{L}*', 'erişte\p{L}*', 'şehriye\p{L}*', 'kuskus\p{L}*', 'galeta\p{L}*', 'bisküvi\p{L}*',
            'simi[td]\p{L}*', 'pide\p{L}*', 'lavaş\p{L}*', 'arpa', 'çavdar\p{L}*', 'yulaf\p{L}*', 'milföy\p{L}*',
            'kadayıf\p{L}*', 'tarhana\p{L}*', 'hamur\p{L}*', 'bazlama\p{L}*', 'kraker\p{L}*', 'soya sos\p{L}*',
            'flour', 'wheat', 'bread\p{L}*', 'pasta', 'spaghetti', 'noodles?', 'couscous', 'bulgur', 'semolina',
            'barley', 'rye', 'oats?', 'spelt', 'filo', 'phyllo', 'pastry', 'tortillas?', 'crackers?', 'biscuits?', 'soy sauce',
        ], [
            '(?:mısır|pirinç|nohut|badem|karabuğday|patates|hindistan cevizi) unu', 'glutensiz \p{L}+',
            '(?:corn|rice|chickpea|almond|buckwheat|potato|coconut) flour', 'cornflour', 'gluten[- ]free \p{L}+', 'hamur maya\p{L}*',
        ]],
        'crustaceans' => [[
            'karides\p{L}*', 'ıstakoz\p{L}*', 'yenge[çc]\p{L}*', 'kerevit\p{L}*',
            'shrimps?', 'prawns?', 'lobsters?', 'crabs?', 'crayfish', 'langoustines?',
        ], []],
        'egg' => [[
            'yumurta\p{L}*', 'mayonez\p{L}*', 'eggs?', 'yolks?', 'mayonnaise', 'mayo', 'meringue',
        ], []],
        'fish' => [[
            'balı[kğ]\p{L}*', 'levrek\p{L}*', 'çipura\p{L}*', 'somon\p{L}*', 'hamsi\p{L}*', 'sardalya\p{L}*', 'uskumru\p{L}*',
            'palamut\p{L}*', 'lüfer\p{L}*', 'mezgit\p{L}*', 'alabalı[kğ]\p{L}*', 'ançüez\p{L}*', 'kefal\p{L}*', 'istavrit\p{L}*',
            'fish', 'salmon', 'tuna', 'anchov\p{L}*', 'cod', 'sea bass', 'sardines?', 'mackerel', 'trout',
        ], ['mürekkep balı[kğ]\p{L}*', 'cuttlefish']],
        'peanut' => [[
            'yer ?fıstı[kğ]\p{L}*', 'fıstık ezme\p{L}*', 'peanuts?',
        ], []],
        'soy' => [[
            'soya\p{L}*', 'tofu', 'edamame', 'miso', 'soy', 'soybeans?',
        ], []],
        'milk' => [[
            'süt\p{L}*', 'peynir\p{L}*', 'hellim\p{L}*', 'yoğur[td]\p{L}*', 'tereya[ğg]\p{L}*', 'krema\p{L}*', 'krem şanti',
            'kayma[kğ]\p{L}*', 'lor', 'labne\p{L}*', 'kaşar\p{L}*', 'çökelek\p{L}*', 'ayran\p{L}*', 'kefir\p{L}*', 'sade ya[ğg]\p{L}*',
            'tarhana\p{L}*', 'mozzarella', 'parmesan', 'feta', 'ricotta', 'mascarpone', 'cheddar', 'anari',
            'milk', 'butter', 'cream', 'cheese', 'yogh?urt', 'ghee', 'halloumi', 'buttermilk',
        ], [
            '(?:hindistan cevizi|badem|soya|yulaf|pirinç|fındık) (?:sütü|kreması)', 'vegan \p{L}+', 'bitkisel \p{L}+',
            '(?:coconut|almond|soy|oat|rice) (?:milk|cream)', '(?:peanut|almond|cocoa|shea) butter', 'cream of tartar', 'kakao yağı',
        ]],
        'tree_nuts' => [[
            'ceviz\p{L}*', 'fındı[kğ]\p{L}*', 'badem\p{L}*', 'antep fıstı[kğ]\p{L}*', 'kaju\p{L}*', 'pekan\p{L}*',
            'walnuts?', 'hazelnuts?', 'almonds?', 'pistachios?', 'cashews?', 'pecans?', 'macadamia', 'brazil nuts?',
        ], ['(?:hindistan|muskat) cevizi\p{L}*', 'hindistancevizi\p{L}*']],
        'celery' => [[
            'kereviz\p{L}*', 'celery', 'celeriac',
        ], []],
        'mustard' => [[
            'hardal\p{L}*', 'mustard',
        ], []],
        'sesame' => [[
            'susam\p{L}*', 'tahin\p{L}*', 'sesame', 'tahini',
        ], []],
        'sulphites' => [[
            'şara[pb]\p{L}*', 'kuru kayısı\p{L}*', 'sülfit\p{L}*', 'wine', 'dried apricots?', 'sul(?:ph|f)ites?',
        ], []],
        'lupin' => [[
            'acı bakla\p{L}*', 'termiye\p{L}*', 'lupin\p{L}*',
        ], []],
        'molluscs' => [[
            'kalamar\p{L}*', 'ahtapot\p{L}*', 'midye\p{L}*', 'istiridye\p{L}*', 'salyangoz\p{L}*', 'mürekkep balı[kğ]\p{L}*',
            'deniz tara[kğ]\p{L}*', 'squids?', 'octopus', 'mussels?', 'oysters?', 'clams?', 'scallops?', 'cuttlefish', 'calamari', 'snails?',
        ], []],
    ];

    /**
     * Tablodaki Türkçe adların AB koduna karşılığı; tabloda kod standart
     * dışı yazılmış olabiliyor ve eşleşme o zaman ada düşer.
     *
     * @var array<string, string>
     */
    private const AD_KOD = [
        'gluten içeren tahıllar' => 'gluten', 'gluten' => 'gluten', 'süt' => 'milk', 'süt ürünleri' => 'milk', 'yumurta' => 'egg',
        'yer fıstığı' => 'peanut', 'sert kabuklu yemişler' => 'tree_nuts', 'kabuklu yemişler' => 'tree_nuts', 'soya' => 'soy',
        'balık' => 'fish', 'kabuklu deniz ürünleri' => 'crustaceans', 'susam' => 'sesame', 'kereviz' => 'celery',
        'hardal' => 'mustard', 'acı bakla' => 'lupin', 'yumuşakçalar' => 'molluscs', 'sülfit' => 'sulphites',
    ];

    /**
     * Tek bir ad listesinden alerjen kodları.
     *
     * @param  iterable<string|null>  $adlar
     * @return list<string>
     */
    public function kodlar(iterable $adlar): array
    {
        $bulunan = [];
        foreach ($adlar as $ad) {
            foreach ($this->metinKodlari((string) $ad) as $kod) {
                $bulunan[$kod] = true;
            }
        }

        return array_values(array_filter(self::KODLAR, fn (string $kod): bool => isset($bulunan[$kod])));
    }

    /**
     * Tarifin malzemelerinden türetilen alerjenler ve kaynak malzemeleri.
     *
     * @return array<string, list<string>> kod => ekrandaki malzeme adları
     */
    public function tarifIcin(Recipe $recipe): array
    {
        $lines = $recipe->relationLoaded('recipeIngredients') ? $recipe->recipeIngredients : $recipe->recipeIngredients()->with('ingredient')->get();
        $sonuc = [];
        foreach ($lines as $line) {
            $ingredient = $line->ingredient;
            if (! $ingredient instanceof Ingredient) {
                continue;
            }
            $adlar = array_merge(
                [(string) $ingredient->getRawOriginal('name'), (string) $ingredient->canonical_name],
                array_map('strval', array_filter((array) ($ingredient->aliases ?? []), 'is_string')),
            );
            foreach ($this->kodlar($adlar) as $kod) {
                $sonuc[$kod][] = (string) $ingredient->name;
            }
        }

        $sirali = [];
        foreach (self::KODLAR as $kod) {
            if (isset($sonuc[$kod])) {
                $sirali[$kod] = array_values(array_unique($sonuc[$kod]));
            }
        }

        return $sirali;
    }

    /**
     * Doğrulanmış kayıt + çıkarım = ekrandaki alerjen özeti.
     *
     * "verified" ancak şu üçü birlikte doğruysa true: doğrulanmamış kayıt
     * yok, malzeme adından çıkan her alerjen doğrulanmış bir kayıtla
     * karşılanıyor ve tarif için en az bir doğrulanmış kayıt var (hiç kayıt
     * olmaması "alerjen yok" demek değil, "bakılmadı" demek).
     *
     * @param  array<string, mixed>  $analiz  AllergenAnalysisService::analyse() çıktısı
     * @return array{contains: list<array{code: string, label: string, ingredients: list<string>}>, may_contain: list<string>, verified: bool, inferred_only: list<string>}
     */
    public function ozet(Recipe $recipe, array $analiz): array
    {
        $gruplar = (array) ($analiz['groups'] ?? []);
        $publicIds = collect(['contains', 'may_contain', 'cross_contamination_risk'])
            ->flatMap(fn (string $grup): array => array_column((array) ($gruplar[$grup] ?? []), 'public_id'))
            ->unique()->values()->all();
        $tablo = $publicIds === [] ? collect() : Allergen::query()->whereIn('public_id', $publicIds)->get(['public_id', 'code', 'name'])->keyBy('public_id');
        $kodu = function (array $item) use ($tablo): string {
            $satir = $tablo->get($item['public_id'] ?? '');
            $kod = (string) ($satir->code ?? '');
            if (in_array($kod, self::KODLAR, true)) {
                return $kod;
            }

            return self::AD_KOD[$this->kucuk((string) ($item['name'] ?? ''))] ?? 'db:'.($item['name'] ?? $kod);
        };

        // Editörün tarif düzeyinde "içermez" dediği alerjen çıkarımı da bastırır
        // (glutensiz un kullanılan tarif gibi); yalnız doğrulanmış kayıt sayılır.
        $overrides = $recipe->relationLoaded('allergenOverrideRecords')
            ? $recipe->allergenOverrideRecords
            : $recipe->allergenOverrideRecords()->with('allergen')->get();
        $icermez = [];
        $dogrulanmisKayitVar = false;
        foreach ($overrides as $override) {
            /** @var RecipeAllergenOverride $override */
            if (! $override->is_active || ! $override->is_verified || $override->allergen === null) {
                continue;
            }
            $dogrulanmisKayitVar = true;
            if ($override->risk_type === 'does_not_contain') {
                $icermez[] = $kodu(['public_id' => $override->allergen->public_id, 'name' => $override->allergen->name]);
            }
        }

        $icerir = [];
        $dogrulanmisKodlar = [];
        foreach ((array) ($gruplar['contains'] ?? []) as $item) {
            $kod = $kodu($item);
            $icerir[$kod] ??= ['label' => $this->etiket($kod, (string) $item['name']), 'ingredients' => []];
            if (! empty($item['ingredient'])) {
                $icerir[$kod]['ingredients'][] = (string) $item['ingredient'];
            }
            if (! empty($item['verified'])) {
                $dogrulanmisKodlar[$kod] = true;
            }
        }
        $belki = [];
        foreach (['may_contain', 'cross_contamination_risk'] as $grup) {
            foreach ((array) ($gruplar[$grup] ?? []) as $item) {
                $kod = $kodu($item);
                $belki[$kod] = $this->etiket($kod, (string) $item['name']);
                if (! empty($item['verified'])) {
                    $dogrulanmisKodlar[$kod] = true;
                }
            }
        }

        $yalnizCikarim = [];
        foreach ($this->tarifIcin($recipe) as $kod => $malzemeler) {
            if (in_array($kod, $icermez, true)) {
                continue;
            }
            if (! isset($dogrulanmisKodlar[$kod])) {
                $yalnizCikarim[] = $kod;
            }
            $icerir[$kod] ??= ['label' => $this->etiket($kod, $kod), 'ingredients' => []];
            $icerir[$kod]['ingredients'] = array_merge($icerir[$kod]['ingredients'], $malzemeler);
        }

        $sirali = [];
        foreach ($icerir as $kod => $satir) {
            $sirali[] = ['code' => $kod, 'label' => $satir['label'], 'ingredients' => array_values(array_unique($satir['ingredients']))];
        }
        $sira = array_flip(self::KODLAR);
        usort($sirali, fn (array $a, array $b): int => ($sira[$a['code']] ?? 99) <=> ($sira[$b['code']] ?? 99));

        $verified = ((array) ($gruplar['unverified'] ?? [])) === []
            && $yalnizCikarim === []
            && ($dogrulanmisKodlar !== [] || $dogrulanmisKayitVar);

        return [
            'contains' => $sirali,
            'may_contain' => array_values(array_diff_key($belki, $icerir)),
            'verified' => $verified,
            'inferred_only' => $yalnizCikarim,
        ];
    }

    /** @return list<string> */
    private function metinKodlari(string $ad): array
    {
        $ad = trim($ad);
        if ($ad === '') {
            return [];
        }
        // "İ" ve "I" Türkçede ayrı küçülür; İngilizce adlar için düz
        // mb_strtolower da denenir ("ICING" -> "icing").
        $metinler = array_unique([$this->kucuk($ad), mb_strtolower($ad, 'UTF-8')]);
        $kodlar = [];
        foreach (self::KALIPLAR as $kod => [$kaliplar, $haric]) {
            foreach ($metinler as $metin) {
                $temiz = $haric === [] ? $metin : (string) preg_replace($this->desen($haric), ' ', $metin);
                if (preg_match($this->desen($kaliplar), $temiz) === 1) {
                    $kodlar[] = $kod;
                    break;
                }
            }
        }

        return $kodlar;
    }

    /** @param list<string> $kaliplar */
    private function desen(array $kaliplar): string
    {
        $parcalar = array_map(fn (string $k): string => str_replace(' ', '\s+', $k), $kaliplar);

        return '/(?<!\p{L})(?:'.implode('|', $parcalar).')(?!\p{L})/u';
    }

    private function kucuk(string $metin): string
    {
        return mb_strtolower(str_replace(['İ', 'I'], ['i', 'ı'], $metin), 'UTF-8');
    }

    private function etiket(string $kod, string $yedek): string
    {
        if (in_array($kod, self::KODLAR, true)) {
            return __('allergen.'.$kod);
        }

        return $yedek;
    }
}
