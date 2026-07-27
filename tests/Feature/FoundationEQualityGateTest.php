<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Models\Ingredient;
use App\Models\Recipe;
use App\Models\User;
use Database\Seeders\HouseChefSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

final class FoundationEQualityGateTest extends TestCase
{
    use RefreshDatabase;

    public function test_environment_check_has_safe_human_and_json_output(): void
    {
        $this->artisan('app:environment-check')->assertSuccessful();
        $this->artisan('app:environment-check --json')->expectsOutputToContain('"valid":true')->assertSuccessful();
    }

    public function test_seed_is_idempotent_for_primary_demo_records(): void
    {
        $this->seed(HouseChefSeeder::class);
        $before = [User::count(), Recipe::count(), Ingredient::count()];
        $this->seed(HouseChefSeeder::class);

        $this->assertSame($before, [User::count(), Recipe::count(), Ingredient::count()]);
        $this->assertDatabaseCount('recipes', 50);
        $this->assertGreaterThanOrEqual(24, Ingredient::count());
    }

    public function test_clean_install_supporting_scripts_pass_shell_syntax_checks(): void
    {
        foreach (glob(base_path('scripts/*.sh')) ?: [] as $script) {
            $exit = 1;
            exec('bash -n '.escapeshellarg($script), $unused, $exit);
            $this->assertSame(0, $exit, basename($script));
        }
    }
}
