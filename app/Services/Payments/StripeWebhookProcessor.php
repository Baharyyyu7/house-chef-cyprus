<?php

namespace App\Services\Payments;

use App\Models\DigitalOrder;
use App\Models\StripeWebhookEvent;
use App\Models\TipContribution;
use App\Models\User;
use App\Models\UserSubscription;
use Illuminate\Support\Facades\DB;

class StripeWebhookProcessor
{
    /** @param array<string, mixed> $event */
    public function handle(array $event): void
    {
        $eventId = (string) ($event['id'] ?? '');
        $eventType = (string) ($event['type'] ?? '');

        if ($eventId === '' || $eventType === '') {
            return;
        }

        DB::transaction(function () use ($event, $eventId, $eventType): void {
            $receipt = StripeWebhookEvent::query()->firstOrCreate(
                ['event_id' => $eventId],
                [
                    'event_type' => $eventType,
                    'payload_hash' => hash('sha256', json_encode($event, JSON_THROW_ON_ERROR)),
                    'processed_at' => now(),
                ],
            );

            if (! $receipt->wasRecentlyCreated) {
                return;
            }

            $this->dispatch($event);
        });
    }

    /** @param array<string, mixed> $event */
    private function dispatch(array $event): void
    {
        $object = data_get($event, 'data.object', []);

        match ($event['type'] ?? null) {
            'checkout.session.completed',
            'checkout.session.async_payment_succeeded' => $this->completeCheckout($object),
            'checkout.session.async_payment_failed',
            'checkout.session.expired' => $this->failCheckout($object),
            'invoice.paid' => $this->renewSubscription($object),
            'invoice.payment_failed' => $this->failSubscription($this->invoiceSubscriptionId($object)),
            'customer.subscription.deleted' => $this->endSubscription((string) data_get($object, 'id', '')),
            'charge.refunded' => $this->refundPayment($object),
            'charge.dispute.created' => $this->openDispute($object),
            'charge.dispute.closed' => $this->closeDispute($object),
            default => null,
        };
    }

    /** @param array<string, mixed> $session */
    private function completeCheckout(array $session): void
    {
        $purchaseType = (string) data_get($session, 'metadata.purchase_type', '');
        $expectedMode = $purchaseType === 'subscription' ? 'subscription' : 'payment';

        if (! in_array($purchaseType, ['subscription', 'digital_order', 'tip'], true)
            || ($session['mode'] ?? null) !== $expectedMode) {
            return;
        }

        if (($session['payment_status'] ?? null) !== 'paid') {
            $this->markCheckoutProcessing($session);

            return;
        }

        match ($purchaseType) {
            'subscription' => $this->activateSubscription($session),
            'digital_order' => $this->completeOrder($session),
            'tip' => $this->completeTip($session),
        };
    }

    /** @param array<string, mixed> $session */
    private function activateSubscription(array $session): void
    {
        $user = User::find((int) data_get($session, 'metadata.user_id'));
        $plan = (string) data_get($session, 'metadata.plan', '');
        $subscriptionId = (string) ($session['subscription'] ?? '');
        $planConfig = config("monetization.plans.{$plan}");

        if (! $user
            || ! is_array($planConfig)
            || $subscriptionId === ''
            || (string) ($session['client_reference_id'] ?? '') !== (string) $user->id
            || ! $this->amountMatches($planConfig['price'], $planConfig['currency'], $session)
            || ($user->stripe_customer_id && ! hash_equals($user->stripe_customer_id, (string) ($session['customer'] ?? '')))) {
            return;
        }

        $expiresAt = $plan === 'yearly' ? now()->addYear() : now()->addMonth();

        DB::transaction(function () use ($user, $plan, $planConfig, $subscriptionId, $expiresAt, $session): void {
            $subscription = UserSubscription::firstOrNew(['provider_subscription_id' => $subscriptionId]);
            $subscription->fill([
                'user_id' => $user->id,
                'plan' => $plan,
                'amount' => $planConfig['price'],
                'currency' => $planConfig['currency'],
                'payment_provider' => 'stripe',
                'status' => 'active',
                'cancelled_at' => null,
                'metadata' => [
                    'checkout_session_id' => $session['id'] ?? null,
                    'invoice_id' => $session['invoice'] ?? null,
                    'verified_by_webhook' => true,
                    'verified_at' => now()->toIso8601String(),
                ],
            ]);
            $subscription->started_at ??= now();
            $subscription->expires_at ??= $expiresAt;
            $subscription->save();

            $user->forceFill([
                'is_premium' => true,
                'premium_plan' => $plan,
                'premium_expires_at' => $subscription->expires_at,
                'stripe_customer_id' => $session['customer'] ?? $user->stripe_customer_id,
            ])->save();
        });
    }

