<?php

declare(strict_types=1);

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Mobil başlıkta menü düğmesi ekranın dışında kalıyordu.
 *
 * 375px genişlikte logo + dil seçici (84px) + tema düğmesi (68px) + menü
 * düğmesi (40px) ve boşluklar toplamı 404px ediyordu. Sayfa yatay
 * kaymadığı için taşma görünmüyordu; görünen tek şey menü düğmesinin
 * yokluğuydu. Telefondan Tarifler, Blog, Giriş dahil hiçbir gezinme
 * bağlantısına ulaşılamıyordu.
 *
 * Dil seçici dar ekranda çubuktan çıkıp çekmeceye giriyor. Çekmecede
 * karşılığı olmadan çıkarılamaz: mobildeki tek dil kontrolü o.
 */
final class MobileHeaderFitsTheScreenTest extends TestCase
{
    use RefreshDatabase;

    public function test_the_bar_hides_the_locale_switcher_on_narrow_screens(): void
    {
        $this->get(route('home'))
            ->assertOk()
            ->assertSee('hidden sm:block', false);
    }

    public function test_the_menu_button_is_still_in_the_bar(): void
    {
        // Taşmanın çözümü menüyü kaldırmak değildi.
        $this->get(route('home'))
            ->assertOk()
            ->assertSee('aria-controls="mobile-nav"', false);
    }

    public function test_the_drawer_carries_a_language_control_instead(): void
    {
        // Dil seçiciyi çubuktan çıkarmak, mobilde dil değiştirmeyi
        // imkânsızlaştırmamalı.
        $icerik = (string) $this->get(route('home'))->assertOk()->getContent();

        $cekmece = substr($icerik, (int) strpos($icerik, 'id="mobile-nav"'));
        $cekmece = substr($cekmece, 0, (int) strpos($cekmece, '</header>'));

        $this->assertStringContainsString('hc-locale', $cekmece, 'Çekmecede dil kontrolü bulunmalı.');
    }

    public function test_the_drawer_language_block_only_shows_where_the_bar_hides_it(): void
    {
        // İkisi aynı anda görünürse aynı kontrol iki kez sunulmuş olur.
        $this->get(route('home'))
            ->assertOk()
            ->assertSee('sm:hidden', false);
    }
}
