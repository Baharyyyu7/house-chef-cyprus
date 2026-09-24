<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

final class LivewireAssetsAreNotShippedToEveryoneTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Betiğin adı ortama göre değişiyor: üretimde livewire.min.js, hata
     * ayıklama açıkken livewire.js. Ortak parça aranıyor.
     */
    private const BETIK = 'livewire/livewire';

    public function test_public_pages_without_a_livewire_component_do_not_load_livewire(): void
    {
        // @livewireScripts koşulsuzdu: her genel sayfa 161 KB Livewire
        // betiğini indirip çalıştırıyordu, oysa anasayfada, tarif detayında
        // ve blogda tek bir wire: niteliği bile yok.
        foreach (['/', '/tarifler', '/blog', '/hakkimizda'] as $yol) {
            $icerik = (string) $this->get($yol)->assertOk()->getContent();

            $this->assertStringNotContainsString(self::BETIK, $icerik, $yol.' hâlâ Livewire yüklüyor.');
            // "wire:" tek basina yetmiyor: sayfalarda livewire:navigated
            // dinleyicileri var. Bunlar bilesen degil, Livewire ile gezinme
            // olursa yeniden koşsun diye konmus zararsiz dinleyiciler.
            // Gercek bilesen isareti wire:id ve wire:snapshot.
            $this->assertStringNotContainsString('wire:id', $icerik, $yol.' beklenmedik bir Livewire bileşeni barındırıyor.');
            $this->assertStringNotContainsString('wire:snapshot', $icerik, $yol.' beklenmedik bir Livewire bileşeni barındırıyor.');
        }
    }

    public function test_the_profile_page_still_gets_livewire(): void
    {
        // Bu layout'u kullanan tek Livewire sayfası; betiği kaybederse
        // formları sessizce çalışmaz olur.
        $icerik = (string) $this->actingAs(User::factory()->create())
            ->get(route('profile'))
            ->assertOk()
            ->getContent();

        $this->assertStringContainsString(self::BETIK, $icerik);
    }

    public function test_the_decision_does_not_leak_from_one_page_to_the_next(): void
    {
        // İlk çözüm @stack/@push'tu ve tam test paketinde sızdı: profil
        // sayfasının yığına ittiği içerik aynı süreçte sonraki isteklerde de
        // basılıyordu. Üretimde LiteSpeed süreci yeniden kullandığı için bu
        // yalnız bir test tuhaflığı değildi.
        $this->actingAs(User::factory()->create())->get(route('profile'))->assertOk();

        $this->assertStringNotContainsString(
            self::BETIK,
            (string) $this->get('/')->assertOk()->getContent(),
            'Livewire kararı bir önceki istekten sızdı.'
        );
    }

    public function test_the_login_page_still_gets_livewire(): void
    {
        // Volt sayfaları guest layout'unu kullanıyor; oradaki çağrıya
        // dokunulmadı ama kırılmadığı doğrulanmalı.
        $this->get('/login')->assertOk()->assertSee(self::BETIK, false);
    }
}
