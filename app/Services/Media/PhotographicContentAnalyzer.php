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
 */
final class PhotographicContentAnalyzer
{
    /** Bu eşiğin altındaki kenar enerjisi fotoğraf değil demektir. */
    public const ENERGY_THRESHOLD = 6.0;

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
                $colors[$pixel >> 3] = true;

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

        return [
            'analyzed' => true,
            'photographic' => $score >= self::ENERGY_THRESHOLD,
            'energy' => $score,
            'unique_colors' => count($colors),
            'reason' => null,
        ];
    }

    /** @return array{analyzed:bool,photographic:bool|null,energy:float|null,unique_colors:int|null,reason:string|null} */
    private function inconclusive(string $reason): array
    {
        return ['analyzed' => false, 'photographic' => null, 'energy' => null, 'unique_colors' => null, 'reason' => $reason];
    }
}
