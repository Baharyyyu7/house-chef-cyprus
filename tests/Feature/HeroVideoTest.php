<?php

declare(strict_types=1);

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

final class HeroVideoTest extends TestCase
{
    use RefreshDatabase;

    private string $klasor;

    protected function setUp(): void
    {
        parent::setUp();

        $this->klasor = public_path('videos');
    }

    protected function tearDown(): void
    {
        foreach (['hero-loop.mp4', 'hero-loop.webm'] as $ad) {
            @unlink($this->klasor.'/'.$ad);
        }

        @rmdir($this->klasor);

        parent::tearDown();
    }

    public function test_no_video_markup_at_all_until_the_file_is_delivered(): void
    {
        // Video henüz üretilmedi. Sayfa bu süre boyunca bugünkü hâliyle
        // çalışmalı: boş bir <video> bile basılmamalı, yoksa tarayıcı
        // olmayan bir dosya için istek açar.
        $this->get(route('home'))
            ->assertOk()
            ->assertDontSee('data-hero-video', false)
            ->assertDontSee('hero-loop', false);
    }

    public function test_the_video_layer_appears_once_the_file_exists(): void
    {
        $this->videoYaz('hero-loop.mp4');

        $this->get(route('home'))
            ->assertOk()
            ->assertSee('data-hero-video', false)
            ->assertSee('hero-loop.mp4', false);
    }

    public function test_the_source_is_not_in_the_markup_so_it_cannot_delay_the_first_paint(): void
    {
        // Kaynak <source> olarak basılsaydı tarayıcı videoyu görselle
        // yarıştırırdı; kahraman görsel sayfanın LCP öğesi ve öne alınmış
        // durumda. Adres data- özniteliğinde durur, JS sayfa yüklendikten
        // sonra takar.
        $this->videoYaz('hero-loop.mp4');

        $icerik = (string) $this->get(route('home'))->assertOk()->getContent();

        $this->assertStringContainsString('data-mp4=', $icerik);
        $this->assertStringNotContainsString('<source type="video/mp4"', $icerik);
        $this->assertMatchesRegularExpression('/<video[^>]*preload="none"/', $icerik);
    }

    public function test_the_layer_is_decorative_and_stays_out_of_the_reading_order(): void
    {
        // Video içerik değil, süs. Ekran okuyucuya ve klavye sırasına
        // girmemeli; altındaki görselin alt metni zaten sahneyi anlatıyor.
        $this->videoYaz('hero-loop.mp4');

        $icerik = (string) $this->get(route('home'))->assertOk()->getContent();

        $this->assertMatchesRegularExpression('/<video[^>]*aria-hidden="true"/', $icerik);
        $this->assertMatchesRegularExpression('/<video[^>]*tabindex="-1"/', $icerik);
        $this->assertMatchesRegularExpression('/<video[^>]*muted/', $icerik);
        $this->assertMatchesRegularExpression('/<video[^>]*playsinline/', $icerik);
    }

    public function test_the_hero_image_still_carries_the_first_paint(): void
    {
        // Video eklendikten sonra da LCP öğesi görsel olmalı.
        $this->videoYaz('hero-loop.mp4');

        $this->get(route('home'))
            ->assertOk()
            ->assertSee('fetchpriority="high"', false)
            ->assertSee('house-chef-hero-v2-1717.avif', false);
    }

    private function videoYaz(string $ad): void
    {
        if (! is_dir($this->klasor)) {
            mkdir($this->klasor, 0755, true);
        }

        file_put_contents($this->klasor.'/'.$ad, 'sahte-video');
    }
}
