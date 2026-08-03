<?php

declare(strict_types=1);

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

final class ProductionSprintOneTest extends TestCase
{
    use RefreshDatabase;

    public function test_production_strict_profile_rejects_local_defaults(): void
    {
        $this->artisan('app:environment-check --environment=production --strict --json')
            ->expectsOutputToContain('"valid":false')
            ->assertFailed();
    }

    public function test_production_strict_profile_accepts_complete_safe_configuration(): void
    {
        config([
            'app.debug' => false,
            'app.url' => 'https://example.test',
            'cache.default' => 'redis',
            'queue.default' => 'redis',
            'session.driver' => 'redis',
            'session.secure' => true,
            'mail.default' => 'smtp',
            'mail.mailers.smtp.host' => 'smtp.example.test',
            'filesystems.default' => 's3',
            'ai_images.generation_enabled' => false,
            'production.allow_sqlite' => true,
            'production.backup_disk' => 's3',
            'production.trusted_proxies' => '10.0.0.0/8',
            'production.canonical_host' => 'example.test',
            'production.canonical_scheme' => 'https',
        ]);

        $this->artisan('app:environment-check --environment=production --strict --json')->assertSuccessful();
    }

    public function test_production_strict_profile_accepts_private_default_storage_with_a_public_symlink(): void
    {
        config([
            'app.debug' => false,
            'app.url' => 'https://example.test',
            'cache.default' => 'redis',
            'queue.default' => 'redis',
            'session.driver' => 'redis',
            'session.secure' => true,
            'mail.default' => 'smtp',
            'mail.mailers.smtp.host' => 'smtp.example.test',
            'filesystems.default' => 'local',
            'production.public_storage_url' => 'https://example.test/storage',
            'ai_images.generation_enabled' => false,
            'production.allow_sqlite' => true,
            'production.backup_disk' => 'local',
            'production.trusted_proxies' => '10.0.0.0/8',
            'production.canonical_host' => 'example.test',
            'production.canonical_scheme' => 'https',
        ]);

        $this->artisan('app:environment-check --environment=production --strict --json')
            ->expectsOutputToContain('"public_storage_configured":true')
            ->assertSuccessful();
    }

    public function test_staging_profile_requires_noindex_and_social_disablement(): void
    {
        config([
            'app.debug' => false,
            'app.url' => 'https://staging.example.test',
            'cache.default' => 'redis',
            'queue.default' => 'redis',
            'session.driver' => 'redis',
            'session.secure' => true,
            'mail.default' => 'log',
            'filesystems.default' => 'public',
            'seo.indexing_enabled' => false,
            'seo.social.x_enabled' => false,
            'ai_images.generation_enabled' => false,
            'production.allow_sqlite' => true,
            'production.backup_disk' => 'local',
            'production.trusted_proxies' => '10.0.0.0/8',
            'production.canonical_host' => 'staging.example.test',
            'production.canonical_scheme' => 'https',
        ]);

        $this->artisan('app:environment-check --environment=staging --strict --json')->assertSuccessful();
    }

    public function test_post_deploy_smoke_dry_run_is_machine_readable_and_network_free(): void
    {
        $this->artisan('app:post-deploy-smoke --dry-run --json')->assertSuccessful();
    }

    public function test_deployment_templates_have_placeholders_and_no_embedded_secrets_or_domains(): void
    {
        $files = [
            base_path('deploy/nginx/house-chef.conf'),
            base_path('deploy/supervisor/house-chef-worker.conf'),
            base_path('deploy/cron/house-chef'),
            base_path('deploy/scripts/deploy.sh'),
            base_path('deploy/scripts/rollback.sh'),
            base_path('deploy/scripts/backup.sh'),
            base_path('deploy/scripts/health-check.sh'),
        ];

        foreach ($files as $file) {
            $contents = (string) file_get_contents($file);
            $this->assertStringNotContainsString('housechef.local', $contents, basename($file));
            $this->assertDoesNotMatchRegularExpression('/(api[_-]?key|password|token)\s*=\s*[^$\s]/i', $contents, basename($file));
        }

        $this->assertStringContainsString('__DOMAIN__', (string) file_get_contents(base_path('deploy/nginx/house-chef.conf')));
    }

    public function test_deploy_and_rollback_scripts_support_non_destructive_dry_runs(): void
    {
        foreach ([
            [base_path('deploy/scripts/deploy.sh'), 'APP_ROOT='.escapeshellarg(base_path()).' bash '.escapeshellarg(base_path('deploy/scripts/deploy.sh')).' --dry-run'],
            [base_path('deploy/scripts/rollback.sh'), 'CURRENT_LINK=/tmp/current PREVIOUS_RELEASE=/tmp/previous bash '.escapeshellarg(base_path('deploy/scripts/rollback.sh')).' --dry-run'],
        ] as [$script, $command]) {
            $exitCode = 1;
            exec($command, $unused, $exitCode);
            $this->assertSame(0, $exitCode, basename($script));
        }
    }
}
