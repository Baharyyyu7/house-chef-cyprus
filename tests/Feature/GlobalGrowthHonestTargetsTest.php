<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Services\Seo\AutonomousGrowthEngine;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

final class GlobalGrowthHonestTargetsTest extends TestCase
{
    use RefreshDatabase;

    public function test_empty_catalogue_does_not_claim_daily_growth_success(): void
    {
        Cache::flush();
        Http::preventStrayRequests();

        $snapshot = app(AutonomousGrowthEngine::class)->getDailyTargetsSnapshot();

        foreach (['seo', 'geo', 'backlinks'] as $channel) {
            $this->assertSame(0, $snapshot[$channel]['achieved']);
            $this->assertFalse($snapshot[$channel]['target_met']);
        }
        $this->assertFalse($snapshot['all_targets_met']);
        $this->assertFalse($snapshot['geo']['measurement_available']);
        Http::assertNothingSent();
    }
}
