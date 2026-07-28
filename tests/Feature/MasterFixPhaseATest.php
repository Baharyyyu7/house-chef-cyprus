<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Models\DataQualityIssue;
use App\Models\Ingredient;
use App\Models\IngredientUnitConversion;
use App\Models\Nutrient;
use App\Models\Recipe;
use App\Models\User;
use App\Services\DataQualityService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

final class MasterFixPhaseATest extends TestCase
{
    use RefreshDatabase;

    public function test_placeholder_media_and_low_confidence_conversion_lower_explainable_scores(): void
    {
        $recipe = $this->recipe();
        $recipe->media()->create(['kind' => 'image', 'disk' => 'public', 'path' => 'placeholders/test.svg', 'visibility' => 'public', 'is_placeholder' => true]);
        $ingredient = Ingredient::create(['name' => 'Test malzemesi', 'canonical_name' => 'Test malzemesi', 'slug' => 'test-malzemesi', 'is_active' => true]);
        IngredientUnitConversion::create(['ingredient_id' => $ingredient->id, 'household_measure_code' => 'adet', 'household_quantity' => 1, 'canonical_quantity' => 100, 'canonical_unit' => 'g', 'source_type' => 'seed_demo', 'verification_status' => 'low', 'confidence_score' => 40, 'conversion_method' => 'editorial_estimate', 'is_active' => true]);

        app(DataQualityService::class)->scan();
        $breakdown = app(DataQualityService::class)->breakdown();

        $this->assertDatabaseHas('data_quality_issues', ['issue_type' => 'recipe_uses_placeholder_image', 'entity_public_id' => $recipe->public_id, 'resolution_status' => 'open']);
        $this->assertDatabaseHas('data_quality_issues', ['issue_type' => 'conversion_seed_demo', 'resolution_status' => 'open']);
        $this->assertLessThan(100, $breakdown['media']['score']);
        $this->assertLessThan(100, $breakdown['measurements']['score']);
        $this->assertContains('Tarif yalnız yer tutucu görsel kullanıyor.', $breakdown['media']['reasons']);
    }

    public function test_nutrition_estimate_is_reported_with_a_safe_public_identifier(): void
    {
        $ingredient = Ingredient::create(['name' => 'Mantar', 'canonical_name' => 'Mantar', 'slug' => 'mantar', 'is_active' => true]);
        $nutrient = Nutrient::create(['code' => 'protein-test', 'name' => 'Protein', 'unit' => 'g', 'decimal_precision' => 2, 'is_active' => true]);
        $ingredient->nutritionFacts()->create(['nutrient_id' => $nutrient->id, 'value' => 2, 'basis_type' => 'per_100g', 'basis_amount' => 100, 'confidence_score' => 50, 'origin' => 'ai_estimated', 'calculation_method' => 'estimate']);

        app(DataQualityService::class)->scan();
        $issue = DataQualityIssue::query()->where('issue_type', 'nutrition_low_confidence')->firstOrFail();

        $this->assertSame('nutrition', $issue->category);
        $this->assertStringContainsString($ingredient->public_id, (string) $issue->entity_public_id);
        $this->assertNotSame((string) $ingredient->id, (string) $issue->entity_public_id);
    }

    public function test_local_qa_fixture_command_is_idempotent_and_creates_ownership_data(): void
    {
        $recipe = $this->recipe();
        $recipe->steps()->create(['position' => 1, 'title' => 'Hazırlık', 'instruction' => 'Hazırla.']);
        Ingredient::create(['name' => 'Fixture malzemesi', 'canonical_name' => 'Fixture malzemesi', 'slug' => 'fixture-malzemesi', 'is_active' => true]);

        $this->artisan('qa:seed-fixtures --json')->assertSuccessful();
        $this->artisan('qa:seed-fixtures --validate-only --json')->assertSuccessful();

        $this->assertDatabaseCount('users', 3);
        $this->assertDatabaseCount('favorites', 1);
        $this->assertDatabaseCount('cooking_sessions', 1);
    }

    public function test_recipe_image_audit_has_stable_json_contract(): void
    {
        $recipe = $this->recipe();
        $recipe->media()->create(['kind' => 'image', 'disk' => 'public', 'path' => 'placeholders/test.svg', 'visibility' => 'public', 'is_placeholder' => true]);

        $this->artisan('recipe-images:audit --json')->expectsOutputToContain('"placeholder_recipes":1')->assertSuccessful();
        $this->artisan('recipe-images:audit --placeholder-only')->expectsOutputToContain('Yer tutucu')->assertSuccessful();
    }

    public function test_admin_can_resolve_and_reopen_an_explainable_issue_without_duplicate_audit_entries(): void
    {
        $admin = User::factory()->create(['role' => 'admin', 'email_verified_at' => now()]);
        $issue = DataQualityIssue::create(['issue_type' => 'recipe_uses_placeholder_image', 'category' => 'media', 'severity' => 'warning', 'entity_type' => Recipe::class, 'entity_public_id' => $this->recipe()->public_id, 'message' => 'Tarif yalnız yer tutucu görsel kullanıyor.', 'suggested_action' => 'Onaylı görsel ekleyin.', 'detected_at' => now(), 'first_detected_at' => now(), 'last_detected_at' => now(), 'resolution_status' => 'open']);

        $this->actingAs($admin)->post(route('admin.data-quality.resolve', $issue))->assertRedirect();
        $this->actingAs($admin)->post(route('admin.data-quality.resolve', $issue))->assertRedirect();
        $this->assertDatabaseCount('audit_logs', 1);
        $this->assertDatabaseHas('audit_logs', ['action' => 'data_quality.resolved', 'auditable_id' => $issue->id]);

        $this->actingAs($admin)->post(route('admin.data-quality.reopen', $issue))->assertRedirect();
        $this->actingAs($admin)->post(route('admin.data-quality.reopen', $issue))->assertRedirect();
        $this->assertDatabaseCount('audit_logs', 2);
        $this->assertDatabaseHas('audit_logs', ['action' => 'data_quality.reopened', 'auditable_id' => $issue->id]);
    }

    public function test_members_cannot_access_the_data_quality_dashboard_or_mutate_issues(): void
    {
        $member = User::factory()->create(['role' => 'member', 'email_verified_at' => now()]);
        $issue = DataQualityIssue::create(['issue_type' => 'missing_description', 'severity' => 'warning', 'entity_type' => Recipe::class, 'entity_public_id' => $this->recipe()->public_id, 'message' => 'Açıklama eksik.', 'detected_at' => now(), 'first_detected_at' => now(), 'last_detected_at' => now(), 'resolution_status' => 'open']);

        $this->actingAs($member)->get(route('admin.data-quality.index'))->assertForbidden();
        $this->actingAs($member)->post(route('admin.data-quality.resolve', $issue))->assertForbidden();
        $this->actingAs($member)->post(route('admin.data-quality.reopen', $issue))->assertForbidden();
    }

    private function recipe(): Recipe
    {
        return Recipe::create(['title' => 'Master Fix Tarifi', 'slug' => 'master-fix-tarifi', 'description' => 'Açıklama', 'servings' => 2, 'prep_minutes' => 1, 'cook_minutes' => 1, 'rest_minutes' => 0, 'difficulty' => 'Kolay', 'status' => 'published', 'verification_status' => 'verified']);
    }
}
