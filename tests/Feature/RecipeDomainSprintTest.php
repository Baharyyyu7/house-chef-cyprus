<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Models\CookingSession;
use App\Models\Recipe;
use App\Models\RecipeStep;
use App\Models\User;
use App\Services\CookingSessionService;
use App\Services\CookingTimerService;
use App\Services\RecipeStepService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use Tests\TestCase;

final class RecipeDomainSprintTest extends TestCase
{
    use RefreshDatabase;

    public function test_step_service_calculates_duration_and_separates_timer_and_optional_steps(): void
    {
        [$recipe, $steps] = $this->recipeWithSteps();
        $service = app(RecipeStepService::class);

        $this->assertSame(900, $service->totalDurationSeconds($recipe));
        $this->assertCount(1, $service->timerSteps($recipe));
        $this->assertSame($steps[1]->id, $service->timerSteps($recipe)->first()->id);
        $this->assertCount(1, $service->optionalSteps($recipe));
    }

    public function test_step_service_rejects_negative_duration_and_invalid_temperature_unit(): void
    {
        $recipe = $this->recipe();
        $service = app(RecipeStepService::class);

        $this->expectException(ValidationException::class);
        $service->create($recipe, ['title' => 'Hatalı', 'instruction' => 'Test', 'duration_seconds' => -1, 'temperature_unit' => 'kelvin']);
    }

    public function test_step_positions_are_unique_and_reordering_and_deletion_keep_a_contiguous_sequence(): void
    {
        [$recipe, $steps] = $this->recipeWithSteps();
        $service = app(RecipeStepService::class);
        $service->reorder($recipe, [$steps[2]->id, $steps[0]->id, $steps[1]->id]);

        $this->assertSame([1, 2, 3], $recipe->steps()->pluck('position')->all());
        $this->assertSame($steps[2]->id, $recipe->steps()->first()->id);

        $service->delete($recipe->steps()->where('position', 2)->firstOrFail());
        $this->assertSame([1, 2], $recipe->steps()->pluck('position')->all());
    }

    public function test_cooking_session_is_resumed_and_progress_uses_only_required_steps(): void
    {
        [$recipe, $steps] = $this->recipeWithSteps();
        $user = User::factory()->create();
        $service = app(CookingSessionService::class);
        $session = $service->startOrResume($user, $recipe, 2);

        $this->assertSame($session->id, $service->startOrResume($user, $recipe, 4)->id);
        $service->completeStep($session, $steps[0]);
        $this->assertSame(50, $service->progress($session));
        $service->completeStep($session, $steps[1]);
        $this->assertSame(50, $service->progress($session));
        $service->completeStep($session, $steps[2]);
        $this->assertSame(100, $service->progress($session));
    }

    public function test_completed_step_can_be_undone_and_session_can_be_completed(): void
    {
        [$recipe, $steps] = $this->recipeWithSteps();
        $session = app(CookingSessionService::class)->startOrResume(User::factory()->create(), $recipe, 4);
        $service = app(CookingSessionService::class);
        $service->completeStep($session, $steps[0]);
        $this->assertDatabaseHas('cooking_session_steps', ['cooking_session_id' => $session->id, 'recipe_step_id' => $steps[0]->id]);
        $service->undoStep($session, $steps[0]);
        $this->assertDatabaseMissing('cooking_session_steps', ['cooking_session_id' => $session->id, 'recipe_step_id' => $steps[0]->id]);
        $this->assertSame('completed', $service->complete($session)->status);
    }

    public function test_users_cannot_access_another_users_cooking_session(): void
    {
        [$recipe] = $this->recipeWithSteps();
        $owner = User::factory()->create();
        $other = User::factory()->create();
        $session = app(CookingSessionService::class)->startOrResume($owner, $recipe, 4);

        $this->actingAs($other)->get(route('cooking-sessions.show', $session))->assertForbidden();
    }

