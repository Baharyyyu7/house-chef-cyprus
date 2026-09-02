<?php

declare(strict_types=1);

namespace Tests\Feature;

use Tests\TestCase;

/**
 * Yayın scriptinin korumaları.
 *
 * Bu script, elle yürütülen dokuz adımlık bir prosedürün yerini alıyor. O
 * prosedürün her adımı bir gün içinde en az bir kez yanlış yapıldı ve
 * hiçbiri kod hatası değildi:
 *
 * - detached HEAD'de commit atıldı; push sessizce hiçbir şey yapmadı ve
 *   deploy "unable to read tree" ile durdu
 * - RELEASE_COMMIT elle yazıldı, üç kez yanlış girildi
 * - artefakt alfabetik sıralamayla seçildi, yanlış dosya yüklendi
 * - taze worktree'de `.env` yoktu; on üç test ortam yüzünden kırmızı verdi
 * - başarısız denemenin bıraktığı release dizini sonraki denemeyi
 *   reddettirdi
 *
 * Testler scriptin kaynağına bakıyor: kabuk scripti için davranış testi
 * kurmak gerçek bir sunucu ister, ama korumanın varlığı okunabilir.
 */
final class ReleaseScriptGuardsTest extends TestCase
{
    public function test_it_refuses_a_detached_head(): void
    {
        // Detached HEAD'de atılan commit hiçbir dala bağlı olmaz.
        $this->assertStringContainsString('git branch --show-current', $this->kaynak());
        $this->assertStringContainsString('Detached HEAD', $this->kaynak());
    }

    public function test_the_commit_sha_is_never_typed_by_hand(): void
    {
        $kaynak = $this->kaynak();

        $this->assertStringContainsString('SHA="$(git rev-parse HEAD)"', $kaynak);
        $this->assertStringContainsString('RELEASE_COMMIT=$SHA', $kaynak);
    }

    public function test_it_checks_the_remote_tip_before_deploying(): void
    {
        // İki ajan aynı dala çalışıyor: kendi commit'inden deploy etmek
        // sonradan gelen commit'leri üretimden düşürür.
        $kaynak = $this->kaynak();

        $this->assertStringContainsString('git fetch --quiet origin "$BRANCH"', $kaynak);
        $this->assertStringContainsString('merge-base --is-ancestor', $kaynak);
    }

    public function test_the_clean_copy_gets_an_environment_file(): void
    {
        // Taze worktree'de .env yok; testler APP_KEY olmadan koşamaz.
        $this->assertStringContainsString('cp "$ROOT/.env" "$WORKTREE/.env"', $this->kaynak());
    }

    public function test_the_artifact_is_addressed_by_name_not_by_listing(): void
    {
        // `ls | tail -1` alfabetik sıralar ve yanlış dosyayı seçer.
        $kaynak = $this->kaynak();

        $this->assertStringContainsString('house-chef-cyprus-full-$KISA.tar.gz', $kaynak);
        $this->assertStringNotContainsString('ls -1t build-artifacts', $kaynak);
    }

    public function test_a_stale_release_directory_is_only_removed_when_inactive(): void
    {
        // Aktif sürümü silmek siteyi düşürürdü.
        $kaynak = $this->kaynak();

        $this->assertStringContainsString('readlink -f', $kaynak);
        $this->assertStringContainsString('dizin aktif surum, dokunulmadi', $kaynak);
    }

    public function test_it_restarts_lsphp_and_verifies_the_live_version(): void
    {
        // LiteSpeed eski kodu opcache'te tutuyor; yeniden başlatılmadan
        // deploy "başarılı" görünüp eski sürüm servis edilir.
        $kaynak = $this->kaynak();

        $this->assertStringContainsString('pkill -f lsphp', $kaynak);
        $this->assertStringContainsString('Surum degismedi', $kaynak);
    }

    public function test_tests_run_in_the_clean_copy_and_block_the_release(): void
    {
        $this->assertStringContainsString("Testler gecmedi; yayin durduruldu.", $this->kaynak());
    }

    public function test_a_dry_run_changes_nothing(): void
    {
        $kaynak = $this->kaynak();

        $this->assertStringContainsString('--dry-run', $kaynak);
        $this->assertStringContainsString('DRY_RUN=false', $kaynak);
    }

    public function test_the_clean_copy_also_builds_its_assets(): void
    {
        // Vite manifesti olmadan sayfa render eden her test 500 dönüyor;
        // ilk denemede 350 test bu yüzden kırmızı verdi. `.env` eksikliğiyle
        // birlikte Codex'in deploy'da durmasının iki sebebinden biriydi.
        $this->assertStringContainsString('npm run build', $this->kaynak());
    }

    private function kaynak(): string
    {
        $yol = base_path('scripts/release.sh');

        $this->assertFileExists($yol);

        return (string) file_get_contents($yol);
    }
}
