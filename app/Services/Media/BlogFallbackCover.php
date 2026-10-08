<?php

declare(strict_types=1);

namespace App\Services\Media;

use Illuminate\Support\Facades\Storage;
use RuntimeException;

/**
 * Görseli olmayan blog yazılarının ortak kapağı.
 *
 * Kapak 2,79 MB'lık 1600 px bir PNG; /blog'daki 12 kartın 12'si onu
 * kullanıyordu ve 400 px'lik kartta tam boy iniyordu. Aynı kaynaktan
 * 400/800/1600 px AVIF ve WebP varyantları üretilir; varyant dosyaları
 * varsa görünüm <picture> ile onları sunar, yoksa (henüz üretilmediyse)
 * eski PNG'ye düşer. Kaynak dosya depoda değil, üretimdeki genel diskte:
 * varyantlar orada `php artisan media:blog-fallback-variants` ile üretilir.
 */
final class BlogFallbackCover
{
    public const WIDTHS = [400, 800, 1600];

    public const FORMATS = ['avif', 'webp'];

    /** @var array<string, string|null>|null */
    private ?array $srcsetler = null;

    public function url(): string
    {
        return asset(ltrim((string) config('media_excellence.blog_fallback_url'), '/'));
    }

    /** Biçim için srcset; varyant yoksa null. */
    public function srcset(string $format): ?string
    {
        $this->srcsetler ??= $this->hesapla();

        return $this->srcsetler[$format] ?? null;
    }

    /**
     * Varyantları kaynaktan üretir (var olanların üzerine yazar).
     *
     * @return list<string> Yazılan dosyaların disk yolları
     */
    public function generate(): array
    {
        $kaynak = $this->kaynakYolu();

        if ($kaynak === null || ! Storage::disk('public')->exists($kaynak)) {
            throw new RuntimeException('Blog yedek kapağının kaynağı genel diskte bulunamadı: '.($kaynak ?? config('media_excellence.blog_fallback_url')));
        }

        $resim = @imagecreatefromstring((string) Storage::disk('public')->get($kaynak));
        if ($resim === false) {
            throw new RuntimeException('Blog yedek kapağı okunamadı.');
        }

        $genislik = imagesx($resim);
        $yukseklik = imagesy($resim);
        $yazilan = [];

        foreach (self::WIDTHS as $hedef) {
            $w = min($hedef, $genislik);
            $h = (int) round($yukseklik * $w / $genislik);
            // imagescale(IMG_BICUBIC) bazı GD derlemelerinde false dönüyor;
            // imagecopyresampled her yerde çalışıyor.
            $kucuk = imagecreatetruecolor($w, $h);
            imagealphablending($kucuk, false);
            imagesavealpha($kucuk, true);
            imagecopyresampled($kucuk, $resim, 0, 0, 0, 0, $w, $h, $genislik, $yukseklik);

            foreach (self::FORMATS as $format) {
                ob_start();
                $tamam = $format === 'avif' ? imageavif($kucuk, null, 55, 6) : imagewebp($kucuk, null, 78);
                $veri = (string) ob_get_clean();

                if ($tamam && $veri !== '') {
                    $yol = $this->varyantYolu($kaynak, $hedef, $format);
                    Storage::disk('public')->put($yol, $veri);
                    $yazilan[] = $yol;
                }
            }

            imagedestroy($kucuk);
        }

        imagedestroy($resim);
        $this->srcsetler = null;

        return $yazilan;
    }

    /** @return array<string, string|null> */
    private function hesapla(): array
    {
        $kaynak = $this->kaynakYolu();
        $sonuc = [];

        foreach (self::FORMATS as $format) {
            $parcalar = [];

            if ($kaynak !== null) {
                foreach (self::WIDTHS as $genislik) {
                    $yol = $this->varyantYolu($kaynak, $genislik, $format);

                    if (Storage::disk('public')->exists($yol)) {
                        $parcalar[] = asset('storage/'.$yol).' '.$genislik.'w';
                    }
                }
            }

            $sonuc[$format] = $parcalar === [] ? null : implode(', ', $parcalar);
        }

        return $sonuc;
    }

    /** Genel diskteki göreli yol; adres diskin dışını gösteriyorsa (CDN) null. */
    private function kaynakYolu(): ?string
    {
        $yol = (string) parse_url((string) config('media_excellence.blog_fallback_url'), PHP_URL_PATH);

        return str_starts_with($yol, '/storage/') ? substr($yol, strlen('/storage/')) : null;
    }

    private function varyantYolu(string $kaynak, int $genislik, string $format): string
    {
        return preg_replace('/\.[a-z0-9]+$/i', '', $kaynak).'-'.$genislik.'.'.$format;
    }
}
