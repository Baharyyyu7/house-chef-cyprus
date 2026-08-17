<?php

namespace Tests\Feature;

use App\Models\AffiliateLink;
use App\Models\DigitalOrder;
use App\Models\DigitalProduct;
use App\Models\User;
use App\Models\UserSubscription;
use App\Notifications\DigitalOrderReady;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\Str;
use Illuminate\Testing\TestResponse;
use Tests\TestCase;

class MonetizationSafetyTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        config()->set('monetization.payments_enabled', false);
    }

    public function test_disabled_payments_do_not_grant_premium_or_create_a_subscription(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)
            ->from(route('pricing'))
            ->post(route('subscription.checkout'), ['plan' => 'monthly'])
            ->assertRedirect(route('pricing'))
            ->assertSessionHasErrors('payment');

        $user->refresh();

        $this->assertFalse($user->isPremium());
        $this->assertFalse($user->is_premium);
        $this->assertDatabaseCount('user_subscriptions', 0);
    }

    public function test_disabled_payments_do_not_create_an_order_or_download_token(): void
    {
        $product = DigitalProduct::query()->create([
            'title' => 'Kıbrıs Mutfağı',
            'slug' => 'kibris-mutfagi',
            'description' => 'Dijital rehber',
            'price' => 9.99,
            'currency' => 'EUR',
            'is_active' => true,
        ]);

        $this->from(route('shop.index'))
            ->post(route('shop.checkout', $product), [
                'email' => 'alici@example.com',
                'name' => 'Alıcı',
            ])
            ->assertRedirect(route('shop.index'))
            ->assertSessionHasErrors('payment');

        $this->assertDatabaseCount('digital_orders', 0);
        $this->assertSame(0, $product->refresh()->sales_count);
    }

    public function test_a_product_without_a_verified_private_file_cannot_be_charged(): void
    {
        $this->enableTestPayments();
        $product = DigitalProduct::query()->create([
            'title' => 'Dosyasız Rehber',
            'slug' => 'dosyasiz-rehber',
            'description' => 'Henüz teslimata hazır değil',
            'price' => 9.99,
            'currency' => 'EUR',
            'file_path' => null,
            'is_active' => true,
        ]);

        $this->from(route('shop.index'))
            ->post(route('shop.checkout', $product), [
                'email' => 'alici@example.com',
                'name' => 'Alıcı',
            ])
            ->assertRedirect(route('shop.index'))
            ->assertSessionHasErrors('payment');

        $this->assertDatabaseCount('digital_orders', 0);
    }

    public function test_disabled_payments_do_not_record_a_tip(): void
    {
        $this->from(route('pricing'))
            ->post(route('tip.process'), ['amount' => '5.00'])
            ->assertRedirect(route('pricing'))
            ->assertSessionHasErrors('payment');

        $this->assertDatabaseCount('tip_contributions', 0);
    }

    public function test_pricing_page_marks_checkout_as_coming_soon_when_payments_are_disabled(): void
    {
        $this->get(route('pricing'))
            ->assertOk()
            ->assertSee('Henüz ödeme almıyoruz.')
            ->assertDontSee('Hemen Başlat');
    }

    public function test_expired_membership_loses_premium_privileges(): void
    {
        $user = User::factory()->create();
        $user->forceFill([
            'is_premium' => true,
            'premium_plan' => 'monthly',
            'premium_expires_at' => now()->subMinute(),
        ])->save();

        $this->assertFalse($user->fresh()->isPremium());
    }

    public function test_premium_flag_without_a_verified_payment_record_grants_no_privileges(): void
    {
        $user = User::factory()->create();
        $user->forceFill([
            'is_premium' => true,
            'premium_plan' => 'monthly',
            'premium_expires_at' => now()->addMonth(),
        ])->save();

        UserSubscription::create([
            'user_id' => $user->id,
            'plan' => 'monthly',
            'amount' => 4.99,
            'currency' => 'EUR',
            'payment_provider' => 'stripe_checkout',
            'provider_subscription_id' => 'sub_sim_legacy',
            'status' => 'active',
            'started_at' => now(),
            'expires_at' => now()->addMonth(),
        ]);

        $this->assertFalse($user->fresh()->isPremium());
    }

    public function test_an_existing_verified_subscriber_cannot_start_a_second_subscription(): void
    {
        $this->enableTestPayments();
        $user = User::factory()->create();
        $user->forceFill([
            'is_premium' => true,
            'premium_plan' => 'monthly',
            'premium_expires_at' => now()->addMonth(),
        ])->save();
        UserSubscription::create([
            'user_id' => $user->id,
            'plan' => 'monthly',
            'amount' => 4.99,
            'currency' => 'EUR',
            'payment_provider' => 'stripe',
            'provider_subscription_id' => 'sub_verified_existing',
            'status' => 'active',
            'started_at' => now(),
            'expires_at' => now()->addMonth(),
            'metadata' => ['verified_by_webhook' => true],
        ]);

        $this->actingAs($user)
            ->from(route('pricing'))
            ->post(route('subscription.checkout'), ['plan' => 'yearly'])
            ->assertRedirect(route('pricing'))
            ->assertSessionHasErrors('payment');

        $this->assertDatabaseCount('user_subscriptions', 1);
    }

    public function test_invalid_webhook_signature_is_rejected_without_granting_premium(): void
    {
        config()->set('services.stripe.webhook_secret', 'whsec_test');
        $user = User::factory()->create();
        $payload = $this->subscriptionCheckoutPayload($user);

        $this->call(
            'POST',
            route('api.stripe.webhook'),
            server: ['CONTENT_TYPE' => 'application/json', 'HTTP_STRIPE_SIGNATURE' => 't=1,v1=invalid'],
            content: $payload,
        )->assertBadRequest();

        $this->assertFalse($user->fresh()->isPremium());
        $this->assertDatabaseCount('user_subscriptions', 0);
    }

    public function test_only_a_validly_signed_paid_webhook_grants_premium(): void
    {
        $secret = 'whsec_test';
        config()->set('services.stripe.webhook_secret', $secret);
        $user = User::factory()->create();
        $payload = $this->subscriptionCheckoutPayload($user);

        $response = $this->call(
            'POST',
            route('api.stripe.webhook'),
            server: [
                'CONTENT_TYPE' => 'application/json',
                'HTTP_STRIPE_SIGNATURE' => $this->stripeSignature($payload, $secret),
            ],
            content: $payload,
        );
        $response->assertOk()->assertJson(['received' => true]);

        $this->call(
            'POST',
            route('api.stripe.webhook'),
            server: [
                'CONTENT_TYPE' => 'application/json',
                'HTTP_STRIPE_SIGNATURE' => $this->stripeSignature($payload, $secret),
            ],
            content: $payload,
        )->assertOk();

        $user->refresh();
        $this->assertTrue($user->isPremium());
        $this->assertSame('monthly', $user->premium_plan);
        $this->assertDatabaseHas('user_subscriptions', [
            'user_id' => $user->id,
            'provider_subscription_id' => 'sub_verified_123',
            'status' => 'active',
        ]);
        $this->assertDatabaseHas('stripe_webhook_events', ['event_id' => 'evt_checkout_paid']);
        $this->assertDatabaseCount('stripe_webhook_events', 1);
        $this->assertDatabaseCount('user_subscriptions', 1);
    }

    public function test_a_signed_test_event_is_rejected_by_a_live_webhook_configuration(): void
    {
        $secret = 'whsec_live_endpoint';
        config()->set('services.stripe.webhook_secret', $secret);
        config()->set('services.stripe.live_mode', true);
        $user = User::factory()->create();
        $payload = $this->subscriptionCheckoutPayload($user);

        $this->call(
            'POST',
            route('api.stripe.webhook'),
            server: [
                'CONTENT_TYPE' => 'application/json',
                'HTTP_STRIPE_SIGNATURE' => $this->stripeSignature($payload, $secret),
            ],
            content: $payload,
        )->assertBadRequest();

        $this->assertFalse($user->fresh()->isPremium());
        $this->assertDatabaseCount('stripe_webhook_events', 0);
    }

    public function test_invoice_paid_can_activate_a_subscription_when_events_arrive_out_of_order(): void
    {
        $user = User::factory()->create();

        $this->postSignedStripeEvent([
            'id' => 'evt_invoice_first',
            'type' => 'invoice.paid',
            'data' => ['object' => [
                'id' => 'in_first_123',
                'status' => 'paid',
                'subscription' => 'sub_first_123',
                'customer' => 'cus_first_123',
                'payment_intent' => 'pi_first_123',
                'amount_paid' => 499,
                'currency' => 'eur',
                'parent' => ['subscription_details' => ['metadata' => [
                    'user_id' => (string) $user->id,
                    'plan' => 'monthly',
                ]]],
                'lines' => ['data' => [['period' => ['end' => now()->addMonth()->timestamp]]]],
            ]],
        ])->assertOk();

        $this->assertTrue($user->fresh()->isPremium());
        $this->assertDatabaseHas('user_subscriptions', [
            'provider_subscription_id' => 'sub_first_123',
            'status' => 'active',
        ]);
    }

    public function test_failed_subscription_payment_revokes_premium_access(): void
    {
        $user = User::factory()->create();
        $user->forceFill([
            'is_premium' => true,
            'premium_plan' => 'monthly',
            'premium_expires_at' => now()->addMonth(),
        ])->save();
        UserSubscription::create([
            'user_id' => $user->id,
            'plan' => 'monthly',
            'amount' => 4.99,
            'currency' => 'EUR',
            'payment_provider' => 'stripe',
            'provider_subscription_id' => 'sub_failed_123',
            'status' => 'active',
            'started_at' => now(),
            'expires_at' => now()->addMonth(),
            'metadata' => ['verified_by_webhook' => true],
        ]);

        $event = [
            'id' => 'evt_invoice_failed',
            'type' => 'invoice.payment_failed',
            'data' => ['object' => ['subscription' => 'sub_failed_123']],
        ];
        $this->postSignedStripeEvent($event)->assertOk();

        $this->assertFalse($user->fresh()->isPremium());
        $this->assertDatabaseHas('user_subscriptions', [
            'provider_subscription_id' => 'sub_failed_123',
            'status' => 'past_due',
        ]);
    }

    public function test_digital_order_is_completed_only_by_a_paid_matching_webhook(): void
    {
        Notification::fake();
        $product = DigitalProduct::query()->create([
            'title' => 'Kıbrıs Mutfağı',
            'slug' => 'kibris-mutfagi-paid',
            'description' => 'Dijital rehber',
            'price' => 9.99,
            'currency' => 'EUR',
            'is_active' => true,
        ]);
        $order = DigitalOrder::create([
            'order_uuid' => '96e3a765-578f-40ca-b5e7-2e6813010fb7',
            'digital_product_id' => $product->id,
            'buyer_email' => 'alici@example.com',
            'buyer_name' => 'Alıcı',
            'amount' => 9.99,
            'currency' => 'EUR',
            'payment_provider' => 'stripe',
            'checkout_session_id' => 'cs_paid_123',
            'status' => 'pending',
            'download_token' => 'pending-download-token',
        ]);

        $this->postSignedStripeEvent([
            'id' => 'evt_order_paid',
            'type' => 'checkout.session.completed',
            'data' => ['object' => [
                'id' => 'cs_paid_123',
                'mode' => 'payment',
                'payment_status' => 'paid',
                'payment_intent' => 'pi_paid_123',
                'amount_total' => 999,
                'currency' => 'eur',
                'metadata' => [
                    'purchase_type' => 'digital_order',
                    'order_uuid' => $order->order_uuid,
                ],
            ]],
        ])->assertOk();

        $this->assertDatabaseHas('digital_orders', [
            'id' => $order->id,
            'status' => 'completed',
            'provider_payment_id' => 'pi_paid_123',
        ]);
        $this->assertSame(1, $product->refresh()->sales_count);
        Notification::assertSentOnDemand(DigitalOrderReady::class);
    }

    public function test_a_paid_checkout_with_the_wrong_session_id_cannot_complete_an_order(): void
    {
        $product = DigitalProduct::query()->create([
            'title' => 'Güvenli Rehber',
            'slug' => 'guvenli-rehber',
            'description' => 'Dijital rehber',
            'price' => 7.50,
            'currency' => 'EUR',
            'is_active' => true,
        ]);
        $order = DigitalOrder::query()->create([
            'order_uuid' => '447c24cd-0794-442f-8a50-915a84ea9a11',
            'digital_product_id' => $product->id,
            'buyer_email' => 'alici@example.com',
            'amount' => 7.50,
            'currency' => 'EUR',
            'payment_provider' => 'stripe',
            'checkout_session_id' => 'cs_expected_123',
            'status' => 'pending',
            'download_token' => 'wrong-session-download-token',
        ]);

        $this->postSignedStripeEvent([
            'id' => 'evt_wrong_session',
            'type' => 'checkout.session.completed',
            'data' => ['object' => [
                'id' => 'cs_different_123',
                'mode' => 'payment',
                'payment_status' => 'paid',
                'payment_intent' => 'pi_different_123',
                'amount_total' => 750,
                'currency' => 'eur',
                'metadata' => [
                    'purchase_type' => 'digital_order',
                    'order_uuid' => $order->order_uuid,
                ],
            ]],
        ])->assertOk();

        $this->assertSame('pending', $order->fresh()->status);
        $this->assertSame(0, $product->fresh()->sales_count);
    }

    public function test_an_asynchronous_checkout_stays_processing_until_payment_succeeds(): void
    {
        $order = $this->digitalOrderFixture('async-download-token', [
            'checkout_session_id' => 'cs_async_123',
        ]);

        $session = [
            'id' => 'cs_async_123',
            'mode' => 'payment',
            'payment_status' => 'unpaid',
            'amount_total' => 500,
            'currency' => 'eur',
            'metadata' => [
                'purchase_type' => 'digital_order',
                'order_uuid' => $order->order_uuid,
            ],
        ];
        $this->postSignedStripeEvent([
            'id' => 'evt_async_processing',
            'type' => 'checkout.session.completed',
            'data' => ['object' => $session],
        ])->assertOk();
        $this->assertSame('processing', $order->fresh()->status);

        $session['payment_status'] = 'paid';
        $session['payment_intent'] = 'pi_async_123';
        $this->postSignedStripeEvent([
            'id' => 'evt_async_paid',
            'type' => 'checkout.session.async_payment_succeeded',
            'data' => ['object' => $session],
        ])->assertOk();

        $this->assertSame('completed', $order->fresh()->status);
        $this->assertSame('pi_async_123', $order->fresh()->provider_payment_id);
    }

    public function test_a_dispute_suspends_and_a_won_dispute_restores_premium(): void
    {
        $user = User::factory()->create();
        $user->forceFill([
            'is_premium' => true,
            'premium_plan' => 'monthly',
            'premium_expires_at' => now()->addMonth(),
        ])->save();
        $subscription = UserSubscription::query()->create([
            'user_id' => $user->id,
            'plan' => 'monthly',
            'amount' => 4.99,
            'currency' => 'EUR',
            'payment_provider' => 'stripe',
            'provider_subscription_id' => 'sub_disputed_123',
            'status' => 'active',
            'started_at' => now(),
            'expires_at' => now()->addMonth(),
            'metadata' => [
                'verified_by_webhook' => true,
                'payment_intent' => 'pi_disputed_123',
            ],
        ]);

        $this->postSignedStripeEvent([
            'id' => 'evt_dispute_opened',
            'type' => 'charge.dispute.created',
            'data' => ['object' => [
                'id' => 'du_123',
                'payment_intent' => 'pi_disputed_123',
                'status' => 'needs_response',
            ]],
        ])->assertOk();
        $this->assertSame('disputed', $subscription->fresh()->status);
        $this->assertFalse($user->fresh()->isPremium());

        $this->postSignedStripeEvent([
            'id' => 'evt_dispute_won',
            'type' => 'charge.dispute.closed',
            'data' => ['object' => [
                'id' => 'du_123',
                'payment_intent' => 'pi_disputed_123',
                'status' => 'won',
            ]],
        ])->assertOk();
        $this->assertSame('active', $subscription->fresh()->status);
        $this->assertTrue($user->fresh()->isPremium());
    }

    public function test_tip_amount_is_bounded_before_any_payment_record_is_created(): void
    {
        $this->enableTestPayments();
        config()->set('monetization.tip_max_amount', 250);

        $this->from(route('pricing'))
            ->post(route('tip.process'), ['amount' => '250.01'])
            ->assertRedirect(route('pricing'))
            ->assertSessionHasErrors('amount');

        $this->assertDatabaseCount('tip_contributions', 0);
    }

    public function test_affiliate_redirect_requires_an_active_https_allowlisted_host(): void
    {
        config()->set('monetization.affiliate_allowed_hosts', ['shop.example.com']);
        $link = AffiliateLink::query()->create([
            'title' => 'Güvenli ürün',
            'slug' => 'guvenli-urun',
            'target_url' => 'https://shop.example.com/product/1',
            'provider' => 'partner',
            'is_active' => true,
        ]);

        $this->get(route('affiliate.redirect', $link->slug))
            ->assertRedirect('https://shop.example.com/product/1');
        $this->assertSame(1, $link->fresh()->click_count);

        $link->update(['target_url' => 'https://evil.example/product/1']);
        $this->get(route('affiliate.redirect', $link->slug))->assertNotFound();
        $this->assertSame(1, $link->fresh()->click_count);
    }

    public function test_full_refund_revokes_a_digital_download(): void
    {
        $product = DigitalProduct::query()->create([
            'title' => 'Hellim Rehberi',
            'slug' => 'hellim-rehberi',
            'description' => 'Dijital rehber',
            'price' => 5,
            'currency' => 'EUR',
            'is_active' => true,
        ]);
        $order = DigitalOrder::create([
            'order_uuid' => 'f37142c8-5829-4b47-893f-11ae02657232',
            'digital_product_id' => $product->id,
            'buyer_email' => 'alici@example.com',
            'buyer_name' => 'Alıcı',
            'amount' => 5,
            'currency' => 'EUR',
            'payment_provider' => 'stripe',
            'provider_payment_id' => 'pi_refunded_123',
            'status' => 'completed',
            'download_token' => 'refunded-download-token',
        ]);

        $this->postSignedStripeEvent([
            'id' => 'evt_charge_refunded',
            'type' => 'charge.refunded',
            'data' => ['object' => [
                'payment_intent' => 'pi_refunded_123',
                'refunded' => true,
            ]],
        ])->assertOk();

        $this->assertSame('refunded', $order->refresh()->status);
        $this->assertFalse($order->canDownload());
    }

    public function test_order_confirmation_requires_a_server_signed_url(): void
    {
        $order = $this->digitalOrderFixture('signed-confirmation-token');

        $this->get(route('shop.success', $order->order_uuid))->assertForbidden();
        $this->get(URL::signedRoute('shop.success', ['orderUuid' => $order->order_uuid]))->assertOk();
    }

    public function test_digital_download_limit_is_reserved_before_the_file_is_sent(): void
    {
        Storage::fake('local');
        Storage::disk('local')->put('products/guide.pdf', 'verified-content');
        $order = $this->digitalOrderFixture('single-download-token', [
            'status' => 'completed',
            'max_downloads' => 1,
        ], ['file_path' => 'products/guide.pdf']);

        $this->get(route('shop.download', $order->download_token))->assertOk();
        $this->assertSame(1, $order->fresh()->download_count);

        $this->from(route('shop.success', $order->order_uuid))
            ->get(route('shop.download', $order->download_token))
            ->assertRedirect(route('shop.success', $order->order_uuid))
            ->assertSessionHasErrors('download');
        $this->assertSame(1, $order->fresh()->download_count);
    }

    private function subscriptionCheckoutPayload(User $user): string
    {
        return json_encode([
            'id' => 'evt_checkout_paid',
            'type' => 'checkout.session.completed',
            'livemode' => false,
            'data' => ['object' => [
                'id' => 'cs_verified_123',
                'mode' => 'subscription',
                'payment_status' => 'paid',
                'client_reference_id' => (string) $user->id,
                'subscription' => 'sub_verified_123',
                'customer' => 'cus_verified_123',
                'invoice' => 'in_verified_123',
                'amount_total' => 499,
                'currency' => 'eur',
                'metadata' => [
                    'purchase_type' => 'subscription',
                    'user_id' => (string) $user->id,
                    'plan' => 'monthly',
                ],
            ]],
        ], JSON_THROW_ON_ERROR);
    }

    /** @param array<string, mixed> $event */
    private function postSignedStripeEvent(array $event): TestResponse
    {
        $secret = 'whsec_test';
        config()->set('services.stripe.webhook_secret', $secret);
        $event['livemode'] ??= false;
        $payload = json_encode($event, JSON_THROW_ON_ERROR);

        return $this->call(
            'POST',
            route('api.stripe.webhook'),
            server: [
                'CONTENT_TYPE' => 'application/json',
                'HTTP_STRIPE_SIGNATURE' => $this->stripeSignature($payload, $secret),
            ],
            content: $payload,
        );
    }

    private function stripeSignature(string $payload, string $secret): string
    {
        $timestamp = now()->timestamp;
        $signature = hash_hmac('sha256', $timestamp.'.'.$payload, $secret);

        return "t={$timestamp},v1={$signature}";
    }

    private function enableTestPayments(): void
    {
        config()->set('monetization.payments_enabled', true);
        config()->set('services.stripe.secret', 'sk_test_ready');
        config()->set('services.stripe.webhook_secret', 'whsec_test_ready');
        config()->set('services.stripe.require_terms_consent', true);
    }

    /** @param array<string, mixed> $orderOverrides @param array<string, mixed> $productOverrides */
    private function digitalOrderFixture(string $downloadToken, array $orderOverrides = [], array $productOverrides = []): DigitalOrder
    {
        $product = DigitalProduct::query()->create(array_merge([
            'title' => 'İndirme Rehberi',
            'slug' => 'indirme-rehberi-'.substr(hash('sha256', $downloadToken), 0, 8),
            'description' => 'Dijital rehber',
            'price' => 5,
            'currency' => 'EUR',
            'is_active' => true,
        ], $productOverrides));

        return DigitalOrder::query()->create(array_merge([
            'order_uuid' => (string) Str::uuid(),
            'digital_product_id' => $product->id,
            'buyer_email' => 'alici@example.com',
            'amount' => 5,
            'currency' => 'EUR',
            'payment_provider' => 'stripe',
            'status' => 'pending',
            'download_token' => $downloadToken,
        ], $orderOverrides));
    }
}
