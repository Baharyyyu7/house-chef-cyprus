<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Models\AiImageGeneration;
use App\Models\Ingredient;
use App\Models\Recipe;
use App\Models\User;
use App\Services\Media\GeminiImageCapabilityHealth;
use App\Services\Media\RecipeImagePromptBuilder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

final class AiImagePilotTest extends TestCase
{
    use RefreshDatabase;

    public function test_pilot_plan_is_a_cost_hard_stop_without_explicit_budget_approval(): void
    {
        config()->set('ai_images.daily_budget_cents', 0);

        $this->artisan('ai-images:pilot-plan', ['--json' => true])
            ->expectsOutputToContain('"generation_allowed":false')
            ->assertSuccessful();
    }

    public function test_provider_live_health_lists_advertised_image_models_without_generating_an_image(): void
    {
        config()->set('ai_images.providers.gemini.key', 'test-key');
        Http::fake(['https://generativelanguage.googleapis.com/*' => Http::response(['models' => [['name' => 'models/imagen-test', 'supportedGenerationMethods' => ['predict']]]])]);

        $health = app(GeminiImageCapabilityHealth::class)->health(true);

        $this->assertSame('image_models_advertised', $health['image_capability']);
        $this->assertSame(1, $health['advertised_image_models']);
        $this->assertSame(200, $health['http_status']);
    }

    public function test_provider_health_returns_a_safe_payment_required_status(): void
    {
        $this->assertSafeProviderFailure(402, 'payment_required');
    }

    public function test_provider_health_returns_a_safe_forbidden_status(): void
    {
        $this->assertSafeProviderFailure(403, 'forbidden');
    }

    public function test_provider_health_returns_a_safe_rate_limited_status(): void
    {
        $this->assertSafeProviderFailure(429, 'rate_limited');
    }

    public function test_prompt_uses_the_recipe_identity_and_declared_ingredient_without_inventing_a_provider_output(): void
    {
        $recipe = $this->recipe();
        $ingredient = Ingredient::create(['name' => 'Hellim', 'slug' => 'hellim-'.str()->lower(str()->random(8))]);
        $recipe->ingredients()->attach($ingredient, ['quantity' => 120, 'unit' => 'g', 'sort_order' => 1]);

        $prompt = app(RecipeImagePromptBuilder::class)->build($recipe);

        $this->assertTrue($prompt['ready']);
        $this->assertStringContainsString('Deneme Hellim Tabağı', $prompt['prompt']);
        $this->assertStringContainsString('Hellim', $prompt['prompt']);
        $this->assertStringContainsString('include only ingredients supported by the recipe data', $prompt['prompt']);
        $this->assertStringContainsString('watermark', $prompt['negative_prompt']);
    }

    public function test_prompt_is_blocked_when_the_recipe_identity_is_not_supported_by_declared_ingredients(): void
    {
        $recipe = Recipe::create(['title' => 'Cacık', 'slug' => 'cacik', 'description' => 'Test açıklaması', 'servings' => 2, 'prep_minutes' => 1, 'cook_minutes' => 1, 'rest_minutes' => 0, 'difficulty' => 'Kolay', 'status' => 'published']);
        $ingredient = Ingredient::create(['name' => 'Pirinç', 'slug' => 'pirinc']);
        $recipe->ingredients()->attach($ingredient, ['quantity' => 1, 'unit' => 'adet', 'sort_order' => 1]);

        $prompt = app(RecipeImagePromptBuilder::class)->build($recipe);

        $this->assertFalse($prompt['ready']);
        $this->assertStringContainsString('PROMPT BLOCKED', $prompt['prompt']);
        $this->assertStringContainsString('yoğurt', $prompt['blocking_reasons'][0]);
    }

    public function test_member_cannot_access_or_reject_ai_image_pilot_records(): void
    {
        $generation = $this->generation($this->recipe());
        $member = User::factory()->create(['role' => 'member', 'email_verified_at' => now()]);

        $this->actingAs($member)->get(route('admin.ai-image-studio.index'))->assertForbidden();
        $this->actingAs($member)->post(route('admin.ai-image-studio.reject', $generation))->assertForbidden();
    }

    public function test_admin_can_reject_a_planned_generation_using_a_public_route_key_and_audit_log(): void
    {
        $admin = User::factory()->create(['role' => 'admin', 'email_verified_at' => now()]);
        $generation = $this->generation($this->recipe(), 'planned');

        $this->actingAs($admin)->post(route('admin.ai-image-studio.reject', $generation), ['review_notes' => 'Malzeme görünümü uyumsuz.'])
            ->assertRedirect(route('admin.ai-image-studio.show', $generation));

        $this->assertSame('rejected', $generation->fresh()->status);
        $this->assertDatabaseHas('audit_logs', ['action' => 'ai_image_generation.reject']);
        $this->assertStringContainsString($generation->public_id, route('admin.ai-image-studio.show', $generation));
        $this->assertStringNotContainsString('/'.$generation->id, route('admin.ai-image-studio.show', $generation));
    }

    private function recipe(): Recipe
    {
        return Recipe::create(['title' => 'Deneme Hellim Tabağı', 'slug' => 'deneme-hellim-tabagi-'.str()->lower(str()->random(8)), 'description' => 'Test açıklaması', 'servings' => 2, 'prep_minutes' => 1, 'cook_minutes' => 1, 'rest_minutes' => 0, 'difficulty' => 'Kolay', 'status' => 'published']);
    }

    private function generation(Recipe $recipe, string $status = 'planned'): AiImageGeneration
    {
        return AiImageGeneration::create(['recipe_id' => $recipe->id, 'provider' => 'gemini', 'model' => 'test-image-model', 'prompt' => 'Safe test prompt', 'negative_prompt' => 'No text', 'prompt_version' => 'test-v1', 'aspect_ratio' => '4:3', 'variation_number' => 1, 'status' => $status]);
    }

    private function assertSafeProviderFailure(int $status, string $expected): void
    {
        config()->set('ai_images.providers.gemini.key', 'test-key');
        Http::fake(fn () => Http::response([], $status));
        $health = app(GeminiImageCapabilityHealth::class)->health(true);
        $this->assertSame($expected, $health['image_capability']);
        $this->assertStringNotContainsString('test-key', $health['safe_message']);
    }
}
