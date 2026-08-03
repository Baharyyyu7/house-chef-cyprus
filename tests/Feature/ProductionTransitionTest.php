<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Models\BlogPost;
use App\Models\ContentQualityProfile;
use App\Models\Recipe;
use App\Models\User;
use App\Services\ProductionContentReadinessService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

final class ProductionTransitionTest extends TestCase
{
    use RefreshDatabase;

    public function test_production_information_pages_are_public_indexable_and_use_real_policy_language(): void
    {
        foreach (['besin-degeri-aciklamasi', 'alerjen-uyarisi', 'gorsel-ve-medya-politikasi', 'icerik-duzeltme-politikasi', 'hata-bildir'] as $page) {
            $this->get(route('information.show', $page))
                ->assertOk()
                ->assertSee('canonical')
                ->assertDontSee('Beta');
        }
    }

    public function test_quality_scan_preserves_catalogue_and_caps_unapproved_or_fallback_content(): void
    {
        $this->seed();
        $result = app(ProductionContentReadinessService::class)->scan();
        $fallbackRecipe = Recipe::query()->where('image_status', 'placeholder')->firstOrFail();
        $draft = Recipe::query()->where('content_origin', 'content_factory_draft')->firstOrFail();

        $this->assertSame(500, $result['recipes']);
        $this->assertSame(200, $result['blogs']);
        $this->assertSame(0, $result['ready_for_human_review']);
        $this->assertSame(50, Recipe::query()->where('status', 'published')->where('verification_status', 'verified')->count());
        $this->assertSame(450, Recipe::query()->where('content_origin', 'content_factory_draft')->where('status', 'draft')->count());
        $this->assertSame(180, BlogPost::query()->where('status', 'draft')->count());
        $this->assertSame(700, ContentQualityProfile::query()->count());
        $this->assertLessThanOrEqual(89, ContentQualityProfile::query()->where('entity_public_id', $fallbackRecipe->public_id)->firstOrFail()->readiness_cap);
        $this->assertLessThanOrEqual(49, ContentQualityProfile::query()->where('entity_public_id', $draft->public_id)->firstOrFail()->readiness_cap);
    }

    public function test_production_command_center_is_restricted_and_rescan_never_publishes_drafts(): void
    {
        $this->seed();
        app(ProductionContentReadinessService::class)->scan();
        $admin = User::factory()->create(['role' => 'admin', 'email_verified_at' => now()]);
        $member = User::factory()->create(['role' => 'member', 'email_verified_at' => now()]);

        $this->actingAs($member)->get(route('admin.production-content.index'))->assertForbidden();
        $this->actingAs($member)->post(route('admin.production-content.rescan'))->assertForbidden();
        $this->actingAs($admin)->get(route('admin.production-content.index'))
            ->assertOk()
            ->assertSee('Production İçerik Merkezi')
            ->assertSee('Otomatik kontroller geçti')
            ->assertDontSee('passed automated checks');
        $this->actingAs($admin)->post(route('admin.production-content.rescan'))->assertRedirect();

        $this->assertSame(0, Recipe::query()->where('content_origin', 'content_factory_draft')->where('status', 'published')->count());
        $this->assertSame(0, BlogPost::query()->where('status', 'draft')->whereNotNull('published_at')->count());
    }

    public function test_production_audit_command_has_a_stable_machine_readable_contract(): void
    {
        $this->seed();

        $this->artisan('content:production-audit --json')
            ->expectsOutputToContain('"human_approval_required":true')
            ->assertSuccessful();
    }

    public function test_public_and_admin_interface_language_audit_rejects_no_production_terms(): void
    {
        $this->artisan('app:production-language-audit --json')
            ->expectsOutputToContain('"passed":true')
            ->assertSuccessful();
    }

    public function test_graduation_requires_a_human_action_and_never_auto_publishes_a_blocked_draft(): void
    {
        $this->seed();
        app(ProductionContentReadinessService::class)->scan();
        $admin = User::factory()->create(['role' => 'admin', 'email_verified_at' => now()]);
        $editor = User::factory()->create(['role' => 'content_editor', 'email_verified_at' => now()]);
        $profile = ContentQualityProfile::query()->where('content_type', 'recipe')->where('publication_status', 'blocked')->firstOrFail();

        $this->actingAs($editor)->post(route('admin.production-content.advance', $profile), ['stage' => 'technical_review'])->assertRedirect();
        $this->actingAs($editor)->post(route('admin.production-content.approve', $profile))->assertForbidden();
        $this->actingAs($admin)->post(route('admin.production-content.approve', $profile))->assertSessionHasErrors('approval');
        $this->actingAs($admin)->post(route('admin.production-content.publish', $profile))->assertSessionHasErrors('publication');

        $this->assertSame('technical_review', $profile->fresh()->graduation_stage);
        $this->assertSame('draft', Recipe::query()->where('public_id', $profile->entity_public_id)->firstOrFail()->status);
    }
}
