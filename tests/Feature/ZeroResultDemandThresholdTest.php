<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Models\ZeroResultSearch;
use App\Services\Seo\ZeroResultRecheck;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

final class ZeroResultDemandThresholdTest extends TestCase
{
    use RefreshDatabase;

    public function test_a_term_searched_once_is_not_counted_as_demand(): void
    {
        // Üretimde açık kalan üç terim "asdfqwer", "functionalities" ve
        // "search term string" idi: klavye ezmesi, bot ve test dizesi.
        // Panelde "3 karşılanmamış talep" olarak duruyorlardı ve hiçbir
        // zaman sıfırlanamayacaklardı.
        $this->terim('asdfqwer', 2);
        $this->terim('search term string', 1);

        $rapor = app(ZeroResultRecheck::class)->run();

        $this->assertSame(0, $rapor['unmet']);
        $this->assertSame(2, $rapor['below_threshold']);
    }

    public function test_a_term_people_keep_searching_still_counts(): void
    {
        // Eşik gerçek talebi susturmamalı: karşılanmamış aramalar bu
        // sitedeki en net içerik sinyali.
        $this->terim('kokorec tarifi', 12);

        $rapor = app(ZeroResultRecheck::class)->run();

        $this->assertSame(1, $rapor['unmet']);
        $this->assertSame(0, $rapor['below_threshold']);
    }

    public function test_the_silenced_rows_are_still_recorded(): void
    {
        // Eşik satırı siliyor değil, manşetten çıkarıyor. Kanıt kalmalı.
        $this->terim('asdfqwer', 1);

        app(ZeroResultRecheck::class)->run();

        $this->assertDatabaseHas('zero_result_searches', ['normalized_query' => 'asdfqwer']);
    }

    private function terim(string $sorgu, int $adet): void
    {
        ZeroResultSearch::query()->create([
            'normalized_query' => $sorgu,
            'locale' => 'tr',
            'search_count' => $adet,
            'last_searched_at' => now(),
        ]);
    }
}
