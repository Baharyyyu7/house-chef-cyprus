<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Models\IntegrityReport;
use App\Services\Integrity\IntegrityReportLog;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

final class IntegrityCauseLineTest extends TestCase
{
    use RefreshDatabase;

    public function test_a_single_cause_is_named_as_such(): void
    {
        // Panelde "47 engelli tarif" ile "45 görselsiz tarif" yan yana
        // duruyordu ve iki ayrı iş gibi okunuyordu. Oysa 47 engelin 47'si de
        // görsel eksiği: tek iş, iki isim.
        $this->kaydet('content:translation-readiness', 47, ['blockers' => ['onayli_gorsel_yok' => 47]]);

        $this->assertSame(
            'hepsinin sebebi aynı: onaylı görseli yok',
            $this->satir('content:translation-readiness')['sebep']
        );
    }

    public function test_the_largest_cause_is_named_when_there_are_several(): void
    {
        $this->kaydet('content:translation-readiness', 9, ['blockers' => ['aciklama_ince' => 6, 'adim_yok' => 3]]);

        $this->assertSame(
            'en büyüğü: 6 tanesinin açıklaması ince',
            $this->satir('content:translation-readiness')['sebep']
        );
    }

    public function test_the_media_gap_says_how_much_of_it_only_needs_approval(): void
    {
        // Eksiğin bir kısmı yeni görsel değil, yalnız onay istiyor.
        $this->kaydet('media:gap-queue', 45, ['missing_media' => 45, 'awaiting_approval' => 14]);

        $this->assertSame(
            '14 tanesinin görseli hazır, yalnız onay bekliyor',
            $this->satir('media:gap-queue')['sebep']
        );
    }

    public function test_a_guard_without_an_attribution_stays_quiet(): void
    {
        // Sebebi olmayan yere cümle uydurmak sayıyı zayıflatır.
        $this->kaydet('content:link-integrity', 0, ['status' => 'ok']);

        $this->assertNull($this->satir('content:link-integrity')['sebep']);
    }

    /** @param array<string, mixed> $payload */
    private function kaydet(string $guard, int $headline, array $payload): void
    {
        IntegrityReport::query()->create([
            'guard' => $guard, 'status' => 'test', 'headline' => $headline, 'payload' => $payload,
        ]);
    }

    /** @return array<string, mixed> */
    private function satir(string $guard): array
    {
        $satir = collect(app(IntegrityReportLog::class)->summary())->firstWhere('guard', $guard);

        $this->assertNotNull($satir, $guard.' özet satırı yok.');

        return $satir;
    }
}
