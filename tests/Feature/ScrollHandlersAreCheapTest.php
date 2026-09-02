<?php

declare(strict_types=1);

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

final class ScrollHandlersAreCheapTest extends TestCase
{
    use RefreshDatabase;

    public function test_the_reveal_safety_net_does_not_queue_a_frame_per_scroll_event(): void
    {
        // Her kaydırma olayı yeni bir requestAnimationFrame kuyruğa alıyordu
        // ve çağrılan işlev tüm belgeyi sorgulayıp her öğe için
        // getBoundingClientRect çağırıyordu. 15.000 piksellik anasayfada
        // kaydırma ana iş parçacığını kilitliyordu.
        $icerik = (string) $this->get('/')->assertOk()->getContent();

        $this->assertStringNotContainsString(
            "window.addEventListener('scroll', function () { window.requestAnimationFrame(hepsiniAc); }",
            $icerik
        );
        $this->assertStringContainsString('kareBekliyor', $icerik);
    }

    public function test_the_safety_net_removes_itself_once_everything_is_revealed(): void
    {
        // Ağın görevi gözlemci tetiklenmezse devreye girmek; iş bitince
        // sayfanın ömrü boyunca kaydırmayı dinlemesi için sebep yok.
        $this->get('/')->assertOk()->assertSee("removeEventListener('scroll', kaydirmada)", false);
    }

    public function test_the_header_state_does_not_depend_on_a_scroll_listener(): void
    {
        // Başlık durumu her kaydırma olayında bir Alpine ifadesi
        // çalıştırıyordu ve kaydırmadan sonra pencereye hiç olay ulaşmadığı
        // durumda başlık hero üstü saydam stilinde kalıyordu: açık zeminin
        // üstünde beyaz yazı. Gözcü öğesi bu sınıfı tamamen kaldırıyor.
        $icerik = (string) $this->get('/')->assertOk()->getContent();

        $this->assertStringNotContainsString('@scroll.window', $icerik);
        $this->assertStringContainsString('IntersectionObserver', $icerik);
        $this->assertStringContainsString('basligiGuncelle', $icerik);
    }

    public function test_the_header_falls_back_to_the_class_before_alpine_starts(): void
    {
        // Alpine geç başlarsa başlık saydam kalmaktansa doğru görünsün.
        $this->get('/')->assertOk()->assertSee("classList.toggle('is-scrolled'", false);
    }
}
