<?php

declare(strict_types=1);

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

final class ScrollHandlersAreCheapTest extends TestCase
{
    use RefreshDatabase;

    public function test_reveals_are_driven_by_the_observer_not_by_scroll(): void
    {
        // Her kaydırma olayı yeni bir requestAnimationFrame kuyruğa alıyordu
        // ve çağrılan işlev tüm belgeyi sorgulayıp her öğe için
        // getBoundingClientRect çağırıyordu. 15.000 piksellik anasayfada
        // kaydırma ana iş parçacığını kilitliyordu.
        //
        // 27 Eylül 2026: açılma resources/js/motion.js'e taşındı. Tek kaydırma
        // dinleyicisi okuma çizgisi; pasif ve kare başına bir kez.
        $kaynak = (string) file_get_contents(resource_path('js/motion.js'));

        $this->assertStringContainsString('new IntersectionObserver', $kaynak);
        $this->assertSame(1, substr_count($kaynak, "addEventListener('scroll'"));
        $this->assertStringContainsString("addEventListener('scroll', () => {\n        if (!bekliyor)", $kaynak);
        $this->assertStringContainsString('{ passive: true }', $kaynak);
    }

    public function test_nothing_on_screen_is_hidden_and_there_is_no_timed_mass_reveal(): void
    {
        // Eski emniyet ağı ekranın altındakileri yüklemeden 1,5 sn sonra
        // topluca açıyordu: animasyon kimse bakmıyorken oynuyordu. Ağa gerek
        // de yok: açılışta ekranda olan öğe hiç gizlenmiyor, gizlenen ekran
        // dışındaki öğe ekrana girdiğinde gözlemci onu açıyor.
        $icerik = (string) $this->get('/')->assertOk()->getContent();
        $kaynak = (string) file_get_contents(resource_path('js/motion.js'));

        $this->assertStringNotContainsString('emniyetAc', $icerik);
        $this->assertStringNotContainsString('hc-reveal-on', $icerik);
        $this->assertMatchesRegularExpression('/if \(olcum\[i\]\) \{\s+bekleyen\.delete\(el\);\s+return;/', $kaynak);
    }

    public function test_the_header_sentinel_does_not_depend_on_motion_preference(): void
    {
        // Gözcü, hareket kısıtlıyken en başta dönen açılma betiğinin içindeydi;
        // o kullanıcılarda hiç kurulmuyor, başlık kaydırınca da saydam
        // kalıyordu.
        $icerik = (string) $this->get('/')->assertOk()->getContent();
        $bas = (int) strrpos(substr($icerik, 0, (int) strpos($icerik, 'function gozcuyuKur')), '<script>');
        $betik = substr($icerik, $bas, (int) strpos($icerik, '</script>', $bas) - $bas);

        $this->assertStringContainsString('gozcuyuKur', $betik);
        $this->assertStringNotContainsString('prefers-reduced-motion', $betik);
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
