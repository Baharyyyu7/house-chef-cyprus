<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Models\AiImageGeneration;
use App\Models\Ingredient;
use App\Models\Media;
use App\Models\Recipe;
use App\Models\User;
use App\Services\Media\AiImagePilotService;
use App\Services\Media\AiImageReviewService;
use App\Services\Media\GeminiImageCapabilityHealth;
use App\Services\Media\GeminiImageGenerationService;
use App\Services\Media\PilotRecipeDataCorrectionService;
use App\Services\Media\RecipeImagePromptBuilder;
use Database\Seeders\HouseChefSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Storage;
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
        $this->actingAs($member)->withSession(['_token' => 'test-csrf'])->post(route('admin.ai-image-studio.reject', $generation), ['_token' => 'test-csrf'])->assertForbidden();
    }

    public function test_admin_can_reject_a_generated_generation_using_a_public_route_key_and_audit_log(): void
    {
        $admin = User::factory()->create(['role' => 'admin', 'email_verified_at' => now()]);
        $generation = $this->generation($this->recipe(), 'ready_for_review');

        $this->actingAs($admin)->withSession(['_token' => 'test-csrf'])->post(route('admin.ai-image-studio.reject', $generation), ['_token' => 'test-csrf', 'review_notes' => 'Malzeme görünümü uyumsuz.'])
            ->assertRedirect(route('admin.ai-image-studio.show', $generation));

        $this->assertSame('rejected', $generation->fresh()->status);
        $this->assertDatabaseHas('audit_logs', ['action' => 'ai_image_generation.reject']);
        $this->assertStringContainsString($generation->public_id, route('admin.ai-image-studio.show', $generation));
        // İddia yolu sınamalı, tüm adresi değil: "http://127.0.0.1:8010"
        // gibi bir taban adres kendi içinde "/1" barındırır ve sayısal
        // kimlik sızmadığı hâlde test kırılır.
        $this->assertStringNotContainsString(
            '/'.$generation->id,
            (string) parse_url(route('admin.ai-image-studio.show', $generation), PHP_URL_PATH)
        );
    }

    public function test_pilot_correction_replaces_generic_lines_and_preserves_an_editorial_before_after_record(): void
    {
        $this->seed(HouseChefSeeder::class);
        $result = app(PilotRecipeDataCorrectionService::class)->apply();

        $this->assertSame(8, $result['corrected']);
        $this->assertSame(0, $result['needs_review']);
        $this->assertSame(8, Recipe::query()->whereHas('provenances', fn ($query) => $query->where('field_name', 'ai_image_fidelity_correction'))->count());
        $this->assertTrue(app(AiImagePilotService::class)->recipes()->every(fn (Recipe $recipe): bool => app(RecipeImagePromptBuilder::class)->build($recipe)['ready']));
    }

    public function test_generation_stays_private_and_is_limited_to_the_approved_twenty_variations(): void
    {
        Storage::fake('local');
        $this->seed(HouseChefSeeder::class);
        config()->set('ai_images.providers.gemini.key', 'test-key');
        config()->set('ai_images.providers.gemini.enabled', true);
        config()->set('ai_images.daily_budget_cents', 200);
        config()->set('ai_images.pilot_cost_estimate_cents_per_image', 4);
        $png = 'iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAQAAAC1HAwCAAAAC0lEQVR42mP8/x8AAusB9Wl6h3IAAAAASUVORK5CYII=';
        Http::fake(['https://generativelanguage.googleapis.com/*' => Http::response(['predictions' => [['bytesBase64Encoded' => $png, 'mimeType' => 'image/png'], ['bytesBase64Encoded' => $png, 'mimeType' => 'image/png']]])]);

        $result = app(GeminiImageGenerationService::class)->execute();

        $this->assertSame(20, $result['generated']);
        $this->assertSame(0, $result['failed']);
        $this->assertSame(20, AiImageGeneration::query()->where('status', 'ready_for_review')->count());
        $this->assertSame(20, Media::query()->where('generated_by_ai', true)->where('visibility', 'private')->count());
        Http::assertSentCount(10);
    }

    public function test_provider_failure_never_creates_media_or_retries_the_consumed_pilot_slots(): void
    {
        Storage::fake('local');
        $this->seed(HouseChefSeeder::class);
        config()->set('ai_images.providers.gemini.key', 'test-key');
        config()->set('ai_images.providers.gemini.enabled', true);
        config()->set('ai_images.daily_budget_cents', 200);
        config()->set('ai_images.pilot_cost_estimate_cents_per_image', 4);
        Http::fake(['https://generativelanguage.googleapis.com/*' => Http::response([], 403)]);

        $first = app(GeminiImageGenerationService::class)->execute();
        $second = app(GeminiImageGenerationService::class)->execute();

        $this->assertSame(20, $first['failed']);
        $this->assertSame(0, $first['generated']);
        $this->assertSame(0, $second['generated']);
        $this->assertSame(20, AiImageGeneration::query()->where('status', 'provider_blocked')->count());
        $this->assertSame(0, Media::query()->where('generated_by_ai', true)->count());
        $this->assertFalse(app(AiImagePilotService::class)->generationAllowed());
        $this->assertDatabaseMissing('ai_image_generations', ['actual_cost_cents' => 4]);
        Http::assertSentCount(10);
    }

    public function test_manual_review_is_required_before_an_ai_media_record_becomes_public(): void
    {
        Storage::fake('local');
        Storage::fake('public');
        $admin = User::factory()->create(['role' => 'admin', 'email_verified_at' => now()]);
        $recipe = $this->recipe();
        $generation = $this->generation($recipe, 'ready_for_review');
        $path = 'media/ai-pilot/'.$recipe->public_id.'/variation-1.png';
        Storage::disk('local')->put($path, base64_decode('iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAQAAAC1HAwCAAAAC0lEQVR42mP8/x8AAusB9Wl6h3IAAAAASUVORK5CYII='));
        $media = $recipe->media()->create(['kind' => 'image', 'disk' => 'local', 'path' => $path, 'mime_type' => 'image/png', 'size' => 68, 'visibility' => 'private', 'processing_status' => 'ready', 'thumbnail_status' => 'not_requested', 'is_placeholder' => false, 'generated_by_ai' => true, 'ai_review_status' => 'pending_review']);
        $generation->update(['media_id' => $media->id]);

        app(AiImageReviewService::class)->approve($generation->fresh('media', 'recipe.media'), $admin, 'İçerik, sunum ve watermark kontrolü tamamlandı.');

        $this->assertSame('approved', $generation->fresh()->status);
        $this->assertSame('public', $media->fresh()->visibility);
        $this->assertTrue(Storage::disk('public')->exists($media->fresh()->path));
        $this->assertDatabaseHas('audit_logs', ['action' => 'ai_image_generation.approve']);
    }

    public function test_diagnostic_recovery_is_limited_to_one_private_tomato_soup_call(): void
    {
        Storage::fake('local');
        $this->seed(HouseChefSeeder::class);
        config()->set('ai_images.providers.gemini.key', 'test-key');
        config()->set('ai_images.providers.gemini.enabled', true);
        config()->set('ai_images.pilot_cost_estimate_cents_per_image', 4);
        config()->set('ai_images.diagnostic_recovery.budget_cents', 10);
        Http::fake(['https://generativelanguage.googleapis.com/*' => Http::response(['predictions' => [['bytesBase64Encoded' => 'iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAQAAAC1HAwCAAAAC0lEQVR42mP8/x8AAusB9Wl6h3IAAAAASUVORK5CYII=', 'mimeType' => 'image/png']]])]);

        $first = app(GeminiImageGenerationService::class)->recoverTomatoSoup();
        $second = app(GeminiImageGenerationService::class)->recoverTomatoSoup();

        $this->assertSame(1, $first['generated']);
        $this->assertSame(1, $second['blocked']);
        $this->assertSame(1, AiImageGeneration::query()->where('generation_scope', 'diagnostic_recovery')->where('status', 'ready_for_review')->count());
        $this->assertSame(1, Media::query()->where('generated_by_ai', true)->where('visibility', 'private')->count());
        Http::assertSentCount(1);
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
