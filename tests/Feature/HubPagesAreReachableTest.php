<?php

declare(strict_types=1);

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

final class HubPagesAreReachableTest extends TestCase
{
    use RefreshDatabase;

    public function test_every_hub_is_reachable_from_the_footer(): void
    {
        // /sure ve /zorluk yayına alındığında hiçbir yerden bağlantı
        // almıyorlardı; content:link-integrity onları öksüz sayfa olarak
        // yakaladı. Site haritasında olup hiçbir sayfadan bağlanmayan adres,
        // tarayıcı için de ziyaretçi için de yok gibidir.
        $cevap = $this->get(route('home'))->assertOk();

        foreach ([
            'cuisines.index',
            'categories.index',
            'ingredients.hub.index',
            'collections.sure.index',
            'collections.zorluk.index',
        ] as $rota) {
            $cevap->assertSee(route($rota), false);
        }
    }

    public function test_the_public_tool_page_is_offered_to_guests_too(): void
    {
        // /ne-pisirebilirim üyelik duvarının arkasından çıkarılmıştı ve site
        // haritasında indekslenebilir olarak duruyor, ama footer bağlantısı
        // hâlâ yalnız giriş yapmışlara görünüyordu.
        $this->get(route('home'))->assertOk()->assertSee(route('ingredients.explore'), false);
    }
}
