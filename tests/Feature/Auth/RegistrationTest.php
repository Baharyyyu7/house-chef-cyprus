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
