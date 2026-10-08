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
        $published = BlogPost::create(['title' => 'Yayınlanan mutfak notu', 'slug' => 'yayinlanan-mutfak-notu', 'excerpt' => 'Özet', 'body' => $this->cesitliMetin('Yazının özgün gövde metni; konusunu taşıyacak uzunlukta. ', 40), 'fact_check_status' => 'passed', 'originality_status' => 'passed',  'status' => 'published', 'published_at' => now()]);
        $draft = BlogPost::create(['title' => 'Taslak mutfak notu', 'slug' => 'taslak-mutfak-notu', 'excerpt' => 'Özet', 'body' => $this->cesitliMetin('Yazının özgün gövde metni; konusunu taşıyacak uzunlukta. ', 40), 'fact_check_status' => 'passed', 'originality_status' => 'passed',  'status' => 'draft']);

        $this->get(route('blog.index'))->assertOk()->assertSee($published->title)->assertDontSee($draft->title);
        $this->get(route('blog.show', $published))->assertOk()->assertSee($published->title);
        $this->get(route('blog.show', $draft))->assertNotFound();
        $this->get('/sitemap.xml')->assertOk()->assertSee('/sitemaps/blog.xml');
        $this->get('/sitemaps/blog.xml')->assertOk()->assertSee($published->slug)->assertDontSee($draft->slug);
    }

    public function test_blog_without_approved_media_uses_the_shared_editorial_cover(): void
    {
        $post = BlogPost::create([
            'title' => 'Kaynağı doğrulanan mutfak notu',
            'slug' => 'kaynagi-dogrulanan-mutfak-notu',
            'excerpt' => 'Editoryal görseli henüz onaylanmamış yazı.',
            'body' => $this->cesitliMetin('Yazının özgün gövde metni; konusunu taşıyacak uzunlukta. ', 40), 'fact_check_status' => 'passed', 'originality_status' => 'passed', 
            'status' => 'published',
            'published_at' => now(),
        ]);

        $this->get(route('blog.index'))
            ->assertOk()
            ->assertSee($post->title)
            ->assertSee(asset('storage/media/system/blog-fallback-cover.png'), false);

        $this->get(route('blog.show', $post))
            ->assertOk()
            ->assertSee(asset('storage/media/system/blog-fallback-cover.png'), false)
            ->assertDontSee('Bu yazı için onaylı görsel bekleniyor');
    }

    public function test_blog_index_schema_lists_only_the_visible_page_items_as_plain_urls(): void
    {
        $visible = collect(range(1, 13))->map(fn (int $i) => BlogPost::create([
            'title' => 'Yazı '.$i, 'slug' => 'yazi-'.$i, 'excerpt' => 'Özet', 'body' => $this->cesitliMetin('Yazının özgün gövde metni; konusunu taşıyacak uzunlukta. ', 40), 'fact_check_status' => 'passed', 'originality_status' => 'passed', 
            'status' => 'published', 'published_at' => now()->subMinutes($i),
        ]));
        BlogPost::create(['title' => 'Taslak', 'slug' => 'taslak', 'excerpt' => 'Özet', 'body' => $this->cesitliMetin('Yazının özgün gövde metni; konusunu taşıyacak uzunlukta. ', 40), 'fact_check_status' => 'passed', 'originality_status' => 'passed',  'status' => 'draft']);

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
        // Yayın kapısı 23 Eylül 2026'da sıkılaştırıldı: en az 900 kelime,
        // 3-6 ikincil anahtar, 3-6 SSS, en az iki H2, 140-158 karakter SEO
        // açıklaması ve insan onayı kaydı isteniyor. Test bu sözleşmeyi
        // karşılayan bir yazıyla akışın hâlâ çalıştığını sabitler.
        $post->update([
            'content_purpose' => 'Özgün rehber', 'search_intent' => 'informational',
            'primary_keyword' => 'kıbrıs mutfağı',
            'secondary_keywords' => ['hellim', 'molohiya', 'kolokas', 'şeftali kebabı'],
            'source_notes' => ['editorial_note' => 'Kontrol edildi'],
            'fact_check_status' => 'passed', 'originality_status' => 'passed', 'editorial_status' => 'approved',
            'meta_title' => 'Kıbrıs mutfağına giriş',
            'meta_description' => 'Kıbrıs mutfağına giriş rehberi: hellim, molohiya, kolokas ve şeftali kebabı gibi ada klasiklerinin evde nasıl pişirildiğini adım adım anlatıyor.',
            'faq_blocks' => [
                ['question' => 'Kıbrıs mutfağı nedir?', 'answer' => 'Akdeniz mutfağının ada koşullarında biçimlenmiş hali.'],
                ['question' => 'Hellim nasıl pişirilir?', 'answer' => 'Kurulanmış dilimler kızgın tavada her yüzü iki üç dakika.'],
                ['question' => 'Molohiya ne zaman yenir?', 'answer' => 'Kışın, tavuk ya da kuzu etiyle.'],
            ],
            'body' => "## Ada mutfağının temeli\n\n".$this->cesitliMetin('Kıbrıs mutfağı ada koşullarında biçimlenmiş bir Akdeniz mutfağıdır ve malzemesini mevsiminde kullanır. ', 60)."\n\n## Nereden başlamalı\n\n".$this->cesitliMetin('Hellim, molohiya ve kolokas bu mutfağın ilk öğrenilen üç yemeğidir ve üçü de sabır ister. ', 60),
            'human_approved_at' => now(),
            'human_approved_by_user_id' => $admin->id,
        ]);
        $this->actingAs($admin)->withSession(['_token' => 'test-csrf'])->post(route('admin.blog.publish', $post), ['_token' => 'test-csrf'])->assertRedirect();
        $this->get(route('blog.show', $post->fresh()))->assertOk();
    }

    public function test_scheduled_posts_only_publish_when_their_time_has_arrived(): void
    {
        $due = $this->yayinaHazirYazi('zamani-gelen-yazi', now()->subMinute());
        $future = $this->yayinaHazirYazi('gelecek-yazi', now()->addHour());

        $this->artisan('blog:publish-scheduled --json')->expectsOutputToContain('"published":1')->assertSuccessful();
        $this->assertSame('published', $due->fresh()->status);
        $this->assertSame('scheduled', $future->fresh()->status);
    }

    /**
     * Sıkılaştırılmış yayın kapısını geçen bir zamanlanmış yazı.
     *
     * Kapı 23 Eylül 2026'da en az 900 kelime, 3-6 ikincil anahtar, 3-6 SSS,
     * iki H2, 140-158 karakterlik SEO açıklaması ve insan onayı istemeye
     * başladı. Zamanlayıcı da aynı kapıya bakıyor.
     */
    private function yayinaHazirYazi(string $slug, \DateTimeInterface $zaman): BlogPost
    {
        $onaylayan = User::factory()->create(['role' => 'admin', 'email_verified_at' => now()]);

        $yazi = BlogPost::create([
            'title' => 'Yazı '.$slug,
            'slug' => $slug,
            'excerpt' => 'Özgün kısa özet.',
            'body' => "## Birinci bölüm\n\n".$this->cesitliMetin('Bu yazının gövdesi kapının istediği uzunluğa ulaşacak kadar ayrıntı taşıyor ve konusunu anlatıyor. ', 60)."\n\n## İkinci bölüm\n\n".$this->cesitliMetin('İkinci bölüm konunun uygulama tarafını ölçülerle ve sürelerle anlatmayı sürdürüyor. ', 60),
            'status' => 'scheduled',
            'scheduled_at' => $zaman,
            'fact_check_status' => 'passed',
            'originality_status' => 'passed',
            'editorial_status' => 'approved',
            'primary_keyword' => 'kıbrıs mutfağı',
            'secondary_keywords' => ['hellim', 'molohiya', 'kolokas', 'fava'],
            'meta_title' => 'Yazı '.$slug,
            'meta_description' => 'Bu yazı yayın kapısının istediği bütün ölçütleri karşılayan bir örnek olarak yazıldı; arama sonucunda görünecek açıklaması da doğru uzunlukta tutuldu.',
            'faq_blocks' => [
                ['question' => 'Birinci soru?', 'answer' => 'Birinci cevap.'],
                ['question' => 'İkinci soru?', 'answer' => 'İkinci cevap.'],
                ['question' => 'Üçüncü soru?', 'answer' => 'Üçüncü cevap.'],
            ],
            'content_purpose' => 'Özgün rehber',
            'search_intent' => 'informational',
            'source_notes' => ['editorial_note' => 'Kontrol edildi'],
            'human_approved_at' => now(),
            'human_approved_by_user_id' => $onaylayan->id,
        ]);

        // Kapı, yazının doğrulanmış bir tarife bağlanmasını da istiyor.
        $tarif = Recipe::query()->create([
            'title' => 'İlgili tarif '.$slug, 'slug' => 'ilgili-tarif-'.$slug,
            'status' => 'published', 'verification_status' => 'verified', 'published_at' => now(),
        ]);
        $yazi->recipes()->sync([$tarif->id]);

        return $yazi->fresh();
    }
}
