<?php

declare(strict_types=1);

namespace App\Services\Monetization;

use App\Models\AffiliateLink;
use App\Models\DigitalOrder;
use App\Models\TipContribution;
use App\Models\UserSubscription;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;

final class RevenueOperationsReport
{
    /** @return array<string, mixed> */
    public function build(): array
    {
        $verifiedActiveSubscriptions = UserSubscription::query()->get()->filter(
            fn (UserSubscription $subscription): bool => $subscription->isActive() && $subscription->isPaymentVerified(),
        );
        $currencies = collect()
            ->merge(DigitalOrder::query()->distinct()->pluck('currency'))
            ->merge(TipContribution::query()->distinct()->pluck('currency'))
            ->merge(UserSubscription::query()->distinct()->pluck('currency'))
            ->filter()
            ->map(fn (mixed $currency): string => mb_strtoupper((string) $currency))
            ->unique()
            ->sort()
            ->values();

        return [
            'generated_at' => now()->toIso8601String(),
            'scope' => 'verified_operational_state',
            'accounting_note' => 'Bu görünüm muhasebe ekstresi değildir; abonelik yenileme geçmişi ayrı bir ödeme defteri olmadan hesaplanamaz.',
            'currencies' => $currencies->mapWithKeys(
                fn (string $currency): array => [$currency => $this->currencyMetrics($currency)],
            )->all(),
            'affiliate' => [
                'active_links' => AffiliateLink::query()->where('is_active', true)->count(),
                'recorded_clicks' => (int) AffiliateLink::query()->sum('click_count'),
                'recognized_revenue_minor' => null,
                'note' => 'Komisyon mutabakat verisi olmadığı için tıklamalar gelire çevrilmez.',
            ],
            'funnel' => [
                'orders_pending' => DigitalOrder::query()->where('status', 'pending')->count(),
                'orders_processing' => DigitalOrder::query()->where('status', 'processing')->count(),
                'orders_completed' => DigitalOrder::query()->where('status', 'completed')->count(),
                'tips_pending' => TipContribution::query()->where('status', 'pending')->count(),
                'tips_processing' => TipContribution::query()->where('status', 'processing')->count(),
                'tips_completed' => TipContribution::query()->where('status', 'completed')->count(),
                'subscriptions_active' => $verifiedActiveSubscriptions->count(),
                'subscriptions_at_risk' => UserSubscription::query()->whereIn('status', ['past_due', 'disputed'])->count(),
            ],
        ];
    }

    /** @return array<string, int> */
    private function currencyMetrics(string $currency): array
    {
        $orders = DigitalOrder::query()
            ->whereRaw('UPPER(currency) = ?', [$currency])
            ->whereNotNull('checkout_session_id')
            ->whereNotNull('provider_payment_id');
        $tips = TipContribution::query()
            ->whereRaw('UPPER(currency) = ?', [$currency])
            ->whereNotNull('checkout_session_id')
            ->whereNotNull('provider_payment_id');
        $subscriptions = UserSubscription::query()->whereRaw('UPPER(currency) = ?', [$currency]);
        $activeSubscriptions = (clone $subscriptions)->get()->filter(
            fn (UserSubscription $subscription): bool => $subscription->isActive() && $subscription->isPaymentVerified(),
        );

        return [
            'recognized_one_time_minor' => $this->sumMinor($this->cloneWithStatuses($orders, ['completed']))
                + $this->sumMinor($this->cloneWithStatuses($tips, ['completed'])),
            'at_risk_one_time_minor' => $this->sumMinor($this->cloneWithStatuses($orders, ['disputed']))
                + $this->sumMinor($this->cloneWithStatuses($tips, ['disputed'])),
            'reversed_one_time_minor' => $this->sumMinor($this->cloneWithStatuses($orders, ['refunded', 'chargeback_lost']))
                + $this->sumMinor($this->cloneWithStatuses($tips, ['refunded', 'chargeback_lost'])),
            'active_subscription_mrr_minor' => $this->subscriptionMrrMinor($activeSubscriptions),
            'active_subscription_count' => $activeSubscriptions->count(),
        ];
    }

    /** @param Builder<DigitalOrder>|Builder<TipContribution> $query @param list<string> $statuses */
    private function cloneWithStatuses(Builder $query, array $statuses): Builder
    {
        return (clone $query)->whereIn('status', $statuses);
    }

    /** @param Builder<DigitalOrder>|Builder<TipContribution> $query */
    private function sumMinor(Builder $query): int
    {
        return $query->pluck('amount')->sum(
            fn (mixed $amount): int => (int) round((float) $amount * 100),
        );
    }

    /** @param Collection<int, UserSubscription> $active */
    private function subscriptionMrrMinor(Collection $active): int
    {
        return (int) round($active->sum(function (UserSubscription $subscription): float {
            $minor = (float) $subscription->amount * 100;

            return $subscription->plan === 'yearly' ? $minor / 12 : $minor;
        }));
    }
}