    public function test_timer_can_be_created_paused_resumed_and_completed(): void
    {
        [$recipe, $steps] = $this->recipeWithSteps();
        $session = app(CookingSessionService::class)->startOrResume(User::factory()->create(), $recipe, 4);
        $service = app(CookingTimerService::class);
        $timer = $service->start($service->create($session, $steps[1], 'Pişirme', 120));

        $this->assertSame('running', $timer->status);
        $this->assertSame('paused', $service->pause($timer)->status);
        $this->assertSame('running', $service->resume($timer)->status);
        $this->assertSame('completed', $service->complete($timer)->status);
    }

    public function test_timer_rejects_zero_duration(): void
    {
        [$recipe, $steps] = $this->recipeWithSteps();
        $session = app(CookingSessionService::class)->startOrResume(User::factory()->create(), $recipe, 4);

        $this->expectException(\InvalidArgumentException::class);
        app(CookingTimerService::class)->create($session, $steps[0], 'Geçersiz', 0);
    }

    public function test_recipe_detail_renders_step_metadata_and_authenticated_user_can_open_cooking_mode(): void
    {
        [$recipe] = $this->recipeWithSteps();
        $user = User::factory()->create();
        $this->get(route('recipes.show', $recipe))->assertOk()->assertSee('Yapım adımları')->assertSee('Şef püf noktası')->assertSee('Güvenlik notu');
        $this->actingAs($user)->post(route('cooking-sessions.start', $recipe), ['servings' => 4])->assertRedirect();
        $session = CookingSession::firstOrFail();
        $this->actingAs($user)->get(route('cooking-sessions.show', $session))->assertOk()->assertSee('Yemek yapma modu');
    }

    public function test_admin_can_manage_steps_while_member_is_denied(): void
    {
        [$recipe] = $this->recipeWithSteps();
        $admin = User::factory()->create(['role' => 'admin']);
        $member = User::factory()->create(['role' => 'member']);
        $payload = ['title' => 'Yeni adım', 'instruction' => 'Yeni talimat', 'duration_seconds' => 60, 'temperature_unit' => 'celsius'];

        $this->actingAs($member)->post(route('admin.recipes.steps.store', $recipe), $payload)->assertForbidden();
        $this->actingAs($admin)->post(route('admin.recipes.steps.store', $recipe), $payload)->assertRedirect();
        $this->assertDatabaseHas('recipe_steps', ['recipe_id' => $recipe->id, 'title' => 'Yeni adım']);
    }

    /** @return array{0: Recipe, 1: array<int, RecipeStep>} */
    private function recipeWithSteps(): array
    {
        $recipe = $this->recipe();
        $steps = [
            RecipeStep::create(['recipe_id' => $recipe->id, 'position' => 1, 'title' => 'Hazırla', 'instruction' => 'Hazırla', 'duration_seconds' => 300, 'safety_note' => 'Bıçağı dikkatli kullanın.']),
            RecipeStep::create(['recipe_id' => $recipe->id, 'position' => 2, 'title' => 'Dinlendir', 'instruction' => 'Dinlendir', 'duration_seconds' => 120, 'timer_required' => true, 'is_optional' => true, 'chef_tip' => 'Bekletmek lezzeti dengeler.']),
            RecipeStep::create(['recipe_id' => $recipe->id, 'position' => 3, 'title' => 'Pişir', 'instruction' => 'Pişir', 'duration_seconds' => 480, 'temperature_value' => 180, 'temperature_unit' => 'celsius']),
        ];

        return [$recipe, $steps];
    }

    private function recipe(): Recipe
    {
        return Recipe::create(['title' => 'Test Tarifi '.Str::random(8), 'slug' => 'test-tarifi-'.Str::lower(Str::random(8)), 'servings' => 4, 'prep_minutes' => 5, 'cook_minutes' => 10, 'rest_minutes' => 0, 'difficulty' => 'Kolay', 'status' => 'published', 'verification_status' => 'verified']);
    }
}
