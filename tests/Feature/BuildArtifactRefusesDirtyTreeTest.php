<?php

declare(strict_types=1);

namespace Tests\Feature;

use Tests\TestCase;

final class BuildArtifactRefusesDirtyTreeTest extends TestCase
{
    private const SCRIPT = 'scripts/build-production-artifact.sh';

    public function test_the_builder_refuses_a_dirty_release_path(): void
    {
        // Arşiv çalışma ağacından paketleniyor ama damga `git rev-parse HEAD`
        // diyor. Ağaç kirliyse commit edilmemiş kod, onu içermeyen bir commit
        // adıyla üretime gider. Bu depoda birden fazla ajan çalışıyor ve bu
        // bir kez gerçekleşti: üretim, commit'lenmemiş bir AI servis dosyasını
        // çalıştırır hale geldi.
        $kaynak = $this->kaynak();

        $this->assertStringContainsString('ALLOW_DIRTY_BUILD', $kaynak);
        $this->assertStringContainsString('git -C "$ROOT" status --porcelain', $kaynak);
    }

    public function test_untracked_files_do_not_block_the_build(): void
    {
        // Takip edilmeyen dosyalar arşive girmiyor; onlara takılmak her
        // derlemeyi durdururdu.
        $this->assertStringContainsString("grep -v '^?? '", $this->kaynak());
    }

    public function test_the_check_covers_every_packaged_path(): void
    {
        // Kontrol edilen liste ile paketlenen liste ayrışırsa boşluk kalır.
        $kaynak = $this->kaynak();

        preg_match('/surum_yollari=\(([^)]+)\)/', $kaynak, $kontrol);
        preg_match('/-cf - ([a-z].*?)\s*\\\\?\s*\|/s', $kaynak, $paket);

        $this->assertNotEmpty($kontrol[1] ?? '');
        $this->assertNotEmpty($paket[1] ?? '');

        $kontrolListesi = preg_split('/\s+/', trim($kontrol[1])) ?: [];
        $paketListesi = preg_split('/\s+/', trim($paket[1])) ?: [];

        foreach ($paketListesi as $yol) {
            $this->assertContains($yol, $kontrolListesi, "'$yol' paketleniyor ama kirlilik kontrolünde yok.");
        }
    }

    public function test_a_dry_run_is_not_blocked_by_a_dirty_tree(): void
    {
        // Kuru koşu hiçbir şey üretmiyor, dolayısıyla kirli ağaç zararsız.
        // Kontrolü kuru koşuda da uygulamak bu depoda sürekli yanlış alarm
        // demekti: üç ajan aynı ağaçta çalışıyor ve birinin commit etmediği
        // dosya, diğerinin kuru koşusunu düşürüyordu.
        $this->assertStringContainsString('"$DRY_RUN" == "false"', $this->kaynak());
    }

    public function test_the_dry_run_exemption_does_not_weaken_the_real_build(): void
    {
        // Muafiyet yalnız kuru koşuya ait: gerçek derleme hâlâ kirli ağacı
        // reddetmeli, yoksa korumanın bir anlamı kalmaz.
        $kaynak = $this->kaynak();

        $this->assertMatchesRegularExpression(
            '/if \[\[ -n "\$kirli" && "\$DRY_RUN" == "false" && "\$\{ALLOW_DIRTY_BUILD:-0\}" != "1" \]\]/',
            $kaynak
        );
    }

    private function kaynak(): string
    {
        $yol = base_path(self::SCRIPT);

        $this->assertFileExists($yol);

        return (string) file_get_contents($yol);
    }
}
