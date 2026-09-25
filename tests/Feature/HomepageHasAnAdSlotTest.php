<?php

declare(strict_types=1);

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

final class HomepageHasAnAdSlotTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Anasayfada hiç reklam yuvası yoktu. Tarif detayı ve blog sayfaları
     * birim basıyordu ama en çok görüntülenen sayfa hiçbir gelir üretmiyordu.
     * AD_SLOT_DISPLAY yapılandırmada tanımlıydı ve hiçbir sayfada
     * kullanılmıyordu.
     */
    public function test_the_homepage_renders_an_ad_unit_when_advertising_is_on(): void
    {
        config()->set('integrations.google.adsense.enabled', true);
        config()->set('integrations.google.adsense.client_id', 'ca-pub-1614195335767159');
        config()->set('integrations.google.adsense.certified_cmp_enabled', true);
        config()->set('integrations.google.adsense.cmp_provider', 'google');

        $icerik = (string) $this->get(route('home'))->assertOk()->getContent();

        $this->assertStringContainsString('class="adsbygoogle"', $icerik);
        $this->assertStringContainsString(config('monetization.slots.display.id'), $icerik);
    }

    public function test_the_homepage_prints_nothing_when_advertising_is_off(): void
    {
        // Kapalıyken istek atmak, incelemedeki bir hesabın reddedilme
        // yollarından biri.
        config()->set('integrations.google.adsense.enabled', false);

        $this->get(route('home'))->assertOk()->assertDontSee('class="adsbygoogle"', false);
    }

    public function test_the_first_screen_stays_free_of_ads(): void
    {
        // Reklam hero ve aramadan sonra gelir; ilk ekranda reklam yok.
        config()->set('integrations.google.adsense.enabled', true);
        config()->set('integrations.google.adsense.client_id', 'ca-pub-1614195335767159');
        config()->set('integrations.google.adsense.certified_cmp_enabled', true);
        config()->set('integrations.google.adsense.cmp_provider', 'google');

        $icerik = (string) $this->get(route('home'))->assertOk()->getContent();

        $reklam = mb_strpos($icerik, 'class="adsbygoogle"');
        $hero = mb_strpos($icerik, 'class="hc-hero ');

        $this->assertNotFalse($reklam);
        $this->assertNotFalse($hero);
        $this->assertGreaterThan($hero, $reklam, 'Reklam hero bölümünden önce basılıyor.');
    }
}
