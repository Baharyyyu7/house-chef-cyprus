<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Models\Media;
use App\Models\Recipe;
use App\Models\RecipeMediaCandidate;
use App\Models\User;
use App\Services\Media\MediaExcellenceService;
use App\Services\Media\MediaOptimizationService;
use App\Services\Media\PexelsCandidateMediaService;
use App\Services\Media\PexelsEditorialReviewService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\ValidationException;
use Tests\TestCase;

final class MediaExcellenceTest extends TestCase
{
    use RefreshDatabase;

    public function test_only_human_approved_licensed_real_media_is_eligible_for_public_media_surfaces(): void
    {
        Storage::fake('public');
        $recipe = $this->recipe();
        $legacy = $recipe->media()->create([
            'kind' => 'image', 'disk' => 'public', 'path' => 'media/legacy.jpg', 'visibility' => 'public',
            'verification_status' => 'verified', 'alt_text' => 'Eski medya',
        ]);
        $approved = $this->approvedMedia($recipe, 'media/approved.jpg', true);

        $this->assertFalse($legacy->isApprovedRealMedia());
        $this->assertTrue($approved->isApprovedRealMedia());
        $this->get(route('sitemap.images'))
            ->assertOk()
            ->assertSee('media/approved.jpg')
            ->assertDontSee('media/legacy.jpg');
    }

    public function test_media_health_is_capped_until_every_entity_has_approved_real_media_and_a_scan(): void
    {
        $recipe = $this->recipe();
        $health = app(MediaExcellenceService::class)->health();
        $this->assertLessThan(100, $health['score']);
        $this->assertTrue($health['scan_required']);

        $this->approvedMedia($recipe, 'media/approved.jpg');
        $health = app(MediaExcellenceService::class)->health();

        $this->assertSame(1, $health['approved_real_media']);
        $this->assertSame(0, $health['private_leakage']);
    }

    public function test_media_operations_and_machine_readable_commands_never_publish_or_approve_media(): void
    {
        $recipe = $this->recipe();
        $candidate = $recipe->media()->create([
            'kind' => 'image', 'disk' => 'local', 'path' => 'media/review/candidate.jpg', 'visibility' => 'private',
            'media_status' => 'candidate_private', 'verification_status' => 'pending_review',
            'license_review_status' => 'pending_human_review', 'attribution_review_status' => 'pending_human_review',
        ]);
        $admin = User::factory()->create(['role' => 'admin', 'email_verified_at' => now()]);

        $this->actingAs($admin)->get(route('admin.media-operations.index'))->assertOk()
            ->assertSee('Medya Operasyonları')
            ->assertSee((string) $recipe->public_id);
        $this->artisan('media:inventory', ['--json' => true])->expectsOutputToContain('"recipes"')->assertSuccessful();
        $this->artisan('media:health', ['--json' => true])->expectsOutputToContain('"score"')->assertSuccessful();
        $this->artisan('media:morning-brief', ['--json' => true])->expectsOutputToContain('"top_priority"')->assertSuccessful();
        $this->artisan('media:fidelity-scan', ['--dry-run' => true, '--json' => true])->assertSuccessful();
        $this->artisan('media:license-audit', ['--dry-run' => true, '--json' => true])->assertSuccessful();
        $this->artisan('media:attribution-audit', ['--dry-run' => true, '--json' => true])->assertSuccessful();
        $this->artisan('media:duplicate-scan', ['--dry-run' => true, '--json' => true])->assertSuccessful();
        $this->artisan('media:optimize', ['--dry-run' => true, '--json' => true])->assertSuccessful();

        $this->assertSame('private', $candidate->fresh()->visibility);
        $this->assertSame('pending_review', $candidate->fresh()->verification_status);
    }

    public function test_member_cannot_access_media_operations_dashboard(): void
    {
        $member = User::factory()->create(['role' => 'member', 'email_verified_at' => now()]);

        $this->actingAs($member)->get(route('admin.media-operations.index'))->assertForbidden();
    }

    public function test_media_reviewer_can_select_a_private_candidate_but_member_cannot_mutate_it(): void
    {
        $candidate = RecipeMediaCandidate::query()->create([
            'recipe_id' => $this->recipe()->id, 'provider' => 'pexels', 'external_photo_id' => 'reviewable-candidate',
            'status' => 'pending_review', 'source_url' => 'https://images.pexels.com/photos/reviewable.jpg',
        ]);
        $reviewer = User::factory()->create(['role' => 'media_reviewer', 'email_verified_at' => now()]);
        $member = User::factory()->create(['role' => 'member', 'email_verified_at' => now()]);

        $this->assertFalse(Gate::forUser($member)->allows('select-media-candidate', $candidate));
        $this->assertSame('pending_review', $candidate->fresh()->status);

        $this->assertTrue(Gate::forUser($reviewer)->allows('select-media-candidate', $candidate));
        app(PexelsEditorialReviewService::class)->select($candidate->load('recipe'), $reviewer);
        $this->assertSame('selected_for_review', $candidate->fresh()->status);
        $this->assertSame($reviewer->id, $candidate->fresh()->selected_by_user_id);
    }

