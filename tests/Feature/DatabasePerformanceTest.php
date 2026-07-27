<?php

declare(strict_types=1);

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Artisan;
use Tests\TestCase;

final class DatabasePerformanceTest extends TestCase
{
    use RefreshDatabase;

    public function test_performance_profile_has_a_stable_json_contract(): void
    {
        $this->assertSame(0, Artisan::call('performance:profile', ['--json' => true]));
        $payload = json_decode(Artisan::output(), true);

        $this->assertIsArray($payload);
        $this->assertSame(['recipe_listing', 'recipe_search'], array_column($payload['metrics'], 'name'));
    }
}
