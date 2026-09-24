<?php

declare(strict_types=1);

namespace Tests\Feature;

use Tests\TestCase;

/**
 * Aynı seçicinin hem @layer içinde hem dışında tanımlanması bu depoda iki kez
 * canlı kusur üretti.
 *
 * Katmansız bildirim, katmanlı olanı her zaman yener - CSS basamağında
 * katmansız stiller en yüksek önceliğe sahiptir. Katmanlı taraf sessizce
 * etkisiz kalır ama dosyada durmaya devam eder, dolayısıyla onu okuyup
 * düzenleyen kişi hiçbir şeyi değiştirmemiş olur.
 *
 * Yaşananlar:
 * - .hc-v3-card katmansız kopyası height:100% veriyordu; yönetim panelinde
 *   249 karakterlik kart 2.829 piksele şişiyordu.
 * - .hc-v3-card--interactive iki kez tanımlıydı, -3px ve -4px diye çelişiyordu.
 *
 * Bu test mevcut çakışmaları tek tek düzeltmiyor - onlar @apply taşıdığı için
 * körü körüne birleştirilemez. Yaptığı şey sayının **artmamasını** sağlamak:
 * yeni bir çakışma eklenirse burada kırılır.
 */
final class CssLayerConflictRatchetTest extends TestCase
{
    /**
     * Bilinen çakışmalar. Bir seçici düzeltildiğinde buradan silinmeli;
     * liste küçüldükçe test daha sıkı hale gelir.
     */
    private const BILINEN_CAKISMALAR = [
        '.hc-body-lg',
        '.hc-btn-outline',
        '.hc-card-hover',
        '.hc-display-lg',
        '.hc-display-xl',
        '.hc-heading-1',
        '.hc-heading-2',
        '.hc-heading-3',
        '.hc-heading-4',
        '.hc-panel',
        '.hc-v3-alert',
        '.hc-v3-badge',
        '.hc-v3-btn--primary',
        '.hc-v3-empty',
        '.hc-v3-input',
        '.mobile-nav-link',
        '.mobile-nav-link-active',
        '.nav-link',
    ];

    public function test_no_new_selector_is_defined_both_inside_and_outside_a_layer(): void
    {
        $bulunan = $this->cakisanSeciciler();
        $yeni = array_values(array_diff($bulunan, self::BILINEN_CAKISMALAR));

        $this->assertSame([], $yeni, "Yeni katman çakışması eklendi. Katmansız tanım katmanlıyı sessizce eziyor; seçiciyi tek yerde tanımla:\n- ".implode("\n- ", $yeni));
    }

    public function test_a_fixed_conflict_is_removed_from_the_known_list(): void
    {
        // Liste gerçeği yansıtmalı: düzeltilmiş bir seçici burada kalırsa
        // kilit gevşer ve yeni bir çakışma onun yerine gizlenebilir.
        $bulunan = $this->cakisanSeciciler();
        $artik_yok = array_values(array_diff(self::BILINEN_CAKISMALAR, $bulunan));

        $this->assertSame([], $artik_yok, "Bu seçiciler düzeltilmiş; BILINEN_CAKISMALAR listesinden çıkar:\n- ".implode("\n- ", $artik_yok));
    }

    /** @return list<string> */
    private function cakisanSeciciler(): array
    {
        $css = (string) file_get_contents(resource_path('css/app.css'));
        $katmanlar = $this->katmanAraliklari($css);

        $konumlar = [];
        preg_match_all('/^[ \t]*(\.[a-zA-Z0-9_-]+)\s*\{/m', $css, $eslesmeler, PREG_OFFSET_CAPTURE | PREG_SET_ORDER);

        foreach ($eslesmeler as $eslesme) {
            $secici = $eslesme[1][0];
            $konum = $eslesme[0][1];
            $konumlar[$secici][] = $this->katmanIcinde($konum, $katmanlar);
        }

        $cakisan = [];

        foreach ($konumlar as $secici => $bayraklar) {
            if (count($bayraklar) > 1 && in_array(true, $bayraklar, true) && in_array(false, $bayraklar, true)) {
                $cakisan[] = $secici;
            }
        }

        sort($cakisan);

        return $cakisan;
    }

    /** @return list<array{0:int,1:int}> */
    private function katmanAraliklari(string $css): array
    {
        $araliklar = [];
        preg_match_all('/@layer\s+[a-z, ]+\s*\{/', $css, $eslesmeler, PREG_OFFSET_CAPTURE);

        foreach ($eslesmeler[0] as $eslesme) {
            $bas = (int) $eslesme[1];
            $i = $bas + strlen($eslesme[0]);
            $derinlik = 1;

            while ($derinlik > 0 && $i < strlen($css)) {
                $derinlik += match ($css[$i]) { '{' => 1, '}' => -1, default => 0 };
                $i++;
            }

            $araliklar[] = [$bas, $i];
        }

        return $araliklar;
    }

    /** @param list<array{0:int,1:int}> $araliklar */
    private function katmanIcinde(int $konum, array $araliklar): bool
    {
        foreach ($araliklar as [$bas, $son]) {
            if ($konum >= $bas && $konum < $son) {
                return true;
            }
        }

        return false;
    }
}
