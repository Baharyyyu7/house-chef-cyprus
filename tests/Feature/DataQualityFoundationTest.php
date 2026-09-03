<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Models\DataQualityIssue;
use App\Models\Ingredient;
use App\Models\IngredientAlias;
use App\Models\IngredientSubstitution;
use App\Models\Recipe;
use App\Services\DataQualityService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use Tests\TestCase;

final class DataQualityFoundationTest extends TestCase
{
    use RefreshDatabase;

    public function test_scan_updates_existing_issue_and_then_resolves_it(): void
    {
        $recipe = $this->recipe(['description' => null]);
        $service = app(DataQualityService::class);
        $service->scan();
        $issue = DataQualityIssue::where('issue_type', 'missing_description')->firstOrFail();
        $firstDetected = $issue->first_detected_at;

        $service->scan();
        $this->assertSame(1, DataQualityIssue::where('issue_type', 'missing_description')->count());
        $this->assertTrue($issue->fresh()->first_detected_at->equalTo($firstDetected));

        $recipe->update(['description' => 'Tamamlandı.']);
        $service->scan();
        $this->assertSame('resolved', $issue->fresh()->resolution_status);
    }

    public function test_quality_report_detects_alias_substitution_and_negative_quantity_problems(): void
    {
        $ingredient = Ingredient::create(['name' => 'Domates', 'canonical_name' => 'Domates', 'slug' => 'domates', 'is_active' => true]);
        $otherIngredient = Ingredient::create(['name' => 'Biber', 'canonical_name' => 'Biber', 'slug' => 'biber', 'is_active' => true]);
        IngredientAlias::create(['ingredient_id' => $ingredient->id, 'alias' => 'Kiraz', 'normalized_alias' => 'kiraz', 'locale' => 'tr']);
        IngredientAlias::create(['ingredient_id' => $otherIngredient->id, 'alias' => 'Kiraz domates', 'normalized_alias' => 'kiraz', 'locale' => 'tr']);
        IngredientSubstitution::create(['ingredient_id' => $ingredient->id, 'substitute_ingredient_id' => $ingredient->id, 'substitution_type' => 'test', 'is_active' => true]);
        $recipe = $this->recipe();
        $recipe->recipeIngredients()->create(['ingredient_id' => $ingredient->id, 'quantity' => '-1', 'unit' => 'g', 'sort_order' => 1]);

        $types = collect(app(DataQualityService::class)->report())->pluck('issue_type');
        $this->assertTrue($types->contains('duplicate_ingredient_alias'));
        $this->assertTrue($types->contains('self_reference_substitution'));
        $this->assertTrue($types->contains('negative_recipe_ingredient_quantity'));
    }

    public function test_category_scores_apply_configured_severity_penalties(): void
    {
        DataQualityIssue::create(['issue_type' => 'missing_recipe_title', 'severity' => 'critical', 'entity_type' => Recipe::class, 'entity_public_id' => (string) Str::ulid(), 'message' => 'Eksik', 'detected_at' => now(), 'first_detected_at' => now(), 'last_detected_at' => now(), 'resolution_status' => 'open']);
        $scores = app(DataQualityService::class)->categoryScores();

        // 96 -> 95: genel skor artık yalnız ölçülen kategoriler üzerinden
        // normalize ediliyor. Alerjen, diyet ve operasyonel QA'yı üretecek
        // hiçbir kontrol yok; ceza alamadıkları için %100 sayılıp ortalamayı
        // yukarı çekiyorlardı. Ölçülen ağırlık 78 olduğundan aynı ceza daha
        // görünür oluyor: (75*16 + 100*62) / 78 = 94,87.
        $this->assertSame(95, $scores['overall']);
        $this->assertSame(75, $scores['content_completeness']);
        $this->assertSame(100, $scores['nutrition']);
    }

    public function test_report_and_scan_commands_have_machine_readable_contracts(): void
    {
        $this->recipe();
        $this->artisan('data:quality-scan')->assertSuccessful();
        $this->artisan('data:quality-report --json')->expectsOutputToContain('"category_scores"')->assertSuccessful();
        $this->artisan('data:quality-report')->expectsOutputToContain('Kalite skoru:')->assertSuccessful();
    }

    private function recipe(array $attributes = []): Recipe
    {
        return Recipe::create(array_merge(['title' => 'Kalite tarif', 'slug' => 'kalite-tarif-'.Str::lower(Str::random(8)), 'description' => 'Açıklama', 'servings' => 2, 'prep_minutes' => 1, 'cook_minutes' => 1, 'rest_minutes' => 0, 'difficulty' => 'Kolay', 'status' => 'published'], $attributes));
    }
}
