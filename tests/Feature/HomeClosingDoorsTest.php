<?php

declare(strict_types=1);

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

final class HomeClosingDoorsTest extends TestCase
{
    use RefreshDatabase;

    public function test_the_homepage_links_to_every_hub_page(): void
    {
        // Dört hub sayfası da yayındaydı ve 200 dönüyordu, ama anasayfanın
        // içeriği hiçbirine bağlanmıyordu: üçü yalnız footer'da duruyordu,
        // /ne-pisirebilirim hiçbir yerde yoktu.
        $cevap = $this->get(route('home'))->assertOk();

        foreach (['ingredients.hub.index', 'cuisines.index', 'categories.index', 'ingredients.explore'] as $rota) {
            $cevap->assertSee(route($rota), false);
        }
    }

    public function test_every_hub_page_the_closing_offers_actually_opens(): void
    {
        // Kapanış kırık bir kapı sunmamalı.
        foreach (['ingredients.hub.index', 'cuisines.index', 'categories.index', 'ingredients.explore'] as $rota) {
            $this->get(route($rota))->assertOk();
        }
    }

    public function test_the_closing_no_longer_repeats_the_hero_offer(): void
    {
        // Dokuz bölüm kaydırıp tıklamamış birine sayfanın başındaki çağrıyı
        // tekrar sunmak en zayıf teklifti.
        $this->get(route('home'))->assertOk()->assertDontSee('Tarifleri keşfet');
    }

    public function test_the_closing_band_text_passes_aa_contrast(): void
    {
        // İlk halinde zemin #f97316'ydı ve bu bantta hiçbir metin AA'yı
        // geçmiyordu: düz turuncu üzerinde beyaz bile 2.8:1. Başlıklar büyük
        // metin sayılıp kurtuluyordu, açıklama satırı kurtulmuyordu.
        $stil = (string) file_get_contents(resource_path('css/app.css'));

        $this->assertStringContainsString('background:#9a3412', $stil);

        // Kart zemini: bandın üzerine %12 beyaz.
        $kart = $this->bindir([255, 255, 255], [0x9a, 0x34, 0x12], 0.12);

        $this->assertGreaterThanOrEqual(4.5, $this->kontrast([255, 255, 255], $kart));
        $this->assertGreaterThanOrEqual(4.5, $this->kontrast([0xff, 0xed, 0xd5], $kart));
    }

    /** @param list<int> $ust @param list<int> $alt @return list<int> */
    private function bindir(array $ust, array $alt, float $oran): array
    {
        return array_map(
            static fn (int $u, int $a): int => (int) round($oran * $u + (1 - $oran) * $a),
            $ust,
            $alt
        );
    }

    /** @param list<int> $a @param list<int> $b */
    private function kontrast(array $a, array $b): float
    {
        $l = static function (array $renk): float {
            $k = array_map(static function (int $v): float {
                $n = $v / 255;

                return $n <= 0.03928 ? $n / 12.92 : (($n + 0.055) / 1.055) ** 2.4;
            }, $renk);

            return 0.2126 * $k[0] + 0.7152 * $k[1] + 0.0722 * $k[2];
        };

        [$x, $y] = [$l($a), $l($b)];

        return (max($x, $y) + 0.05) / (min($x, $y) + 0.05);
    }
}
