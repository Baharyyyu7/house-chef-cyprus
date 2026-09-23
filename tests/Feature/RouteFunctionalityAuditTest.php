<?php

declare(strict_types=1);

namespace Tests\Feature;

use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\File;
use Tests\TestCase;

final class RouteFunctionalityAuditTest extends TestCase
{
    public function test_command_reports_every_registered_route_without_invoking_mutations(): void
    {
        $expected = Route::getRoutes()->count();

        $this->artisan('route:functionality-audit', ['--json' => true, '--no-probe' => true])
            ->expectsOutputToContain('"total": '.$expected)
            ->assertSuccessful();
    }

    public function test_json_rows_contain_the_required_contract(): void
    {
        $relativePath = 'storage/app/testing/route-functionality.json';
        $absolutePath = base_path($relativePath);

        try {
            $this->artisan('route:functionality-audit', ['--output' => $relativePath, '--no-probe' => true])
                ->assertSuccessful();

            $report = json_decode((string) File::get($absolutePath), true, flags: JSON_THROW_ON_ERROR);
            $this->assertArrayHasKey('routes', $report);
            $this->assertNotEmpty($report['routes']);
            foreach (['method', 'surface', 'expected_status', 'primary_action', 'issues'] as $key) {
                $this->assertArrayHasKey($key, $report['routes'][0]);
            }
        } finally {
            File::delete($absolutePath);
        }
    }

    public function test_command_is_inventory_only_unless_probe_is_explicitly_requested(): void
    {
        $expected = Route::getRoutes()->count();

        $this->artisan('route:functionality-audit', ['--json' => true])
            ->expectsOutputToContain('"inventory_only": '.$expected)
            ->assertSuccessful();
    }
}
