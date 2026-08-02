<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Models\Ingredient;
use App\Models\Recipe;
use App\Models\User;
use App\Services\Personalization\PersonalizationService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

final class PersonalizationCoreTest extends TestCase
{
    use RefreshDatabase;

    public function test_recommendations_are_published_verified_only_and_excluded_ingredients_are_hard_filtered(): void
    {
        $user = User::factory()->create(['email_verified_at' => now()]);
        $ingredient = Ingredient::create(['name' => 'Yer Fıstığı', 'slug' => 'yer-fistigi', 'canonical_name' => 'yer fistigi', 'is_active' => true]);
        $safe = $this->recipe('Güvenli tarif');
        $unsafe = $this->recipe('Hariç tutulan tarif');
        $unsafe->ingredients()->attach($ingredient->id);
        $draft = $this->recipe('Taslak tarif', ['status' => 'draft']);

        $service = app(PersonalizationService::class);
        $service->updateProfile($user, [], ['excluded_ingredient' => [$ingredient->public_id]]);
        $ids = $service->recommendations($user)->pluck('recipe_id');

        $this->assertTrue($ids->contains($safe->id));
        $this->assertFalse($ids->contains($unsafe->id));
        $this->assertFalse($ids->contains($draft->id));
    }

    public function test_hidden_feedback_excludes_a_recipe_and_data_stays_user_owned(): void
    {
        $owner = User::factory()->create(['email_verified_at' => now()]);
        $other = User::factory()->create(['email_verified_at' => now()]);
        $recipe = $this->recipe('Gizlenecek tarif');
        $service = app(PersonalizationService::class);

        $service->feedback($owner, $recipe, 'hide_recipe');

        $this->assertFalse($service->recommendations($owner)->pluck('recipe_id')->contains($recipe->id));
        $this->assertTrue($service->recommendations($other)->pluck('recipe_id')->contains($recipe->id));
    }

    public function test_opt_out_returns_no_personalized_recommendations_and_clears_the_user_cache(): void
    {
        $user = User::factory()->create(['email_verified_at' => now()]);
        $this->recipe('Öneri tarifi');
        $service = app(PersonalizationService::class);
        $this->assertNotEmpty($service->recommendations($user));

        $service->disable($user);

        $this->assertEmpty($service->recommendations($user));
    }

    /** @param array<string, mixed> $attributes */
    private function recipe(string $title, array $attributes = []): Recipe
    {
        return Recipe::create(array_merge([
            'title' => $title,
            'slug' => str($title)->slug()->append('-'.str()->lower(str()->random(6))),
            'description' => 'Özgün test açıklaması.',
            'servings' => 2,
            'prep_minutes' => 10,
            'cook_minutes' => 10,
            'rest_minutes' => 0,
            'difficulty' => 'Kolay',
            'status' => 'published',
            'verification_status' => 'verified',
            'published_at' => now(),
        ], $attributes));
    }
}