    /** @param array<string, mixed> $invoice */
    private function renewSubscription(array $invoice): void
    {
        if (($invoice['status'] ?? null) !== 'paid') {
            return;
        }

        $subscriptionId = (string) ($invoice['subscription'] ?? data_get($invoice, 'parent.subscription_details.subscription', ''));
        $subscription = UserSubscription::query()->where('provider_subscription_id', $subscriptionId)->first();

        if (! $subscription) {
            $this->activateSubscriptionFromInvoice($invoice, $subscriptionId);

            return;
        }

        $periodEnd = (int) data_get($invoice, 'lines.data.0.period.end', 0);
        $expiresAt = $periodEnd > 0
            ? now()->setTimestamp($periodEnd)
            : ($subscription->plan === 'yearly' ? now()->addYear() : now()->addMonth());
        $metadata = $subscription->metadata ?? [];
        $metadata['payment_intent'] = $invoice['payment_intent'] ?? null;
        $metadata['invoice_id'] = $invoice['id'] ?? data_get($metadata, 'invoice_id');
        $metadata['verified_by_webhook'] = true;
        $metadata['verified_at'] = $metadata['verified_at'] ?? now()->toIso8601String();

        $subscription->update([
            'status' => 'active',
            'expires_at' => $expiresAt,
            'cancelled_at' => null,
            'metadata' => $metadata,
        ]);
        $subscription->user->forceFill([
            'is_premium' => true,
            'premium_plan' => $subscription->plan,
            'premium_expires_at' => $expiresAt,
        ])->save();
    }

    /** @param array<string, mixed> $session */
    private function completeOrder(array $session): void
    {
        DB::transaction(function () use ($session): void {
            $order = DigitalOrder::query()
                ->where('order_uuid', data_get($session, 'metadata.order_uuid'))
                ->lockForUpdate()
                ->first();

            if (! $order || ! in_array($order->status, ['pending', 'processing'], true) || ! $this->amountMatches($order->amount, $order->currency, $session)) {
                return;
            }

            if (! $order->checkout_session_id || ! hash_equals($order->checkout_session_id, (string) ($session['id'] ?? ''))) {
                return;
            }

            $order->update([
                'status' => 'completed',
                'provider_payment_id' => $session['payment_intent'] ?? $session['id'],
            ]);
            $order->product()->increment('sales_count');
        });
    }

    /** @param array<string, mixed> $session */
    private function completeTip(array $session): void
    {
        DB::transaction(function () use ($session): void {
            $tip = TipContribution::query()->lockForUpdate()->find((int) data_get($session, 'metadata.tip_id'));

            if (! $tip
                || ! in_array($tip->status, ['pending', 'processing'], true)
                || ! $this->amountMatches($tip->amount, $tip->currency, $session)
                || ! $tip->checkout_session_id
                || ! hash_equals($tip->checkout_session_id, (string) ($session['id'] ?? ''))) {
                return;
            }

            $tip->update([
                'status' => 'completed',
                'provider_payment_id' => $session['payment_intent'] ?? $session['id'],
            ]);
        });
    }

    /** @param array<string, mixed> $session */
    private function failCheckout(array $session): void
    {
        if (data_get($session, 'metadata.purchase_type') === 'digital_order') {
            DigitalOrder::query()
                ->where('order_uuid', data_get($session, 'metadata.order_uuid'))
                ->where('checkout_session_id', $session['id'] ?? null)
                ->whereIn('status', ['pending', 'processing'])
                ->update(['status' => 'failed']);
        }

        if (data_get($session, 'metadata.purchase_type') === 'tip') {
            TipContribution::query()
                ->whereKey((int) data_get($session, 'metadata.tip_id'))
                ->where('checkout_session_id', $session['id'] ?? null)
                ->whereIn('status', ['pending', 'processing'])
                ->update(['status' => 'failed']);
        }
    }

    private function failSubscription(string $subscriptionId): void
    {
        $subscription = UserSubscription::query()
            ->where('provider_subscription_id', $subscriptionId)
            ->first();

        if (! $subscription) {
            return;
        }

        $subscription->update(['status' => 'past_due']);
        $this->refreshPremiumStatus($subscription->user);
    }

