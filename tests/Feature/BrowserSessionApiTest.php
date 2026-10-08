<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Models\Recipe;
use App\Models\User;
use Illuminate\Foundation\Http\Middleware\PreventRequestForgery;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Str;
use Tests\TestCase;

/**
 * Kişiselleştirme API'si ve WebMCP save_recipe tarayıcıdaki oturumla
 * çağrılır. api grubunda oturum yoktu: 'auth' oturum çerezini okuyamıyor,
 * giriş yapmış üye bile hep 401 alıyordu. actingAs guard'ı doğrudan
 * doldurduğu için bu kusuru göremiyordu; burada gerçek bir oturum satırı ve
 * gerçek oturum çerezi kullanılır (getJson çerezi yalnız withCredentials ile
 * gönderir).
 */
final class BrowserSessionApiTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        // Dizi sürücüsü veriyi süreç belleğinde tutar ve çerezsiz de okur;
        // gerçek davranış için oturum veritabanından, çerezdeki kimlikle gelir.
        config()->set('session.driver', 'database');
        $this->app['session']->forgetDrivers();
        $this->app->forgetInstance('session.store');
        // Guard oturum deposunu ilk çözümlendiğinde tutar; eskisini bırakmalı.
        $this->app['auth']->forgetGuards();
    }

    public function test_a_signed_in_member_reaches_personalization_with_the_browser_session(): void
    {
        $uye = User::factory()->create();

        $this->withCredentials()->withCookie(config('session.cookie'), $this->oturumAc($uye))
            ->getJson('/api/v1/personalization/profile')
            ->assertOk()
            ->assertJsonPath('data.enabled', true);
    }

    public function test_without_a_session_personalization_stays_closed(): void
    {
        $this->getJson('/api/v1/personalization/profile')->assertUnauthorized();
    }

    public function test_a_suspended_members_session_is_not_honoured_by_the_api(): void
    {
        $uye = User::factory()->create(['account_status' => 'suspended']);

        $this->withCredentials()->withCookie(config('session.cookie'), $this->oturumAc($uye))
            ->getJson('/api/v1/personalization/profile')
            ->assertForbidden();
    }

    public function test_save_recipe_uses_the_browser_session(): void
    {
        $uye = User::factory()->create();
        $tarif = Recipe::query()->create([
            'title' => 'Molehiya',
            'slug' => 'molehiya-'.uniqid(),
            'excerpt' => 'Özet.',
            'description' => 'Yeterince uzun bir açıklama metni burada duruyor.',
            'servings' => 4,
            'status' => 'published',
            'verification_status' => 'verified',
            'published_at' => now(),
        ]);

        $this->withCredentials()->withCookie(config('session.cookie'), $this->oturumAc($uye))
            ->postJson(route('api.agent.tools.save-recipe'), ['slug' => $tarif->slug, 'confirmed' => true])
            ->assertOk()
            ->assertJsonPath('is_favorited', true);

        $this->assertTrue($uye->favoriteRecipes()->whereKey($tarif->id)->exists());
    }

    public function test_session_backed_api_routes_require_csrf(): void
    {
        // Oturum çerezi tarayıcı tarafından kendiliğinden gönderilir; CSRF
        // olmadan başka bir site üye adına favori ya da tercih değiştirebilirdi.
        $kaydet = Route::getRoutes()->getByName('api.agent.tools.save-recipe');
        $this->assertContains(PreventRequestForgery::class, app('router')->gatherRouteMiddleware($kaydet));

        $kisisel = collect(Route::getRoutes()->getRoutes())
            ->filter(fn ($rota) => str_starts_with($rota->uri(), 'api/v1/personalization/'));
        $this->assertNotEmpty($kisisel);
        foreach ($kisisel as $rota) {
            $this->assertContains(PreventRequestForgery::class, app('router')->gatherRouteMiddleware($rota), $rota->uri());
        }
    }

    private function oturumAc(User $uye): string
    {
        $kimlik = Str::random(40);
        DB::table('sessions')->insert([
            'id' => $kimlik,
            'user_id' => $uye->id,
            'ip_address' => '127.0.0.1',
            'user_agent' => 'phpunit',
            'payload' => base64_encode(json_encode(['_token' => Str::random(40), Auth::guard('web')->getName() => $uye->id])),
            'last_activity' => time(),
        ]);

        return $kimlik;
    }
}
