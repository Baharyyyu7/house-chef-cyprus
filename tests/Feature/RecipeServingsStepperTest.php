<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Models\Recipe;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

final class RecipeServingsStepperTest extends TestCase
{
    use RefreshDatabase;

    public function test_both_step_directions_change_the_serving_count(): void
    {
        $recipe = $this->publishedRecipe();

        $this->get(route('recipes.show', $recipe).'?servings=6&servings_step=-1')
            ->assertOk()->assertViewHas('servings', 5);

        $this->get(route('recipes.show', $recipe).'?servings=6&servings_step=1')
            ->assertOk()->assertViewHas('servings', 7);
    }

    public function test_stepping_is_clamped_at_both_ends(): void
    {
        $recipe = $this->publishedRecipe();

        $this->get(route('recipes.show', $recipe).'?servings=1&servings_step=-1')
            ->assertOk()->assertViewHas('servings', 1);

        $this->get(route('recipes.show', $recipe).'?servings=99&servings_step=1')
            ->assertOk()->assertViewHas('servings', 99);
    }

    public function test_invalid_serving_input_falls_back_without_error(): void
    {
        $recipe = $this->publishedRecipe();

        foreach (['0', 'abc', '-5', ''] as $value) {
            $this->get(route('recipes.show', $recipe).'?servings='.$value)
                ->assertOk()->assertViewHas('servings', fn (int $servings): bool => $servings >= 1);
        }
    }

    /**
     * Adım butonları input ile aynı adı taşırsa tarayıcı ikisini de gönderir ve
     * DOM sırası yüzünden azalt butonu sessizce etkisiz kalır. Adlar ayrı kalmalı.
     */
    public function test_step_buttons_do_not_share_the_serving_input_name(): void
    {
        $response = $this->get(route('recipes.show', $this->publishedRecipe()));

        $response->assertOk()
            ->assertSee('name="servings_step" value="-1"', false)
            ->assertSee('name="servings_step" value="1"', false)
            ->assertSee('name="servings" value="4"', false)
            ->assertDontSee('type="submit" name="servings"', false);
    }

    private function publishedRecipe(): Recipe
    {
        return Recipe::query()->create([
            'title' => 'Porsiyon Test Tarifi',
            'slug' => 'porsiyon-test-tarifi',
            'status' => 'published',
            'verification_status' => 'verified',
            'servings' => 4,
        ]);
    }
}
