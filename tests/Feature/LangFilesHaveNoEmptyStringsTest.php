<?php

declare(strict_types=1);

namespace Tests\Feature;

use Tests\TestCase;

/**
 * Boş çeviri değeri, eksik anahtar kadar görünür bir kusurdur.
 *
 * 2026-10'da Türkçe çerez bandı ilk ekranda "consent.ask_beforeÖlçüm ve
 * reklam çerezleri…" basıyordu: tr.json'da değer "" idi ve diğer altı dilde
 * doluydu. Var olan eksik-anahtar testi bunu yakalamaz, çünkü anahtar vardı.
 * Burada iki şart var: hiçbir dil dosyasında boş değer olmaz ve varsayılan
 * dildeki her JSON anahtarı depodaki her JSON dil dosyasında bulunur (kapalı
 * diller dahil; bugün hepsi tam, bu bir gerileme korumasıdır).
 */
final class LangFilesHaveNoEmptyStringsTest extends TestCase
{
    public function test_hicbir_json_dil_dosyasinda_bos_deger_yok(): void
    {
        $bos = [];

        foreach (glob(lang_path('*.json')) ?: [] as $dosya) {
            $veri = json_decode((string) file_get_contents($dosya), true, flags: JSON_THROW_ON_ERROR);
            foreach ((array) $veri as $anahtar => $deger) {
                if (is_string($deger) && trim($deger) === '') {
                    $bos[] = basename($dosya).': '.$anahtar;
                }
            }
        }

        $this->assertSame([], $bos, 'Boş çeviri değeri ekrana anahtarın kendisi ya da boşluk olarak çıkar.');
    }

    public function test_varsayilan_dilin_json_anahtarlari_her_json_dosyasinda_var(): void
    {
        $varsayilan = (string) config('locales.default', 'tr');
        $kaynak = (array) json_decode((string) file_get_contents(lang_path($varsayilan.'.json')), true);
        $this->assertNotSame([], $kaynak);

        foreach (glob(lang_path('*.json')) ?: [] as $dosya) {
            $hedef = (array) json_decode((string) file_get_contents($dosya), true);
            $eksik = array_keys(array_diff_key($kaynak, $hedef));

            $this->assertSame([], $eksik, basename($dosya).' dosyasında '.count($eksik).' anahtar eksik: '.implode(', ', array_slice($eksik, 0, 5)));
        }
    }

    public function test_cerez_bandi_cumlesi_her_dilde_anahtar_basmaz(): void
    {
        foreach (glob(lang_path('*.json')) ?: [] as $dosya) {
            $dil = basename($dosya, '.json');
            foreach (['consent.ask_before', 'consent.ask_measure', 'consent.ask_measure_ads', 'consent.ask_after'] as $anahtar) {
                $metin = __($anahtar, [], $dil);
                $this->assertNotSame($anahtar, $metin, $dil.' dilinde '.$anahtar.' çevrilmemiş.');
                $this->assertNotSame('', trim($metin), $dil.' dilinde '.$anahtar.' boş.');
            }
        }

        $this->assertSame(
            'Bunun dışında yalnız ölçüm çerezleri için izninizi soruyoruz.',
            __('consent.ask_before', [], 'tr').__('consent.ask_measure', [], 'tr').__('consent.ask_after', [], 'tr'),
        );
    }
}
