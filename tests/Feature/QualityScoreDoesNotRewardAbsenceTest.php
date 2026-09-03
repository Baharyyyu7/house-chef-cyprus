<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Services\DataQualityService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

final class QualityScoreDoesNotRewardAbsenceTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Skor 100'den başlayıp yalnız bulgu oldukça düşüyor. Alerjen, diyet ve
     * operasyonel QA kategorilerini üretecek hiçbir kontrol yok - report()
     * içinde bu türde bir bulgu hiç oluşmuyor - dolayısıyla ceza alamıyor ve
     * sonsuza kadar %100 görünüyorlardı. Panelde "Alerjenler %100" yazıyordu,
     * oysa veritabanında sıfır alerjen eşleşmesi var.
     */
    public function test_a_category_with_no_check_behind_it_reports_no_score(): void
    {
        $skorlar = app(DataQualityService::class)->categoryScores();

        foreach (['allergens', 'dietary', 'operational_confidence'] as $kategori) {
            $this->assertNull($skorlar[$kategori], $kategori.' ölçülmediği hâlde skor bildiriyor.');
        }
    }

    public function test_measured_categories_still_report_a_score(): void
    {
        $skorlar = app(DataQualityService::class)->categoryScores();

        foreach (['content_completeness', 'media', 'nutrition'] as $kategori) {
            $this->assertIsInt($skorlar[$kategori], $kategori.' ölçülüyor ama skor vermiyor.');
        }
    }

    public function test_the_overall_score_ignores_unmeasured_weight(): void
    {
        // Ölçülmeyen üç kategori ağırlığın %22'siydi ve %100 sayıldıkları
        // için genel skoru yukarı çekiyorlardı. Ağırlık artık ölçülenler
        // üzerinden normalize ediliyor.
        $dokum = app(DataQualityService::class)->breakdown();

        $olculenAgirlik = collect($dokum)
            ->forget('overall')
            ->filter(fn (array $satir): bool => $satir['measured'])
            ->sum('weight');

        $this->assertSame(78, $olculenAgirlik, 'Ölçülen ağırlık toplamı değişmiş.');
        $this->assertLessThanOrEqual(100, $dokum['overall']['score']);
    }

    public function test_the_panel_says_unmeasured_instead_of_a_full_bar(): void
    {
        // Panel, tarama hiç koşmadıysa skorları hiç göstermiyor - bu doğru
        // bir davranış ve zaten yerinde. Test o korumayı değil, taranmış
        // durumdaki gösterimi ölçüyor.
        \Illuminate\Support\Facades\Cache::forever('data_quality.last_scanned_at', now()->toIso8601String());

        $icerik = (string) $this->actingAs(\App\Models\User::factory()->create(['role' => 'admin']))
            ->get('/yonetim')->assertOk()->getContent();

        $this->assertStringContainsString('ölçülmedi', $icerik);
    }
}
