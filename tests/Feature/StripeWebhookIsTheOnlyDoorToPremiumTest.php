<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Models\StripeWebhookEvent;
use App\Models\User;
use App\Models\UserSubscription;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Testing\TestResponse;
use Tests\TestCase;

/**
 * Para burada üyeliğe dönüşüyor ve bu yolun hiç testi yoktu.
 *
 * Kod dikkatliydi - imza doğrulaması, livemode eşleşmesi, olay kimliğiyle
 * tekrar koruması, tutar ve sahiplik kontrolleri - ama hiçbiri sınanmıyordu.
 * Bu dosyanın işi, o korumaların sessizce kaybolmasını engellemek. Burada
 * kırılan her şey ya bedava premium ya da ödeyene verilmeyen premium demek.
 */
final class StripeWebhookIsTheOnlyDoorToPremiumTest extends TestCase
{
    use RefreshDatabase;

    private const SIR = 'whsec_testonly_not_a_real_secret';

    protected function setUp(): void
    {
        parent::setUp();

        config()->set('services.stripe.webhook_secret', self::SIR);
        config()->set('services.stripe.live_mode', false);
    }

    public function test_an_unsigned_request_is_refused(): void
    {
        $this->postJson(route('api.stripe.webhook'), ['id' => 'evt_1', 'type' => 'checkout.session.completed'])
            ->assertStatus(400);

        $this->assertSame(0, StripeWebhookEvent::query()->count());
    }

    public function test_a_forged_signature_is_refused(): void
    {
        $govde = json_encode(['id' => 'evt_2', 'type' => 'checkout.session.completed', 'livemode' => false]);

        $this->call('POST', route('api.stripe.webhook'), [], [], [], [
            'CONTENT_TYPE' => 'application/json',
            'HTTP_STRIPE_SIGNATURE' => 't='.time().',v1='.str_repeat('a', 64),
        ], $govde)->assertStatus(400);

        $this->assertSame(0, StripeWebhookEvent::query()->count());
    }

    public function test_without_a_configured_secret_nothing_is_accepted(): void
    {
        config()->set('services.stripe.webhook_secret', '');

        $this->postJson(route('api.stripe.webhook'), ['id' => 'evt_3'])->assertStatus(503);
    }

    public function test_a_test_mode_event_can_never_grant_live_premium(): void
    {
        // Sunucu canlı modda; gelen olay test modunda. Bu, ücretsiz premium
        // almanın en kolay yolu olurdu.
        config()->set('services.stripe.live_mode', true);

        $uye = User::factory()->create();

        $this->imzaliGonder($this->abonelikOlayi($uye, 'evt_4', livemode: false))->assertStatus(400);

        $this->assertFalse($uye->fresh()->isPremium());
    }

    public function test_a_paid_checkout_grants_premium_once(): void
    {
        $uye = User::factory()->create();

        $this->imzaliGonder($this->abonelikOlayi($uye, 'evt_5'))->assertOk();

        $uye->refresh();
        $this->assertTrue($uye->isPremium());
        $this->assertSame(1, UserSubscription::query()->where('user_id', $uye->id)->count());
    }

    public function test_the_same_event_replayed_does_not_grant_a_second_subscription(): void
    {
        $uye = User::factory()->create();
        $olay = $this->abonelikOlayi($uye, 'evt_6');

        $this->imzaliGonder($olay)->assertOk();
        $this->imzaliGonder($olay)->assertOk();

        $this->assertSame(1, UserSubscription::query()->where('user_id', $uye->id)->count());
        $this->assertSame(1, StripeWebhookEvent::query()->where('event_id', 'evt_6')->count());
    }

    public function test_paying_less_than_the_plan_price_grants_nothing(): void
    {
        $uye = User::factory()->create();
        $olay = $this->abonelikOlayi($uye, 'evt_7');
        $olay['data']['object']['amount_total'] = 100; // 1,00 EUR

        $this->imzaliGonder($olay)->assertOk();

        $this->assertFalse($uye->fresh()->isPremium());
    }

    public function test_a_checkout_referencing_someone_else_grants_nothing(): void
    {
        $odeyen = User::factory()->create();
        $baskasi = User::factory()->create();

        $olay = $this->abonelikOlayi($odeyen, 'evt_8');
        $olay['data']['object']['client_reference_id'] = (string) $baskasi->id;

        $this->imzaliGonder($olay)->assertOk();

        $this->assertFalse($odeyen->fresh()->isPremium());
        $this->assertFalse($baskasi->fresh()->isPremium());
    }

    /** @return array<string, mixed> */
    private function abonelikOlayi(User $uye, string $olayId, bool $livemode = false): array
    {
        $plan = config('monetization.plans.monthly');

        return [
            'id' => $olayId,
            'type' => 'checkout.session.completed',
            'livemode' => $livemode,
            'data' => ['object' => [
                'id' => 'cs_'.$olayId,
                'mode' => 'subscription',
                'payment_status' => 'paid',
                'status' => 'complete',
                'subscription' => 'sub_'.$olayId,
                'customer' => 'cus_'.$olayId,
                'client_reference_id' => (string) $uye->id,
                'amount_total' => (int) round(((float) $plan['price']) * 100),
                'currency' => mb_strtolower((string) $plan['currency']),
                'metadata' => ['user_id' => (string) $uye->id, 'plan' => 'monthly', 'purchase_type' => 'subscription'],
            ]],
        ];
    }

    /** @param array<string, mixed> $olay */
    private function imzaliGonder(array $olay): TestResponse
    {
        $govde = json_encode($olay, JSON_THROW_ON_ERROR);
        $zaman = time();
        $imza = hash_hmac('sha256', $zaman.'.'.$govde, self::SIR);

        return $this->call('POST', route('api.stripe.webhook'), [], [], [], [
            'CONTENT_TYPE' => 'application/json',
            'HTTP_STRIPE_SIGNATURE' => 't='.$zaman.',v1='.$imza,
        ], $govde);
    }
}