    private function endSubscription(string $subscriptionId): void
    {
        $subscription = UserSubscription::query()
            ->where('provider_subscription_id', $subscriptionId)
            ->first();

        if (! $subscription) {
            return;
        }

        $subscription->update([
            'status' => 'cancelled',
            'cancelled_at' => now(),
            'expires_at' => now(),
        ]);
        $this->refreshPremiumStatus($subscription->user);
    }

    /** @param array<string, mixed> $charge */
    private function refundPayment(array $charge): void
    {
        if (($charge['refunded'] ?? false) !== true) {
            return;
        }

        $paymentIntent = (string) data_get($charge, 'payment_intent', '');
        $invoiceId = (string) data_get($charge, 'invoice', '');

        if ($paymentIntent === '' && $invoiceId === '') {
            return;
        }

        DigitalOrder::query()
            ->where('provider_payment_id', $paymentIntent)
            ->where('status', 'completed')
            ->update(['status' => 'refunded']);

        TipContribution::query()
            ->where('provider_payment_id', $paymentIntent)
            ->where('status', 'completed')
            ->update(['status' => 'refunded']);

        $subscription = UserSubscription::query()
            ->where('status', 'active')
            ->get()
            ->first(fn (UserSubscription $item) => ($paymentIntent !== '' && data_get($item->metadata, 'payment_intent') === $paymentIntent)
                || ($invoiceId !== '' && data_get($item->metadata, 'invoice_id') === $invoiceId));

        if ($subscription) {
            $subscription->update(['status' => 'refunded', 'expires_at' => now()]);
            $this->refreshPremiumStatus($subscription->user);
        }
    }

    /** @param array<string, mixed> $session */
    private function markCheckoutProcessing(array $session): void
    {
        if (($session['payment_status'] ?? null) !== 'unpaid') {
            return;
        }

        if (data_get($session, 'metadata.purchase_type') === 'digital_order') {
            DigitalOrder::query()
                ->where('order_uuid', data_get($session, 'metadata.order_uuid'))
                ->where('checkout_session_id', $session['id'] ?? null)
                ->where('status', 'pending')
                ->update(['status' => 'processing']);
        }

        if (data_get($session, 'metadata.purchase_type') === 'tip') {
            TipContribution::query()
                ->whereKey((int) data_get($session, 'metadata.tip_id'))
                ->where('checkout_session_id', $session['id'] ?? null)
                ->where('status', 'pending')
                ->update(['status' => 'processing']);
        }
    }

    /** @param array<string, mixed> $dispute */
    private function openDispute(array $dispute): void
    {
        $paymentIntent = (string) ($dispute['payment_intent'] ?? '');
        $disputeId = (string) ($dispute['id'] ?? '');

        if ($paymentIntent === '' || $disputeId === '') {
            return;
        }

        DigitalOrder::query()
            ->where('provider_payment_id', $paymentIntent)
            ->where('status', 'completed')
            ->update(['status' => 'disputed', 'dispute_id' => $disputeId, 'disputed_at' => now()]);
        TipContribution::query()
            ->where('provider_payment_id', $paymentIntent)
            ->where('status', 'completed')
            ->update(['status' => 'disputed', 'dispute_id' => $disputeId, 'disputed_at' => now()]);

        $subscription = $this->subscriptionByPaymentIntent($paymentIntent);
        if ($subscription) {
            $metadata = $subscription->metadata ?? [];
            $metadata['dispute_id'] = $disputeId;
            $metadata['disputed_at'] = now()->toIso8601String();
            $subscription->update(['status' => 'disputed', 'metadata' => $metadata]);
            $this->refreshPremiumStatus($subscription->user);
        }
    }

