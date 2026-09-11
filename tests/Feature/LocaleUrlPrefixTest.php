<?php

declare(strict_types=1);

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

final class LocaleUrlPrefixTest extends TestCase
{
    use RefreshDatabase;

    public function test_a_disabled_locale_prefix_is_not_served(): void
    {
        config()->set('locales.supported.en.enabled', false);
        // Kapalı dile sayfa açmak, Türkçe içeriği yabancı dil sayfası gibi
        // sunmak olurdu; zayıf dil sayfaları site geneli kalite sinyalini
        // aşağı çeker.
        foreach (['/en', '/pl', '/fa', '/de'] as $adres) {
            $this->get($adres)->assertNotFound();
        }
    }

    public function test_an_enabled_locale_prefix_serves_the_same_page(): void
    {
        config()->set('locales.supported.en.enabled', true);

        $this->get('/en')->assertOk();
        $this->get('/en/tarifler')->assertOk();
    }

    public function test_the_url_prefix_wins_over_the_cookie(): void
    {
        // Çerez içeriği belirleseydi aynı adres farklı ziyaretçilere farklı
        // dil gösterirdi ve arama motoru hangisini indeksleyeceğini bilemezdi.
        config()->set('locales.supported.en.enabled', true);

        $this->withCookie('hc_locale', 'tr')
            ->get('/en/tarifler')
            ->assertOk()
            ->assertSee('lang="en"', false);
    }

    public function test_the_default_locale_prefix_redirects_to_the_root(): void
    {
        // /tr/tarifler ayrı bir adres olarak kalsaydı aynı içerik iki adreste
        // görünürdü.
        $this->get('/tr/tarifler')->assertRedirect('/tarifler');
        $this->get('/tr')->assertRedirect('/');
    }

    public function test_a_normal_path_is_untouched(): void
    {
        $this->get('/tarifler')->assertOk();
        $this->get('/blog')->assertOk();
    }

    public function test_hreflang_targets_actually_resolve(): void
    {
        // Hedefi 404 olan hreflang kümesini Google tamamen atar. Etiketi
        // basmak, işaret ettiği adresin gerçekten açılmasıyla birlikte
        // anlamlı; ikisi ayrı ayrı doğru olması yetmiyor.
        config()->set('locales.supported.en.enabled', true);

        $icerik = (string) $this->get('/tarifler')->assertOk()->getContent();

        preg_match_all('/hreflang="[^"]+" href="([^"]+)"/', $icerik, $eslesme);

        $this->assertNotEmpty($eslesme[1], 'hreflang kümesi basılmalı.');

        foreach (array_unique($eslesme[1]) as $adres) {
            $yol = parse_url($adres, PHP_URL_PATH) ?: '/';
            $this->get($yol)->assertOk();
        }
    }
}
