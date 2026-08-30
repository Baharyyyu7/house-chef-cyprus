<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Models\User;
use App\Models\ZeroResultSearch;
use App\Services\Integrity\IntegrityReportLog;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Cache;
use Illuminate\Testing\TestResponse;
use Tests\TestCase;

/**
 * Otonom denetimler panelde görünüyor.
 *
 * Dört muhafız her gece koşuyordu ama sonuçları yalnız log'a gidiyordu:
 * site kendini denetliyordu, kimse göremiyordu. Panel bunu gösterince
 * "eksik görsel 59" bir sayı olmaktan çıkıp bir yön kazanıyor.
 */
final class AdminDashboardShowsAutonomousAuditsTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        Cache::flush();
    }

    public function test_the_panel_shows_the_audit_section(): void
    {
        $this->panel()->assertSee('Sitenin kendini denetlemesi', false);
    }

    public function test_a_guard_that_never_ran_says_so_instead_of_looking_healthy(): void
    {
        // Sessiz bir kart "sorun yok" gibi okunur; en tehlikeli yanlış bu.
        $this->panel()->assertSee('Henüz koşmadı', false);
    }

    public function test_an_improvement_is_shown_as_a_decrease(): void
    {
        $this->kaydet(62);
        $this->kaydet(59);

        $this->panel()
            ->assertSee('59 görselsiz tarif', false)
            ->assertSee('3 azaldı', false);
    }

    public function test_a_regression_is_shown_as_an_increase(): void
    {
        $this->kaydet(59);
        $this->kaydet(64);

        $this->panel()->assertSee('5 arttı', false);
    }

    public function test_a_first_run_does_not_invent_a_comparison(): void
    {
        $this->kaydet(59);

        $this->panel()->assertSee('karşılaştırılacak önceki koşu yok', false);
    }

    public function test_the_panel_turns_zero_result_demand_into_an_actionable_view(): void
    {
        ZeroResultSearch::query()->create([
            'normalized_query' => 'hellim nasıl saklanır',
            'locale' => 'tr',
            'search_count' => 17,
            'last_searched_at' => now(),
        ]);
        ZeroResultSearch::query()->create([
            'normalized_query' => '20 dakikada ne pişirsem',
            'locale' => 'tr',
            'search_count' => 11,
            'last_searched_at' => now(),
            'resolved_at' => now(),
            'resolved_result_count' => 16,
            'suggested_action' => '/sure/20-dakika',
        ]);

        $this->panel()
            ->assertSee('Sonuçsuz arama talepleri', false)
            ->assertSee('hellim nasıl saklanır', false)
            ->assertSee('17 arama', false)
            ->assertSee('20 dakikada ne pişirsem', false)
            ->assertSee('href="'.url('/sure/20-dakika').'"', false);
    }

    private function kaydet(int $sayi): void
    {
        app(IntegrityReportLog::class)->record('media:gap-queue', 'gaps_found', $sayi, ['missing_media' => $sayi]);
    }

    private function panel(): TestResponse
    {
        Cache::flush();

        return $this->actingAs(User::factory()->create(['role' => 'admin', 'email_verified_at' => now()]))
            ->get(route('admin.dashboard'))
            ->assertOk();
    }
}
