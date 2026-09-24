<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Models\DigitalProduct;
use App\Models\Recipe;
use App\Models\User;
use App\Models\UserSubscription;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

final class MonetizationTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        config()->set('monetization.payments_enabled', false);
    }

    public function test_disabled_payments_never_grant_a_free_subscription(): void
    {
        $user = User::factory()->create();
        $product = DigitalProduct::query()->create([
            'title' => 'Güvenli Dijital Rehber',
            'slug' => 'guvenli-dijital-rehber',
            'description' => 'Ödeme doğrulaması gerektiren ürün.',
            'price' => 9.99,
            'currency' => 'EUR',
            'is_active' => true,
        ]);

        $this->actingAs($user)
            ->from(route('pricing'))
            ->post(route('subscription.checkout'), ['plan' => 'monthly'])
            ->assertRedirect(route('pricing'))
            ->assertSessionHasErrors([
                'payment' => 'Ödeme altyapısı yakında aktif olacaktır',
            ]);

        $this->from(route('shop.index'))
            ->post(route('shop.checkout', $product), [
                'email' => 'alici@example.com',
                'name' => 'Alıcı',
            ])
            ->assertRedirect(route('shop.index'))
            ->assertSessionHasErrors([
                'payment' => 'Ödeme altyapısı yakında aktif olacaktır',
            ]);

        $this->from(route('pricing'))
            ->post(route('tip.process'), ['amount' => 5])
            ->assertRedirect(route('pricing'))
            ->assertSessionHasErrors([
                'payment' => 'Ödeme altyapısı yakında aktif olacaktır',
            ]);

        $this->assertFalse($user->fresh()->is_premium);
        $this->assertDatabaseCount('user_subscriptions', 0);
        $this->assertDatabaseCount('digital_orders', 0);
        $this->assertDatabaseCount('tip_contributions', 0);
        $this->get(route('pricing'))->assertSee('Yakında Aktif');
    }

    public function test_premium_flag_without_a_verified_webhook_record_grants_no_access(): void
    {
        $user = User::factory()->create();
        $user->forceFill([
            'is_premium' => true,
            'premium_plan' => 'monthly',
            'premium_expires_at' => now()->addMonth(),
        ])->save();

        UserSubscription::query()->create([
            'user_id' => $user->id,
            'plan' => 'monthly',
            'amount' => 4.99,
            'currency' => 'EUR',
            'payment_provider' => 'stripe',
            'provider_subscription_id' => 'sub_unverified',
            'status' => 'active',
            'started_at' => now(),
            'expires_at' => now()->addMonth(),
            'metadata' => ['verified_by_webhook' => false],
        ]);

        $this->assertFalse($user->fresh()->isPremium());
    }

    public function test_invalid_webhook_signature_is_rejected_without_mutation(): void
    {
        config()->set('services.stripe.webhook_secret', 'whsec_test');
        $user = User::factory()->create();
        $payload = json_encode([
            'id' => 'evt_invalid_signature',
            'type' => 'checkout.session.completed',
            'data' => ['object' => [
                'payment_status' => 'paid',
                'metadata' => ['user_id' => (string) $user->id],
            ]],
        ], JSON_THROW_ON_ERROR);

        $this->call(
            'POST',
            route('api.stripe.webhook'),
            server: [
                'CONTENT_TYPE' => 'application/json',
                'HTTP_STRIPE_SIGNATURE' => 't=1,v1=invalid',
            ],
            content: $payload,
        )->assertBadRequest();

        $this->assertFalse($user->fresh()->is_premium);
        $this->assertDatabaseCount('user_subscriptions', 0);
    }

    public function test_expired_subscription_loses_premium_access(): void
    {
        $user = User::factory()->create();
        $user->forceFill([
            'is_premium' => true,
            'premium_plan' => 'monthly',
            'premium_expires_at' => now()->subMinute(),
        ])->save();

        UserSubscription::query()->create([
            'user_id' => $user->id,
            'plan' => 'monthly',
            'amount' => 4.99,
            'currency' => 'EUR',
            'payment_provider' => 'stripe',
            'provider_subscription_id' => 'sub_expired',
            'status' => 'active',
            'started_at' => now()->subMonths(2),
            'expires_at' => now()->subMinute(),
            'metadata' => ['verified_by_webhook' => true],
        ]);

        $this->assertFalse($user->fresh()->isPremium());
    }

    public function test_verified_premium_member_does_not_see_ad_markup(): void
    {
        config()->set('integrations.google.adsense.enabled', true);
        config()->set('integrations.google.adsense.certified_cmp_enabled', true);
        config()->set('integrations.google.adsense.client_id', 'ca-pub-1614195335767159');
        config()->set('monetization.slots.in_article', ['id' => '9988776655', 'format' => 'in_article']);

        $user = User::factory()->create();
        $user->forceFill([
            'is_premium' => true,
            'premium_plan' => 'monthly',
            'premium_expires_at' => now()->addMonth(),
        ])->save();
        UserSubscription::query()->create([
            'user_id' => $user->id,
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
        $recipe = Recipe::query()->create([
            'title' => 'Reklamsız Tarif',
            'slug' => 'reklamsiz-tarif',
            'status' => 'published',
            'verification_status' => 'verified',
            'published_at' => now(),
        ]);

        $this->actingAs($user)
            ->withUnencryptedCookie('hc_analytics_consent', 'granted')
            ->withUnencryptedCookie('hc_advertising_consent', 'granted')
            ->get(route('recipes.show', $recipe))
            ->assertOk()
            ->assertDontSee('data-ad-slot', false);
    }
}
