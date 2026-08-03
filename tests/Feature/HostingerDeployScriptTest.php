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
        $this->assertStringContainsString('prepare_hostinger_web_root || true', $script);
        $this->assertStringContainsString('run "$PHP_BIN" "$(active_artisan)" up --no-interaction', $script);
    }

    public function test_pre_switch_failures_do_not_change_current_and_hostinger_uses_a_physical_web_root(): void
    {
        $script = $this->script();
        $switch = strpos($script, 'run ln -sfn "$RELEASE_DIR" "$CURRENT_LINK"');
        $migration = strpos($script, 'artisan migrate --force --no-interaction');

        $this->assertIsInt($switch);
        $this->assertIsInt($migration);
        $this->assertLessThan($switch, $migration);
        $this->assertStringContainsString('ensure_physical_web_root()', $script);
        $this->assertStringContainsString('tar --exclude=\'./storage\' -cf - .', $script);
        $this->assertStringContainsString("__DIR__.'/../current/vendor/autoload.php'", $script);
        $this->assertStringContainsString("__DIR__.'/../current/bootstrap/app.php'", $script);
        $this->assertStringContainsString('HOSTINGER_WEB_ROOT_DIAGNOSTIC', $script);
    }

    public function test_public_html_sync_recreates_storage_without_deleting_hostinger_hidden_files(): void
    {
        $script = $this->script();

        $this->assertStringContainsString('tar copies dotfiles too, does not delete Hostinger-owned hidden files', $script);
        $this->assertStringContainsString('Preserving existing public_html/storage', $script);
        $this->assertStringContainsString('ln -s "$SHARED_DIR/storage/app/public" "$storage_link"', $script);
        $this->assertStringContainsString('test -f "$WEB_ROOT/.htaccess"', $script);
        $this->assertStringContainsString('test -f "$WEB_ROOT/build/manifest.json"', $script);
    }

    public function test_failed_public_smoke_emits_hostinger_diagnostics_before_rollback(): void
    {
        $script = $this->script();
        $smoke = strpos($script, 'app:post-deploy-smoke --json');
        $diagnostic = strpos($script, 'hostinger_404_diagnostic');

        $this->assertIsInt($smoke);
        $this->assertIsInt($diagnostic);
        $this->assertStringContainsString("fail 'HOSTINGER_POST_SWITCH_SMOKE_FAILED'", $script);
        $this->assertGreaterThan($smoke, strrpos($script, 'hostinger_404_diagnostic'));
    }

    private function script(): string
    {
        return (string) file_get_contents(base_path('scripts/deploy-hostinger-release.sh'));
    }
}
