<?php

declare(strict_types=1);

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

final class HomeClosingDoorsTest extends TestCase
{
    use RefreshDatabase;

    public function test_the_homepage_links_to_every_hub_page(): void
    {
        // Dört hub sayfası da yayındaydı ve 200 dönüyordu, ama anasayfanın
        // içeriği hiçbirine bağlanmıyordu: üçü yalnız footer'da duruyordu,
        // /ne-pisirebilirim hiçbir yerde yoktu.
        $cevap = $this->get(route('home'))->assertOk();

        foreach (['ingredients.hub.index', 'cuisines.index', 'categories.index', 'ingredients.explore'] as $rota) {
            $cevap->assertSee(route($rota), false);
        }
    }

    public function test_every_hub_page_the_closing_offers_actually_opens(): void
    {
        // Kapanış kırık bir kapı sunmamalı.
        foreach (['ingredients.hub.index', 'cuisines.index', 'categories.index', 'ingredients.explore'] as $rota) {
            $this->get(route($rota))->assertOk();
        }
    }

    public function test_the_closing_no_longer_repeats_the_hero_offer(): void
    {
        // Dokuz bölüm kaydırıp tıklamamış birine sayfanın başındaki çağrıyı
        // tekrar sunmak en zayıf teklifti.
        $this->get(route('home'))->assertOk()->assertDontSee('Tarifleri keşfet');
    }
}
