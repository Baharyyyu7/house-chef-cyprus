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

    public function test_the_header_only_writes_its_state_when_it_changes(): void
    {
        // Değişmese de her kaydırma olayında atama yapmak, gövde
        // kapsamındaki bütün Alpine bağlamalarını yeniden değerlendiriyordu.
        $icerik = (string) $this->get('/')->assertOk()->getContent();

        $this->assertStringContainsString('@scroll.window.passive', $icerik);
        $this->assertStringContainsString('!== scrolled', $icerik);
    }
}
