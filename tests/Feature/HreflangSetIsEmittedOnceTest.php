<?php

declare(strict_types=1);

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * hreflang kümesi sayfa başına bir kez basılır.
 *
 * Google çelişkili bir hreflang kümesini kısmen değil tamamen yok sayar; iki
 * ayrı küme yan yana basıldığında sonuç sıfır kümedir. Bu yüzden "iki kez
 * basmak" zararsız bir tekrar değil, hreflang'i tamamen kaybetmek demektir.
 *
 * Kusur yalnız birden çok dil ETKİN olduğunda görünüyordu; tek dil açıkken
 * ikinci küme zaten boş basıyordu. Yani hata tam da dil açıldığı gün ortaya
 * çıkacaktı - test o günü beklemesin diye dili burada açıyoruz.
 */
final class HreflangSetIsEmittedOnceTest extends TestCase
{
    use RefreshDatabase;

    /** @return list<array{0: string, 1: string}> */
    private function hreflangCiftleri(string $html): array
    {
        preg_match_all(
            '/<link\s+rel="alternate"\s+hreflang="([^"]+)"\s+href="([^"]+)"/',
            $html,
            $eslesme,
            PREG_SET_ORDER
        );

        return array_map(static fn (array $r): array => [$r[1], $r[2]], $eslesme);
    }

    public function test_anasayfa_her_dili_bir_kez_bildirir(): void
    {
        config(['locales.supported.en.enabled' => true]);

        $ciftler = $this->hreflangCiftleri($this->get('/')->getContent());

        $diller = array_column($ciftler, 0);
        $this->assertSame(
            array_unique($diller),
            $diller,
            'Aynı hreflang birden çok kez basıldı; küme geçersiz: '.implode(', ', $diller)
        );
        $this->assertContains('x-default', $diller);
        $this->assertContains('tr', $diller);
        $this->assertContains('en', $diller);
    }

    public function test_tek_dil_acikken_hreflang_basilmaz(): void
    {
        config(['locales.supported.en.enabled' => false]);

        $this->assertSame([], $this->hreflangCiftleri($this->get('/')->getContent()));
    }

    public function test_hreflang_partiali_link_etiketi_basmaz(): void
    {
        // Tek kaynak LocaleUrlService::alternates(). Partial yeniden <link>
        // basmaya başlarsa küme sessizce ikiye katlanır ve hiçbir sayfa
        // kırılmadığı için fark edilmez.
        $kaynak = file_get_contents(resource_path('views/partials/hreflang.blade.php'));

        $this->assertStringNotContainsString('rel="alternate"', $kaynak);
    }
}
