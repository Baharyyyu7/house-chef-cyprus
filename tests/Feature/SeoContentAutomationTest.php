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
        // Kapıdan geçmiş yazı: indekse açık olduğu için eksik SEO metni
        // gerçekten arama sonucunda görünen bir kusur.
        $post = $this->makePost([
            'status' => 'published', 'published_at' => now(), 'meta_title' => null, 'meta_description' => null,
            'body' => str_repeat('House Chef Cyprus için özgün editoryal içerik. ', 60),
            'editorial_status' => 'approved', 'fact_check_status' => 'passed', 'originality_status' => 'passed',
        ]);

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

        $this->actingAs($admin)->withSession(['_token' => 'test-csrf'])->post(route('admin.blog.publish', $post), ['_token' => 'test-csrf'])
            ->assertRedirect()
            ->assertSessionHasErrors('publication');

        $recipe = Recipe::query()->create([
            'title' => 'Doğrulanmış tarif', 'slug' => 'dogrulanmis-tarif', 'excerpt' => 'Özgün tarif özeti', 'description' => 'Doğrulanmış tarif açıklaması',
            'status' => 'published', 'verification_status' => 'verified', 'published_at' => now(),
        ]);
        $post->recipes()->sync([$recipe->id]);
        $post->update([
            'content_purpose' => 'Ev mutfağı için özgün rehber', 'search_intent' => 'informational', 'primary_keyword' => 'ev mutfağı',
            'secondary_keywords' => ['ev mutfağı rehberi', 'mutfak teknikleri', 'doğrulanmış tarif'],
            'faq_blocks' => [['question' => 'Nedir?', 'answer' => 'Rehberdir.'], ['question' => 'Nasıl?', 'answer' => 'Adımlarla.'], ['question' => 'Neden?', 'answer' => 'Doğruluk için.']],
            'body' => '## Temel bilgiler'.PHP_EOL.implode(' ', array_fill(0, 896, 'kelime')).PHP_EOL.'## Uygulama adımları',
            'source_notes' => ['editorial_note' => 'Birincil kaynaklar ve editoryal notlar kontrol edildi.'], 'fact_check_status' => 'passed',
            'originality_status' => 'passed', 'editorial_status' => 'approved', 'meta_title' => 'Ev mutfağı rehberi', 'meta_description' => str_repeat('a', 150),
            'human_approved_at' => now(), 'human_approved_by_user_id' => $admin->id,
        ]);

        $this->actingAs($admin)->withSession(['_token' => 'test-csrf'])->post(route('admin.blog.publish', $post), ['_token' => 'test-csrf'])->assertRedirect();
        $this->assertSame('published', $post->fresh()->status);
    }

    public function test_failed_single_publication_returns_to_blog_list_with_reasons(): void
    {
        $admin = User::factory()->create(['role' => 'admin', 'email_verified_at' => now()]);
        $post = $this->makePost();

        $this->actingAs($admin)
            ->from(route('admin.blog.index'))
            ->post(route('admin.blog.publish', $post))
            ->assertRedirect(route('admin.blog.index'))
            ->assertSessionHasErrors('publication');

        $this->assertSame('draft', $post->fresh()->status);
    }

    public function test_blog_index_has_a_separate_safe_bulk_publication_section(): void
    {
        $admin = User::factory()->create(['role' => 'admin', 'email_verified_at' => now()]);

        $this->actingAs($admin)->get(route('admin.blog.index'))
            ->assertOk()
            ->assertSee('Uygun olanların tümünü yayınla')
            ->assertSee(route('admin.blog.publish-ready'), false);
    }

    public function test_bulk_publication_publishes_only_ready_posts_and_reports_skipped_count(): void
    {
        $admin = User::factory()->create(['role' => 'admin', 'email_verified_at' => now()]);
        $ready = $this->makePost([
            'slug' => 'toplu-yayina-hazir',
            'fact_check_status' => 'passed',
            'originality_status' => 'passed',
            'editorial_status' => 'approved',
        ]);
        $this->completeForPublication($ready);
        $blocked = $this->makePost([
            'title' => 'Tamamen farklı ve eksik bir içerik',
            'slug' => 'toplu-yayina-uygun-degil',
            'excerpt' => 'Bu kayıt kalite alanları eksik olduğu için atlanmalıdır.',
        ]);
        $assessment = app(\App\Services\Seo\BlogPublicationReadinessService::class)->evaluate($ready->fresh());
        $this->assertTrue($assessment['passed'], json_encode($assessment['issues'], JSON_UNESCAPED_UNICODE));

        $this->actingAs($admin)->post(route('admin.blog.publish-ready'))
            ->assertRedirect(route('admin.blog.index'))
            ->assertSessionHas('status', '1 yazı yayınlandı. 1 yazı atlandı.');

        $this->assertSame('published', $ready->fresh()->status);
        $this->assertSame('draft', $blocked->fresh()->status);
    }

    public function test_bulk_publication_with_no_ready_posts_explains_why_and_shows_reasons(): void
    {
        $admin = User::factory()->create(['role' => 'admin', 'email_verified_at' => now()]);
        $blocked = $this->makePost([
            'title' => 'Otomatik üretilmiş kısa taslak',
            'slug' => 'otomatik-kisa-taslak',
            'excerpt' => 'Bu kayıt kalite alanları eksik olduğu için taslak kalmalıdır.',
        ]);

        $this->actingAs($admin)->post(route('admin.blog.publish-ready'))
            ->assertRedirect(route('admin.blog.index'))
            ->assertSessionHas('status', 'Yayına hazır yazı bulunamadı: 1 taslak kalite ve onay kapılarını henüz geçmiyor, bu yüzden taslak olarak bırakıldı. Nedenler aşağıda.');

        $this->assertSame('draft', $blocked->fresh()->status);

        $this->actingAs($admin)
            ->withSession(['status' => 'x', 'publication_reasons' => ['İnsan yayın onayı kaydı gerekli.' => 1]])
            ->get(route('admin.blog.index'))
            ->assertOk()
            ->assertSee('<details class="mt-4" open>', false)
            ->assertSee('İnsan yayın onayı kaydı gerekli. (1 yazı)');
    }

    public function test_scheduled_publication_requires_all_approval_states(): void
    {
        $approved = $this->makePost(['slug' => 'onayli-planli-yazi', 'status' => 'scheduled', 'scheduled_at' => now()->subMinute(), 'fact_check_status' => 'passed', 'originality_status' => 'passed', 'editorial_status' => 'approved']);
        $this->completeForPublication($approved);
        $blocked = $this->makePost(['slug' => 'engelli-planli-yazi', 'status' => 'scheduled', 'scheduled_at' => now()->subMinute(), 'fact_check_status' => 'pending', 'originality_status' => 'passed', 'editorial_status' => 'approved']);

        $this->artisan('blog:publish-scheduled --json')->expectsOutputToContain('"published":1')->assertSuccessful();
        $this->assertSame('published', $approved->fresh()->status);
        $this->assertSame('scheduled', $blocked->fresh()->status);
    }

    public function test_scheduled_publication_rejects_short_post_without_human_approval(): void
    {
        $post = $this->makePost([
            'status' => 'scheduled', 'scheduled_at' => now()->subMinute(),
            'editorial_status' => 'approved', 'fact_check_status' => 'passed', 'originality_status' => 'passed',
        ]);

        $this->artisan('blog:publish-scheduled --json')->expectsOutputToContain('"published":0')->assertSuccessful();
        $this->assertSame('scheduled', $post->fresh()->status);
    }

    public function test_auto_drip_dry_run_never_changes_a_draft(): void
    {
        $post = $this->makePost([
            'editorial_status' => 'approved', 'fact_check_status' => 'passed', 'originality_status' => 'passed',
        ]);

        $this->artisan('seo:auto-drip --dry-run --limit=3')->assertSuccessful();
        $this->assertSame('draft', $post->fresh()->status);
        $this->assertNull($post->fresh()->published_at);
    }

    public function test_draft_readiness_report_is_read_only(): void
    {
        $post = $this->makePost(['status' => 'draft']);
        $updatedAt = $post->updated_at;

        $this->artisan('seo:blog-draft-readiness --json --limit=5')
            ->expectsOutputToContain('"drafts":1')
            ->assertSuccessful();

        $this->assertTrue($updatedAt->equalTo($post->fresh()->updated_at));
        $this->assertSame('draft', $post->fresh()->status);
    }

    public function test_auto_drip_schedule_uses_daily_limit_of_three(): void
    {
        $source = file_get_contents(base_path('routes/console.php'));

        $this->assertStringContainsString("Artisan::call('seo:auto-drip', ['--limit' => 3])", $source);
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

    private function completeForPublication(BlogPost $post): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $recipe = Recipe::query()->create([
            'title' => 'Planlı doğrulanmış tarif', 'slug' => 'planli-dogrulanmis-tarif', 'excerpt' => 'Özgün tarif özeti',
            'description' => 'Doğrulanmış tarif açıklaması', 'status' => 'published', 'verification_status' => 'verified', 'published_at' => now(),
        ]);
        $post->recipes()->sync([$recipe->id]);
        $post->update([
            'body' => '## Temel bilgiler'.PHP_EOL.implode(' ', array_fill(0, 896, 'kelime')).PHP_EOL.'## Uygulama adımları',
            'content_purpose' => 'Doğrulanmış mutfak rehberi', 'search_intent' => 'informational', 'primary_keyword' => 'mutfak rehberi',
            'secondary_keywords' => ['mutfak rehberi', 'pişirme tekniği', 'doğrulanmış tarif'],
            'faq_blocks' => [['question' => 'Nedir?', 'answer' => 'Rehberdir.'], ['question' => 'Nasıl?', 'answer' => 'Adımlarla.'], ['question' => 'Neden?', 'answer' => 'Doğruluk için.']],
            'source_notes' => ['editorial_note' => 'Kaynaklar doğrulandı.'], 'meta_title' => 'Doğrulanmış mutfak rehberi', 'meta_description' => str_repeat('a', 150),
            'human_approved_at' => now(), 'human_approved_by_user_id' => $admin->id,
        ]);
    }
}
