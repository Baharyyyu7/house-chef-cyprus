<?php

declare(strict_types=1);

namespace App\Services\Coni;

/**
 * Bir dilde haftanın sayfasını seçer.
 *
 * Aranan sayfa şu: aramada zaten görünüyor, yeterince gösterim alıyor ama
 * ilk üçün dışında kaldığı için tıklanmıyor. Sıra birkaç basamak yükselirse
 * tıklama doğrudan artar. Bu site için dönüşüm tıklamadır: gelir reklamdan
 * geliyor ve reklam ancak sayfa açılınca gösteriliyor.
 *
 * Çok gösterim alıp sıfır tıklayan her sayfa aday değildir. Zaten ilk
 * üçteyse sorunu sıra değil; yirminci sıranın gerisindeyse bir hafta
 * yetmez. İkisi de listeye girmez.
 */
final class ConiOdakSecici
{
    /**
     * Sıraya göre beklenen organik tıklama oranı. Sektör ortalamalarının
     * yuvarlanmış hali; kesin değer değil, adayları sıralamak için.
     */
    private const TO_EGRISI = [1 => 0.28, 2 => 0.15, 3 => 0.11, 4 => 0.08, 5 => 0.07, 6 => 0.05, 7 => 0.04, 8 => 0.03, 9 => 0.025, 10 => 0.02];

    /** Bunun altındaki gösterimde tıklama yokluğu rastlantı olabilir. */
    public const ASGARI_GOSTERIM = 30;

    /** Zaten ilk üçteki sayfanın sorunu sıra değildir. */
    public const EN_IYI_SIRA = 3.5;

    /** İlk yirminin gerisindeki sayfayı bir hafta yukarı taşımaz. */
    public const SON_SIRA = 20.0;

    /** Potansiyel, sayfa bu sıraya çıksaydı gelecek tıklamayla ölçülür. */
    public const HEDEF_SIRA = 3;

    public function beklenenTo(float $sira): float
    {
        $tam = max(1, (int) round($sira));

        return self::TO_EGRISI[$tam] ?? 0.01;
    }

    /**
     * @param  list<array{page: string, impressions: int, clicks: int, position: float}>  $satirlar  tek dilin, sayfa başına birleşmiş satırları
     * @param  list<string>  $dislanan  şu an üzerinde çalışılan ya da yakın zamanda odak olmuş sayfalar
     * @return list<array{page: string, impressions: int, clicks: int, position: float, ctr: float, potansiyel: float}>
     */
    public function adaylar(array $satirlar, array $dislanan = []): array
    {
        $dislanan = array_map(static fn (string $a): string => rtrim($a, '/'), $dislanan);
        $hedefTo = $this->beklenenTo(self::HEDEF_SIRA);
        $sonuc = [];

        foreach ($satirlar as $satir) {
            $gosterim = (int) $satir['impressions'];
            $sira = (float) $satir['position'];

            if ($gosterim < self::ASGARI_GOSTERIM || $sira <= self::EN_IYI_SIRA || $sira > self::SON_SIRA) {
                continue;
            }

            if (in_array(rtrim((string) $satir['page'], '/'), $dislanan, true)) {
                continue;
            }

            $to = $gosterim > 0 ? (int) $satir['clicks'] / $gosterim : 0.0;

            $sonuc[] = [
                'page' => (string) $satir['page'],
                'impressions' => $gosterim,
                'clicks' => (int) $satir['clicks'],
                'position' => round($sira, 1),
                'ctr' => round($to * 100, 2),
                'potansiyel' => round($gosterim * max(0.0, $hedefTo - $to), 1),
            ];
        }

        usort($sonuc, static fn (array $a, array $b): int => [$b['potansiyel'], $b['impressions']] <=> [$a['potansiyel'], $a['impressions']]);

        return $sonuc;
    }

    /**
     * Aynı dilde, adresi yalnız sondaki beş karakterlik ekle ayrışan sayfalar.
     *
     * Bu site aynı yazının kopyalarını "-B253B" gibi eklerle üretti ve
     * ikisi aynı sorguda yarışınca ikisi de zayıflıyor. Burada yalnız
     * adlandırılır; birleştirme mevcut konsolidasyon komutlarının işi.
     *
     * @param  list<array{page: string, impressions: int, clicks: int, position: float}>  $satirlar
     * @return list<array{kok: string, sayfalar: list<string>, impressions: int}>
     */
    public function nushalar(array $satirlar): array
    {
        $gruplar = [];

        foreach ($satirlar as $satir) {
            $adres = rtrim((string) $satir['page'], '/');
            $kok = (string) preg_replace('/-[A-Z0-9]{5}$/', '', $adres);
            $gruplar[$kok]['sayfalar'][$adres] = true;
            $gruplar[$kok]['impressions'] = ($gruplar[$kok]['impressions'] ?? 0) + (int) $satir['impressions'];
        }

        $sonuc = [];

        foreach ($gruplar as $kok => $grup) {
            if (count($grup['sayfalar']) > 1) {
                $sonuc[] = ['kok' => $kok, 'sayfalar' => array_keys($grup['sayfalar']), 'impressions' => $grup['impressions']];
            }
        }

        usort($sonuc, static fn (array $a, array $b): int => $b['impressions'] <=> $a['impressions']);

        return $sonuc;
    }
}
