<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

final class IntegrationReadinessTest extends TestCase
{
    use RefreshDatabase;

    public function test_google_tags_are_absent_when_credentials_are_not_configured(): void
    {
        config()->set('integrations.google.analytics.enabled', false);
        config()->set('integrations.google.tag_manager.enabled', false);

        $this->get('/')->assertOk()->assertDontSee('googletagmanager.com');
    }

    public function test_gtm_prevents_direct_ga_double_tagging(): void
    {
        config()->set('integrations.google.tag_manager.enabled', true);
        config()->set('integrations.google.tag_manager.container_id', 'GTM-ABC123');
        config()->set('integrations.google.analytics.enabled', true);
        config()->set('integrations.google.analytics.measurement_id', 'G-ABC123');

        $this->get('/')->assertOk()->assertDontSee('GTM-ABC123')->assertSee('Analitik tercihiniz');
        $response = $this->withUnencryptedCookie('hc_analytics_consent', 'granted')->get('/')->assertOk();
        $response->assertSee('GTM-ABC123')->assertDontSee('gtag/js?id=G-ABC123');
        $response->assertSee("'analytics_storage':'granted'", false)
            ->assertSee("'ad_storage':'denied'", false);
        $this->assertStringContainsString('https://www.googletagmanager.com', (string) $response->headers->get('Content-Security-Policy'));
    }

    public function test_direct_ga_collects_page_views_only_after_explicit_analytics_consent(): void
    {
        config()->set('integrations.google.tag_manager.enabled', false);
        config()->set('integrations.google.analytics.enabled', true);
        config()->set('integrations.google.analytics.measurement_id', 'G-ABC123');

        $this->get('/')->assertOk()->assertDontSee('gtag/js?id=G-ABC123');
        $this->withUnencryptedCookie('hc_analytics_consent', 'granted')->get('/')
            ->assertOk()
            ->assertSee('gtag/js?id=G-ABC123')
            ->assertSee("'analytics_storage':'granted'", false)
            ->assertDontSee("'send_page_view':false", false);
    }

    public function test_site_verification_meta_is_production_only(): void
    {
        config()->set('integrations.google.site_verification', 'public-verification-token');
        $this->app->detectEnvironment(fn (): string => 'production');

        $this->get('/')->assertOk()->assertSee('google-site-verification')->assertSee('public-verification-token');
    }

    public function test_adsense_verification_meta_is_production_only_and_requires_a_valid_publisher_id(): void
    {
        config()->set('integrations.google.adsense.verification_meta_enabled', true);
        config()->set('integrations.google.adsense.client_id', 'ca-pub-6871524033163016');
        $this->app->detectEnvironment(fn (): string => 'production');

        $this->get('/')->assertOk()
            ->assertSee('google-adsense-account')
            ->assertSee('ca-pub-6871524033163016');

        config()->set('integrations.google.adsense.client_id', 'not-a-publisher-id');
        $this->get('/')->assertOk()->assertDontSee('google-adsense-account');
    }

    public function test_adsense_never_loads_from_analytics_consent_or_without_explicit_advertising_consent(): void
    {
        config()->set('integrations.google.adsense.enabled', true);
        config()->set('integrations.google.adsense.client_id', 'ca-pub-6871524033163016');

        $analyticsOnly = $this->withUnencryptedCookie('hc_analytics_consent', 'granted')
            ->withUnencryptedCookie('hc_advertising_consent', 'denied')
            ->get('/')
            ->assertOk();
        $this->assertStringNotContainsString('<script async src="https://pagead2.googlesyndication.com/', $analyticsOnly->getContent());

        $response = $this->withUnencryptedCookie('hc_advertising_consent', 'granted')->get('/')->assertOk();
        $response->assertSee('pagead2.googlesyndication.com')
            ->assertSee('ca-pub-6871524033163016');
        $this->assertStringContainsString('https://pagead2.googlesyndication.com', (string) $response->headers->get('Content-Security-Policy'));
    }

    public function test_ads_txt_contains_the_exact_authorized_seller_line(): void
    {
        $adsTxt = file_get_contents(public_path('ads.txt'));

        $this->assertIsString($adsTxt);
        $this->assertSame("google.com, pub-6871524033163016, DIRECT, f08c47fec0942fa0\n", $adsTxt);
    }

    public function test_integration_center_is_admin_only_and_never_displays_secrets(): void
    {
        config()->set('integrations.google.auth.client_secret', 'must-not-leak');
        $admin = User::factory()->create(['role' => 'admin', 'email_verified_at' => now()]);
        $member = User::factory()->create(['role' => 'member', 'email_verified_at' => now()]);

        $this->actingAs($admin)->get(route('admin.integrations.index'))
            ->assertOk()->assertSee('READY_NOT_CONNECTED')->assertSee('NOT_AVAILABLE')->assertDontSee('must-not-leak');
        $this->actingAs($member)->get(route('admin.integrations.index'))->assertForbidden();
    }
}
