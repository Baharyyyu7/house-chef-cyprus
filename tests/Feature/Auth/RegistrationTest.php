<?php

namespace Tests\Feature\Auth;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\RateLimiter;
use Livewire\Volt\Volt;
use Tests\TestCase;

class RegistrationTest extends TestCase
{
    use RefreshDatabase;

    public function test_registration_screen_can_be_rendered(): void
    {
        $response = $this->get('/register');

        $response
            ->assertOk()
            ->assertSeeVolt('pages.auth.register');
    }

    public function test_new_users_can_register(): void
    {
        $component = Volt::test('pages.auth.register')
            ->set('first_name', 'Test')
            ->set('last_name', 'User')
            ->set('email', 'test@example.com')
            ->set('password', 'StrongPass123!')
            ->set('password_confirmation', 'StrongPass123!')
            ->set('terms_and_privacy_acceptance', true);

        $component->call('register');

        $component->assertRedirect(route('dashboard', absolute: false));

        $this->assertAuthenticated();
        $this->assertTrue(Hash::check('StrongPass123!', User::query()->where('email', 'test@example.com')->value('password')));
    }

    public function test_form_yalniz_ad_eposta_sifre_ve_onay_ister(): void
    {
        // Dokuz alanlık form 2026-10'da dörde indi: soyad, telefon, ülke ve
        // şehir formdan çıktı, arka uçta isteğe bağlı kaldı.
        $icerik = (string) $this->get('/register')->assertOk()->getContent();

        foreach (['id="first_name"', 'id="email"', 'id="password"', 'id="password_confirmation"', 'id="terms_and_privacy_acceptance"'] as $alan) {
            $this->assertStringContainsString($alan, $icerik);
        }
        foreach (['id="last_name"', 'id="phone"', 'id="country"', 'id="city_or_region"'] as $alan) {
            $this->assertStringNotContainsString($alan, $icerik);
        }
    }

    public function test_soyadsiz_kayit_olur(): void
    {
        Volt::test('pages.auth.register')
            ->set('first_name', 'Ela Nur')
            ->set('email', 'ela@example.com')
            ->set('password', 'StrongPass123!')
            ->set('password_confirmation', 'StrongPass123!')
            ->set('terms_and_privacy_acceptance', true)
            ->call('register')
            ->assertHasNoErrors()
            ->assertRedirect(route('dashboard', absolute: false));

        $uye = User::query()->where('email', 'ela@example.com')->firstOrFail();
        $this->assertSame('Ela Nur', $uye->name);
        $this->assertNull($uye->last_name);
        $this->assertNull($uye->phone);
    }

    public function test_registration_rejects_weak_passwords_and_duplicate_emails(): void
    {
        User::factory()->create(['email' => 'used@example.com']);

        Volt::test('pages.auth.register')
            ->set('first_name', 'Test')
            ->set('last_name', 'User')
            ->set('email', 'used@example.com')
            ->set('password', 'password')
            ->set('password_confirmation', 'password')
            ->set('terms_and_privacy_acceptance', true)
            ->call('register')
            ->assertHasErrors(['email', 'password']);

        $this->assertSame(1, User::query()->where('email', 'used@example.com')->count());
    }

    public function test_registration_submission_is_rate_limited(): void
    {
        $key = 'registration|127.0.0.1';
        RateLimiter::clear($key);
        RateLimiter::hit($key, 3600);
        RateLimiter::hit($key, 3600);
        RateLimiter::hit($key, 3600);
        RateLimiter::hit($key, 3600);
        RateLimiter::hit($key, 3600);

        Volt::test('pages.auth.register')
            ->set('first_name', 'Rate')
            ->set('last_name', 'Limited')
            ->set('email', 'limited@example.com')
            ->set('password', 'StrongPass123!')
            ->set('password_confirmation', 'StrongPass123!')
            ->set('terms_and_privacy_acceptance', true)
            ->call('register')
            ->assertHasErrors('email');

        $this->assertDatabaseMissing('users', ['email' => 'limited@example.com']);
    }
}
