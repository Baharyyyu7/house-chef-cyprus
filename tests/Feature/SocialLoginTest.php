<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

final class SocialLoginTest extends TestCase
{
    use RefreshDatabase;

    public function test_no_button_is_shown_while_no_provider_is_configured(): void
    {
        config()->set('social_login.providers.google.enabled', false);
        config()->set('social_login.providers.github.enabled', false);

        // Anahtarı olmayan bir düğme, tıklayan herkesi hata sayfasına götürür.
        $this->get(route('login'))->assertOk()->assertDontSee('ile devam et');
        $this->get(route('register'))->assertOk()->assertDontSee('ile devam et');
    }

    public function test_the_button_appears_once_a_provider_is_configured(): void
    {
        config()->set('social_login.providers.google.enabled', true);

        $this->get(route('login'))
            ->assertOk()
            ->assertSee('Google ile devam et')
            ->assertDontSee('GitHub ile devam et');
    }

    public function test_an_unconfigured_provider_route_is_not_found(): void
    {
        config()->set('social_login.providers.github.enabled', false);

        $this->get(route('social.redirect', 'github'))->assertNotFound();
    }

    public function test_an_unknown_provider_is_rejected_by_the_route(): void
    {
        // Rota yalnız bilinen sağlayıcıları kabul eder; keyfi bir ad
        // Socialite'a hiç ulaşmaz.
        $this->get('/giris/facebook/yonlendir')->assertNotFound();
    }

    public function test_apple_is_not_offered_because_it_is_not_free(): void
    {
        // Apple ile giriş yıllık ücretli bir geliştirici hesabı istiyor.
        $this->assertArrayNotHasKey('apple', config('social_login.providers'));
    }

    public function test_a_social_account_can_exist_without_a_password(): void
    {
        // Sosyal girişte parola yok. Rastgele bir dize koymak, kullanıcının
        // bilmediği bir parolayı varmış gibi göstermek olurdu.
        $kullanici = User::query()->create([
            'name' => 'Sosyal Üye',
            'email' => 'sosyal@example.test',
            'password' => null,
            'email_verified_at' => now(),
            'social_provider' => 'google',
            'social_id' => '1234567890',
        ]);

        $this->assertNull($kullanici->fresh()->password);
        $this->assertSame('google', $kullanici->fresh()->social_provider);
    }
}
