<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Models\BlogPost;
use App\Services\Blog\CoverArtService;
use Illuminate\Http\Response;

/**
 * Sosyal paylaşımda 216 yazının tamamı aynı varsayılan marka görselini
 * kullanıyordu. Bu uç nokta her yazı için kendi başlığını taşıyan bir
 * og:image üretir. Sayfadaki kapak SVG'dir; paylaşım platformları raster
 * beklediği için burada GD ile PNG üretilir.
 */
final class BlogCoverController extends Controller
{
    private const WIDTH = 1200;

    private const HEIGHT = 630;

    public function __invoke(BlogPost $post, CoverArtService $cover): Response
    {
        abort_unless(BlogPost::query()->publiclyVisible()->whereKey($post->id)->exists(), 404);

        $palette = $cover->palette($post);
        $canvas = imagecreatetruecolor(self::WIDTH, self::HEIGHT);

        $bg = $this->allocate($canvas, $palette['bg']);
        $accent = $this->allocate($canvas, $palette['accent']);
        $ink = $this->allocate($canvas, $palette['ink']);

        imagefilledrectangle($canvas, 0, 0, self::WIDTH, self::HEIGHT, $bg);
        $this->softCircle($canvas, $palette['accent'], 1090, 80, 300, 0.13);
        $this->softCircle($canvas, $palette['accent'], 1160, 30, 150, 0.10);

        imagettftext($canvas, 21, 0, 76, 104, $accent, $cover->fontPath(800), $this->spaced($cover->overline($post), 2));

        // 76 kenar boşluğu iki yandan => 1048 kullanılabilir genişlik.
        $lines = $cover->titleLines($post, 58, 1048, 3);
        $lineHeight = 74;
        $startY = (int) (self::HEIGHT / 2) - (int) ((count($lines) * $lineHeight) / 2) + 56;

        foreach ($lines as $i => $line) {
            imagettftext($canvas, 58, 0, 76, $startY + ($i * $lineHeight), $ink, $cover->fontPath(800), $line);
        }

        imagefilledrectangle($canvas, 76, 536, 136, 540, $accent);
        imagettftext($canvas, 18, 0, 76, 578, $ink, $cover->fontPath(700), $this->spaced('HOUSE CHEF CYPRUS', 2));

        ob_start();
        imagepng($canvas, null, 8);
        $binary = (string) ob_get_clean();
        imagedestroy($canvas);

        return response($binary, 200, [
            'Content-Type' => 'image/png',
            'Cache-Control' => 'public, max-age=604800',
        ]);
    }

    private function allocate(\GdImage $canvas, string $hex): int
    {
        [$r, $g, $b] = sscanf(ltrim($hex, '#'), '%2x%2x%2x') ?: [0, 0, 0];

        return (int) imagecolorallocate($canvas, (int) $r, (int) $g, (int) $b);
    }

    private function softCircle(\GdImage $canvas, string $hex, int $x, int $y, int $diameter, float $opacity): void
    {
        [$r, $g, $b] = sscanf(ltrim($hex, '#'), '%2x%2x%2x') ?: [0, 0, 0];
        $colour = imagecolorallocatealpha($canvas, (int) $r, (int) $g, (int) $b, (int) round(127 * (1 - $opacity)));

        if ($colour !== false) {
            imagefilledellipse($canvas, $x, $y, $diameter, $diameter, $colour);
        }
    }

    /** Harf aralığı GD'de doğrudan ayarlanamaz; overline için elle boşluk eklenir. */
    private function spaced(string $text, int $spaces): string
    {
        $letters = preg_split('//u', $text, -1, PREG_SPLIT_NO_EMPTY) ?: [];

        return implode(str_repeat(' ', $spaces), $letters);
    }
}