    /** @param array<string, mixed> $dispute */
    private function closeDispute(array $dispute): void
    {
        $disputeId = (string) ($dispute['id'] ?? '');
        $status = (string) ($dispute['status'] ?? '');

        if ($disputeId === '' || ! in_array($status, ['won', 'lost'], true)) {
            return;
        }

        $restoredStatus = $status === 'won' ? 'completed' : 'chargeback_lost';
        DigitalOrder::query()->where('dispute_id', $disputeId)->where('status', 'disputed')->update(['status' => $restoredStatus]);
        TipContribution::query()->where('dispute_id', $disputeId)->where('status', 'disputed')->update(['status' => $restoredStatus]);

        $subscription = UserSubscription::query()
            ->where('status', 'disputed')
            ->get()
            ->first(fn (UserSubscription $item) => data_get($item->metadata, 'dispute_id') === $disputeId);

        if (! $subscription) {
            return;
        }

        $metadata = $subscription->metadata ?? [];
        $metadata['dispute_outcome'] = $status;
        $metadata['dispute_closed_at'] = now()->toIso8601String();
        $subscription->update([
            'status' => $status === 'won' && ($subscription->expires_at === null || $subscription->expires_at->isFuture())
                ? 'active'
                : 'chargeback_lost',
            'metadata' => $metadata,
        ]);

        if ($subscription->status === 'active') {
            $subscription->user->forceFill([
                'is_premium' => true,
                'premium_plan' => $subscription->plan,
                'premium_expires_at' => $subscription->expires_at,
            ])->save();
        } else {
            $this->refreshPremiumStatus($subscription->user);
        }
    }

    private function subscriptionByPaymentIntent(string $paymentIntent): ?UserSubscription
    {
        return UserSubscription::query()
            ->where('status', 'active')
            ->get()
            ->first(fn (UserSubscription $item) => data_get($item->metadata, 'payment_intent') === $paymentIntent);
    }

    /** @param array<string, mixed> $invoice */
    private function invoiceSubscriptionId(array $invoice): string
    {
        return (string) ($invoice['subscription'] ?? data_get($invoice, 'parent.subscription_details.subscription', ''));
    }

    private function refreshPremiumStatus(User $user): void
    {
        $active = $user->subscriptions()->get()->contains(
            fn (UserSubscription $subscription) => $subscription->isActive() && $subscription->isPaymentVerified(),
        );

        if (! $active) {
            $user->forceFill([
                'is_premium' => false,
                'premium_plan' => null,
                'premium_expires_at' => null,
            ])->save();
        }
    }

    /** @param array<string, mixed> $session */
    private function amountMatches(mixed $amount, string $currency, array $session): bool
    {
        return (int) round((float) $amount * 100) === (int) ($session['amount_total'] ?? -1)
            && strtolower($currency) === strtolower((string) ($session['currency'] ?? ''));
    }

    /** @param array<string, mixed> $invoice */
    private function activateSubscriptionFromInvoice(array $invoice, string $subscriptionId): void
    {
        $metadata = data_get($invoice, 'parent.subscription_details.metadata', []);
        $user = User::find((int) data_get($metadata, 'user_id'));
        $plan = (string) data_get($metadata, 'plan', '');
        $planConfig = config("monetization.plans.{$plan}");

        if (! $user || ! is_array($planConfig) || ! $this->invoiceAmountMatches($planConfig, $invoice)) {
            return;
        }

        $periodEnd = (int) data_get($invoice, 'lines.data.0.period.end', 0);
        $expiresAt = $periodEnd > 0
            ? now()->setTimestamp($periodEnd)
            : ($plan === 'yearly' ? now()->addYear() : now()->addMonth());

        $subscription = UserSubscription::query()->create([
            'user_id' => $user->id,
            'plan' => $plan,
            'amount' => $planConfig['price'],
            'currency' => $planConfig['currency'],
            'payment_provider' => 'stripe',
            'provider_subscription_id' => $subscriptionId,
            'status' => 'active',
            'started_at' => now(),
            'expires_at' => $expiresAt,
            'metadata' => [
                'payment_intent' => $invoice['payment_intent'] ?? null,
                'invoice_id' => $invoice['id'] ?? null,
                'verified_by_webhook' => true,
                'verified_at' => now()->toIso8601String(),
            ],
        ]);

        $user->forceFill([
            'is_premium' => true,
            'premium_plan' => $plan,
            'premium_expires_at' => $expiresAt,
            'stripe_customer_id' => $invoice['customer'] ?? $user->stripe_customer_id,
        ])->save();
    }

    /** @param array<string, mixed> $planConfig @param array<string, mixed> $invoice */
    private function invoiceAmountMatches(array $planConfig, array $invoice): bool
    {
        return (int) round((float) $planConfig['price'] * 100) === (int) ($invoice['amount_paid'] ?? -1)
            && strtolower((string) $planConfig['currency']) === strtolower((string) ($invoice['currency'] ?? ''));
    }
}
