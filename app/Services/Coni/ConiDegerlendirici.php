<?php

declare(strict_types=1);

namespace App\Services\Coni;

use App\Models\ConiAction;

/**
 * Bir değişikliğin işe yarayıp yaramadığı.
 *
 * Ölçüt tıklama. Sıra yükselip tıklama gelmediyse kazanç yazılmaz: grafik
 * güzelleşir ama site bir kuruş fazla kazanmaz. Sıra aynı kalıp tıklama
 * arttıysa kazançtır, tartışmasız.
 *
 * Küçük dalgalanma sonuç değildir. Eşik, önceki tıklamanın beşte biri ve
 * en az üç tıklama: 2'den 4'e çıkan sayfa "iki katına çıktı" diye
 * kutlanmaz.
 */
final class ConiDegerlendirici
{
    public const ASGARI_FARK = 3;

    public const ORAN = 0.2;

    /**
     * @param  array{clicks: int, impressions: int, position: float|null}  $once
     * @param  array{clicks: int, impressions: int, position: float|null}  $sonra
     * @return array{status: string, clicks: int, impressions: int, position: float|null, esik: int}
     */
    public function sonuc(array $once, array $sonra): array
    {
        $fark = (int) $sonra['clicks'] - (int) $once['clicks'];
        $esik = max(self::ASGARI_FARK, (int) ceil((int) $once['clicks'] * self::ORAN));

        $durum = match (true) {
            $fark >= $esik => ConiAction::KAZANC,
            -$fark >= $esik => ConiAction::KAYIP,
            default => ConiAction::NOTR,
        };

        return [
            'status' => $durum,
            'clicks' => $fark,
            'impressions' => (int) $sonra['impressions'] - (int) $once['impressions'],
            // Sırada küçülme iyileşmedir; işaret olduğu gibi bırakılır.
            'position' => $once['position'] !== null && $sonra['position'] !== null
                ? round((float) $sonra['position'] - (float) $once['position'], 2)
                : null,
            'esik' => $esik,
        ];
    }
}
