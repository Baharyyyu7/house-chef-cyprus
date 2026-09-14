<?php

declare(strict_types=1);

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\RateLimiter;
use Livewire\Volt\Volt;
use Tests\TestCase;

/**
 * Kayıt sayfasını görüntülemek sınırlanmaz; kayıt denemesi sınırlanır.
 *
 * Rotada throttle:registration vardı ve saatte 5 isteğe izin veriyordu. Form
 * Livewire ile gönderildiği için bu sınır hiçbir kayıt denemesine
 * dokunmuyordu; yalnız sayfa görüntülemelerini sayıyordu. Sayfayı altıncı kez
 * açan ziyaretçi 429 alıyordu. 14 Eylül 2026'da art arda yapılan yayınların
 * duman testi de /register'ı açtığı için iki yayın geri alındı.
 *
 * Asıl koruma register() içinde duruyor ve kalmalı.
 */
final class RegisterPageViewsAreNotRateLimitedTest extends TestCase
{
    use RefreshDatabase;

    public function test_kayit_sayfasi_art_arda_acilabilir(): void
    {
        foreach (range(1, 12) as $sira) {
            $this->get('/register')->assertOk();
        }
    }

    public function test_altinci_kayit_denemesi_hala_engellenir(): void
    {
        RateLimiter::clear('registration|127.0.0.1');

        foreach (range(1, 5) as $sira) {
            RateLimiter::hit('registration|127.0.0.1', 3600);
        }

        Volt::test('pages.auth.register')
            ->set('first_name', 'Ela')
            ->set('last_name', 'Kaya')
            ->set('email', 'sinir@example.com')
            ->set('password', 'StrongPass123!')
            ->set('password_confirmation', 'StrongPass123!')
            ->set('terms_and_privacy_acceptance', true)
            ->call('register')
            ->assertHasErrors(['email']);

        $this->assertDatabaseMissing('users', ['email' => 'sinir@example.com']);
    }
}
