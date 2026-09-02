<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Models\BlogCategory;
use App\Models\BlogPost;
use App\Models\BlogTag;
use App\Models\Recipe;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

final class BlogPublishingTest extends TestCase
{
    use RefreshDatabase;

    public function test_only_published_posts_are_visible_to_guests(): void
    {
        $published = BlogPost::create(['title' => 'Yayınlanan mutfak notu', 'slug' => 'yayinlanan-mutfak-notu', 'excerpt' => 'Özet', 'body' => str_repeat('Yazının özgün gövde metni; konusunu taşıyacak uzunlukta. ', 40), 'fact_check_status' => 'passed', 'originality_status' => 'passed',  'status' => 'published', 'published_at' => now()]);
        $draft = BlogPost::create(['title' => 'Taslak mutfak notu', 'slug' => 'taslak-mutfak-notu', 'excerpt' => 'Özet', 'body' => str_repeat('Yazının özgün gövde metni; konusunu taşıyacak uzunlukta. ', 40), 'fact_check_status' => 'passed', 'originality_status' => 'passed',  'status' => 'draft']);

        $this->get(route('blog.index'))->assertOk()->assertSee($published->title)->assertDontSee($draft->title);
        $this->get(route('blog.show', $published))->assertOk()->assertSee($published->title);
        $this->get(route('blog.show', $draft))->assertNotFound();
        $this->get('/sitemap.xml')->assertOk()->assertSee('/sitemaps/blog.xml');
        $this->get('/sitemaps/blog.xml')->assertOk()->assertSee($published->slug)->assertDontSee($draft->slug);
    }

    public function test_blog_without_approved_media_uses_an_honest_non_photographic_state(): void
    {
        $post = BlogPost::create([
            'title' => 'Kaynağı doğrulanan mutfak notu',
            'slug' => 'kaynagi-dogrulanan-mutfak-notu',
            'excerpt' => 'Editoryal görseli henüz onaylanmamış yazı.',
            'body' => str_repeat('Yazının özgün gövde metni; konusunu taşıyacak uzunlukta. ', 40), 'fact_check_status' => 'passed', 'originality_status' => 'passed', 
            'status' => 'published',
            'published_at' => now(),
        ]);

        // Liste artık görselsiz: yazının kendisi başlık ve özetle temsil
        // ediliyor, olmayan fotoğrafın yerine hiçbir şey konmuyor.
        $this->get(route('blog.index'))
            ->assertOk()
            ->assertSee($post->title)
            ->assertDontSee('<picture>', false);

        $this->get(route('blog.show', $post))
            ->assertOk()
            ->assertSee('Bu yazı için onaylı görsel bekleniyor')
            ->assertSee('genel veya ilgisiz fotoğraf gösterilmez.');
    }

    public function test_blog_index_schema_lists_only_the_visible_page_items_as_plain_urls(): void
    {
        $visible = collect(range(1, 13))->map(fn (int $i) => BlogPost::create([
            'title' => 'Yazı '.$i, 'slug' => 'yazi-'.$i, 'excerpt' => 'Özet', 'body' => str_repeat('Yazının özgün gövde metni; konusunu taşıyacak uzunlukta. ', 40), 'fact_check_status' => 'passed', 'originality_status' => 'passed', 
            'status' => 'published', 'published_at' => now()->subMinutes($i),
        ]));
        BlogPost::create(['title' => 'Taslak', 'slug' => 'taslak', 'excerpt' => 'Özet', 'body' => str_repeat('Yazının özgün gövde metni; konusunu taşıyacak uzunlukta. ', 40), 'fact_check_status' => 'passed', 'originality_status' => 'passed',  'status' => 'draft']);

        $html = (string) $this->get(route('blog.index'))->assertOk()->getContent();
        preg_match_all('#<script type="application/ld\+json">(.*?)</script>#s', $html, $matches);
        $schema = collect($matches[1] ?? [])->map(fn (string $json) => json_decode($json, true))
            ->first(fn ($json) => collect($json['@graph'] ?? [])->contains('@type', 'CollectionPage'));
        $collection = collect($schema['@graph'])->firstWhere('@type', 'CollectionPage');
        $items = $collection['mainEntity']['itemListElement'];

        $this->assertCount(12, $items);
        $this->assertSame(['@type', 'position', 'url'], array_keys($items[0]));
        $this->assertSame(route('blog.show', $visible->first()), $items[0]['url']);
        $this->assertStringNotContainsString(route('blog.show', 'taslak'), json_encode($schema));
    }

