<?php

declare(strict_types=1);

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

final class ScrollHandlersAreCheapTest extends TestCase
{
    use RefreshDatabase;

    public function test_the_reveal_safety_net_does_not_listen_to_scroll_at_all(): void
    {
        // Her kaydırma olayı yeni bir requestAnimationFrame kuyruğa alıyordu
        // ve çağrılan işlev tüm belgeyi sorgulayıp her öğe için
        // getBoundingClientRect çağırıyordu. 15.000 piksellik anasayfada
        // kaydırma ana iş parçacığını kilitliyordu.
        //
        // Ağ artık kaydırmayı hiç dinlemiyor: görevi gözlemci tetiklenmezse
        // devreye girmek, bunun için yüklemeden sonra tek atış yeterli.
        $icerik = (string) $this->get('/')->assertOk()->getContent();

        $this->assertStringNotContainsString('hepsiniAc', $icerik);
        $this->assertStringNotContainsString("addEventListener('scroll', kaydirmada", $icerik);
        $this->assertStringContainsString('emniyetAc', $icerik);
    }

    public function test_the_safety_net_runs_once_after_load(): void
    {
        // Ağ yüklemeden sonra bir kez çalışır; kalan blokları ölçmeden açar,
        // böylece kaydırma sırasında zorunlu yeniden yerleşim oluşmaz.
        $icerik = (string) $this->get('/')->assertOk()->getContent();

        $this->assertStringContainsString("window.addEventListener('load'", $icerik);
        $this->assertStringContainsString('setTimeout(emniyetAc', $icerik);

        // Ölçüm yalnız başlık yüksekliği için yapılıyor; emniyet ağının
        // kendisi hiçbir öğeyi ölçmemeli, yoksa zorunlu yeniden yerleşim olur.
        $blok = substr($icerik, (int) strpos($icerik, 'function emniyetAc'), 500);
        $this->assertStringNotContainsString('getBoundingClientRect', $blok);
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

    public function test_the_sentinel_waits_for_the_body_to_exist(): void
    {
        // Bu betik <head> içinde koşuyor; document.body henüz yok. İlk
        // yazışımda bunu atladım, prepend hata verdi ve gözcü hiç eklenmedi.
        $icerik = (string) $this->get('/')->assertOk()->getContent();

        $this->assertStringContainsString("addEventListener('DOMContentLoaded', gozcuyuKur", $icerik);
    }

    public function test_the_header_falls_back_to_the_class_before_alpine_starts(): void
    {
        // Alpine geç başlarsa başlık saydam kalmaktansa doğru görünsün.
        $this->get('/')->assertOk()->assertSee("classList.toggle('is-scrolled'", false);
    }
}
