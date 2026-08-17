<?php

declare(strict_types=1);

namespace App\Console\Commands;

use App\Models\AffiliateLink;
use App\Models\DigitalOrder;
use App\Models\DigitalProduct;
use App\Models\TipContribution;
use App\Models\User;
use App\Models\UserSubscription;
use App\Services\Payments\StripeCheckoutService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Storage;

final class MonetizationAudit extends Command
{
    protected $signature = 'monetization:audit
                            {--launch : Canlı tahsilat için sağlayıcı kapılarını da zorunlu tutar}
                            {--json : Makine okunabilir çıktı verir}';

    protected $description = 'Gelir kayıtlarını ve canlı ödeme kapılarını hiçbir veri değiştirmeden denetler.';

    public function handle(StripeCheckoutService $stripe): int
    {
        $tablesReady = collect([
            'user_subscriptions',
            'affiliate_links',
            'digital_products',
            'digital_orders',
            'tip_contributions',
            'stripe_webhook_events',
        ])->every(fn (string $table): bool => Schema::hasTable($table));

        $checks = ['tables_ready' => $tablesReady];
        $metrics = [];

        if ($tablesReady) {
            $metrics = $this->metrics();
            $checks += [
                'premium_entitlements_verified' => $metrics['unverified_premium_users'] === 0,
                'completed_orders_verified' => $metrics['unverified_completed_orders'] === 0,
                'completed_tips_verified' => $metrics['unverified_completed_tips'] === 0,
                'active_products_deliverable' => $metrics['undeliverable_active_products'] === 0,
                'sales_counts_reconciled' => $metrics['sales_count_mismatches'] === 0,
                'active_affiliates_allowlisted' => $metrics['unsafe_active_affiliates'] === 0,
                'no_expired_pending_checkouts' => $metrics['expired_pending_checkouts'] === 0,
            ];
        }

        if ($this->option('launch')) {
            $checks['stripe_checkout_ready'] = $stripe->isReady();
            $checks['stripe_terms_consent'] = (bool) config('services.stripe.require_terms_consent', false);
            $checks['stripe_prices_ready'] = collect(config('monetization.plans', []))
                ->every(fn (mixed $plan): bool => is_array($plan) && str_starts_with((string) ($plan['stripe_price_id'] ?? ''), 'price_'));
        }

        $result = [
            'valid' => ! in_array(false, $checks, true),
            'launch_audit' => (bool) $this->option('launch'),
            'payments_enabled' => (bool) config('monetization.payments_enabled', false),
            'checks' => $checks,
            'metrics' => $metrics,
        ];

        if ($this->option('json')) {
            $this->line((string) json_encode($result, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES));
        } else {
            $this->table(['Kontrol', 'Durum'], collect($checks)->map(
                fn (bool $ok, string $name): array => [$name, $ok ? 'OK' : 'BLOCKER'],
            )->values()->all());
        }

        return $result['valid'] ? self::SUCCESS : self::FAILURE;
    }

    /** @return array<string, int> */
    private function metrics(): array
    {
        $verifiedPremiumUserIds = UserSubscription::query()
            ->where('payment_provider', 'stripe')
            ->where('status', 'active')
            ->whereNotNull('provider_subscription_id')
            ->get()
            ->filter(fn (UserSubscription $subscription): bool => $subscription->isActive() && $subscription->isPaymentVerified())
            ->pluck('user_id')
            ->unique();

        $activeProducts = DigitalProduct::query()->where('is_active', true)->get();
        $allowedHosts = array_map('mb_strtolower', config('monetization.affiliate_allowed_hosts', []));
        $unsafeAffiliates = AffiliateLink::query()->where('is_active', true)->get()->filter(
            function (AffiliateLink $link) use ($allowedHosts): bool {
                $scheme = mb_strtolower((string) parse_url($link->target_url, PHP_URL_SCHEME));
                $host = mb_strtolower((string) parse_url($link->target_url, PHP_URL_HOST));

                return $scheme !== 'https' || $host === '' || ! in_array($host, $allowedHosts, true);
            },
        )->count();

        $salesMismatches = DigitalProduct::query()->get()->filter(function (DigitalProduct $product): bool {
            $verifiedSales = $product->orders()
                ->whereIn('status', ['completed', 'refunded', 'disputed', 'chargeback_lost'])
                ->count();

            return $product->sales_count !== $verifiedSales;
        })->count();

        return [
            'unverified_premium_users' => User::query()->where('is_premium', true)->whereNotIn('id', $verifiedPremiumUserIds)->count(),
            'unverified_completed_orders' => DigitalOrder::query()->where('status', 'completed')
                ->where(fn ($query) => $query->whereNull('checkout_session_id')->orWhereNull('provider_payment_id'))
                ->count(),
            'unverified_completed_tips' => TipContribution::query()->where('status', 'completed')
                ->where(fn ($query) => $query->whereNull('checkout_session_id')->orWhereNull('provider_payment_id'))
                ->count(),
            'undeliverable_active_products' => $activeProducts->filter(
                fn (DigitalProduct $product): bool => ! $product->file_path || ! Storage::disk('local')->exists($product->file_path),
            )->count(),
            'sales_count_mismatches' => $salesMismatches,
            'unsafe_active_affiliates' => $unsafeAffiliates,
            'expired_pending_checkouts' => DigitalOrder::query()->where('status', 'pending')->where('checkout_expires_at', '<=', now())->count()
                + TipContribution::query()->where('status', 'pending')->where('checkout_expires_at', '<=', now())->count(),
        ];
    }
}
