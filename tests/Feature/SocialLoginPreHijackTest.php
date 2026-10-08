<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Laravel\Socialite\Facades\Socialite;
use Laravel\Socialite\Two\User as SosyalKullanici;
use Mockery;
use Tests\TestCase;

/**
 * Pre-hijack: saldırgan kurbanın adresiyle kayıt olur, e-postayı hiç
 * doğrulamaz ve parolasını bekletir. Kurban Google ile geldiğinde hesap
 * ona bağlanır; saldırganın parolası, hatırla jetonu ve oturumu ölmelidir.
 */
final class SocialLoginPreHijackTest extends TestCase
{
    use RefreshDatabase;

    public function test_linking_an_unverified_account_kills_the_squatters_credentials(): void
    {
        $hesap = User::factory()->unverified()->create([
            'email' => 'kurban@example.test',
            'password' => 'saldirgan-parolasi',
            'remember_token' => 'saldirgan-jetonu',
        ]);
        DB::table('sessions')->insert([
            'id' => 'saldirgan-oturumu', 'user_id' => $hesap->id, 'ip_address' => null,
            'user_agent' => null, 'payload' => '', 'last_activity' => time(),
        ]);

        $this->sosyalHesap('g-kurban', 'kurban@example.test');

        $this->get(route('social.callback', 'google'))->assertRedirect(route('dashboard'));

        $taze = $hesap->fresh();
        $this->assertSame('g-kurban', $taze->social_id);
        $this->assertNotNull($taze->email_verified_at);
        $this->assertFalse(Hash::check('saldirgan-parolasi', (string) $taze->password));
        $this->assertNotSame('saldirgan-jetonu', $taze->remember_token);
        $this->assertDatabaseMissing('sessions', ['id' => 'saldirgan-oturumu']);
        $this->assertAuthenticatedAs($taze, 'web');
    }

    public function test_linking_a_verified_account_keeps_its_password(): void
    {
        $hesap = User::factory()->create([
            'email' => 'sahip@example.test',
            'password' => 'kendi-parolasi',
            'remember_token' => 'kendi-jetonu',
        ]);

        $this->sosyalHesap('g-sahip', 'sahip@example.test');

        $this->get(route('social.callback', 'google'))->assertRedirect(route('dashboard'));

        $taze = $hesap->fresh();
        $this->assertSame('g-sahip', $taze->social_id);
        $this->assertTrue(Hash::check('kendi-parolasi', (string) $taze->password));
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
