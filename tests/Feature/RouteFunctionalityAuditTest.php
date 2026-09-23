<?php

declare(strict_types=1);

namespace Tests\Feature;

use Illuminate\Support\Facades\Route;
use Tests\TestCase;

final class RouteFunctionalityAuditTest extends TestCase
{
    public function test_command_reports_every_registered_route_without_invoking_mutations(): void
    {
        $expected = Route::getRoutes()->count();

        $this->artisan('route:functionality-audit', ['--json' => true, '--no-probe' => true])
            ->expectsOutputToContain('"total":'.$expected)
            ->assertSuccessful();
    }

    public function test_json_rows_contain_the_required_contract(): void
    {
        $this->artisan('route:functionality-audit', ['--json' => true, '--no-probe' => true])
            ->expectsOutputToContain('"method"')
            ->expectsOutputToContain('"surface"')
            ->expectsOutputToContain('"expected_status"')
            ->expectsOutputToContain('"primary_action"')
            ->expectsOutputToContain('"issues"')
            ->assertSuccessful();
    }
}
