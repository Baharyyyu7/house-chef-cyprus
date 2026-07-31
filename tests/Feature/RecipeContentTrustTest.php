<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Models\ContentSource;
use App\Models\Ingredient;
use App\Models\Media;
use App\Models\Recipe;
use App\Models\RecipeIngredient;
use App\Models\User;
use App\Services\DataQualityService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\File;
use Tests\TestCase;

final class RecipeContentTrustTest extends TestCase
{
    use RefreshDatabase;

    public function test_first_fifty_catalogue_has_stable_public_records_and_editorial_source_coverage(): void
    {
        $this->seed();

        $this->assertSame(500, Recipe::query()->count());
        $this->assertSame(500, Recipe::query()->distinct('public_id')->count('public_id'));
        $this->assertSame(500, Recipe::query()->distinct('slug')->count('slug'));
        $this->assertSame(500, ContentSource::query()->where('is_active', true)->count());
        $this->assertSame(0, Recipe::query()->where('status', 'published')->where('verification_status', '!=', 'verified')->count());
        $this->assertSame(0, Recipe::query()->whereNull('completeness_score')->count());
    }

    public function test_editorial_catalogue_covers_the_minimum_category_mix_and_measurement_contract(): void
    {
        $this->seed();
        $minimums = ['Kahvaltı' => 5, 'Çorba' => 5, 'Ana Yemek' => 12, 'Zeytinyağlı / Sebze' => 5, 'Hamur İşi' => 5, 'Tatlı' => 6, 'Salata / Meze' => 6, 'Atıştırmalık / Pratik' => 5];

        foreach ($minimums as $category => $minimum) {
            $this->assertGreaterThanOrEqual($minimum, Recipe::query()->whereHas('category', fn ($query) => $query->where('name', $category))->count());
        }
        $this->assertSame(0, RecipeIngredient::query()->where(fn ($query) => $query->whereNull('display_quantity')->orWhereNull('display_measure_code')->orWhereNull('canonical_quantity')->orWhereNull('canonical_unit'))->count());
    }

    public function test_public_api_excludes_unverified_recipes_and_does_not_leak_internal_editorial_fields(): void
    {
        $this->seed();
        $hidden = Recipe::query()->firstOrFail();
        $hidden->update(['verification_status' => 'needs_review']);

        $response = $this->getJson('/api/v1/recipes')->assertOk();
        $response->assertJsonMissing(['public_id' => $hidden->public_id]);
        $response->assertJsonPath('data.0.content_trust.verification_status', 'verified');
        $response->assertJsonMissingPath('data.0.source_name');
        $response->assertJsonMissingPath('data.0.reliability_score');

        $this->getJson('/api/v1/recipes/'.$hidden->public_id)->assertNotFound();
    }

    public function test_data_quality_detects_missing_content_source_without_exposing_source_notes(): void
    {
        $this->seed();
        $recipe = Recipe::query()->firstOrFail();
        $recipe->provenances()->delete();

        $issues = app(DataQualityService::class)->report();
        $this->assertTrue(collect($issues)->contains(fn (array $issue): bool => $issue['issue_type'] === 'missing_content_source' && $issue['entity_public_id'] === $recipe->public_id));
    }

    public function test_admin_can_open_verification_queue_while_member_is_forbidden(): void
    {
        $this->seed();
        $admin = User::query()->where('email', 'admin@housechef.local')->firstOrFail();
        $member = User::factory()->create(['role' => 'member', 'email_verified_at' => now()]);

        $this->actingAs($admin)->get(route('admin.recipe-verification.index'))->assertOk();
        $this->actingAs($member)->get(route('admin.recipe-verification.index'))->assertForbidden();
    }

    public function test_branded_placeholder_variants_and_initial_ingredient_icon_metadata_are_present(): void
    {
        $this->seed();

        foreach (range(1, 8) as $variant) {
            $this->assertTrue(File::exists(public_path("placeholders/{$variant}/recipe.svg")));
        }
        $this->assertSame(24, Ingredient::query()->whereNotNull('icon_key')->count());
    }

    public function test_seeded_catalogue_has_a_unique_branded_fallback_for_every_recipe(): void
    {
        $this->seed();

        $media = Media::query()->where('mediable_type', Recipe::class)->where('sort_order', 0)->get();
        $this->assertSame(50, $media->count());
        $this->assertSame(50, $media->pluck('path')->unique()->count());
        $this->assertTrue($media->every(fn (Media $item): bool => $item->provider_metadata['media_status'] === 'branded_fallback'));
        $this->assertTrue($media->every(fn (Media $item): bool => filled($item->alt_text) && $item->thumbnail_status === 'ready'));
    }
}
