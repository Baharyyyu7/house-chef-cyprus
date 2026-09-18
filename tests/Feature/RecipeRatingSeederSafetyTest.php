<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Models\Recipe;
use Database\Seeders\RecipeTestKitchenRatingsSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use LogicException;
use Tests\TestCase;

final class RecipeRatingSeederSafetyTest extends TestCase
{
    use RefreshDatabase;

    public function test_even_forced_seeding_cannot_manufacture_visitor_ratings(): void
    {
        $recipe = Recipe::query()->create([
            'title' => 'Yayla Çorbası',
            'slug' => 'yayla-corbasi-safety',
            'status' => 'published',
            'verification_status' => 'verified',
        ]);

        $blocked = false;
        try {
            $this->artisan('db:seed', [
                '--class' => RecipeTestKitchenRatingsSeeder::class,
                '--force' => true,
            ])->run();
        } catch (LogicException $exception) {
            $blocked = true;
            $this->assertStringContainsString('Synthetic recipe ratings are disabled', $exception->getMessage());
        }

        $this->assertDatabaseCount('recipe_ratings', 0);
        $this->assertSame(['average' => null, 'count' => 0], $recipe->ratingSummary());
        $this->assertTrue($blocked, 'The retired seeder must explicitly reject execution.');
    }
}
