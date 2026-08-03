<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Contracts\Search\RecipeSearchInterface;
use Database\Seeders\HouseChefSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\DB;
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

    public function test_unfiltered_recipe_search_uses_database_pagination_without_loading_analysis_graph(): void
    {
        $this->seed(HouseChefSeeder::class);

        DB::flushQueryLog();
        DB::enableQueryLog();

        $results = app(RecipeSearchInterface::class)->search(['per_page' => 5, 'page' => 1]);

        $this->assertCount(5, $results->items());
        $this->assertSame(50, $results->total());
        $this->assertLessThanOrEqual(8, count(DB::getQueryLog()));
        $this->assertTrue(collect(DB::getQueryLog())->every(
            fn (array $query): bool => ! str_contains($query['query'], 'nutrition_facts')
                && ! str_contains($query['query'], 'recipe_ingredients')
                && ! str_contains($query['query'], 'allergen'),
        ));
    }
}
