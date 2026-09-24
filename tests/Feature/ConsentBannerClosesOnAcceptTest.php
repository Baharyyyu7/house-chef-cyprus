<?php

declare(strict_types=1);

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

final class ConsentBannerClosesOnAcceptTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        config()->set('integrations.google.analytics.enabled', true);
        config()->set('integrations.google.adsense.enabled', true);
        config()->set('integrations.google.adsense.certified_cmp_enabled', true);
        config()->set('integrations.google.adsense.client_id', 'ca-pub-1614195335767159');
    }

    public function test_the_accept_button_carries_both_decisions_in_one_click(): void
    {
        // Bant iki ayrı karar soruyor: ölçüm ve reklam. "Kabul et" ikisini
        // birden vermeli, yoksa sayfa yenilendiğinde eksik kalan karar için
        // bant geri gelir ve düğme çalışmamış gibi görünür.
        $icerik = (string) $this->get(route('home'))->assertOk()->getContent();

        // İki öznitelik aynı düğmede olmalı; aralarındaki boşluk Blade'in
        // koşulundan geliyor, o yüzden birebir dize yerine desen aranır.
        $this->assertMatchesRegularExpression(
            '/data-consent-choice="granted"\s+data-advertising-choice="granted"/',
            $icerik
        );
    }

    public function test_the_banner_disappears_only_when_both_cookies_are_set(): void
    {
        $this->withCookie('hc_analytics_consent', 'granted')
            ->get(route('home'))
            ->assertOk()
            // Reklam kararı hâlâ verilmedi: bant durmaya devam etmeli.
            ->assertSee('data-analytics-consent', false);

        $this->withCookies([
            'hc_analytics_consent' => 'granted',
            'hc_advertising_consent' => 'granted',
        ])
            ->get(route('home'))
            ->assertOk()
            ->assertDontSee('data-analytics-consent', false);
    }

    public function test_measurement_consent_alone_never_opens_advertising(): void
    {
        // Ölçüme izin veren biri kişiselleştirilmiş reklama izin vermiş sayılmaz.
        config()->set('integrations.google.adsense.cmp_provider', 'custom');

        $this->withCookies([
            'hc_analytics_consent' => 'granted',
            'hc_advertising_consent' => 'denied',
        ])
            ->get(route('recipes.index'))
            ->assertOk()
            ->assertDontSee('data-ad-slot', false);
    }
}
