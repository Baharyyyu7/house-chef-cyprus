<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Services\ProductionOperationsService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

final class ProductionOperationsTest extends TestCase
{
    use RefreshDatabase;

    public function test_production_operations_commands_have_safe_machine_readable_contracts(): void
    {
        foreach ([
            'app:database-readiness',
            'app:queue-health',
            'app:scheduler-health',
            'app:mail-health --no-send',
            'app:security-readiness',
            'app:release-manifest',
            'app:health',
            'backup:health',
        ] as $command) {
            $this->artisan($command.' --json')->expectsOutputToContain('"')->assertSuccessful();
        }
    }

    public function test_release_manifest_uses_configured_commit_metadata_without_shell_access(): void
    {
        config(['production.commit_sha' => '6546f5a12e9c9601ca9c1f8ab1546854433183ad']);

        $manifest = app(ProductionOperationsService::class)->releaseManifest();

        $this->assertSame('6546f5a12e9c9601ca9c1f8ab1546854433183ad', $manifest['commit_sha']);
    }

    public function test_artifact_and_shared_hosting_scripts_offer_non_destructive_dry_runs(): void
    {
        $root = base_path();
        $commands = [
            'ARTIFACT_OUTPUT_DIR='.escapeshellarg(sys_get_temp_dir().'/house-chef-artifact-test').' bash '.escapeshellarg(base_path('scripts/build-production-artifact.sh')).' --dry-run',
            'APP_ROOT='.escapeshellarg($root).' bash '.escapeshellarg(base_path('scripts/post-deploy-smoke.sh')).' --dry-run',
        ];

        foreach ($commands as $command) {
            $exitCode = 1;
            exec($command, $unused, $exitCode);
            $this->assertSame(0, $exitCode, $command);
        }
    }
}
