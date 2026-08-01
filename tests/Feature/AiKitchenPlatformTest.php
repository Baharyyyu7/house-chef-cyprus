<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Models\AiInteraction;
use App\Models\AiMealPlan;
use App\Models\Ingredient;
use App\Models\PantryItem;
use App\Models\Recipe;
use App\Models\RecipeIngredient;
use App\Models\User;
use App\Services\AI\AiKitchenService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Cache;
use Tests\TestCase;

final class AiKitchenPlatformTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        config()->set('ai_kitchen.enabled', true);
        config()->set('ai_kitchen.cache_ttl_seconds', 900);
        config()->set('ai_kitchen.default_provider', 'mock');
        config()->set('ai_kitchen.fallback_chain', ['mock']);
        config()->set('ai_kitchen.providers.mock.model', 'house-chef-local-v1');
        config()->set('ai_kitchen.rate_limit_per_hour', 30);
        Cache::flush();
    }

    public function test_safe_local_assistant_is_cached_audited_and_never_changes_recipe_trust(): void
    {
        $user = User::factory()->create(['email_verified_at' => now()]);
        $recipe = $this->recipe('Güvenli tarif');
        $service = app(AiKitchenService::class);

        $first = $service->assistant($user, 'Alerjim varken ne yapmalıyım?');
        $second = $service->assistant($user, 'Alerjim varken ne yapmalıyım?');

        $this->assertSame('completed', $first['status']);
        $this->assertFalse($first['cache_hit']);
        $this->assertTrue($second['cache_hit']);
        $this->assertNotEmpty($first['safety_events']);
        $this->assertSame('published', $recipe->fresh()->status);
        $this->assertSame('verified', $recipe->fresh()->verification_status);
        $this->assertSame(2, AiInteraction::query()->count());
        $this->assertDatabaseHas('audit_logs', ['action' => 'ai_kitchen.assistant', 'user_id' => $user->id]);
    }

    public function test_pantry_meal_and_shopping_work_from_public_recipe_catalogue_only(): void
    {
        $user = User::factory()->create(['email_verified_at' => now()]);
        $ingredient = Ingredient::create(['name' => 'Domates', 'canonical_name' => 'Domates', 'slug' => 'domates', 'is_active' => true]);
        $recipe = $this->recipe('Domatesli tarif');
        RecipeIngredient::create(['recipe_id' => $recipe->id, 'ingredient_id' => $ingredient->id, 'quantity' => 2, 'unit' => 'adet', 'is_required' => true, 'sort_order' => 1]);
        PantryItem::create(['user_id' => $user->id, 'ingredient_id' => $ingredient->id, 'quantity' => 2, 'unit' => 'adet', 'is_available' => true]);
        $service = app(AiKitchenService::class);

        $pantry = $service->pantry($user);
        $menu = $service->mealPlan($user, 7, ['max_calories' => 650]);
        $plan = AiMealPlan::query()->where('public_id', $menu['meal_plan_id'])->firstOrFail();
        $shopping = $service->shopping($user, $plan);

        $this->assertSame($recipe->public_id, $pantry['data']['recipes'][0]['public_id']);
        $this->assertCount(7, $menu['plan']);
        $this->assertNotEmpty($shopping['items']);
        $this->assertArrayNotHasKey('id', $menu['plan'][0]['recipe']);
    }

    public function test_admin_can_view_operations_and_member_is_forbidden(): void
    {
        $admin = User::factory()->create(['role' => 'admin', 'email_verified_at' => now()]);
        $member = User::factory()->create(['role' => 'member', 'email_verified_at' => now()]);
        app(AiKitchenService::class)->assistant($admin, 'Pratik akşam yemeği');

        $this->actingAs($admin)->get(route('admin.ai-kitchen.index'))->assertOk()->assertSee('AI Operasyonları')->assertDontSee('OPENAI_API_KEY');
        $this->actingAs($member)->get(route('admin.ai-kitchen.index'))->assertForbidden();
        $this->actingAs($member)->get(route('ai-kitchen.index'))->assertOk()->assertSee('AI Mutfak Asistanı');
    }

    public function test_ai_commands_have_machine_readable_safe_contracts(): void
    {
        $this->artisan('ai-kitchen:health --json')->assertSuccessful();
        $this->artisan('ai-kitchen:cost-report --json')->assertSuccessful();
        $this->artisan('ai-kitchen:morning-brief --json')->assertSuccessful();
    }

    private function recipe(string $title): Recipe
    {
        return Recipe::create(['title' => $title, 'slug' => str($title)->slug().'-'.str()->lower(str()->random(6)), 'description' => 'Özgün açıklama.', 'servings' => 2, 'prep_minutes' => 5, 'cook_minutes' => 10, 'rest_minutes' => 0, 'difficulty' => 'Kolay', 'status' => 'published', 'verification_status' => 'verified', 'published_at' => now()]);
    }
}
