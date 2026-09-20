<?php

declare(strict_types=1);

namespace App\Services\Seo;

/**
 * Günlük büyümeyi ölçer ve büyümeyi durduran sayfaları adlandırır.
 *
 * 20 Eylül 2026'da ölçüldü: 572 gösterim, 6 tıklama, ortalama sıra 9,7.
 * Yani site aranıyor ve görünüyor; görünen sayfalar tıklanmıyor. Sıra
 * yükseltmek uzun iştir, ama zaten ilk yirmide olup hiç tıklanmayan bir
 * sayfanın başlığı ve açıklaması bugün düzeltilebilir ve etkisi günler
 * içinde görülür. Bu servis o sayfaları ayıklar.
 *
 * Bir de dünün sayısını saklar: büyüme iddiası ancak bir önceki ölçümle
 * karşılaştırılırsa doğrulanabilir. "Dünden yüksek mi" sorusunun cevabı
 * tahmin değil kayıt olur.
 */
final class DailyGrowthService
{
    /** Bu gösterimin altındaki sayfada tıklanma yokluğu rastlantı olabilir. */
    public const ASGARI_GOSTERIM = 10;

    /** İlk yirmi dışındaki sayfanın sorunu başlık değil sıradır. */
    public const SON_SIRA = 20.0;

    /**
     * @param  list<array{page: string, impressions: int|float, clicks: int|float, position: int|float}>  $satirlar
     * @return array{clicks: int, impressions: int, pages: int, ctr: float, position: float}
     */
    public function toplam(array $satirlar): array
    {
        $tiklama = 0;
        $gosterim = 0;
        $siraAgirlik = 0.0;

        foreach ($satirlar as $satir) {
            $tiklama += (int) $satir['clicks'];
            $gosterim += (int) $satir['impressions'];
            $siraAgirlik += (float) $satir['position'] * (int) $satir['impressions'];
        }

        return [
            'clicks' => $tiklama,
            'impressions' => $gosterim,
            'pages' => count($satirlar),
            'ctr' => $gosterim > 0 ? round($tiklama / $gosterim * 100, 2) : 0.0,
            'position' => $gosterim > 0 ? round($siraAgirlik / $gosterim, 2) : 0.0,
        ];
    }

    /**
     * Görünen ama tıklanmayan sayfalar: başlık ve açıklama işi.
     *
     * @param  list<array{page: string, impressions: int|float, clicks: int|float, position: int|float}>  $satirlar
     * @return list<array{page: string, impressions: int, position: float}>
     */
    public function tiklanmayanlar(array $satirlar): array
    {
        $sonuc = [];

        foreach ($satirlar as $satir) {
            if ((int) $satir['clicks'] > 0 || (int) $satir['impressions'] < self::ASGARI_GOSTERIM) {
                continue;
            }

            if ((float) $satir['position'] <= 0.0 || (float) $satir['position'] > self::SON_SIRA) {
                continue;
            }

            $sonuc[] = [
                'page' => (string) $satir['page'],
                'impressions' => (int) $satir['impressions'],
                'position' => round((float) $satir['position'], 1),
            ];
        }

        usort($sonuc, static fn (array $a, array $b): int => $b['impressions'] <=> $a['impressions']);

        return $sonuc;
    }

    /**
     * Bugünün ölçümü bir öncekine göre nerede.
     *
     * Önceki ölçüm yoksa yön "bilinmiyor"dur; sıfır değildir. İlk gün
     * karşılaştırma yapılamaz ve yapılamadığı söylenir.
     *
     * @param  array{clicks: int, impressions: int, pages: int, ctr: float, position: float}  $bugun
     * @param  array{clicks: int, impressions: int, pages: int, ctr: float, position: float}|null  $onceki
     * @return array{direction: string, clicks: int|null, impressions: int|null, position: float|null}
     */
    public function fark(array $bugun, ?array $onceki): array
    {
        if ($onceki === null) {
            return ['direction' => 'bilinmiyor', 'clicks' => null, 'impressions' => null, 'position' => null];
        }

        $tiklamaFarki = $bugun['clicks'] - $onceki['clicks'];
        $gosterimFarki = $bugun['impressions'] - $onceki['impressions'];

        // Tıklama asıl ölçüt; tıklama değişmediyse gösterim yönü söylenir.
        $yon = match (true) {
            $tiklamaFarki > 0 => 'buyudu',
            $tiklamaFarki < 0 => 'kucculdu',
            $gosterimFarki > 0 => 'buyudu',
            $gosterimFarki < 0 => 'kucculdu',
            default => 'ayni',
        };

        return [
            'direction' => $yon,
            'clicks' => $tiklamaFarki,
            'impressions' => $gosterimFarki,
            // Sırada küçülme iyileşmedir; işaret olduğu gibi bırakılır.
            'position' => round($bugun['position'] - $onceki['position'], 2),
        ];
    }
}
