<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Models\ContentFactoryReview;
use App\Models\EditorialReviewPack;
use App\Models\Recipe;
use App\Models\RecipeMediaCandidate;
use App\Models\User;
use Database\Seeders\HouseChefSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

final class PublishedRecipeRepairProposalTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(HouseChefSeeder::class);
    }

    public function test_preview_prepares_complete_batch_without_writes(): void
    {
        $before = Recipe::query()->where('status', 'published')->get(['id', 'title', 'status', 'verification_status', 'updated_at'])->toArray();

        $this->artisan('content:published-recipe-repair', ['--batch' => 1, '--json' => true])
            ->expectsOutputToContain('"identity_confirmed":0')
            ->assertSuccessful();

        $this->assertSame(0, ContentFactoryReview::query()->count());
        $this->assertSame(0, EditorialReviewPack::query()->count());
        $this->assertSame($before, Recipe::query()->where('status', 'published')->get(['id', 'title', 'status', 'verification_status', 'updated_at'])->toArray());
    }

    public function test_write_creates_idempotent_private_comparison_packs_without_public_mutation(): void
    {
        $before = Recipe::query()->where('status', 'published')->withCount(['media', 'mediaCandidates'])->get()->keyBy('id');

        $this->artisan('content:published-recipe-repair', ['--batch' => 1, '--write' => true, '--json' => true])->assertSuccessful();
        $this->artisan('content:published-recipe-repair', ['--batch' => 1, '--write' => true, '--json' => true])->assertSuccessful();

        $this->assertSame(10, ContentFactoryReview::query()->where('stage', 'repair_proposal')->where('status', 'ready_for_editor_review')->count());
        $this->assertSame(10, ContentFactoryReview::query()->where('stage', 'media_brief')->where('status', 'ready_for_media_review')->count());
        $this->assertSame(10, EditorialReviewPack::query()->where('status', 'blocked_human_review')->count());
        $this->assertSame(0, RecipeMediaCandidate::query()->count());

        foreach (EditorialReviewPack::query()->get() as $pack) {
            $proposal = (array) data_get($pack->snapshot, 'proposed');
            $this->assertTrue((bool) data_get($proposal, 'human_approval_required'));
            $this->assertFalse((bool) data_get($proposal, 'auto_publish'));
            $this->assertFalse((bool) data_get($proposal, 'auto_verify'));
            $this->assertGreaterThanOrEqual(5, count((array) data_get($proposal, 'steps')));
            $this->assertGreaterThanOrEqual(6, count((array) data_get($proposal, 'ingredients')));
            $this->assertGreaterThan(0, data_get($proposal, 'timing.total'));
            $this->assertNotEmpty(data_get($proposal, 'seo.title'));
            $this->assertNotEmpty(data_get($proposal, 'seo.description'));
            $this->assertNotEmpty(data_get($proposal, 'seo.canonical'));
            $this->assertSame('Recipe', data_get($proposal, 'schema.type'));
            $this->assertFalse((bool) data_get($proposal, 'media_brief.candidate_generation_allowed'));
            foreach ((array) data_get($proposal, 'ingredients') as $ingredient) {
                $this->assertNotEmpty(data_get($ingredient, 'household'));
                $this->assertIsNumeric(data_get($ingredient, 'canonicalQuantity'));
                $this->assertNotEmpty(data_get($ingredient, 'canonicalUnit'));
            }
        }

        foreach ($before as $id => $state) {
            $recipe = Recipe::query()->withCount(['media', 'mediaCandidates'])->findOrFail($id);
            $this->assertSame($state->title, $recipe->title);
            $this->assertSame($state->status, $recipe->status);
            $this->assertSame($state->verification_status, $recipe->verification_status);
            $this->assertSame($state->media_count, $recipe->media_count);
            $this->assertSame($state->media_candidates_count, $recipe->media_candidates_count);
        }
    }

    public function test_admin_sees_structured_current_proposed_comparison_and_member_is_forbidden(): void
    {
        $this->artisan('content:published-recipe-repair', ['--write' => true, '--json' => true])->assertSuccessful();
        $pack = EditorialReviewPack::query()->firstOrFail();
        $admin = User::factory()->create(['role' => 'admin', 'email_verified_at' => now()]);
        $member = User::factory()->create(['role' => 'member', 'email_verified_at' => now()]);

        $this->actingAs($admin)->get(route('admin.editorial-operations.packs.show', $pack))
            ->assertOk()
            ->assertSee('Mevcut yayın')
            ->assertSee('Önerilen özgün onarım')
            ->assertSee('Yemeğe özel medya briefi')
            ->assertSee('Görsel adayı oluşturulmadı');
        $this->actingAs($member)->get(route('admin.editorial-operations.packs.show', $pack))->assertForbidden();
    }
}
