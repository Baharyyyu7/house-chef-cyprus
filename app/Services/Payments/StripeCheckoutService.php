<?php

namespace App\Services\Payments;

use App\Models\DigitalOrder;
use App\Models\TipContribution;
use App\Models\User;
use Illuminate\Support\Facades\URL;
use RuntimeException;
use Stripe\StripeClient;

class StripeCheckoutService
{
    public function isReady(): bool
    {
        $secret = (string) config('services.stripe.secret', '');
        $webhookSecret = (string) config('services.stripe.webhook_secret', '');

        if (! config('monetization.payments_enabled', false)
            || ! str_starts_with($secret, 'sk_')
            || ! str_starts_with($webhookSecret, 'whsec_')
            || ! config('services.stripe.require_terms_consent', false)) {
            return false;
        }

        return ! app()->environment('production')
            || (config('services.stripe.live_mode', false) && str_starts_with($secret, 'sk_live_'));
    }

    public function subscription(User $user, string $plan): string
    {
        $this->assertPaymentsEnabled();
        $priceId = (string) config("monetization.plans.{$plan}.stripe_price_id", '');

        if (! str_starts_with($priceId, 'price_') || str_ends_with($priceId, '_xxx')) {
            throw new RuntimeException('Stripe abonelik fiyatı yapılandırılmadı.');
        }

        $parameters = [
            'mode' => 'subscription',
            'client_reference_id' => (string) $user->id,
            'line_items' => [['price' => $priceId, 'quantity' => 1]],
            'metadata' => [
                'purchase_type' => 'subscription',
                'user_id' => (string) $user->id,
                'plan' => $plan,
            ],
            'subscription_data' => [
                'metadata' => [
                    'user_id' => (string) $user->id,
                    'plan' => $plan,
                ],
            ],
            'success_url' => route('pricing', ['checkout' => 'success']),
            'cancel_url' => route('pricing', ['checkout' => 'cancelled']),
            'billing_address_collection' => 'required',
            'consent_collection' => ['terms_of_service' => 'required'],
        ];

        if ($user->stripe_customer_id) {
            $parameters['customer'] = $user->stripe_customer_id;
        } else {
            $parameters['customer_email'] = $user->email;
        }

        $session = $this->client()->checkout->sessions->create($parameters, [
            'idempotency_key' => "subscription:{$user->id}:{$plan}:".now()->format('Y-m-d-H'),
        ]);

        return $this->checkoutUrl($session->url);
    }

    public function digitalOrder(DigitalOrder $order): string
    {
        $this->assertPaymentsEnabled();
        $session = $this->client()->checkout->sessions->create([
            'mode' => 'payment',
            'customer_email' => $order->buyer_email,
            'line_items' => [[
                'quantity' => 1,
                'price_data' => [
                    'currency' => strtolower($order->currency),
                    'unit_amount' => (int) round((float) $order->amount * 100),
                    'product_data' => ['name' => $order->product->title],
                ],
            ]],
            'metadata' => [
                'purchase_type' => 'digital_order',
                'order_uuid' => $order->order_uuid,
            ],
            'success_url' => URL::signedRoute('shop.success', ['orderUuid' => $order->order_uuid]),
            'cancel_url' => route('shop.index', ['checkout' => 'cancelled']),
            'billing_address_collection' => 'required',
            'consent_collection' => ['terms_of_service' => 'required'],
        ], ['idempotency_key' => 'digital-order:'.$order->order_uuid]);

        $order->update([
            'checkout_session_id' => $session->id,
            'checkout_expires_at' => $session->expires_at ? now()->setTimestamp($session->expires_at) : null,
        ]);

        return $this->checkoutUrl($session->url);
    }

    public function tip(TipContribution $tip): string
    {
        $this->assertPaymentsEnabled();
        $parameters = [
            'mode' => 'payment',
            'line_items' => [[
                'quantity' => 1,
                'price_data' => [
                    'currency' => strtolower($tip->currency),
                    'unit_amount' => (int) round((float) $tip->amount * 100),
                    'product_data' => ['name' => 'House Chef Cyprus desteği'],
                ],
            ]],
            'metadata' => [
                'purchase_type' => 'tip',
                'tip_id' => (string) $tip->id,
            ],
            'success_url' => route('pricing', ['support' => 'success']),
            'cancel_url' => route('pricing', ['support' => 'cancelled']),
            'billing_address_collection' => 'required',
            'consent_collection' => ['terms_of_service' => 'required'],
        ];

        if ($tip->supporter_email) {
            $parameters['customer_email'] = $tip->supporter_email;
        }

        $session = $this->client()->checkout->sessions->create($parameters, [
            'idempotency_key' => 'tip:'.$tip->id,
        ]);

        $tip->update([
            'checkout_session_id' => $session->id,
            'checkout_expires_at' => $session->expires_at ? now()->setTimestamp($session->expires_at) : null,
        ]);

        return $this->checkoutUrl($session->url);
    }

    public function billingPortal(User $user): string
    {
        $this->assertPaymentsEnabled();
        if (! $user->stripe_customer_id) {
            throw new RuntimeException('Stripe müşteri kaydı bulunamadı.');
        }

        $session = $this->client()->billingPortal->sessions->create([
            'customer' => $user->stripe_customer_id,
            'return_url' => route('pricing'),
        ]);

        return $this->checkoutUrl($session->url);
    }

    public function cancelAtPeriodEnd(string $subscriptionId): void
    {
        $this->assertPaymentsEnabled();
        $this->client()->subscriptions->update($subscriptionId, [
            'cancel_at_period_end' => true,
        ]);
    }

    private function client(): StripeClient
    {
        $secret = (string) config('services.stripe.secret', '');

        if ($secret === '') {
            throw new RuntimeException('Stripe gizli anahtarı yapılandırılmadı.');
        }

        return new StripeClient($secret);
    }

    private function checkoutUrl(?string $url): string
    {
        if (! $url) {
            throw new RuntimeException('Stripe yönlendirme adresi üretmedi.');
        }

        return $url;
    }

    private function assertPaymentsEnabled(): void
    {
        if (! $this->isReady()) {
            throw new RuntimeException('Stripe ödeme ve webhook yapılandırması hazır değil.');
        }
    }
}
