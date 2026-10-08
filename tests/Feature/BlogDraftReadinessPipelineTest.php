<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Models\BlogPost;
use App\Models\Recipe;
use App\Models\User;
use App\Services\Seo\BlogDraftPreparationService;
use App\Services\Seo\BlogDraftReadinessSnapshotService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

final class BlogDraftReadinessPipelineTest extends TestCase
{
    use RefreshDatabase;

    public function test_snapshot_reports_grouped_blockers_without_changing_drafts(): void
    {
        $ready = $this->readyPost();
        $blocked = BlogPost::query()->create([
            'author_id' => $ready->author_id, 'title' => 'Eksik taslak', 'slug' => 'eksik-taslak',
            'excerpt' => 'Kısa özet', 'body' => 'Kısa içerik', 'status' => 'draft',
        ]);
        $before = $blocked->fresh()->getAttributes();

        $snapshot = app(BlogDraftReadinessSnapshotService::class)->snapshot(1);

        $this->assertSame(2, $snapshot['total']);
        $this->assertSame(1, $snapshot['ready']);
        $this->assertSame(1, $snapshot['blocked']);
        $this->assertSame(1, $snapshot['reasons']['Özgün içerik amacı belirtilmemiş.']);
        $this->assertCount(1, $snapshot['samples']);
        $this->assertSame($before, $blocked->fresh()->getAttributes());
    }

    public function test_preparation_derives_only_safe_existing_metadata_and_is_idempotent(): void
    {
        $post = BlogPost::query()->create([
            'author_id' => User::factory()->create(['role' => 'admin'])->id,
            'title' => 'Kıbrıs Mutfağında Mevsimsel Sebzeler İçin Uygulamalı ve Uzun Rehber',
            'slug' => 'mevsimsel-sebzeler',
            'excerpt' => str_repeat('Mevcut metinden alınan doğrulanmamış yeni iddia eklemeyen açıklama. ', 4),
            'body' => 'Mevcut gövde', 'status' => 'draft', 'fact_check_status' => 'pending',
            'originality_status' => 'pending', 'editorial_status' => 'draft',
        ]);

        $first = app(BlogDraftPreparationService::class)->prepare($post);
        $post->refresh();

        $this->assertEqualsCanonicalizing(['meta_title', 'meta_description', 'primary_keyword'], $first['changed']);
        $this->assertLessThanOrEqual(60, mb_strlen((string) $post->meta_title));
        $this->assertGreaterThanOrEqual(140, mb_strlen((string) $post->meta_description));
        $this->assertSame('draft', $post->status);
        $this->assertSame('pending', $post->fact_check_status);
        $this->assertSame('pending', $post->originality_status);
        $this->assertSame('draft', $post->editorial_status);
        $this->assertNull($post->human_approved_at);
        $this->assertSame([], app(BlogDraftPreparationService::class)->prepare($post)['changed']);
    }

    public function test_admin_can_scan_and_prepare_but_member_cannot(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $member = User::factory()->create(['role' => 'member']);
        BlogPost::query()->create([
            'author_id' => $admin->id, 'title' => 'Taslak', 'slug' => 'taslak',
            'excerpt' => str_repeat('Mevcut açıklama ', 15), 'body' => 'Kısa içerik', 'status' => 'draft',
        ]);

        $this->actingAs($admin)->post(route('admin.blog.readiness-scan'))
            ->assertRedirect(route('admin.blog.index'))->assertSessionHas('readiness_snapshot');
        $this->actingAs($admin)->post(route('admin.blog.prepare'))
            ->assertRedirect(route('admin.blog.index'))->assertSessionHas('preparation_summary');
        $this->actingAs($member)->post(route('admin.blog.readiness-scan'))->assertForbidden();
        $this->actingAs($member)->post(route('admin.blog.prepare'))->assertForbidden();
    }

    public function test_batch_moves_past_prepared_records_instead_of_reprocessing_them(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        foreach (range(1, 3) as $number) {
            BlogPost::query()->create([
                'author_id' => $admin->id, 'title' => 'Hazırlanabilir taslak '.$number,
                'slug' => 'hazirlanabilir-taslak-'.$number, 'excerpt' => str_repeat('Mevcut açıklama ', 15),
                'body' => 'Kısa içerik', 'status' => 'draft',
            ]);
        }

        $service = app(BlogDraftPreparationService::class);
        $this->assertSame(2, $service->prepareBatch(2)['changed']);
        $this->assertSame(1, $service->prepareBatch(2)['changed']);
        $this->assertSame(0, $service->prepareBatch(2)['processed']);
    }

    public function test_blog_index_renders_distinct_scan_prepare_and_publish_controls(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);

        $this->actingAs($admin)->get(route('admin.blog.index'))
            ->assertOk()
            ->assertSee('Taslakları denetle')
            ->assertSee('Güvenli alanları hazırla')
            ->assertSee('Hazır olanlardan 5 tanesini yayınla')
            ->assertSee(route('admin.blog.readiness-scan'), false)
            ->assertSee(route('admin.blog.prepare'), false);
    }

    private function readyPost(): BlogPost
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $post = BlogPost::query()->create([
            'author_id' => $admin->id, 'human_approved_by_user_id' => $admin->id, 'human_approved_at' => now(),
            'title' => 'Kıbrıs mutfağı için kapsamlı rehber', 'slug' => 'kibris-mutfagi-kapsamli-rehber',
            'excerpt' => 'Kıbrıs mutfağını ölçüler, teknikler ve doğrulanmış tariflerle anlatan kapsamlı rehber.',
            'body' => '## Temel bilgiler'.PHP_EOL.implode(' ', array_fill(0, 896, 'kelime')).PHP_EOL.'## Uygulama adımları',
            'status' => 'draft', 'content_purpose' => 'Ev aşçılarına doğrulanmış rehber sunmak',
            'search_intent' => 'informational', 'primary_keyword' => 'Kıbrıs mutfağı',
            'secondary_keywords' => ['Kıbrıs yemekleri', 'Kıbrıs tarifleri', 'Akdeniz mutfağı'],
            'faq_blocks' => [
                ['question' => 'Nedir?', 'answer' => 'Yanıt.'], ['question' => 'Nasıl?', 'answer' => 'Yanıt.'], ['question' => 'Ne zaman?', 'answer' => 'Yanıt.'],
            ],
            'source_notes' => ['editorial_note' => 'Kaynaklar editoryal olarak doğrulandı.'],
            'fact_check_status' => 'passed', 'originality_status' => 'passed', 'editorial_status' => 'approved',
            'meta_title' => 'Kıbrıs Mutfağı: Doğrulanmış Başlangıç Rehberi', 'meta_description' => str_repeat('a', 150),
        ]);
        $recipe = Recipe::query()->create([
            'title' => 'Doğrulanmış tarif', 'slug' => 'dogrulanmis-tarif', 'excerpt' => 'Özet',
            'description' => 'Açıklama', 'status' => 'published', 'verification_status' => 'verified', 'published_at' => now(),
        ]);
        $post->recipes()->sync([$recipe->id]);

        return $post;
    }
}
