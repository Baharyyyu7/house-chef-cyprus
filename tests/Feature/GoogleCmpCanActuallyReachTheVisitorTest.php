<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Models\Recipe;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Testing\TestResponse;
use Tests\TestCase;

/**
 * Reklamlar aylardır çıkamıyordu ve sebebi bir kilitlenmeydi.
 *
 * Google'ın sertifikalı CMP'si (Privacy & messaging) AdSense betiğinin
 * içinden gelir. Betik onay çerezinin arkasındaydı: onay yoksa betik
 * yüklenmiyor, betik yüklenmeyince mesaj görünmüyor, mesaj görünmeyince onay
 * hiç oluşmuyordu. AdSense panelinde ne seçilirse seçilsin döngü kırılmıyordu.
 *
 * Bu testler döngünün geri gelmesini engeller ve gevşemenin yalnız 'google'
 * modunda geçerli olduğunu, 'custom' modunda hiçbir şeyin değişmediğini
 * sabitler.
 */
final class GoogleCmpCanActuallyReachTheVisitorTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        config()->set('integrations.google.adsense.enabled', true);
        config()->set('integrations.google.adsense.certified_cmp_enabled', true);
        config()->set('integrations.google.adsense.client_id', 'ca-pub-1614195335767159');
        config()->set('monetization.slots.sidebar', '9988776655');
    }

    public function test_the_google_cmp_loads_before_consent_or_it_can_never_ask(): void
    {
        config()->set('integrations.google.adsense.cmp_provider', 'google');

        $this->tarifSayfasi(reklamOnayi: null)
            ->assertOk()
            ->assertSee('adsbygoogle.js', false);
    }

    public function test_it_starts_with_every_advertising_permission_denied(): void
    {
        config()->set('integrations.google.adsense.cmp_provider', 'google');

        $this->tarifSayfasi(reklamOnayi: null)
            ->assertOk()
            ->assertSee("'ad_storage':'denied'", false)
            ->assertSee("'ad_user_data':'denied'", false)
            ->assertSee("'ad_personalization':'denied'", false);
    }

    public function test_the_consent_message_is_allowed_through_the_content_policy(): void
    {
        config()->set('integrations.google.adsense.cmp_provider', 'google');

        $politika = $this->tarifSayfasi(reklamOnayi: null)->headers->get('Content-Security-Policy');

        $this->assertStringContainsString('https://fundingchoicesmessages.google.com', (string) $politika);
    }

    public function test_our_own_banner_still_owns_consent_in_custom_mode(): void
    {
        config()->set('integrations.google.adsense.cmp_provider', 'custom');

        $this->tarifSayfasi(reklamOnayi: null)
            ->assertOk()
            ->assertDontSee('adsbygoogle.js', false)
            ->assertDontSee('data-ad-slot', false);
    }

    public function test_switching_off_advertising_still_stops_everything(): void
    {
        config()->set('integrations.google.adsense.cmp_provider', 'google');
        config()->set('integrations.google.adsense.enabled', false);

        $this->tarifSayfasi(reklamOnayi: null)
            ->assertOk()
            ->assertDontSee('adsbygoogle.js', false)
            ->assertDontSee('data-ad-slot', false);
    }

    private function tarifSayfasi(?string $reklamOnayi): TestResponse
    {
        $recipe = Recipe::query()->create([
            'title' => 'Adana Kebabı',
            'slug' => 'adana-kebabi',
            'status' => 'published',
            'verification_status' => 'verified',
            'published_at' => now(),
        ]);

        $istek = $this->withUnencryptedCookie('hc_analytics_consent', 'granted');

        if ($reklamOnayi !== null) {
            $istek = $istek->withUnencryptedCookie('hc_advertising_consent', $reklamOnayi);
        }

        return $istek->get(route('recipes.show', $recipe));
    }
}
