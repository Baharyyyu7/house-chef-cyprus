<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Models\ContentOpportunity;
use App\Models\ZeroResultSearch;
use App\Services\Seo\ContentGapService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

final class ContentGapIgnoresNoiseSearchesTest extends TestCase
{
    use RefreshDatabase;

    public function test_a_one_off_search_does_not_become_an_editorial_recommendation(): void
    {
        // Eşik yokken tek seferlik test aramaları öneri listesine giriyordu:
        // üretimde beş fırsattan ikisi "functionalities" (1 arama) ve
        // "search term string" (2 arama) idi. Gerçek talepler 18-25 aramada.
        $this->arama('functionalities', 1);

        app(ContentGapService::class)->refresh();

        $this->assertSame(0, $this->fırsatSayisi('functionalities'));
    }

    public function test_a_repeated_search_still_becomes_a_recommendation(): void
    {
        // Daralma motoru işlevsiz bırakmamalı: gerçek talep geçmeli.
        $this->arama('kıbrıs mutfağı', 21);

        app(ContentGapService::class)->refresh();

        $this->assertSame(1, $this->fırsatSayisi('kıbrıs mutfağı'));
    }

    public function test_the_threshold_follows_the_configured_value(): void
    {
        config()->set('seo.audit.minimum_zero_result_searches', 2);

        $this->arama('iki-kere-arandi', 2);

        app(ContentGapService::class)->refresh();

        $this->assertSame(1, $this->fırsatSayisi('iki-kere-arandi'));
    }

    private function arama(string $sorgu, int $adet): void
    {
        ZeroResultSearch::query()->create([
            'normalized_query' => $sorgu,
            'locale' => 'tr',
            'search_count' => $adet,
        ]);
    }

    private function fırsatSayisi(string $sorgu): int
    {
        return ContentOpportunity::query()->where('normalized_key', 'zero-'.$sorgu)->count();
    }
}
