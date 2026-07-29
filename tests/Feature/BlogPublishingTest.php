<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Models\BlogCategory;
use App\Models\BlogPost;
use App\Models\BlogTag;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

final class BlogPublishingTest extends TestCase
{
    use RefreshDatabase;

    public function test_only_published_posts_are_visible_to_guests(): void
    {
        $published = BlogPost::create(['title' => 'Yayınlanan mutfak notu', 'slug' => 'yayinlanan-mutfak-notu', 'excerpt' => 'Özet', 'body' => 'İçerik', 'status' => 'published', 'published_at' => now()]);
        $draft = BlogPost::create(['title' => 'Taslak mutfak notu', 'slug' => 'taslak-mutfak-notu', 'excerpt' => 'Özet', 'body' => 'İçerik', 'status' => 'draft']);

        $this->get(route('blog.index'))->assertOk()->assertSee($published->title)->assertDontSee($draft->title);
        $this->get(route('blog.show', $published))->assertOk()->assertSee($published->title);
        $this->get(route('blog.show', $draft))->assertNotFound();
        $this->get('/sitemap.xml')->assertOk()->assertSee('/sitemaps/blog.xml');
        $this->get('/sitemaps/blog.xml')->assertOk()->assertSee($published->slug)->assertDontSee($draft->slug);
    }

    public function test_admin_can_create_and_publish_a_post_while_a_member_is_forbidden(): void
    {
        $admin = User::factory()->create(['role' => 'admin', 'email_verified_at' => now()]);
        $member = User::factory()->create(['role' => 'member', 'email_verified_at' => now()]);
        $category = BlogCategory::create(['name' => 'Mutfak', 'slug' => 'mutfak']);
        $tag = BlogTag::create(['name' => 'Kıbrıs', 'slug' => 'kibris']);

        $this->actingAs($member)->get(route('admin.blog.index'))->assertForbidden();
        $this->actingAs($admin)->post(route('admin.blog.store'), [
            'title' => 'Kıbrıs mutfağına giriş', 'excerpt' => 'Özgün kısa özet.', 'body' => 'Özgün güvenli içerik.', 'status' => 'draft', 'blog_category_id' => $category->id, 'tag_ids' => [$tag->id],
        ])->assertRedirect();

        $post = BlogPost::query()->firstOrFail();
        $this->assertSame('draft', $post->status);
        $this->assertTrue($post->tags()->whereKey($tag)->exists());
        $this->actingAs($admin)->post(route('admin.blog.publish', $post))->assertRedirect();
        $this->get(route('blog.show', $post->fresh()))->assertOk();
    }

    public function test_scheduled_posts_only_publish_when_their_time_has_arrived(): void
    {
        $due = BlogPost::create(['title' => 'Zamanı gelen yazı', 'slug' => 'zamani-gelen-yazi', 'excerpt' => 'Özet', 'body' => 'İçerik', 'status' => 'scheduled', 'scheduled_at' => now()->subMinute()]);
        $future = BlogPost::create(['title' => 'Gelecek yazı', 'slug' => 'gelecek-yazi', 'excerpt' => 'Özet', 'body' => 'İçerik', 'status' => 'scheduled', 'scheduled_at' => now()->addHour()]);

        $this->artisan('blog:publish-scheduled --json')->expectsOutputToContain('"published":1')->assertSuccessful();
        $this->assertSame('published', $due->fresh()->status);
        $this->assertSame('scheduled', $future->fresh()->status);
    }
}
