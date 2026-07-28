<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Models\Allergen;
use App\Models\DataQualityIssue;
use App\Models\Ingredient;
use App\Models\IngredientUnitConversion;
use App\Models\Nutrient;
use App\Models\Unit;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

final class AdminDataV3Test extends TestCase
{
    use RefreshDatabase;

    public function test_admin_can_filter_measurements_and_create_a_turkish_household_measure(): void
    {
        $admin = $this->admin();
        Unit::create(['public_id' => '01J00000000000000000000001', 'code' => 'v3_kase', 'name' => 'Kase', 'singular_name' => 'Kase', 'unit_type' => 'household', 'precision' => 2, 'supports_fractions' => true, 'is_active' => true, 'is_user_visible' => true]);

        $this->actingAs($admin)->get(route('admin.measurements.units', ['q' => 'Kase', 'fractions' => '1']))->assertOk()->assertSee('Household ölçü birimleri')->assertSee('v3_kase');
        $this->actingAs($admin)->post(route('admin.measurements.units.store'), ['code' => 'v3_fincan', 'singular_name' => 'Fincan', 'plural_name' => 'Fincan', 'short_label' => 'fincan', 'unit_type' => 'household', 'precision' => 2, 'sort_order' => 1, 'is_active' => '1', 'is_user_visible' => '1'])->assertRedirect();
        $this->assertDatabaseHas('units', ['code' => 'v3_fincan', 'singular_name' => 'Fincan']);
        $this->actingAs($admin)->post(route('admin.measurements.units.store'), ['code' => 'v3_fincan', 'singular_name' => 'Tekrar', 'unit_type' => 'household', 'precision' => 2])->assertSessionHasErrors('code');
    }

    public function test_conversion_and_nutrition_v3_screens_are_safe_and_admin_only(): void
    {
        $admin = $this->admin();
        $member = User::factory()->create(['role' => 'member', 'email_verified_at' => now()]);
        $ingredient = Ingredient::create(['name' => 'V3 Zeytinyağı', 'canonical_name' => 'V3 Zeytinyağı', 'slug' => 'v3-zeytinyagi', 'is_active' => true]);
        Unit::create(['public_id' => '01J00000000000000000000002', 'code' => 'v3_kasik', 'name' => 'Kaşık', 'singular_name' => 'Kaşık', 'unit_type' => 'household', 'precision' => 2, 'is_active' => true, 'is_user_visible' => true]);
        IngredientUnitConversion::create(['public_id' => '01J00000000000000000000003', 'ingredient_id' => $ingredient->id, 'household_measure_code' => 'v3_kasik', 'household_quantity' => 1, 'canonical_quantity' => 12, 'canonical_unit' => 'ml', 'source_type' => 'editorial', 'source_name' => 'Test kaynağı', 'verification_status' => 'medium', 'confidence_score' => 70, 'conversion_method' => 'ingredient_specific', 'is_active' => true]);
        Nutrient::create(['public_id' => '01J00000000000000000000004', 'code' => 'v3-fat', 'name' => 'Yağ', 'unit' => 'g', 'decimal_precision' => 2, 'is_active' => true]);
        Allergen::create(['public_id' => '01J00000000000000000000005', 'code' => 'v3-sut', 'name' => 'Süt', 'is_active' => true]);

        $this->actingAs($admin)->get(route('admin.measurements.conversions', ['confidence' => 74]))->assertOk()->assertSee('Malzeme dönüşümleri')->assertSee('V3 Zeytinyağı')->assertDontSee('>'.$ingredient->id.'<', false);
        $this->actingAs($admin)->get(route('admin.nutrition.index'))->assertOk()->assertSee('Beslenme ve uygunluk')->assertSee('Besin öğeleri')->assertSee('Alerjen sözlüğü');
        $this->actingAs($member)->get(route('admin.measurements.units'))->assertForbidden();
        $this->actingAs($member)->get(route('admin.nutrition.index'))->assertForbidden();
    }

    public function test_data_quality_v3_dashboard_and_detail_keep_safe_identifiers(): void
    {
        $admin = $this->admin();
        $issue = DataQualityIssue::create(['issue_type' => 'missing_description', 'severity' => 'warning', 'entity_type' => Ingredient::class, 'entity_public_id' => '01J00000000000000000000006', 'message' => 'Açıklama eksik.', 'detected_at' => now(), 'first_detected_at' => now(), 'last_detected_at' => now(), 'resolution_status' => 'open', 'metadata' => ['token' => '[MASKED]']]);

        $this->actingAs($admin)->get(route('admin.data-quality.index', ['severity' => 'warning']))->assertOk()->assertSee('Veri kalitesi')->assertSee('Açıklama eksik.');
        $this->actingAs($admin)->get(route('admin.data-quality.show', $issue))->assertOk()->assertSee('Güvenli metadata')->assertSee('MASKED')->assertDontSee('>'.$issue->id.'<', false);
        $this->actingAs($admin)->post(route('admin.data-quality.bulk'), ['action' => 'resolve', 'issues' => [$issue->id]])->assertRedirect();
        $this->assertDatabaseHas('data_quality_issues', ['id' => $issue->id, 'resolution_status' => 'resolved']);
    }

    private function admin(): User
    {
        return User::factory()->create(['role' => 'admin', 'email_verified_at' => now()]);
    }
}
