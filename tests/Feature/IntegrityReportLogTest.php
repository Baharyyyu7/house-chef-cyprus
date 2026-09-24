<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Models\IntegrityReport;
use App\Services\Integrity\IntegrityReportLog;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Otonom denetimlerin koşu geçmişi.
 *
 * Muhafızlar günlerdir koşuyordu ama sonuçları yalnız log'a yazıyordu:
 * kimse görmüyordu ve iki koşuyu karşılaştırmak mümkün değildi. "Eksik
 * görsel 59" tek başına bir sayı; "dün 62'ydi" ise ilerleme.
 */
final class IntegrityReportLogTest extends TestCase
{
    use RefreshDatabase;

    public function test_a_guard_that_never_ran_is_not_shown_as_healthy(): void
    {
        // En tehlikeli yanlış bu olurdu: hiç bakılmamış bir denetimi
        // "sorun yok" diye göstermek.
        $satir = $this->satir('media:gap-queue');

        $this->assertSame('hic_kosmadi', $satir['durum']);
        $this->assertArrayNotHasKey('sayi', $satir);
    }

    public function test_the_difference_from_the_previous_run_is_reported(): void
    {
        $this->kaydet('media:gap-queue', 62);
        $this->kaydet('media:gap-queue', 59);

        $satir = $this->satir('media:gap-queue');

        $this->assertSame(59, $satir['sayi']);
        $this->assertSame(62, $satir['onceki_sayi']);
        $this->assertSame(-3, $satir['fark'], 'Azalma iyileşmedir ve negatif görünmeli.');
    }

    public function test_a_first_run_has_nothing_to_compare_against(): void
    {
        $this->kaydet('media:gap-queue', 59);

        $this->assertNull($this->satir('media:gap-queue')['fark']);
    }

    public function test_the_latest_run_wins_over_older_ones(): void
    {
        $this->kaydet('media:gap-queue', 90);
        $this->kaydet('media:gap-queue', 62);
        $this->kaydet('media:gap-queue', 59);

        $satir = $this->satir('media:gap-queue');

        $this->assertSame(59, $satir['sayi']);
        $this->assertSame(62, $satir['onceki_sayi']);
    }

    public function test_guards_do_not_read_each_others_history(): void
    {
        $this->kaydet('media:gap-queue', 59);
        $this->kaydet('content:translation-readiness', 12);

        $this->assertNull($this->satir('media:gap-queue')['fark']);
        $this->assertSame(12, $this->satir('content:translation-readiness')['sayi']);
    }

    public function test_every_known_guard_appears_even_with_no_history(): void
    {
        // Panel eksik bir kart göstermemeli: koşmamış olması da bir durum.
        $this->assertCount(count(IntegrityReportLog::MUHAFIZLAR), $this->gunluk()->summary());
    }

    public function test_bulky_lists_are_not_stored(): void
    {
        // Kuyruklar yüzlerce satır olabiliyor; tabloyu şişirmesinler.
        $this->gunluk()->record('media:gap-queue', 'gaps_found', 59, [
            'missing_media' => 59,
            'queue' => array_fill(0, 200, ['slug' => 'tarif']),
        ]);

        $kayit = IntegrityReport::query()->latest('id')->firstOrFail();

        $this->assertArrayNotHasKey('queue', $kayit->payload);
        $this->assertSame(59, $kayit->payload['missing_media']);
    }

    public function test_old_runs_are_pruned_but_recent_ones_survive(): void
    {
        $this->kaydet('media:gap-queue', 59);
        IntegrityReport::query()->update(['created_at' => now()->subDays(120)]);
        $this->kaydet('media:gap-queue', 55);

        $this->gunluk()->prune(90);

        $this->assertSame(1, IntegrityReport::query()->count());
        $this->assertSame(55, IntegrityReport::query()->firstOrFail()->headline);
    }

    private function kaydet(string $guard, int $sayi): void
    {
        $this->gunluk()->record($guard, 'issues_found', $sayi, ['total' => $sayi]);
    }

    /** @return array<string, mixed> */
    private function satir(string $guard): array
    {
        foreach ($this->gunluk()->summary() as $satir) {
            if ($satir['guard'] === $guard) {
                return $satir;
            }
        }

        $this->fail("Özet {$guard} satırını içermiyor.");
    }

    private function gunluk(): IntegrityReportLog
    {
        return app(IntegrityReportLog::class);
    }
}
