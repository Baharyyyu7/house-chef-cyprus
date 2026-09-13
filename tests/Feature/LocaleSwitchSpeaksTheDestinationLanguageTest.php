<?php

declare(strict_types=1);

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Dil değiştirme onayı hedef dilde yazılır.
 *
 * Kullanıcı İngilizceye geçtiyse Türkçe bir onay okumak geçişin çalışmadığı
 * izlenimi verir: kişi zaten Türkçe okuyamadığı için dili değiştirmiştir.
 *
 * Dil adı o dilin kendi yazımıyla geçer. "Deutsch" yerine "German" yazmak,
 * Almanca'ya geçen birine dilini yabancı bir adla tanıtmak olurdu.
 */
final class LocaleSwitchSpeaksTheDestinationLanguageTest extends TestCase
{
    use RefreshDatabase;

    public function test_her_acik_dil_kendi_dilinde_onaylar(): void
    {
        foreach (['en', 'de', 'pl'] as $dil) {
            config(['locales.supported.'.$dil.'.enabled' => true]);
        }

        foreach (['en' => 'English', 'de' => 'Deutsch', 'pl' => 'Polski'] as $dil => $ad) {
            $beklenen = trans('ux.locale_switched', ['language' => $ad], $dil);

            $this->post('/dil', ['locale' => $dil, 'return_to' => '/'])
                ->assertSessionHas('status', $beklenen);

            $this->assertStringContainsString($ad, (string) $beklenen);
        }
    }

    public function test_onay_metni_her_acik_dilde_cevrilidir(): void
    {
        // Çevrilmemiş bir anahtar sessizce "ux.locale_switched" olarak
        // basılır; kullanıcı onay yerine anahtar görür.
        foreach ((array) config('locales.supported', []) as $dil => $ayar) {
            if (($ayar['enabled'] ?? false) !== true) {
                continue;
            }

            $metin = trans('ux.locale_switched', ['language' => 'X'], (string) $dil);

            $this->assertNotSame(
                'ux.locale_switched',
                $metin,
                $dil.' dilinde onay metni çevrilmemiş.'
            );
            $this->assertStringContainsString('X', (string) $metin, $dil.' metninde dil adı yer tutucusu kaybolmuş.');
        }
    }

    public function test_kapali_dile_gecilemez(): void
    {
        config(['locales.supported.de.enabled' => false]);

        $this->from('/')->post('/dil', ['locale' => 'de', 'return_to' => '/'])
            ->assertSessionHasErrors('locale');
    }
}
