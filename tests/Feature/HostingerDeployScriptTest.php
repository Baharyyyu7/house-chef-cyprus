<?php

declare(strict_types=1);

namespace Tests\Feature;

use Tests\TestCase;

final class HostingerDeployScriptTest extends TestCase
{
    public function test_live_http_smoke_runs_only_after_the_new_release_leaves_maintenance_mode(): void
    {
        $script = $this->script();
        $switch = strpos($script, 'run ln -sfn "$RELEASE_DIR" "$CURRENT_LINK"');
        $up = strpos($script, 'run "$PHP_BIN" "$CURRENT_LINK/artisan" up --no-interaction');
        $smoke = strpos($script, 'run "$PHP_BIN" "$CURRENT_LINK/artisan" app:post-deploy-smoke --json');

        $this->assertIsInt($switch);
        $this->assertIsInt($up);
        $this->assertIsInt($smoke);
        $this->assertLessThan($up, $switch);
        $this->assertLessThan($smoke, $up);
        $this->assertStringNotContainsString('artisan app:post-deploy-smoke --json', substr($script, 0, $switch));
    }

    public function test_failed_post_switch_validation_restores_the_previous_release_and_leaves_maintenance_mode(): void
    {
        $script = $this->script();

        $this->assertStringContainsString('rollback_post_switch_failure()', $script);
        $this->assertStringContainsString('[[ "$SWITCHED" == true && -n "$PREVIOUS_RELEASE"', $script);
        $this->assertStringContainsString('run ln -sfn "$PREVIOUS_RELEASE" "$CURRENT_LINK"', $script);
        $this->assertStringContainsString('run "$PHP_BIN" "$(active_artisan)" up --no-interaction', $script);
    }

    public function test_pre_switch_failures_do_not_change_current_and_public_html_adoption_is_reversible(): void
    {
        $script = $this->script();
        $switch = strpos($script, 'run ln -sfn "$RELEASE_DIR" "$CURRENT_LINK"');
        $migration = strpos($script, 'artisan migrate --force --no-interaction');

        $this->assertIsInt($switch);
        $this->assertIsInt($migration);
        $this->assertLessThan($switch, $migration);
        $this->assertStringContainsString('WEB_ROOT_BACKUP=', $script);
        $this->assertStringContainsString('run mv "$WEB_ROOT" "$WEB_ROOT_BACKUP"', $script);
        $this->assertStringContainsString('run rm -f "$WEB_ROOT"', $script);
        $this->assertStringContainsString('run mv "$WEB_ROOT_BACKUP" "$WEB_ROOT"', $script);
    }

    private function script(): string
    {
        return (string) file_get_contents(base_path('scripts/deploy-hostinger-release.sh'));
    }
}
