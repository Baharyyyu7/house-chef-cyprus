<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Models\BlogPost;
use App\Models\ContentOpportunity;
use App\Models\Recipe;
use App\Models\SeoIssue;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

final class SeoContentAutomationTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_can_open_seo_operations_while_members_are_forbidden(): void
    {
        $admin = User::factory()->create(['role' => 'admin', 'email_verified_at' => now()]);
        $member = User::factory()->create(['role' => 'member']);

        $this->actingAs($admin)->get(route('admin.seo.index'))->assertOk()->assertSee('SEO ve Büyüme');
        $this->actingAs($member)->get(route('admin.seo.index'))->assertForbidden();
    }

    public function test_seo_audit_is_idempotent_and_resolves_disappearing_issues(): void
    {
        $post = $this->makePost(['status' => 'published', 'published_at' => now(), 'meta_title' => null, 'meta_description' => null]);

        $this->artisan('seo:audit --json')->assertSuccessful();
        $issue = SeoIssue::query()->firstOrFail();
        $firstDetected = $issue->first_detected_at;
        $this->artisan('seo:audit')->assertSuccessful();

        $this->assertSame(2, SeoIssue::query()->count());
        $this->assertTrue($firstDetected->equalTo($issue->fresh()->first_detected_at));

        $post->update(['meta_title' => 'Özgün başlık', 'meta_description' => 'Yeterli uzunlukta, özgün ve yararlı açıklama.']);
        $this->artisan('seo:audit')->assertSuccessful();
        $this->assertNotNull($issue->fresh()->resolved_at);
    }

    public function test_publish_requires_people_first_editorial_quality_gate(): void
    {
        $admin = User::factory()->create(['role' => 'admin', 'email_verified_at' => now()]);
        $post = $this->makePost();

        $this->actingAs($admin)->withSession(['_token' => 'test-csrf'])->post(route('admin.blog.publish', $post), ['_token' => 'test-csrf'])->assertStatus(422);

        $recipe = Recipe::query()->create([
            'title' => 'Doğrulanmış tarif', 'slug' => 'dogrulanmis-tarif', 'excerpt' => 'Özgün tarif özeti', 'description' => 'Doğrulanmış tarif açıklaması',
            'status' => 'published', 'verification_status' => 'verified', 'published_at' => now(),
        ]);
        $post->recipes()->sync([$recipe->id]);
        $post->update([
            'content_purpose' => 'Ev mutfağı için özgün rehber', 'search_intent' => 'informational', 'primary_keyword' => 'ev mutfağı',
            'source_notes' => ['editorial_note' => 'Birincil kaynaklar ve editoryal notlar kontrol edildi.'], 'fact_check_status' => 'passed',
            'originality_status' => 'passed', 'editorial_status' => 'approved', 'meta_title' => 'Ev mutfağı rehberi', 'meta_description' => 'Özgün, kısa ve yararlı ev mutfağı rehberi açıklaması.',
        ]);

        $this->actingAs($admin)->withSession(['_token' => 'test-csrf'])->post(route('admin.blog.publish', $post), ['_token' => 'test-csrf'])->assertRedirect();
        $this->assertSame('published', $post->fresh()->status);
    }

    public function test_scheduled_publication_requires_all_approval_states(): void
    {
        $approved = $this->makePost(['slug' => 'onayli-planli-yazi', 'status' => 'scheduled', 'scheduled_at' => now()->subMinute(), 'fact_check_status' => 'passed', 'originality_status' => 'passed', 'editorial_status' => 'approved']);
        $blocked = $this->makePost(['slug' => 'engelli-planli-yazi', 'status' => 'scheduled', 'scheduled_at' => now()->subMinute(), 'fact_check_status' => 'pending', 'originality_status' => 'passed', 'editorial_status' => 'approved']);

        $this->artisan('blog:publish-scheduled --json')->expectsOutputToContain('"published":1')->assertSuccessful();
        $this->assertSame('published', $approved->fresh()->status);
        $this->assertSame('scheduled', $blocked->fresh()->status);
    }

    public function test_zero_result_queries_are_anonymous_and_can_create_safe_opportunities(): void
    {
        // Fırsat üretimi artık tekrar şartına bağlı: tek seferlik test
        // aramaları editoryal öneri listesine giriyordu. Eşiği aşacak kadar
        // arama yapılıyor.
        $esik = (int) config('seo.audit.minimum_zero_result_searches', 5);

        for ($i = 0; $i < $esik; $i++) {
            $this->get(route('recipes.index', ['q' => 'limonlu nohut kasesi']))->assertOk();
        }

        $this->artisan('seo:content-opportunities --json')->assertSuccessful();

        $this->assertDatabaseHas('zero_result_searches', ['normalized_query' => 'limonlu nohut kasesi']);
        $this->assertDatabaseHas('content_opportunities', ['normalized_key' => 'zero-limonlu nohut kasesi']);
        $this->assertDatabaseMissing('zero_result_searches', ['normalized_query' => '127.0.0.1']);
    }

    public function test_generated_content_opportunities_are_editorial_candidates_and_never_publication_instructions(): void
    {
        $this->artisan('seo:content-opportunities --json')->assertSuccessful();

        $opportunity = ContentOpportunity::query()
            ->where('normalized_key', 'cluster-kibris-mutfagi')
            ->sole();

        $this->assertSame('open', $opportunity->status);
        $this->assertTrue($opportunity->evidence['publication_requires_human_review']);
        $this->assertSame('draft_only', $opportunity->evidence['delivery_policy']);
        $this->assertArrayHasKey('collected_at', $opportunity->evidence);
    }

    public function test_safe_seo_commands_never_call_external_providers_by_default(): void
    {
        $this->artisan('seo:provider-health --json')->assertSuccessful();
        $this->artisan('seo:indexnow-submit --json')->expectsOutputToContain('provider_disabled')->assertSuccessful();
        $this->artisan('seo:search-console-import --json')->expectsOutputToContain('provider_disabled')->assertSuccessful();
        $this->artisan('seo:social-drafts --dry-run --json')->expectsOutputToContain('"auto_posted":false')->assertSuccessful();
    }

    /** @param array<string, mixed> $overrides */
    private function makePost(array $overrides = []): BlogPost
    {
        return BlogPost::query()->create(array_merge([
            'title' => 'Özgün blog rehberi '.str()->random(8), 'slug' => 'ozgun-blog-rehberi-'.str()->lower(str()->random(8)),
            'excerpt' => 'Özgün ve faydalı kısa içerik özeti.', 'body' => 'House Chef Cyprus için özgün editoryal içerik.', 'status' => 'draft',
        ], $overrides));
    }
}
