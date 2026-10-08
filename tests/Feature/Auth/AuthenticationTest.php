<?php

namespace Tests\Feature\Auth;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Volt\Volt;
use Tests\TestCase;

class AuthenticationTest extends TestCase
{
    use RefreshDatabase;

    public function test_login_screen_can_be_rendered(): void
    {
        $response = $this->get('/login');

        $response
            ->assertOk()
            ->assertSeeVolt('pages.auth.login');
    }

    public function test_users_can_authenticate_using_the_login_screen(): void
    {
        $user = User::factory()->create();

        $component = Volt::test('pages.auth.login')
            ->set('form.email', $user->email)
            ->set('form.password', 'password');

        $component->call('login');

        $component
            ->assertHasNoErrors()
            ->assertRedirect(route('dashboard', absolute: false));

        $this->assertAuthenticated();
    }

    public function test_remember_me_creates_a_persistent_login_token(): void
    {
        $user = User::factory()->create(['remember_token' => null]);

        Volt::test('pages.auth.login')
            ->set('form.email', $user->email)
            ->set('form.password', 'password')
            ->set('form.remember', true)
            ->call('login')
            ->assertHasNoErrors();

        $this->assertAuthenticatedAs($user);
        $this->assertNotNull($user->refresh()->remember_token);
    }

    public function test_guests_are_redirected_from_member_pages_and_authenticated_users_are_redirected_from_guest_pages(): void
    {
        $this->get('/profile')->assertRedirect('/login');
        $this->get('/favoriler')->assertRedirect('/login');

        $user = User::factory()->create();
        $this->actingAs($user)->get('/login')->assertRedirect('/dashboard');
        $this->actingAs($user)->get('/register')->assertRedirect('/dashboard');
    }

    public function test_users_can_not_authenticate_with_invalid_password(): void
    {
        $user = User::factory()->create();

        $component = Volt::test('pages.auth.login')
            ->set('form.email', $user->email)
            ->set('form.password', 'wrong-password');

        $component->call('login');

        $component
            ->assertHasErrors()
            ->assertNoRedirect();

        $this->assertGuest();
    }

    public function test_navigation_menu_can_be_rendered(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user);

        $response = $this->get('/dashboard');

        // Üye sayfaları ayrı bir düzen ve ayrı bir menü kullanıyordu; artık
        // sitenin kendi başlık çubuğunu kullanıyorlar. Test menünün varlığını
        // ve çıkış yolunun durduğunu doğruluyor, hangi bileşenden geldiğini
        // değil.
        $response
            ->assertOk()
            ->assertSee('hc-public-header', false)
            ->assertSee('hc-user-trigger', false)
            ->assertSee(route('logout'), false)
            ->assertSee('<title>Hesabım · House Chef Cyprus</title>', false)
            ->assertDontSee('<title>Laravel</title>', false);
    }

    public function test_users_can_logout(): void
    {
        $user = User::factory()->create();

        // Çıkış eskiden yalnız kullanılmayan layouts/app'in Volt menüsünden
        // sınanıyordu; o menü silindi. Sitedeki tek çıkış yolu bu rota.
        $this->actingAs($user)
            ->post(route('logout'))
            ->assertRedirect(route('home'));

        $this->assertGuest();
    }
}
