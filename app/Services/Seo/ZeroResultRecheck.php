<?php

declare(strict_types=1);

namespace App\Services\Seo;

use App\Contracts\Search\RecipeSearchInterface;
use App\Models\ZeroResultSearch;

/**
 * Kayıtlı sıfır sonuçlu terimleri güncel katalogla yeniden dener.
 *
 * Kayıt yalnız biri o terimi tekrar arayınca kapanır; kimse aramazsa
 * bayat satır sonsuza dek "karşılanmamış talep" görünür ve içerik görevi
 * üretmeye devam eder. Üretimde tam olarak bu oldu: arama düzeltildikten
 * sonra "kolay tarifler" 24 tarif dönüyordu ama kayıt hâlâ sıfır diyordu.
 *
 * Bu yüzden kapanma beklemeye bırakılmıyor: site kendi kaydını denetliyor.
 */
final class ZeroResultRecheck
{
    public function __construct(
        private readonly RecipeSearchInterface $search,
        private readonly ZeroResultSearchService $kayit,
    ) {}

    /** @return array<string, mixed> */
    public function run(): array
    {
        $cozulen = [];
        $acik = 0;

        ZeroResultSearch::query()->whereNull('resolved_at')->orderByDesc('search_count')->each(
            function (ZeroResultSearch $satir) use (&$cozulen, &$acik): void {
                $adet = $this->search->search(['q' => $satir->normalized_query])->total();

                if ($adet > 0) {
                    $this->kayit->resolve($satir->normalized_query, $adet);
                    $cozulen[] = ['term' => $satir->normalized_query, 'results' => $adet, 'search_count' => (int) $satir->search_count];

                    return;
                }

                $acik++;
            }
        );

        return [
            'status' => $cozulen === [] ? 'unchanged' : 'resolved',
            'checked_at' => now()->toIso8601String(),
            // Manşet sayı açık kalan talep: azalması iyileşmedir.
            'unmet' => $acik,
            'resolved' => count($cozulen),
            'resolved_terms' => $cozulen,
        ];
    }
}
