<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Models\Recipe;
use App\Services\Seo\LocaleUrlService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

final class HreflangOnlyForEnabledLocalesTest extends TestCase
{
    use RefreshDatabase;

    public function test_no_hreflang_while_only_one_locale_is_enabled(): void
    {
        foreach (array_keys(config('locales.supported', [])) as $loc) {
            if ($loc !== 'tr') {
                config()->set("locales.supported.{$loc}.enabled", false);
            }
        }
        // Şu an yalnız Türkçe etkin. Tek dil varken hreflang'in bağlayacağı
        // alternatif yok; boş yere etiket basmak hata raporu üretir.
        $this->get(route('home'))
            ->assertOk()
            ->assertDontSee('rel="alternate" hreflang', false);
    }

    public function test_hreflang_appears_once_a_second_locale_is_enabled(): void
    {
        config()->set('locales.supported.en.enabled', true);

        $icerik = (string) $this->get(route('home'))->assertOk()->getContent();

        $this->assertStringContainsString('hreflang="tr"', $icerik);
        $this->assertStringContainsString('hreflang="en"', $icerik);
        $this->assertStringContainsString('hreflang="x-default"', $icerik);
    }

    public function test_the_set_includes_the_page_itself(): void
    {
        // Kendine gönderme yapmayan hreflang kümesini Google tamamen yok
        // sayar; en sık yapılan hata bu.
        config()->set('locales.supported.en.enabled', true);

        $icerik = (string) $this->get(route('home'))->assertOk()->getContent();

        $this->assertStringContainsString(
            '<link rel="alternate" hreflang="tr" href="'.rtrim(url('/'), '/').'"',
            $icerik
        );
    }

    public function test_a_disabled_locale_never_gets_a_url_or_a_tag(): void
    {
        // Kapalı dile URL açmak, Türkçe içeriği yabancı dil sayfası gibi
        // sunmak olurdu. Zayıf dil sayfaları site geneli kalite sinyalini
        // aşağı çeker.
        config()->set('locales.supported.en.enabled', true);
        config()->set('locales.supported.pl.enabled', false);

        $icerik = (string) $this->get(route('home'))->assertOk()->getContent();

        foreach (['pl'] as $kapali) {
            $this->assertStringNotContainsString('hreflang="'.$kapali.'"', $icerik);
        }
    }

    public function test_the_default_locale_lives_at_the_root_and_others_are_prefixed(): void
    {
        $servis = app(LocaleUrlService::class);

        $this->assertSame(rtrim(url('/tarifler'), '/'), $servis->urlFor('tr', '/tarifler'));
        $this->assertSame(rtrim(url('/en/tarifler'), '/'), $servis->urlFor('en', '/tarifler'));
    }

    public function test_a_path_that_already_carries_a_prefix_is_not_doubled(): void
    {
        // /en/en/tarifler üretmek hreflang kümesini bozar ve 404 hedefi
        // gösterir; hedefi 404 olan küme tamamen atılır.
        $servis = app(LocaleUrlService::class);

        $this->assertSame(rtrim(url('/pl/tarifler'), '/'), $servis->urlFor('pl', '/en/tarifler'));
        $this->assertSame(rtrim(url('/tarifler'), '/'), $servis->urlFor('tr', '/en/tarifler'));
    }

    public function test_the_sitemap_stays_plain_while_only_one_locale_is_enabled(): void
    {
        foreach (array_keys(config('locales.supported', [])) as $loc) {
            if ($loc !== 'tr') {
                config()->set("locales.supported.{$loc}.enabled", false);
            }
        }
        $icerik = (string) $this->get(route('sitemap.pages'))->assertOk()->getContent();

        $this->assertStringNotContainsString('xhtml:link', $icerik);
        $this->assertStringNotContainsString('xmlns:xhtml', $icerik);
    }

    public function test_the_sitemap_only_advertises_locales_where_the_record_exists(): void
    {
        // Sitemap var olmayan bir adresi ilan ederse hedef 404 verir ve
        // Google o hreflang çiftini atar. Kayıt başına hesaplanmak zorunda.
        config()->set('locales.supported.en.enabled', true);

        $cevirisiz = Recipe::query()->create([
            'title' => 'Çevirisiz Tarif', 'slug' => 'cevirisiz-'.uniqid(),
            'excerpt' => 'Özet.', 'description' => 'Açıklama.',
            'status' => 'published', 'verification_status' => 'verified', 'published_at' => now(),
        ]);
        $this->makeRecipePubliclyEligible($cevirisiz);

        $icerik = (string) $this->get(route('sitemap.recipes'))->assertOk()->getContent();

        $this->assertStringContainsString($cevirisiz->slug, $icerik, 'Türkçe sürüm sitemap\'te olmalı.');
        $this->assertStringNotContainsString('hreflang="en"', $icerik, 'Çevirisi olmayan kayıt için en ilan edilmemeli.');
    }

    public function test_the_sitemap_advertises_a_locale_once_the_record_is_translated(): void
    {
        config()->set('locales.supported.en.enabled', true);

        $tarif = Recipe::query()->create([
            'title' => 'Çevrili Tarif', 'slug' => 'cevrili-'.uniqid(),
            'excerpt' => 'Özet.', 'description' => 'Açıklama.',
            'status' => 'published', 'verification_status' => 'verified', 'published_at' => now(),
        ]);
        $this->makeRecipePubliclyEligible($tarif);

        foreach (['title' => 'Translated', 'excerpt' => 'Summary.', 'description' => 'Description.'] as $alan => $deger) {
            $tarif->translations()->create(['locale' => 'en', 'field' => $alan, 'value' => $deger]);
        }

        $icerik = (string) $this->get(route('sitemap.recipes'))->assertOk()->getContent();

        $this->assertStringContainsString('xmlns:xhtml="http://www.w3.org/1999/xhtml"', $icerik);
        $this->assertStringContainsString('hreflang="en"', $icerik);
        $this->assertStringContainsString('hreflang="x-default"', $icerik);
    }

    public function test_polish_is_available_as_a_supported_locale(): void
    {
        $this->assertArrayHasKey('pl', (array) config('locales.supported'));
        $this->assertTrue(config('locales.supported.pl.enabled'), 'İçerik hazır olduğunda dil açılmış olmalı.');
    }
}
