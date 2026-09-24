<?php

declare(strict_types=1);

namespace App\Services\Blog;

use App\Models\BlogPost;

/**
 * Rehber içeriğinde fotoğraf bilgi taşımaz; "ev ölçüleri" yazısının fotoğrafı
 * dekoratiftir. Fotoğraf yerine başlıktan üretilen tipografik kapak kullanılır:
 * asla yanlış yemeği gösteremez, üretim maliyeti yoktur ve her yazı sosyal
 * paylaşımda kendi başlığıyla görünür.
 *
 * Renk yazının slug'ından türetilir; aynı yazı her zaman aynı kapağı alır.
 */
final class CoverArtService
{
    /** Marka paletiyle uyumlu, birbirinden ayırt edilebilir zemin çiftleri. */
    private const PALETTES = [
        ['bg' => '#1c1917', 'accent' => '#fb923c', 'ink' => '#fffaf5'],
        ['bg' => '#14261d', 'accent' => '#f59e0b', 'ink' => '#f6fbf7'],
        ['bg' => '#2a1710', 'accent' => '#fdba74', 'ink' => '#fff7ed'],
        ['bg' => '#111b2e', 'accent' => '#f97316', 'ink' => '#f5f8ff'],
        ['bg' => '#241426', 'accent' => '#fb7185', 'ink' => '#fdf4ff'],
        ['bg' => '#0f2027', 'accent' => '#38bdf8', 'ink' => '#f0f9ff'],
    ];

    /** @return array{bg:string,accent:string,ink:string} */
    public function palette(BlogPost $post): array
    {
        $index = hexdec(substr(md5((string) $post->slug), 0, 8)) % count(self::PALETTES);

        return self::PALETTES[$index];
    }

    public function overline(BlogPost $post): string
    {
        $category = $post->category;

        return mb_strtoupper($category === null ? 'Mutfak Rehberi' : $category->name, 'UTF-8');
    }

    /**
     * Başlığı görsel genişliğine göre satırlara böler. Ölçü karakter sayısına
     * değil, harflerin gerçek genişliğine dayanır; aksi hâlde "İİİ" ile "www"
     * aynı sayılır ve uzun başlıklar taşar.
     *
     * @return list<string>
     */
    public function titleLines(BlogPost $post, float $fontSize, float $maxWidth, int $maxLines = 4): array
    {
        $words = preg_split('/\s+/u', trim((string) $post->title)) ?: [];
        $font = $this->fontPath(800);
        $lines = [];
        $current = '';

        foreach ($words as $word) {
            $candidate = $current === '' ? $word : $current.' '.$word;

            if ($current !== '' && $this->textWidth($candidate, $fontSize, $font) > $maxWidth) {
                $lines[] = $current;
                $current = $word;

                if (count($lines) === $maxLines) {
                    break;
                }

                continue;
            }

            $current = $candidate;
        }

        if ($current !== '' && count($lines) < $maxLines) {
            $lines[] = $current;
        }

        if ($lines === []) {
            return [(string) $post->title];
        }

        // Sığmayan başlıkta son satır üç noktayla biter, ortadan kesilmez.
        $son = count($lines) - 1;
        if ($this->joined($lines) !== trim((string) $post->title)) {
            $lines[$son] = rtrim($lines[$son], " \t.,;:").'…';
        }

        return $lines;
    }

    public function fontPath(int $weight): string
    {
        return resource_path('fonts/manrope-'.($weight >= 800 ? '800' : '700').'.ttf');
    }

    private function textWidth(string $text, float $size, string $font): float
    {
        if (! is_file($font)) {
            // Font yoksa kaba tahmin: ortalama harf genişliği punto başına ~0.55.
            return mb_strlen($text) * $size * 0.55;
        }

        $box = imagettfbbox($size, 0, $font, $text);

        return $box === false ? mb_strlen($text) * $size * 0.55 : abs($box[2] - $box[0]);
    }

    /** @param list<string> $lines */
    private function joined(array $lines): string
    {
        return trim(implode(' ', $lines));
    }
}
