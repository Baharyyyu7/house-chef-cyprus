<?php

declare(strict_types=1);

namespace App\Services\Media;

use Illuminate\Support\Facades\Storage;

/**
 * Onaylı medyanın gerçekten fotoğraf olup olmadığını görüntü içeriğinden ölçer.
 *
 * Soyut gradient/placeholder grafikleri komşu pikseller arasında neredeyse hiç
 * fark üretmez; gerçek yemek fotoğrafları yüksek frekanslı detay taşır. Onay
 * alanları (media_status, verification_status) bu ayrımı yakalayamadığı için
 * marka grafikleri "approved_real_media" olarak public yüzeye çıkabiliyordu.
 *
 * Kenar enerjisi tek başına yetmiyor. "Sütlaç" için üretilmiş pembe zemin,
 * beyaz elips ve gri dikdörtgenden ibaret bir grafik 23.86 enerji ölçtü ve
 * eşiği rahatça geçti: birkaç keskin kenar, yüzeyin tamamı düz olsa bile
 * yüksek enerji üretiyor. Renk çeşitliliği bu tuzağa düşmez -- 64x64'e
 * indirilip 4 bit kuantalanan bir fotoğraf yüzlerce ton taşır, üretilmiş düz
 * grafik onlarca. Ölçülen korpusta ayrım keskindi: yayındaki 15 fotoğrafın en
 * düşüğü 275 renk, sahte grafiklerin en yükseği 83.
 */
final class PhotographicContentAnalyzer
{
    /** Bu eşiğin altındaki kenar enerjisi fotoğraf değil demektir. */
    public const ENERGY_THRESHOLD = 6.0;

    /** Bu sayıdan az farklı ton taşıyan görüntü fotoğraf değildir. */
    public const COLOUR_THRESHOLD = 150;

    private const SAMPLE_SIZE = 64;

    /**
     * @return array{analyzed:bool,photographic:bool|null,energy:float|null,unique_colors:int|null,reason:string|null}
     */
    public function analyze(string $disk, string $path): array
    {
        if (! Storage::disk($disk)->exists($path)) {
            return $this->inconclusive('file_missing');
        }

        $raw = Storage::disk($disk)->get($path);
        if (! is_string($raw) || $raw === '') {
            return $this->inconclusive('file_unreadable');
        }

        $image = @imagecreatefromstring($raw);
        if ($image === false) {
            return $this->inconclusive('not_a_raster_image');
        }

        $sample = imagescale($image, self::SAMPLE_SIZE, self::SAMPLE_SIZE);
        imagedestroy($image);
        if ($sample === false) {
            return $this->inconclusive('scale_failed');
        }

        $colors = [];
        $energy = 0;
        $comparisons = 0;

        for ($y = 0; $y < self::SAMPLE_SIZE; $y++) {
            $previous = null;
            for ($x = 0; $x < self::SAMPLE_SIZE; $x++) {
                $pixel = imagecolorat($sample, $x, $y);
                // Kanal başına 4 bite indirilir: JPEG sıkıştırmasının ürettiği
                // bir tık farklı tonlar ayrı renk sayılmasın, gerçek ton
                // zenginliği sayılsın.
                $colors[((($pixel >> 20) & 15) << 8) | ((($pixel >> 12) & 15) << 4) | (($pixel >> 4) & 15)] = true;

                if ($previous !== null) {
                    $energy += abs((($pixel >> 16) & 255) - (($previous >> 16) & 255))
                        + abs((($pixel >> 8) & 255) - (($previous >> 8) & 255))
                        + abs(($pixel & 255) - ($previous & 255));
                    $comparisons++;
                }

                $previous = $pixel;
            }
        }

        imagedestroy($sample);

        if ($comparisons === 0) {
            return $this->inconclusive('no_comparable_pixels');
        }

        $score = round($energy / $comparisons, 2);
        $tonSayisi = count($colors);

        // Fotoğraf sayılmak için ikisini birden geçmesi gerekir: yalnız
        // enerjiye bakmak keskin kenarlı düz grafikleri içeri alıyordu.
        return [
            'analyzed' => true,
            'photographic' => $score >= self::ENERGY_THRESHOLD && $tonSayisi >= self::COLOUR_THRESHOLD,
            'energy' => $score,
            'unique_colors' => $tonSayisi,
            'reason' => null,
        ];
    }

    /** @return array{analyzed:bool,photographic:bool|null,energy:float|null,unique_colors:int|null,reason:string|null} */
    private function inconclusive(string $reason): array
    {
        return ['analyzed' => false, 'photographic' => null, 'energy' => null, 'unique_colors' => null, 'reason' => $reason];
    }
}
