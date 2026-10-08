<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Laravel\Socialite\Facades\Socialite;
use Laravel\Socialite\Two\User as SosyalKullanici;
use Mockery;
use Tests\TestCase;

/**
 * Askı yalnız parola formunda kontrol ediliyordu; hatırla çerezi, askıdan
 * önce açılmış oturum ve sosyal giriş onu atlatıyordu. Bu testler gerçek
 * oturum ve gerçek çerezle gelir: actingAs guard'ı doğrudan doldurur ve
 * kapının okunduğu yolu atlar.
 */
final class SuspendedAccountCannotStayInTest extends TestCase
{
    use RefreshDatabase;

    public function test_an_open_session_is_closed_once_the_account_is_suspended(): void
    {
        $uye = User::factory()->create(['account_status' => 'suspended']);

        $this->withSession([Auth::guard('web')->getName() => $uye->id])
            ->get(route('dashboard'))
            ->assertRedirect(route('login'))
            ->assertSessionHasErrors('email');

        $this->assertGuest('web');
    }

    public function test_an_active_session_still_works(): void
    {
        $uye = User::factory()->create(['account_status' => 'active']);

        $this->withSession([Auth::guard('web')->getName() => $uye->id])
            ->get(route('dashboard'))
            ->assertOk();

        $this->assertAuthenticatedAs($uye, 'web');
    }

    public function test_a_remember_cookie_does_not_bring_a_suspended_account_back(): void
    {
        $uye = User::factory()->create(['account_status' => 'suspended', 'remember_token' => 'eski-jeton']);

        $this->withCookie($this->hatirlaCerezAdi(), $uye->id.'|eski-jeton|'.$uye->password)
            ->get(route('dashboard'))
            ->assertRedirect(route('login'));

        $this->assertGuest('web');
    }

    public function test_a_valid_remember_cookie_signs_an_active_member_in(): void
    {
        // Kontrol testi: aşağıdaki "çerez ölür" testleri ancak çerez biçimi
        // doğruysa bir şey kanıtlar.
        $uye = User::factory()->create(['remember_token' => 'gecerli-jeton']);

        $this->withCookie($this->hatirlaCerezAdi(), $uye->id.'|gecerli-jeton|'.$uye->password)
            ->get(route('dashboard'))
            ->assertOk();

        $this->assertAuthenticatedAs($uye, 'web');
    }

    public function test_suspending_rotates_the_remember_token_so_old_cookies_die(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $uye = User::factory()->create(['remember_token' => 'eski-jeton']);
        DB::table('sessions')->insert([
            'id' => 'acik-oturum', 'user_id' => $uye->id, 'ip_address' => null,
            'user_agent' => null, 'payload' => '', 'last_activity' => time(),
        ]);

        $this->actingAs($admin)
            ->post(route('admin.users.suspend', ['user' => $uye->public_id]))
            ->assertRedirect();

        $this->assertNotSame('eski-jeton', $uye->fresh()->remember_token);
        $this->assertDatabaseMissing('sessions', ['user_id' => $uye->id]);
    }

    public function test_revoking_sessions_also_kills_remember_cookies(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $uye = User::factory()->create(['remember_token' => 'eski-jeton']);

        $this->actingAs($admin)
            ->post(route('admin.users.revoke-sessions', ['user' => $uye->public_id]))
            ->assertRedirect();

        Auth::guard('web')->logout();
        $this->flushSession();

        // Askıya alınmamış hesap: kapıyı askı değil, döndürülmüş jeton kapatır.
        $this->withCookie($this->hatirlaCerezAdi(), $uye->id.'|eski-jeton|'.$uye->password)
            ->get(route('dashboard'))
            ->assertRedirect(route('login'));
    }

    public function test_a_suspended_account_cannot_sign_in_with_a_social_provider(): void
    {
        $uye = User::factory()->create([
            'account_status' => 'suspended',
            'email' => 'askida@example.test',
            'social_provider' => 'google',
            'social_id' => 'g-123',
        ]);

        $this->sosyalHesap('g-123', 'askida@example.test');

        $this->get(route('social.callback', 'google'))
            ->assertRedirect(route('login'))
            ->assertSessionHasErrors('email');

        $this->assertGuest('web');
        $this->assertSame('suspended', $uye->fresh()->account_status);
    }

    public function test_a_suspended_account_is_not_linked_by_email_either(): void
    {
        $uye = User::factory()->create(['account_status' => 'suspended', 'email' => 'askida@example.test']);

        $this->sosyalHesap('g-yeni', 'askida@example.test');

        $this->get(route('social.callback', 'google'))->assertRedirect(route('login'));

        $this->assertGuest('web');
        $this->assertNull($uye->fresh()->social_id);
    }

    private function hatirlaCerezAdi(): string
    {
        /** @var \Illuminate\Auth\SessionGuard $guard */
        $guard = Auth::guard('web');

        return $guard->getRecallerName();
    }

    private function sosyalHesap(string $kimlik, string $eposta): void
    {
        config()->set('social_login.providers.google.enabled', true);

        $hesap = (new SosyalKullanici)->map(['id' => $kimlik, 'email' => $eposta, 'name' => 'Sosyal Üye']);
        $surucu = Mockery::mock();
        $surucu->shouldReceive('user')->andReturn($hesap);
        Socialite::shouldReceive('driver')->with('google')->andReturn($surucu);
    }
}
