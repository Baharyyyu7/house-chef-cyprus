<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Models\Ingredient;
use App\Models\Recipe;
use App\Models\RecipeIngredient;
use App\Models\Unit;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use Tests\TestCase;

/**
 * 2962 malzeme satırının 409'unda ölçü, birim kodu yerine okunabilir adıyla
 * yazılmıştı ("yemek kaşığı" yerine "yemek_kasigi") ve 16'sında ölçünün yanına
 * parantezli bir not sıkışmıştı. Eşleşmeyen kodda birim hiç basılmaz: tarif
 * sayfasında "1 Pul Biber" görünür ve okuyucu ne kadar koyacağını bilemez.
 */
final class IngredientMeasureCodeRepairTest extends TestCase
{
    use RefreshDatabase;

    public function test_a_readable_name_becomes_the_matching_unit_code(): void
    {
        $satir = $this->line('tatlı kaşığı');

        $this->artisan('recipes:repair-measure-codes', ['--apply' => true])->assertSuccessful();

        $this->assertSame('tatli_kasigi', $satir->fresh()->display_measure_code);
    }

    public function test_a_note_stuck_in_the_measure_moves_to_its_own_field(): void
    {
        $satir = $this->line('gram (Zırh kıyması)');

        $this->artisan('recipes:repair-measure-codes', ['--apply' => true])->assertSuccessful();

        $taze = $satir->fresh();
        $this->assertSame('gram', $taze->display_measure_code);
        $this->assertSame('Zırh kıyması', $taze->display_note);
    }

    public function test_a_valid_code_is_left_alone(): void
    {
        $satir = $this->line('yemek_kasigi');

        $this->artisan('recipes:repair-measure-codes', ['--apply' => true])->assertSuccessful();

        $this->assertSame('yemek_kasigi', $satir->fresh()->display_measure_code);
    }

    public function test_without_apply_nothing_is_written(): void
    {
        $satir = $this->line('çay kaşığı');

        $this->artisan('recipes:repair-measure-codes')->assertSuccessful();

        $this->assertSame('çay kaşığı', $satir->fresh()->display_measure_code);
    }

    public function test_an_unknown_measure_is_reported_rather_than_guessed(): void
    {
        $satir = $this->line('bir tutam kadar bilinmeyen ölçü');

        $this->artisan('recipes:repair-measure-codes', ['--apply' => true])
            ->expectsOutputToContain('çözülemedi')
            ->assertSuccessful();

        $this->assertSame('bir tutam kadar bilinmeyen ölçü', $satir->fresh()->display_measure_code);
    }

    private function line(string $measure): RecipeIngredient
    {
        foreach ([['gram', 'gram'], ['tatli_kasigi', 'tatlı kaşığı'], ['yemek_kasigi', 'yemek kaşığı'], ['cay_kasigi', 'çay kaşığı']] as [$code, $name]) {
            Unit::query()->firstOrCreate(['code' => $code], ['public_id' => (string) Str::ulid(), 'name' => $name, 'unit_type' => 'mass', 'is_active' => true]);
        }

        $recipe = Recipe::query()->create(['title' => 'Ölçü Tarifi', 'slug' => 'olcu-tarifi-'.uniqid(), 'status' => 'published']);
        $ingredient = Ingredient::query()->create(['public_id' => (string) Str::ulid(), 'name' => 'Pul Biber '.uniqid(), 'slug' => 'pul-biber-'.uniqid()]);

        return RecipeIngredient::query()->create([
            'recipe_id' => $recipe->id,
            'ingredient_id' => $ingredient->id,
            'display_quantity' => 1,
            'display_measure_code' => $measure,
            'sort_order' => 1,
        ]);
    }
}
