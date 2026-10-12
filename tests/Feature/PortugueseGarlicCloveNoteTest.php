<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Models\ContentTranslation;
use App\Models\Ingredient;
use App\Models\RecipeIngredient;
use App\Models\Unit;
use App\Services\Measurements\RecipeIngredientDisplayService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use Tests\TestCase;

/** Canlıda "3 dentes de alho, ralado": not sarımsağa uyuyordu, dişe uymalı. */
final class PortugueseGarlicCloveNoteTest extends TestCase
{
    use RefreshDatabase;

    public function test_note_agrees_with_the_clove_count(): void
    {
        Unit::query()->create(['public_id' => (string) Str::ulid(), 'code' => 'dis', 'name' => 'diş', 'singular_name' => 'diş', 'unit_type' => 'household', 'supports_fractions' => true, 'precision' => 2, 'is_active' => true, 'is_user_visible' => true]);
        $alho = Ingredient::query()->create(['name' => 'Sarımsak', 'slug' => 'sarimsak', 'is_active' => true]);
        ContentTranslation::query()->create(['translatable_type' => $alho->getMorphClass(), 'translatable_id' => $alho->id, 'locale' => 'pt', 'field' => 'name', 'value' => 'alho']);
        $satir = fn (string $miktar): RecipeIngredient => (new RecipeIngredient(['display_quantity' => $miktar, 'display_measure_code' => 'dis', 'preparation_note' => 'rendelenmiş']))->setRelation('ingredient', $alho);

        app()->setLocale('pt');
        $servis = app(RecipeIngredientDisplayService::class);

        $this->assertSame('3 dentes de alho, ralados', $servis->forRecipe([$satir('3')], 4, 4)[0]['formatted']);
        $this->assertSame('1 dente de alho, ralado', $servis->forRecipe([$satir('1')], 4, 4)[0]['formatted']);
    }
}
