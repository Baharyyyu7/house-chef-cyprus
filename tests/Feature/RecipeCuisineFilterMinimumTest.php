<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Models\Cuisine;
use App\Models\Recipe;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

final class RecipeCuisineFilterMinimumTest extends TestCase
{
    use RefreshDatabase;

    public function test_public_filter_only_lists_cuisines_with_ten_visible_recipes(): void
    {
        $ready = Cuisine::query()->create(['name' => 'Hazır Mutfak', 'slug' => 'hazir-mutfak']);
        $thin = Cuisine::query()->create(['name' => 'İnce Mutfak', 'slug' => 'ince-mutfak']);

        foreach (range(1, 10) as $number) {
            $this->recipe($ready, 'Hazır Tarif '.$number, 'hazir-'.$number);
        }
        foreach (range(1, 9) as $number) {
            $this->recipe($thin, 'İnce Tarif '.$number, 'ince-'.$number);
        }

        $html = (string) $this->get(route('recipes.index'))->assertOk()->getContent();

        $this->assertMatchesRegularExpression('/<option value="hazir-mutfak"[^>]*>Hazır Mutfak<\/option>/', $html);
        $this->assertDoesNotMatchRegularExpression('/<option value="ince-mutfak"[^>]*>İnce Mutfak<\/option>/', $html);
    }

    private function recipe(Cuisine $cuisine, string $title, string $slug): void
    {
        Recipe::query()->create([
            'cuisine_id' => $cuisine->id,
            'title' => $title,
            'slug' => $slug,
            'excerpt' => 'Uygulanabilir ve doğru ölçülerle hazırlanmış tarif özeti.',
            'description' => 'Ev mutfağında uygulanabilecek, açık adımları ve dengeli ölçüleri bulunan ayrıntılı tarif açıklaması.',
            'status' => 'published',
            'verification_status' => 'verified',
            'published_at' => now(),
        ]);
    }
}
