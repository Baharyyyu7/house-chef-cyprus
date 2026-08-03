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
        $response = $this->withCookie('hc_analytics_consent', 'granted')->get('/')->assertOk();
        $response->assertSee('GTM-ABC123')->assertDontSee('gtag/js?id=G-ABC123');
        $this->assertStringContainsString('https://www.googletagmanager.com', (string) $response->headers->get('Content-Security-Policy'));
    }

    public function test_site_verification_meta_is_production_only(): void
    {
        config()->set('integrations.google.site_verification', 'public-verification-token');
        $this->app->detectEnvironment(fn (): string => 'production');

        $this->get('/')->assertOk()->assertSee('google-site-verification')->assertSee('public-verification-token');
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
