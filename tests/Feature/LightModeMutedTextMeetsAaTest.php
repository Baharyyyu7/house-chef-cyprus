<?php

declare(strict_types=1);

namespace Tests\Feature;

use Tests\TestCase;

/**
 * Açık temadaki ikincil metin rengi AA'yı geçmeli - ama hiyerarşiyi de
 * korumalı.
 *
 * `--hc-text-muted` (#7b6e65) en açık olmayan zeminde (#fdf1e6) 4.44:1
 * kalıyordu; AA gövde metni için 4.5 istiyor. Sitedeki en yaygın ikincil
 * metin rengi bu.
 *
 * Önerilen düzeltme #62564f idi ve AA'yı rahatça geçiyordu - ama o değer
 * `--hc-text-secondary`in kendisi. Kabul edilseydi iki tipografi kademesi
 * tek renge çökerdi: "ikincil" ve "sönük" ayrımı görsel olarak kaybolurdu.
 * AA'yı geçen ama ayırt edilebilir kalan bir değer seçildi.
 */
final class LightModeMutedTextMeetsAaTest extends TestCase
{
    private const MUTED = '#71645b';

    private const SECONDARY = '#62564f';

    /** Açık temada kullanılan zeminlerin en zoru. */
    private const EN_ZOR_ZEMIN = '#fdf1e6';

    public function test_the_token_is_the_measured_value(): void
    {
        $this->assertStringContainsString('--hc-text-muted:'.self::MUTED, $this->css());
    }

    public function test_it_meets_aa_on_the_hardest_light_surface(): void
    {
        $this->assertGreaterThanOrEqual(4.5, $this->oran(self::MUTED, self::EN_ZOR_ZEMIN));
    }

    public function test_the_previous_value_really_did_fail(): void
    {
        // Var olmayan bir sorunu çözmediğimizin kaydı.
        $this->assertLessThan(4.5, $this->oran('#7b6e65', self::EN_ZOR_ZEMIN));
    }

    public function test_it_stays_distinguishable_from_secondary(): void
    {
        // Asıl koruma bu: AA'yı geçmek için hiyerarşiyi feda etmiyoruz.
        $this->assertNotSame(self::SECONDARY, self::MUTED);

        // Sönük metin ikincilden daha açıktır, yani açık zeminde kontrastı
        // daha düşüktür. İkisi eşitlenirse kademe kaybolur.
        $this->assertLessThan(
            $this->oran(self::SECONDARY, self::EN_ZOR_ZEMIN),
            $this->oran(self::MUTED, self::EN_ZOR_ZEMIN),
            'Sönük metin, ikincil metinden daha açık kalmalı.'
        );
    }

    public function test_the_map_node_shows_a_focus_ring(): void
    {
        // `outline:none` konmuş ama yerine bir şey konmamıştı; parlaklık
        // değişimi tek başına odak göstergesi sayılmaz.
        $this->assertMatchesRegularExpression(
            '/\.hc-world-map__ulke-aktif:focus-visible \{[^}]*outline:2px solid/',
            $this->css()
        );
    }

    private function css(): string
    {
        return (string) file_get_contents(resource_path('css/app.css'));
    }

    private function oran(string $on, string $arka): float
    {
        $l = function (string $hex): float {
            $f = fn (float $c): float => $c <= 0.04045 ? $c / 12.92 : (($c + 0.055) / 1.055) ** 2.4;
            [$r, $g, $b] = array_map(fn (string $p): float => hexdec($p) / 255, str_split(ltrim($hex, '#'), 2));

            return 0.2126 * $f($r) + 0.7152 * $f($g) + 0.0722 * $f($b);
        };

        $a = $l($on);
        $b = $l($arka);

        return (max($a, $b) + 0.05) / (min($a, $b) + 0.05);
    }
}
