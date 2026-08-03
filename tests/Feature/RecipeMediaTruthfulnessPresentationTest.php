<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Models\Recipe;
use Database\Seeders\HouseChefSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

final class RecipeMediaTruthfulnessPresentationTest extends TestCase
{
    use RefreshDatabase;

    public function test_branded_fallback_is_never_presented_as_approved_food_photography(): void
    {
        $this->seed(HouseChefSeeder::class);
        $recipe = Recipe::query()->where('status', 'published')->firstOrFail();

        $this->get(route('recipes.index'))
            ->assertOk()
            ->assertSee('Fotoğraf incelemede')
            ->assertSee('İnceleme sürüyor');
        $this->get(route('recipes.show', $recipe))
            ->assertOk()
            ->assertSee('Onaylı yemek fotoğrafı henüz yok')
            ->assertSee('yer tutucu görsel yemek fotoğrafı olarak gösterilmez')
            ->assertDontSee('✓ Kayıt doğrulandı');
    }
}
