<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Models\Ingredient;
use App\Models\Recipe;
use App\Models\RecipeIngredient;
use App\Services\Measurements\RecipeIngredientDisplayService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use Tests\TestCase;

/**
 * Canlıda "6 adet orta boy sakız kabağı, her biri yaklaşık 200 g Kabak":
 * Türkçede not adın önüne konuyordu ve satırın altında bir kez daha
 * basılıyordu. Yayındaki 108 notun 66'sı virgüllü ya da cümleli.
 */
final class TurkishIngredientNoteTest extends TestCase
{
    use RefreshDatabase;

    public function test_note_follows_the_name_after_a_comma(): void
    {
        $kabak = Ingredient::query()->create(['name' => 'Kabak', 'slug' => 'kabak', 'is_active' => true]);
        $satir = (new RecipeIngredient(['display_quantity' => '6', 'preparation_note' => 'orta boy sakız kabağı, her biri yaklaşık 200 g']))->setRelation('ingredient', $kabak);

        app()->setLocale('tr');

        $this->assertSame('6 Kabak, orta boy sakız kabağı, her biri yaklaşık 200 g', app(RecipeIngredientDisplayService::class)->forRecipe([$satir], 4, 4)[0]['formatted']);
    }

    public function test_recipe_page_prints_the_note_once(): void
    {
        $tarif = Recipe::query()->create([
            'title' => 'Notlu Tarif', 'slug' => 'notlu-tarif-'.Str::lower(Str::random(5)), 'excerpt' => 'Özet.', 'description' => 'Açıklama.',
            'difficulty' => 'easy', 'servings' => 4, 'prep_minutes' => 10, 'cook_minutes' => 20,
            'status' => 'published', 'verification_status' => 'verified', 'published_at' => now(),
        ]);
        $sogan = Ingredient::query()->create(['name' => 'Soğan', 'slug' => 'sogan', 'is_active' => true]);
        $tarif->recipeIngredients()->create(['ingredient_id' => $sogan->id, 'display_quantity' => '1', 'preparation_note' => 'kabuğu soyulup rendelenmiş', 'sort_order' => 1]);

        $icerik = (string) $this->get(route('recipes.show', $tarif))->assertOk()->getContent();

        $this->assertStringContainsString('1 Soğan, kabuğu soyulup rendelenmiş', $icerik);
        $this->assertSame(0, substr_count($icerik, '>kabuğu soyulup rendelenmiş<'), 'Not satırın altında ikinci kez basılmamalı.');
    }
}
