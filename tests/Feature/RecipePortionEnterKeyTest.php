<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Models\Recipe;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

final class RecipePortionEnterKeyTest extends TestCase
{
    use RefreshDatabase;

    public function test_enter_in_the_portion_field_does_not_trigger_the_decrease_button(): void
    {
        // 9 Ekim denetimi: porsiyon alanında Enter, formdaki ilk submit düğmesini
        // ("−", servings_step=-1) tetikliyordu. Varsayılan düğme gizli olmalı.
        $tarif = Recipe::query()->create([
            'title' => 'Porsiyon tarifi', 'slug' => 'porsiyon-tarifi', 'excerpt' => 'Özet.', 'description' => 'Açıklama.', 'servings' => 4,
            'status' => 'published', 'verification_status' => 'verified', 'published_at' => now(),
        ]);

        $html = $this->get(route('recipes.show', $tarif))->assertOk()->getContent();
        $gizli = strpos($html, '<button type="submit" class="sr-only" tabindex="-1" aria-hidden="true">');
        $azalt = strpos($html, 'name="servings_step" value="-1"');

        $this->assertNotFalse($gizli);
        $this->assertNotFalse($azalt);
        $this->assertLessThan($azalt, $gizli, 'Gizli varsayılan düğme, azaltma düğmesinden önce gelmeli.');
    }
}
