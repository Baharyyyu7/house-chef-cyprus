<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Models\Recipe;
use App\Models\User;
use App\Models\UserSubscription;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Testing\TestResponse;
use Tests\TestCase;

/**
 * Reklam yuvası iki ayrı yapılandırmadan besleniyordu. Dikkatli olan yol onay
 * çerezine ve sertifikalı CMP şartına bağlıydı; ikinci yol ise hiçbirine
 * bakmıyor, üstelik okuduğu anahtar (monetization.ads_enabled) yapılandırmada
 * hiç tanımlı olmadığı için ADS_ENABLED=false yazılsa bile kendi varsayılanı
 * "true"ya düşüyordu. Yer tutucu yayıncı kimliğiyle ("ca-pub-XXXX...") istek
 * atmak, başvurusu incelemede olan bir hesabın reddedilme yollarından biridir.
 *
 * Yuva artık tek kaynaktan okur ve kapıların hepsi açık değilse hiçbir şey
 * basmaz.
 */
final class AdSlotStaysClosedUntilEveryGateOpensTest extends TestCase
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

    public function test_it_renders_when_every_gate_is_open(): void
    {
        $this->tarifSayfasi(reklamOnayi: 'granted')
            ->assertOk()
            ->assertSee('data-ad-slot="9988776655"', false)
            ->assertSee('data-ad-client="ca-pub-1614195335767159"', false);
    }

    public function test_it_stays_closed_without_advertising_consent(): void
    {
        // Ölçüm izni vermiş olmak reklam izni sayılmaz.
        $this->tarifSayfasi(reklamOnayi: null)
            ->assertOk()
            ->assertDontSee('data-ad-slot', false);
    }

    public function test_it_stays_closed_when_consent_was_refused(): void
    {
        $this->tarifSayfasi(reklamOnayi: 'denied')
            ->assertOk()
            ->assertDontSee('data-ad-slot', false);
    }

    public function test_it_stays_closed_without_a_certified_cmp(): void
    {
        config()->set('integrations.google.adsense.certified_cmp_enabled', false);

        $this->tarifSayfasi(reklamOnayi: 'granted')
            ->assertOk()
            ->assertDontSee('data-ad-slot', false);
    }

    public function test_a_placeholder_publisher_id_never_reaches_google(): void
    {
        config()->set('integrations.google.adsense.client_id', 'ca-pub-XXXXXXXXXXXXXXXX');

        $this->tarifSayfasi(reklamOnayi: 'granted')
            ->assertOk()
            ->assertDontSee('data-ad-slot', false);
    }

    public function test_a_placeholder_slot_id_never_reaches_google(): void
    {
        config()->set('monetization.slots.sidebar', '1234567895');

        $this->tarifSayfasi(reklamOnayi: 'granted')
            ->assertOk()
            ->assertDontSee('data-ad-slot', false);
    }

    public function test_it_stays_closed_when_advertising_is_switched_off(): void
    {
        config()->set('integrations.google.adsense.enabled', false);

        $this->tarifSayfasi(reklamOnayi: 'granted')
            ->assertOk()
            ->assertDontSee('data-ad-slot', false);
    }

    public function test_a_paid_member_never_sees_an_ad(): void
    {
        $member = User::factory()->create();
        $member->forceFill([
            'is_premium' => true,
            'premium_plan' => 'monthly',
            'premium_expires_at' => now()->addMonth(),
        ])->save();
        UserSubscription::create([
            'user_id' => $member->id,
            'plan' => 'monthly',
            'amount' => 4.99,
            'currency' => 'EUR',
            'payment_provider' => 'stripe',
            'provider_subscription_id' => 'sub_verified_ad_free',
            'status' => 'active',
            'started_at' => now(),
            'expires_at' => now()->addMonth(),
            'metadata' => ['verified_by_webhook' => true],
        ]);

        $this->actingAs($member)
            ->tarifSayfasi(reklamOnayi: 'granted')
            ->assertOk()
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
