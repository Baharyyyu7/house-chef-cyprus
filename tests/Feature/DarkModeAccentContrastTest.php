<?php

declare(strict_types=1);

namespace Tests\Feature;

use Tests\TestCase;

/**
 * Marka vurgusu koyu temada WCAG AA'yı geçmeli.
 *
 * `text-gold-700` (#c2410c) koyu zeminde 3.80:1 kalıyordu; AA gövde metni
 * için 4.5:1 istiyor. Bu renk 21 yerde bağlantı rengi olarak kullanılıyordu -
 * herkese açık kayıt, şifre sıfırlama ve e-posta doğrulama sayfaları dahil.
 *
 * Her çağrı yerine ayrı `dark:` sınıfı eklemek hem churn hem de bir sonraki
 * yeni sayfada yine unutulacak bir iş. Kural tek yerde duruyor.
 */
final class DarkModeAccentContrastTest extends TestCase
{
    public function test_the_accent_is_lightened_in_dark_mode(): void
    {
        $this->assertStringContainsString(
            'html.dark .text-gold-700 { color:#fb923c; }',
            (string) file_get_contents(resource_path('css/app.css'))
        );
    }

    public function test_the_replacement_actually_meets_aa(): void
    {
        // Düzeltmenin yönü değil, sayısı ölçülüyor: yanlış bir renk seçmek
        // de sessiz bir erişilebilirlik hatası olurdu.
        $this->assertGreaterThanOrEqual(4.5, $this->oran('#fb923c', '#0b0b0c'));
        $this->assertGreaterThanOrEqual(4.5, $this->oran('#fb923c', '#1d1d1e'), 'Kart zemininde de geçmeli.');
    }

    public function test_the_original_colour_really_did_fail(): void
    {
        // Var olmayan bir sorunu "çözmediğimizin" kaydı.
        $this->assertLessThan(4.5, $this->oran('#c2410c', '#0b0b0c'));
    }

    private function oran(string $on, string $arka): float
    {
        $l = fn (string $hex): float => (function (array $k): float {
            $f = fn (float $c): float => $c <= 0.03928 ? $c / 12.92 : (($c + 0.055) / 1.055) ** 2.4;

            return 0.2126 * $f($k[0]) + 0.7152 * $f($k[1]) + 0.0722 * $f($k[2]);
        })(array_map(fn (string $p): float => hexdec($p) / 255, str_split(ltrim($hex, '#'), 2)));

        $a = $l($on);
        $b = $l($arka);

        return (max($a, $b) + 0.05) / (min($a, $b) + 0.05);
    }
}
