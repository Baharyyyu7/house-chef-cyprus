<?php

declare(strict_types=1);

namespace Tests\Feature;

use PHPUnit\Framework\TestCase;

/**
 * Yayın ve geri alma betiklerinin sırası. 24 Eylül 2026'da yanlış daldan
 * yayın siteyi eski tasarıma döndürdü; geri alma betiği duman testini
 * `up`tan önce koşup siteyi bakımda bıraktı; duman testi lsphp yeniden
 * başlatılmadan önce koştuğu için eski kodu ölçtü.
 */
final class ReleaseSafetyOrderTest extends TestCase
{
    private function betik(string $ad): string
    {
        return (string) file_get_contents(dirname(__DIR__, 2).'/scripts/'.$ad);
    }

    private function sira(string $metin, string ...$parcalar): void
    {
        $onceki = -1;
        foreach ($parcalar as $parca) {
            $konum = strpos($metin, $parca, max(0, $onceki));
            $this->assertNotFalse($konum, "Bulunamadı: {$parca}");
            $this->assertGreaterThan($onceki, $konum, "Sıra bozuk: {$parca}");
            $onceki = $konum;
        }
    }

    public function test_release_refuses_a_commit_that_does_not_descend_from_live(): void
    {
        $betik = $this->betik('release.sh');

        $this->assertStringContainsString('merge-base --is-ancestor "$CANLI" "$SHA"', $betik);
        $this->assertStringContainsString('ALLOW_NON_DESCENDANT', $betik);
        $this->assertStringContainsString('ALLOW_BRANCH', $betik);
        $this->assertStringNotContainsString('/Users/kaganbahar', $betik);
        // Ata denetimi derleme ve gönderimden önce.
        $this->sira($betik, 'readlink $APP_HOME/current', 'merge-base --is-ancestor "$CANLI"', 'git worktree add', 'git push');
    }

    public function test_rollback_brings_the_site_up_before_the_smoke_test(): void
    {
        $betik = $this->betik('rollback-hostinger-release.sh');

        $this->assertStringContainsString('trap on_exit EXIT', $betik);
        $this->sira(
            $betik,
            'ln -sfn "$TARGET_RELEASE"',
            'sync_physical_web_root',
            'optimize:clear',
            'config:cache', 'route:cache', 'view:cache', 'event:cache',
            'artisan" up --no-interaction',
            "\nrestart_lsphp",
            'app:post-deploy-smoke',
        );
    }

    public function test_deploy_restarts_lsphp_before_the_smoke_and_checks_the_served_commit(): void
    {
        $betik = $this->betik('deploy-hostinger-release.sh');

        $this->sira($betik, 'artisan" up --no-interaction', "\nrestart_lsphp\n", 'app:post-deploy-smoke --json; then', 'verify_served_commit "$RELEASE_COMMIT"');
    }

    public function test_scripts_parse(): void
    {
        foreach (['release.sh', 'rollback-hostinger-release.sh', 'deploy-hostinger-release.sh'] as $ad) {
            exec('bash -n '.escapeshellarg(dirname(__DIR__, 2).'/scripts/'.$ad).' 2>&1', $cikti, $kod);
            $this->assertSame(0, $kod, $ad.': '.implode("\n", $cikti));
        }
    }
}
