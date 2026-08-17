<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Models\BlogPost;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Yayındaki 216 yazının 180'i birebir aynı başlığı taşıyordu ve gövdeleri tek
 * şablondan üretilmişti; yalnız mutfak adı ve bir numara değişiyordu. Bir blog
 * aynı başlıkta 180 yazı taşıyamaz.
 *
 * Kural başlığa göre çalışır ve her başlıktan bir yazı yayında kalır: en dolu
 * gövdeli olan. Testler asıl riski sabitler -- tekil başlıklı gerçek bir
 * yazının yayından kalkması.
 */
final class DuplicateBlogPostsLeavePublicationTest extends TestCase
{
    use RefreshDatabase;

    public function test_only_one_post_survives_a_shared_title(): void
    {
        $this->publish('Kusursuz Rehberler', 'kusursuz-1', 'Kısa gövde.');
        $this->publish('Kusursuz Rehberler', 'kusursuz-2', 'Kısa gövde.');
        $this->publish('Kusursuz Rehberler', 'kusursuz-3', 'Kısa gövde.');

        $this->artisan('blog:unpublish-duplicates', ['--apply' => true])->assertSuccessful();

        $this->assertSame(1, BlogPost::query()->where('status', 'published')->count());
    }

    public function test_the_fullest_body_is_the_one_that_stays(): void
    {
        $kisa = $this->publish('Aynı Başlık', 'kisa', 'Kısa.');
        $uzun = $this->publish('Aynı Başlık', 'uzun', str_repeat('Dolu ve ayrıntılı gövde metni. ', 40));

        $this->artisan('blog:unpublish-duplicates', ['--apply' => true])->assertSuccessful();

        $this->assertSame('published', $uzun->fresh()->status);
        $this->assertSame('draft', $kisa->fresh()->status);
    }

    public function test_a_post_with_its_own_title_is_never_touched(): void
    {
        $tekil = $this->publish('Kıbrıs Mutfağına Başlangıç', 'kibris-baslangic', 'Özgün içerik.');
        $this->publish('Kusursuz Rehberler', 'kusursuz-1', 'Şablon.');
        $this->publish('Kusursuz Rehberler', 'kusursuz-2', 'Şablon.');

        $this->artisan('blog:unpublish-duplicates', ['--apply' => true])->assertSuccessful();

        $this->assertSame('published', $tekil->fresh()->status);
    }

    public function test_without_apply_nothing_changes(): void
    {
        $birinci = $this->publish('Aynı Başlık', 'bir', 'Gövde.');
        $ikinci = $this->publish('Aynı Başlık', 'iki', 'Gövde.');

        $this->artisan('blog:unpublish-duplicates')->assertSuccessful();

        $this->assertSame('published', $birinci->fresh()->status);
        $this->assertSame('published', $ikinci->fresh()->status);
    }

    public function test_an_unpublished_post_leaves_the_sitemap(): void
    {
        $kalan = $this->publish('Aynı Başlık', 'kalan', str_repeat('Uzun gövde metni. ', 40));
        $giden = $this->publish('Aynı Başlık', 'giden', 'Kısa.');

        $this->artisan('blog:unpublish-duplicates', ['--apply' => true])->assertSuccessful();

        $this->get('/sitemaps/blog.xml')
            ->assertOk()
            ->assertSee($kalan->slug, false)
            ->assertDontSee($giden->slug, false);
    }

    private function publish(string $title, string $slug, string $body): BlogPost
    {
        return BlogPost::query()->create([
            'author_id' => User::factory()->create()->id,
            'title' => $title,
            'slug' => $slug,
            'excerpt' => 'Özet.',
            'body' => $body,
            'status' => 'published',
            'published_at' => now()->subDays(2),
        ]);
    }
}