    public function test_admin_can_create_and_publish_a_post_while_a_member_is_forbidden(): void
    {
        $admin = User::factory()->create(['role' => 'admin', 'email_verified_at' => now()]);
        $member = User::factory()->create(['role' => 'member', 'email_verified_at' => now()]);
        $category = BlogCategory::create(['name' => 'Mutfak', 'slug' => 'mutfak']);
        $tag = BlogTag::create(['name' => 'Kıbrıs', 'slug' => 'kibris']);

        $this->actingAs($member)->get(route('admin.blog.index'))->assertForbidden();
        $this->actingAs($admin)->withSession(['_token' => 'test-csrf'])->post(route('admin.blog.store'), [
            '_token' => 'test-csrf', 'title' => 'Kıbrıs mutfağına giriş', 'excerpt' => 'Özgün kısa özet.', 'body' => 'Özgün güvenli içerik.', 'status' => 'draft', 'blog_category_id' => $category->id, 'tag_ids' => [$tag->id],
        ])->assertRedirect();

        $post = BlogPost::query()->firstOrFail();
        $this->assertSame('draft', $post->status);
        $this->assertTrue($post->tags()->whereKey($tag)->exists());
        $this->assertDatabaseHas('seo_metadata', ['seoable_type' => BlogPost::class, 'seoable_id' => $post->id]);
        $recipe = Recipe::query()->create(['title' => 'İlgili doğrulanmış tarif', 'slug' => 'ilgili-dogrulanmis-tarif', 'status' => 'published', 'verification_status' => 'verified', 'published_at' => now()]);
        $post->recipes()->sync([$recipe->id]);
        $post->update(['content_purpose' => 'Özgün rehber', 'search_intent' => 'informational', 'primary_keyword' => 'kıbrıs mutfağı', 'source_notes' => ['editorial_note' => 'Kontrol edildi'], 'fact_check_status' => 'passed', 'originality_status' => 'passed', 'editorial_status' => 'approved', 'meta_title' => 'Kıbrıs mutfağına giriş', 'meta_description' => 'Kıbrıs mutfağına özgün ve güvenli giriş rehberi.']);
        $this->actingAs($admin)->withSession(['_token' => 'test-csrf'])->post(route('admin.blog.publish', $post), ['_token' => 'test-csrf'])->assertRedirect();
        $this->get(route('blog.show', $post->fresh()))->assertOk();
    }

    public function test_scheduled_posts_only_publish_when_their_time_has_arrived(): void
    {
        $due = BlogPost::create(['title' => 'Zamanı gelen yazı', 'slug' => 'zamani-gelen-yazi', 'excerpt' => 'Özet', 'body' => str_repeat('Yazının özgün gövde metni; konusunu taşıyacak uzunlukta. ', 40), 'fact_check_status' => 'passed', 'originality_status' => 'passed',  'status' => 'scheduled', 'scheduled_at' => now()->subMinute(), 'fact_check_status' => 'passed', 'originality_status' => 'passed', 'editorial_status' => 'approved']);
        $future = BlogPost::create(['title' => 'Gelecek yazı', 'slug' => 'gelecek-yazi', 'excerpt' => 'Özet', 'body' => str_repeat('Yazının özgün gövde metni; konusunu taşıyacak uzunlukta. ', 40), 'fact_check_status' => 'passed', 'originality_status' => 'passed',  'status' => 'scheduled', 'scheduled_at' => now()->addHour(), 'fact_check_status' => 'passed', 'originality_status' => 'passed', 'editorial_status' => 'approved']);

        $this->artisan('blog:publish-scheduled --json')->expectsOutputToContain('"published":1')->assertSuccessful();
        $this->assertSame('published', $due->fresh()->status);
        $this->assertSame('scheduled', $future->fresh()->status);
    }
}
