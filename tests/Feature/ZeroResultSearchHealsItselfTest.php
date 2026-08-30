<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Models\ContentOpportunity;
use App\Models\Ingredient;
use App\Models\Recipe;
use App\Models\ZeroResultSearch;
use App\Services\Seo\ContentGapService;
use App\Services\Seo\ZeroResultRecheck;
use App\Services\Seo\ZeroResultSearchService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use Tests\TestCase;

/**
 * Sıfır sonuçlu arama kaydı kendini düzeltmeliydi, düzeltmiyordu.
 *
 * Kayıt yalnız sonuç sıfırken yazılıyor, bir daha hiç kapanmıyordu.
 * Üretimde sonuç şuydu: arama düzeltildikten sonra "kolay tarifler" 24
 * tarif dönüyordu ama kayıt hâlâ sıfır diyor ve içerik üretim sistemi o
 * konuda rehber yazılmasını istemeye devam ediyordu. Site kendi kendisiyle
 * çelişiyordu.
 */
final class ZeroResultSearchHealsItselfTest extends TestCase
{
    use RefreshDatabase;

    public function test_a_term_that_now_returns_results_is_closed_on_the_next_search(): void
    {
        $this->kayit()->record('hellim', 0);

        $this->kayit()->record('hellim', 4);

        $satir = ZeroResultSearch::query()->firstOrFail();
        $this->assertNotNull($satir->resolved_at);
        $this->assertSame(4, $satir->resolved_result_count);
    }

    public function test_the_demand_signal_itself_is_kept(): void
    {
        // Satır silinmiyor: "bu terim 24 kez arandı" bilgisi karşılanmış
        // olsa da içerik kararları için değerli.
        $this->kayit()->record('hellim', 0);
        $this->kayit()->record('hellim', 0);
        $this->kayit()->record('hellim', 4);

        $this->assertSame(2, ZeroResultSearch::query()->firstOrFail()->search_count);
    }

    public function test_a_term_that_goes_empty_again_reopens(): void
    {
        // Tarif yayından kalkarsa talep yeniden karşılanmamıştır.
        $this->kayit()->record('hellim', 0);
        $this->kayit()->record('hellim', 4);

        $this->kayit()->record('hellim', 0);

        $this->assertNull(ZeroResultSearch::query()->firstOrFail()->resolved_at);
    }

    public function test_the_recheck_closes_records_without_waiting_for_a_visitor(): void
    {
        // Asıl sorun buydu: kapanma birinin o terimi tekrar aramasına
        // bağlıydı. Kimse aramazsa bayat satır sonsuza dek kalıyordu.
        $this->tarif('Hellimli Gözleme');
        ZeroResultSearch::query()->create(['normalized_query' => 'hellimli', 'search_count' => 24, 'last_searched_at' => now()]);

        $rapor = app(ZeroResultRecheck::class)->run();

        $this->assertSame(1, $rapor['resolved']);
        $this->assertSame(0, $rapor['unmet']);
        $this->assertNotNull(ZeroResultSearch::query()->firstOrFail()->resolved_at);
    }

    public function test_genuinely_unmet_demand_stays_open(): void
    {
        ZeroResultSearch::query()->create(['normalized_query' => 'karşılığı olmayan terim', 'search_count' => 9, 'last_searched_at' => now()]);

        $rapor = app(ZeroResultRecheck::class)->run();

        $this->assertSame(0, $rapor['resolved']);
        $this->assertSame(1, $rapor['unmet']);
        $this->assertNull(ZeroResultSearch::query()->firstOrFail()->resolved_at);
    }

    public function test_a_closed_record_stops_generating_content_work(): void
    {
        // Somut zarar buydu: karşılanmış talep için içerik görevi üretmek.
        $this->tarif('Hellimli Gözleme');
        ZeroResultSearch::query()->create(['normalized_query' => 'hellimli', 'search_count' => 24, 'last_searched_at' => now()]);
        app(ZeroResultRecheck::class)->run();

        app(ContentGapService::class)->refresh();

        $this->assertFalse(
            ContentOpportunity::query()->where('normalized_key', 'zero-hellimli')->exists(),
            'Karşılanmış talep için içerik görevi üretilmemeli.'
        );
    }

    private function kayit(): ZeroResultSearchService
    {
        return app(ZeroResultSearchService::class);
    }

    private function tarif(string $baslik): Recipe
    {
        $tarif = Recipe::query()->create([
            'title' => $baslik,
            'slug' => Str::slug($baslik).'-'.uniqid(),
            'excerpt' => 'Özet.',
            'description' => 'Açıklama.',
            'status' => 'published',
            'verification_status' => 'verified',
            'published_at' => now(),
        ]);

        $malzeme = Ingredient::query()->firstOrCreate(['name' => 'Hellim'], ['slug' => 'hellim-'.uniqid()]);
        $tarif->recipeIngredients()->create(['ingredient_id' => $malzeme->id, 'display_quantity' => 1, 'display_measure_code' => 'g', 'sort_order' => 1]);

        return $tarif;
    }
}
