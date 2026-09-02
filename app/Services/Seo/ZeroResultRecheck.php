<?php

declare(strict_types=1);

namespace App\Services\Seo;

use App\Contracts\Search\RecipeSearchInterface;
use App\Http\Controllers\RecipeCollectionLandingController;
use App\Models\ZeroResultSearch;
use App\Services\Search\TurkishRecipeQueryParser;

/**
 * Kayıtlı sıfır sonuçlu terimleri güncel katalogla yeniden dener.
 *
 * Kayıt yalnız biri o terimi tekrar arayınca kapanır; kimse aramazsa
 * bayat satır sonsuza dek "karşılanmamış talep" görünür ve içerik görevi
 * üretmeye devam eder. Üretimde tam olarak bu oldu: arama düzeltildikten
 * sonra "kolay tarifler" 24 tarif dönüyordu ama kayıt hâlâ sıfır diyordu.
 *
 * Bu yüzden kapanma beklemeye bırakılmıyor: site kendi kaydını denetliyor.
 *
 * Manşet sayı yalnız tekrarlanmış terimleri sayıyor. Sebebi veride:
 * 2026-09-02'de açık kalan üç terim "asdfqwer", "functionalities" ve
 * "search term string" idi - klavye ezmesi, bot ve test dizesi, hepsi
 * 1-2 aramalı. Gerçekten talep olan üçü ise 19, 24 ve 26 aramayla
 * gelmişti ve çoktan karşılanmıştı. Aradaki boşluk büyük.
 *
 * Üzerine gidilemeyen ve hiç sıfırlanmayacak bir sayı gürültüdür; paneli
 * okumayı bırakmayı öğretir. Satırlar yine de kaydediliyor - kanıt orada
 * kalıyor - yalnız manşete girmiyorlar.
 */
final class ZeroResultRecheck
{
    /** Bir terim talep sayılmadan önce kaç kez aranmış olmalı. */
    private const TALEP_ESIGI = 3;

    public function __construct(
        private readonly RecipeSearchInterface $search,
        private readonly ZeroResultSearchService $kayit,
        private readonly TurkishRecipeQueryParser $parser,
    ) {}

    /** @return array<string, mixed> */
    public function run(): array
    {
        $cozulen = [];
        $acik = 0;
        $tekil = 0;

        ZeroResultSearch::query()->whereNull('resolved_at')->orderByDesc('search_count')->each(
            function (ZeroResultSearch $satir) use (&$cozulen, &$acik, &$tekil): void {
                $adet = $this->search->search(['q' => $satir->normalized_query])->total();

                if ($adet > 0) {
                    // Yol saklanıyor, tam adres değil: sütun varchar(40) ve
                    // "https://housechefcyprus.com/sure/20-dakika" 42 karakter.
                    // Üretim MySQL'i STRICT_TRANS_TABLES ile çalışıyor, yani
                    // bu satır "Data too long" fırlatıp gece koşan işi
                    // düşürürdü. SQLite varchar sınırını uygulamadığı için
                    // testler bunu göremez. Yol ayrıca alan adından bağımsız.
                    $suggestedAction = RecipeCollectionLandingController::yapisalSorguAdresi(
                        $this->parser->parse($satir->normalized_query)
                    );
                    $suggestedAction = is_string($suggestedAction)
                        ? (parse_url($suggestedAction, PHP_URL_PATH) ?: null)
                        : null;
                    if ($suggestedAction !== null) {
                        $satir->forceFill(['suggested_action' => $suggestedAction])->save();
                    }

                    $this->kayit->resolve($satir->normalized_query, $adet);
                    $cozulen[] = [
                        'term' => $satir->normalized_query,
                        'results' => $adet,
                        'search_count' => (int) $satir->search_count,
                        'suggested_action' => $suggestedAction,
                    ];

                    return;
                }

                if ($satir->search_count >= self::TALEP_ESIGI) {
                    $acik++;

                    return;
                }

                $tekil++;
            }
        );

        return [
            'status' => $cozulen === [] ? 'unchanged' : 'resolved',
            'checked_at' => now()->toIso8601String(),
            // Manşet sayı açık kalan talep: azalması iyileşmedir.
            'unmet' => $acik,
            // Ayrı sayılıyor, gizlenmiyor: eşik bir gün yanlış çıkarsa
            // bunun kaç satırı sustursduğu görünür olmalı.
            'below_threshold' => $tekil,
            'resolved' => count($cozulen),
            'resolved_terms' => $cozulen,
        ];
    }
}
