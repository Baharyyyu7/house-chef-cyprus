<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Services\CatalogueReviewReadinessService;
use Database\Seeders\HouseChefSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

final class CatalogueReviewReadinessTest extends TestCase
{
    use RefreshDatabase;

    public function test_readiness_report_is_read_only_and_never_infers_approved_media(): void
    {
        $this->seed(HouseChefSeeder::class);

        $report = app(CatalogueReviewReadinessService::class)->report();
        $this->assertSame(0, $report['media']['approved_recipe_media']);
        $this->assertGreaterThanOrEqual(0, $report['media']['placeholder_recipe_media']);
        $this->assertFalse($report['auto_publish']);
        $this->assertFalse($report['auto_verify']);
        $this->assertTrue($report['human_approval_required']);

        $this->artisan('content:catalogue-review-readiness', ['--json' => true])
            ->expectsOutputToContain('"approved_recipe_media":0')
            ->assertSuccessful();
    }
}
