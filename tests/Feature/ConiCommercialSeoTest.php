<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Models\User;
use App\Services\Seo\ConiCommercialSeoService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

final class ConiCommercialSeoTest extends TestCase
{
    use RefreshDatabase;

    private function adminUser(): User
    {
        return User::factory()->create(['role' => 'admin', 'email_verified_at' => now()]);
    }

    public function test_service_calculates_mathematical_expected_value_accurately(): void
    {
        $service = app(ConiCommercialSeoService::class);

        $result = $service->evaluateKeyword('cyprus private chef', [
            'volume' => 750,
            'intent' => ConiCommercialSeoService::INTENT_TRANSACTIONAL,
            'product_fit' => ConiCommercialSeoService::FIT_DIRECT,
            'locale' => 'en',
            'conversion_type' => 'lead_private_chef',
        ]);

        $this->assertSame('cyprus private chef', $result['keyword']);
        $this->assertSame(ConiCommercialSeoService::INTENT_TRANSACTIONAL, $result['intent']);
        $this->assertSame(1.0, $result['product_fit']['score']);
        $this->assertGreaterThan(0, $result['expected_clicks_monthly']['mid']);
        $this->assertGreaterThan(0, $result['expected_conversions_monthly']['mid']);
        $this->assertGreaterThan(0, $result['expected_value_monthly_try']['mid']);
        $this->assertGreaterThan(0, $result['expected_value_monthly_eur']['mid']);
        $this->assertGreaterThanOrEqual(70, $result['priority_score']);
    }

    public function test_service_portfolio_summary_aggregates_total_financial_metrics(): void
    {
        $service = app(ConiCommercialSeoService::class);
        $summary = $service->getPortfolioSummary();

        $this->assertGreaterThanOrEqual(10, $summary['total_keywords']);
        $this->assertGreaterThan(5000, $summary['total_volume']);
        $this->assertGreaterThan(10000, $summary['potential_monthly_try']);
        $this->assertGreaterThan(100000, $summary['potential_yearly_try']);
        $this->assertArrayHasKey('TRANSACTIONAL', $summary['intent_distribution']);
        $this->assertNotEmpty($summary['top_opportunities']);
    }

    public function test_artisan_command_evaluates_portfolio_and_single_keyword(): void
    {
        $this->artisan('seo:coni-evaluate', ['--all' => true])
            ->assertSuccessful()
            ->expectsOutputToContain('CONISEOBOT: TİCARİ DEĞER VE GELİR PORTFÖYÜ ANALİZİ');

        $this->artisan('seo:coni-evaluate', [
            'keyword' => 'kıbrıs özel şef',
            '--json' => true,
        ])->assertSuccessful();
    }

    public function test_admin_can_access_coni_bot_workspace(): void
    {
        $admin = $this->adminUser();

        $response = $this->actingAs($admin)->get(route('admin.seo.coni-bot'));
        $response->assertOk();
        $response->assertSee('ConiSEOBot (Ticari Değer)');
        $response->assertSee('Matematiksel Değer Formülü');
    }

    public function test_admin_can_evaluate_custom_keyword_via_post(): void
    {
        $admin = $this->adminUser();

        $response = $this->actingAs($admin)->post(route('admin.seo.coni-bot.evaluate'), [
            'keyword' => 'girne villa catering',
            'locale' => 'tr',
            'volume' => 300,
        ]);

        $response->assertRedirect();
        $this->assertStringContainsString('keyword=girne', (string) $response->headers->get('Location'));
    }

    public function test_guest_and_regular_user_cannot_access_coni_bot(): void
    {
        $this->get(route('admin.seo.coni-bot'))->assertRedirect(route('login'));

        $member = User::factory()->create(['role' => 'user', 'email_verified_at' => now()]);
        $this->actingAs($member)->get(route('admin.seo.coni-bot'))->assertForbidden();
    }
}
