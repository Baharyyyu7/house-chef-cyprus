<?php

declare(strict_types=1);

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

/**
 * Kırık medya koruması sağlam görseli yer tutucuyla değiştirmemeli.
 *
 * Anasayfanın hero görseli üretimde gri bir kareye dönüşmüştü. Sunucudan
 * doğru `src` geliyordu, yedi görsel dosyasının hepsi 200 veriyordu ve
 * tarayıcıda tek tek yüklendiklerinde sorunsuz çözülüyorlardı; buna rağmen
 * DOM'da `data:image/svg+xml` yer tutucusu, `hcMediaFailed=1` ve
 * `hc-media-fallback` sınıfı vardı.
 *
 * Sebep: `<picture>` içindeki `<img>`, tarayıcı henüz bir kaynak seçmemişken
 * `complete = true` ve `naturalWidth = 0` bildiriyor. Koruma bunu hata
 * sayıyordu.
 */
final class BrokenMediaFallbackDoesNotEatGoodImagesTest extends TestCase
{
    use RefreshDatabase;

    public function test_the_fallback_requires_a_chosen_source_before_declaring_failure(): void
    {
        $betik = $this->betik();

        $this->assertStringContainsString(
            'img.naturalWidth === 0 && img.currentSrc',
            $betik,
            'currentSrc kontrolü olmadan <picture> içindeki sağlam görsel hatalı sayılıyor.'
        );
    }

    public function test_genuine_load_errors_are_still_caught(): void
    {
        // Daralma korumayı işlevsiz bırakmamalı: gerçek hatalar error
        // olayıyla yakalanmaya devam ediyor.
        $betik = $this->betik();

        $this->assertStringContainsString("document.addEventListener('error'", $betik);
        $this->assertStringContainsString('fail(target)', $betik);
    }

    public function test_a_failed_image_is_hidden_from_assistive_technology(): void
    {
        // Yer tutucuya düşen görsel ekran okuyucuya alt metnini okutmamalı.
        $betik = $this->betik();

        $this->assertStringContainsString("img.setAttribute('aria-hidden', 'true')", $betik);
    }

    public function test_the_home_page_serves_the_video_hero_without_a_cover_image(): void
    {
        // Kapak görseli kalktı: anasayfada v2 hero görseli basılmamalı,
        // video katmanı dosya varken gelmeli.
        Storage::fake('public');
        Storage::disk('public')->put('videos/hero-quality-20260908.mp4', 'sahte-video');

        $icerik = (string) $this->get(route('home'))->assertOk()->getContent();

        $this->assertStringNotContainsString('house-chef-hero-v2', $icerik);
        $this->assertStringContainsString('data-hero-video', $icerik);
    }

    private function betik(): string
    {
        return (string) file_get_contents(resource_path('views/layouts/site.blade.php'));
    }
}
