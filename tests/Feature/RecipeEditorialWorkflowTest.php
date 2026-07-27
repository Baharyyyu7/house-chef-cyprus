<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Models\ContentSource;
use App\Models\Recipe;
use App\Models\User;
use App\Services\RecipeCompletenessService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

final class RecipeEditorialWorkflowTest extends TestCase
{
    use RefreshDatabase;

    public function test_editorial_import_is_idempotent_and_creates_the_first_fifty_catalogue_as_verified_published_records(): void
    {
        $this->seed();
        $this->assertSame(50, Recipe::query()->count());
        $this->assertSame(50, Recipe::query()->where('status', 'published')->count());
        $this->assertSame(50, Recipe::query()->where('verification_status', 'verified')->count());
        $this->assertSame(50, ContentSource::query()->count());

        $this->artisan('recipes:import-editorial --json')->assertSuccessful();
        $this->assertSame(50, Recipe::query()->count());
        $this->assertSame(50, ContentSource::query()->count());
        $this->assertSame(0, Recipe::query()->whereNull('public_id')->count());
    }

    public function test_editorial_import_supports_validation_and_dry_run_without_writing_records(): void
    {
        $this->seed();
        $before = Recipe::query()->count();
        $this->artisan('recipes:import-editorial --validate-only --json')->assertSuccessful();
        $this->artisan('recipes:import-editorial --dry-run --batch=1 --json')->assertSuccessful();
        $this->assertSame($before, Recipe::query()->count());
    }

    public function test_publication_is_blocked_until_the_recipe_is_verified_and_complete(): void
    {
        $this->seed();
        $admin = User::query()->where('email', 'admin@housechef.local')->firstOrFail();
        $recipe = Recipe::query()->create(['title' => 'Eksik editoryal tarif', 'slug' => 'eksik-editoryal-tarif', 'servings' => 4, 'difficulty' => 'Kolay', 'status' => 'draft']);
        $this->actingAs($admin)->post(route('admin.recipe-verification.publish', $recipe))->assertSessionHasErrors('publication');
        $this->assertSame('draft', $recipe->fresh()->status);

        $report = app(RecipeCompletenessService::class)->evaluate($recipe);
        $this->assertContains($report['status'], ['incomplete', 'needs_review', 'ready_for_review']);
    }

    public function test_member_cannot_access_the_editorial_verification_queue(): void
    {
        $this->seed();
        $member = User::factory()->create(['role' => 'member', 'email_verified_at' => now()]);
        $this->actingAs($member)->get(route('admin.recipe-verification.index'))->assertForbidden();
    }
}
