<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Models\Ingredient;
use App\Models\PantryItem;
use App\Models\Recipe;
use App\Models\RecipeIngredient;
use App\Models\User;
use App\Services\Personalization\PersonalizationService;
use App\Services\Personalization\PersonalizedPlanningService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Tests\TestCase;

final class PersonalizationOperationsTest extends TestCase
{
    use RefreshDatabase;

    public function test_onboarding_preserves_explicit_preference_source_and_export_is_user_owned(): void
    {
        $user = User::factory()->create();
        $service = app(PersonalizationService::class);
        $profile = $service->completeOnboarding($user, ['household_size' => 3], ['favorite_cuisine' => ['turk']]);

        $this->assertTrue($profile->onboarding_completed);
        $this->assertSame('explicitly_selected', $profile->preferences()->first()->source);
        $export = $service->requestExport($user);
        $this->assertSame($user->id, $export->user_id);
        $this->assertArrayNotHasKey('email', $service->export($user));
    }

    public function test_deterministic_plan_and_shopping_list_keep_ownership_and_subtract_safe_pantry(): void
    {
        $user = User::factory()->create();
        $other = User::factory()->create();
        $ingredient = Ingredient::create(['name' => 'Domates', 'canonical_name' => 'domates', 'slug' => 'domates', 'is_active' => true]);
        $recipe = Recipe::create(['title' => 'Doğrulanmış domates yemeği', 'slug' => 'dogrulanmis-domates-yemegi', 'status' => 'published', 'verification_status' => 'verified', 'servings' => 2]);
        RecipeIngredient::create(['recipe_id' => $recipe->id, 'ingredient_id' => $ingredient->id, 'quantity' => 2, 'canonical_quantity' => 2, 'canonical_unit' => 'adet', 'unit' => 'adet', 'is_required' => true]);
        PantryItem::create(['user_id' => $user->id, 'ingredient_id' => $ingredient->id, 'quantity' => 1, 'canonical_quantity' => 1, 'canonical_unit' => 'adet', 'unit' => 'adet', 'is_available' => true]);

        $planning = app(PersonalizedPlanningService::class);
        $plan = $planning->mealPlan($user, 7);
        $this->assertCount(7, $plan->plan);
        $this->assertSame($recipe->public_id, $plan->plan[0]['recipe']['public_id']);
        $list = $planning->shopping($user, $plan);
        $this->assertSame(1.0, (float) $list->items[0]['quantity']);
        $this->expectException(HttpException::class);
        $planning->shopping($other, $plan);
    }

    public function test_personalization_api_requires_auth_and_never_exposes_internal_recommendation_score(): void
    {
        $this->getJson('/api/v1/personalization/profile')->assertUnauthorized();
        $user = User::factory()->create();
        $this->actingAs($user)->getJson('/api/v1/personalization/profile')->assertOk()->assertJsonPath('data.enabled', true)->assertJsonMissingPath('data.id');
    }

    public function test_opt_out_prevents_recommendations_and_clear_data_removes_inferred_only_preferences(): void
    {
        $user = User::factory()->create();
        $service = app(PersonalizationService::class);
        $profile = $service->updateProfile($user, [], ['favorite_cuisine' => ['turk']]);
        $profile->preferences()->create(['type' => 'favorite_cuisine', 'value' => 'italian', 'source' => 'inferred_from_feedback', 'is_active' => true]);

        $service->clearLearnedData($user);
        $this->assertSame(1, $profile->preferences()->count());
        $service->disable($user);
        $this->assertTrue($service->recommendations($user)->isEmpty());
    }

    public function test_recommendations_api_excludes_drafts_and_internal_fields(): void
    {
        $user = User::factory()->create();
        Recipe::create(['title' => 'Taslak tarif', 'slug' => 'taslak-tarif', 'status' => 'draft', 'verification_status' => 'unverified']);
        $published = Recipe::create(['title' => 'Yayın tarif', 'slug' => 'yayin-tarif', 'status' => 'published', 'verification_status' => 'verified']);

        $this->actingAs($user)->getJson('/api/v1/personalization/recommendations')
            ->assertOk()->assertJsonFragment(['public_id' => $published->public_id])->assertJsonMissing(['score' => 10])->assertJsonMissing(['title' => 'Taslak tarif']);
    }

    public function test_admin_sees_aggregate_operations_only_and_member_is_forbidden(): void
    {
        $admin = User::factory()->create(['role' => 'admin', 'email_verified_at' => now()]);
        $member = User::factory()->create(['role' => 'member', 'email_verified_at' => now()]);
        $this->actingAs($admin)->get('/yonetim/kisisellestirme-operasyonlari')->assertOk()->assertSee('Kişiselleştirme operasyonları');
        $this->actingAs($member)->get('/yonetim/kisisellestirme-operasyonlari')->assertForbidden();
    }
}
