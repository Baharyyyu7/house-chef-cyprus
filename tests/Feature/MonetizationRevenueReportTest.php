<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Models\AffiliateLink;
use App\Models\ContactRequest;
use App\Models\DigitalOrder;
use App\Models\DigitalProduct;
use App\Models\TipContribution;
use App\Models\User;
use App\Models\UserSubscription;
use App\Services\Monetization\RevenueOperationsReport;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use Tests\TestCase;

final class MonetizationRevenueReportTest extends TestCase
{
    use RefreshDatabase;

    public function test_commercial_demand_excludes_spam_and_does_not_recognize_resolved_requests_as_revenue(): void
    {
        foreach (['new', 'resolved', 'spam', 'archived'] as $status) {
            ContactRequest::create([
                'first_name' => 'Test', 'last_name' => 'User', 'email' => 'private@example.com',
                'subject' => 'menu_content', 'message' => 'Private proposal message',
                'status' => $status, 'priority' => 'normal', 'submitted_at' => now(),
                'privacy_consent_at' => now(),
            ]);
        }
        $report = app(RevenueOperationsReport::class)->build();
        $this->assertSame(['total' => 2, 'open' => 1], $report['commercial_demand']['by_subject']['menu_content']);
        $this->assertNull($report['commercial_demand']['recognized_revenue_minor']);
        $this->assertStringNotContainsString('private@example.com', json_encode($report));
        $this->assertSame([], $report['currencies']);
    }

    public function test_it_reports_verified_operational_revenue_without_inventing_affiliate_income(): void
    {
        $user = User::factory()->create();
        $product = DigitalProduct::query()->create([
            'title' => 'Gelir Raporu Rehberi',
            'slug' => 'gelir-raporu-rehberi',
            'description' => 'Test ürünü',
            'price' => 10,
            'currency' => 'EUR',
            'is_active' => true,
        ]);

        $this->order($product->id, 'completed', 10);
        $this->order($product->id, 'refunded', 7.50);
        $this->tip('completed', 5);
        $this->tip('disputed', 3);
        UserSubscription::query()->create([
            'user_id' => $user->id,
            'plan' => 'monthly',
            'amount' => 4.99,
            'currency' => 'EUR',
            'payment_provider' => 'stripe',
            'provider_subscription_id' => 'sub_report_monthly',
            'status' => 'active',
            'started_at' => now(),
            'expires_at' => now()->addMonth(),
            'metadata' => ['verified_by_webhook' => true],
        ]);
        UserSubscription::query()->create([
            'user_id' => $user->id,
            'plan' => 'yearly',
            'amount' => 49.99,
            'currency' => 'EUR',
            'payment_provider' => 'stripe',
            'provider_subscription_id' => 'sub_report_yearly',
            'status' => 'active',
            'started_at' => now(),
            'expires_at' => now()->addYear(),
            'metadata' => ['verified_by_webhook' => true],
        ]);
        AffiliateLink::query()->create([
            'title' => 'Test ortağı',
            'slug' => 'test-ortagi',
            'target_url' => 'https://example.com/product',
            'provider' => 'test',
            'is_active' => true,
            'click_count' => 25,
        ]);

        $report = app(RevenueOperationsReport::class)->build();

        $this->assertSame(1500, data_get($report, 'currencies.EUR.recognized_one_time_minor'));
        $this->assertSame(300, data_get($report, 'currencies.EUR.at_risk_one_time_minor'));
        $this->assertSame(750, data_get($report, 'currencies.EUR.reversed_one_time_minor'));
        $this->assertSame(916, data_get($report, 'currencies.EUR.active_subscription_mrr_minor'));
        $this->assertSame(25, data_get($report, 'affiliate.recorded_clicks'));
        $this->assertNull(data_get($report, 'affiliate.recognized_revenue_minor'));

        $this->artisan('monetization:revenue-report', ['--json' => true])->assertSuccessful();
    }

    private function order(int $productId, string $status, float $amount): void
    {
        DigitalOrder::query()->create([
            'order_uuid' => (string) Str::uuid(),
            'digital_product_id' => $productId,
            'buyer_email' => 'buyer@example.com',
            'amount' => $amount,
            'currency' => 'EUR',
            'payment_provider' => 'stripe',
            'checkout_session_id' => 'cs_'.Str::random(12),
            'provider_payment_id' => 'pi_'.Str::random(12),
            'status' => $status,
            'download_token' => Str::random(48),
        ]);
    }

    private function tip(string $status, float $amount): void
    {
        TipContribution::query()->create([
            'supporter_email' => 'supporter@example.com',
            'amount' => $amount,
            'currency' => 'EUR',
            'payment_provider' => 'stripe',
            'checkout_session_id' => 'cs_'.Str::random(12),
            'provider_payment_id' => 'pi_'.Str::random(12),
            'status' => $status,
        ]);
    }
}
