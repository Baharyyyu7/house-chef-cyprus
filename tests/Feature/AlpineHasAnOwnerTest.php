<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

final class AlpineHasAnOwnerTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Alpine bu uygulamada iki yerden gelebiliyor: Livewire onu kendisi
     * başlatıyor, Livewire'sız sayfalarda ise app.js başlatıyor.
     *
     * Livewire'ı genel sayfalardan kaldırdığımda Alpine de onunla gitti ve
     * hiçbir test bunu yakalamadı: yapışkan başlık is-scrolled alamadı,
     * açılır menüler öldü, x-cloak taşıyan öğeler kalıcı olarak gizli kaldı.
     * Bu test o boşluğu kapatıyor - Alpine kullanan her sayfanın bir sahibi
     * olmalı.
     */
    public function test_every_page_that_uses_alpine_declares_who_starts_it(): void
    {
        $yollar = ['/', '/tarifler', '/blog', '/hakkimizda', '/mutfaklar'];

        foreach ($yollar as $yol) {
            $icerik = (string) $this->get($yol)->assertOk()->getContent();

            if (! str_contains($icerik, 'x-data')) {
                continue;
            }

            $livewireVar = str_contains($icerik, 'livewire/livewire');
            $appSahibi = str_contains($icerik, 'data-alpine-owner="app"');

            $this->assertTrue(
                $livewireVar || $appSahibi,
                $yol.' Alpine kullanıyor ama onu kimin başlatacağı belli değil.'
            );
        }
    }

    public function test_a_livewire_page_does_not_start_a_second_alpine(): void
    {
        // İki kopya Livewire'ın navigate sözleşmesini kırıyor.
        $icerik = (string) $this->actingAs(User::factory()->create())
            ->get(route('profile'))
            ->assertOk()
            ->getContent();

        $this->assertStringContainsString('data-alpine-owner="livewire"', $icerik);
        $this->assertStringNotContainsString('data-alpine-owner="app"', $icerik);
    }

    public function test_the_bundle_actually_contains_alpine(): void
    {
        // Sahiplik doğru olsa da paket Alpine taşımıyorsa sayfa yine ölü.
        $manifest = json_decode((string) file_get_contents(public_path('build/manifest.json')), true);
        $giris = $manifest['resources/js/app.js']['file'] ?? null;

        $this->assertNotNull($giris, 'app.js manifestte yok.');
        $this->assertStringContainsString(
            'Alpine',
            (string) file_get_contents(public_path('build/'.$giris))
        );
    }
}
