<?php

declare(strict_types=1);

namespace Tests\Feature;

use Tests\TestCase;

/**
 * Açık bir dilin arayüz metinleri eksiksiz olmak zorundadır.
 *
 * Laravel, karşılığı olmayan bir anahtarı sessizce anahtarın kendisi olarak
 * basar. Yani eksik çeviri hata vermez, boş da bırakmaz - ziyaretçiye
 * "home.hero_title" yazar. 13 Eylül 2026'da canlıda tam olarak bu oldu:
 * Almanca, Farsça ve Lehçe anasayfalarda 69 ham anahtar basıldı ve üç dil de
 * indekslenebilir durumdaydı.
 *
 * Hiçbir sayfa kırılmadığı için bunu ancak sayfaya bakan biri fark eder.
 * Test o yüzden var: dil açılmasını içeriğin varlığına bağlar.
 */
final class EnabledLocalesHaveCompleteUiStringsTest extends TestCase
{
    /** @return array<string, string> */
    private function anahtarlar(string $dil): array
    {
        $duz = [];

        $json = lang_path($dil.'.json');
        if (is_file($json)) {
            foreach ((array) json_decode((string) file_get_contents($json), true) as $k => $v) {
                if (is_string($v)) {
                    $duz[(string) $k] = $v;
                }
            }
        }

        foreach (glob(lang_path($dil).'/*.php') ?: [] as $dosya) {
            $grup = basename($dosya, '.php');
            $veri = require $dosya;
            $yassilat = function (array $dizi, string $onek) use (&$yassilat, &$duz): void {
                foreach ($dizi as $k => $v) {
                    is_array($v) ? $yassilat($v, $onek.$k.'.') : $duz[$onek.$k] = (string) $v;
                }
            };
            $yassilat(is_array($veri) ? $veri : [], $grup.'.');
        }

        return $duz;
    }

    public function test_acik_her_dil_varsayilan_dilin_butun_anahtarlarini_tasir(): void
    {
        $varsayilan = (string) config('locales.default', 'tr');
        $kaynak = $this->anahtarlar($varsayilan);

        $this->assertNotSame([], $kaynak, 'Varsayılan dilin metinleri okunamadı.');

        foreach ((array) config('locales.supported', []) as $dil => $ayar) {
            if ($dil === $varsayilan || ($ayar['enabled'] ?? false) !== true) {
                continue;
            }

            $hedef = $this->anahtarlar((string) $dil);
            $eksik = array_keys(array_diff_key($kaynak, $hedef));

            $this->assertSame(
                [],
                $eksik,
                $dil.' dili açık ama '.count($eksik).' arayüz metni eksik; '
                .'ziyaretçi bunların yerine anahtarın kendisini görür. İlk beşi: '
                .implode(', ', array_slice($eksik, 0, 5))
            );
        }
    }

    public function test_kapali_dil_bu_sarta_girmez(): void
    {
        // Kapalı dil sayfa servis etmiyor; eksik metni kimse görmez. Şartı
        // oraya da uygulamak, dil hazırlamayı imkânsız kılardı: dosya
        // tamamlanana kadar test kırık kalırdı.
        config(['locales.supported.de.enabled' => false]);

        $this->test_acik_her_dil_varsayilan_dilin_butun_anahtarlarini_tasir();
    }
}