    public function test_final_media_approval_rejects_self_selected_candidates_and_system_reviewers(): void
    {
        $recipe = $this->recipe();
        $admin = User::factory()->create(['role' => 'admin', 'email_verified_at' => now()]);
        $candidate = RecipeMediaCandidate::query()->create([
            'recipe_id' => $recipe->id, 'provider' => 'pexels', 'external_photo_id' => 'self-approval',
            'status' => 'selected_for_review', 'selected_by_user_id' => $admin->id,
        ]);
        $system = User::factory()->create(['role' => 'admin', 'email' => 'system@housechef.local', 'email_verified_at' => now()]);

        $this->assertFalse(Gate::forUser($admin)->allows('approve-media-final', $candidate));
        $this->assertFalse(Gate::forUser($system)->allows('approve-media-final', $candidate));
    }

    public function test_approved_local_real_media_gets_idempotent_responsive_derivatives(): void
    {
        Storage::fake('public');
        $recipe = $this->recipe();
        $source = UploadedFile::fake()->image('source.jpg', 100, 60)->store('media/source', 'public');
        $media = $this->approvedMedia($recipe, $source);
        $media->update(['optimization_status' => 'pending']);

        $this->assertSame(['processed' => 1, 'skipped' => 0, 'failed' => 0], app(MediaOptimizationService::class)->optimize());
        $media->refresh();
        $this->assertSame('ready', $media->optimization_status);
        $this->assertNotEmpty($media->responsive_variants);
        $this->assertStringContainsString('/storage/media/optimized/', (string) $media->responsiveUrl(100));
        foreach ($media->responsive_variants as $variant) {
            Storage::disk('public')->assertExists($variant['jpeg']);
        }

        $this->assertSame(['processed' => 0, 'skipped' => 0, 'failed' => 0], app(MediaOptimizationService::class)->optimize());
    }

    public function test_private_import_rejects_a_non_allowlisted_source_before_any_network_request(): void
    {
        $candidate = RecipeMediaCandidate::query()->create([
            'recipe_id' => $this->recipe()->id, 'provider' => 'pexels', 'external_photo_id' => 'unsafe-source',
            'status' => 'selected_for_review', 'source_url' => 'https://127.0.0.1/private.jpg',
        ]);
        Http::fake();

        try {
            app(PexelsCandidateMediaService::class)->importStoredCandidate($candidate->load('recipe'));
            $this->fail('An untrusted host was accepted for private import.');
        } catch (ValidationException) {
            $this->addToAssertionCount(1);
        }

        Http::assertNothingSent();
        $this->assertSame(0, Media::query()->count());
    }

    public function test_license_and_attribution_audits_do_not_replace_a_prior_human_approval(): void
    {
        $media = $this->approvedMedia($this->recipe(), 'media/approved.jpg');

        app(MediaExcellenceService::class)->auditLicense();
        app(MediaExcellenceService::class)->auditAttribution();

        $media->refresh();
        $this->assertSame('approved', $media->license_review_status);
        $this->assertSame('approved', $media->attribution_review_status);
        $this->assertTrue($media->isApprovedRealMedia());
    }

    private function recipe(): Recipe
    {
        return Recipe::query()->create([
            'title' => 'Medya mükemmellik tarif testi', 'slug' => 'medya-mukemmellik-'.str()->lower(str()->random(8)),
            'excerpt' => 'Doğrulanmış medya çalışma örneği.', 'description' => str_repeat('Tarif doğrulama bağlamı. ', 8),
            'servings' => 2, 'prep_minutes' => 10, 'cook_minutes' => 20, 'rest_minutes' => 0,
            'difficulty' => 'easy', 'status' => 'published', 'verification_status' => 'verified', 'published_at' => now(),
        ]);
    }

    private function approvedMedia(Recipe $recipe, string $path, bool $publicReady = false): Media
    {
        $attributes = [
            'kind' => 'image', 'disk' => 'public', 'path' => $path, 'visibility' => 'public',
            'media_status' => 'approved_real_media', 'fidelity_status' => 'strong_match', 'fidelity_confidence' => 95,
            'license_review_status' => 'approved', 'attribution_review_status' => 'approved', 'optimization_status' => 'ready',
            'verification_status' => 'verified', 'alt_text' => 'Tarifle eşleşen lisanslı görsel', 'caption' => 'Tarifle eşleşen lisanslı görsel.',
            'source_name' => 'Pexels', 'source_url' => 'https://www.pexels.com/photo/test', 'license' => 'Pexels License',
            'attribution' => 'Fotoğraf: House Chef test / Pexels', 'media_reviewed_at' => now(),
            'license_reviewed_at' => now(), 'attribution_reviewed_at' => now(), 'health_scanned_at' => now(),
        ];

        if ($publicReady) {
            $reviewer = User::factory()->create(['role' => 'admin', 'email_verified_at' => now()]);
            Storage::disk('public')->put($path, 'approved source');
            $attributes['responsive_variants'] = collect(['thumbnail', 'square', 'card', 'open_graph', 'hero'])->map(function (string $profile) use ($path): array {
                $prefix = str($path)->beforeLast('.')->toString().'-'.$profile;
                $jpeg = $prefix.'.jpg';
                $webp = $prefix.'.webp';
                Storage::disk('public')->put($jpeg, 'jpeg');
                Storage::disk('public')->put($webp, 'webp');

                return ['name' => $profile, 'width' => 1200, 'height' => 800, 'jpeg' => $jpeg, 'webp' => $webp];
            })->all();
            $attributes['media_reviewer_user_id'] = $reviewer->id;
            $attributes['approved_by_user_id'] = $reviewer->id;
            $attributes['approved_at'] = now();
        }

        return $recipe->media()->create($attributes);
    }
}
