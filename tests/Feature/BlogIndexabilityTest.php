<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Models\BlogPost;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

final class BlogIndexabilityTest extends TestCase
{
    use RefreshDatabase;

    public function test_a_post_that_passed_its_gates_is_indexable(): void
    {
        $yazi = $this->yazi();

        $this->get(route('blog.show', $yazi))
            ->assertOk()
            ->assertSee('content="index,follow"', false);

        $this->get(route('sitemap.blog'))->assertOk()->assertSee($yazi->slug);
    }

    public function test_a_thin_post_stays_published_but_leaves_the_index(): void
    {
        // Bu site bir kez ölçekli zayıf içerik yüzünden ceza yedi. İnce yazı
        // okunabilir kalıyor - hiçbir bağlantı kırılmıyor - ama aranabilir
        // olmuyor.
        $yazi = $this->yazi(['body' => 'Çok kısa bir not.']);

        $this->get(route('blog.show', $yazi))
            ->assertOk()
            ->assertSee('content="noindex,follow"', false);
    }

    public function test_a_post_with_a_pending_fact_check_is_not_indexed(): void
    {
        // Kapılar kayıtta vardı ama indekslemeye etkisi yoktu: olgu denetimi
        // "pending" olan bir yazı da dizine giriyordu.
        $yazi = $this->yazi(['fact_check_status' => 'pending']);

        $this->get(route('blog.show', $yazi))->assertSee('content="noindex,follow"', false);
    }

    public function test_a_post_with_a_pending_originality_check_is_not_indexed(): void
    {
        $yazi = $this->yazi(['originality_status' => 'pending']);

        $this->get(route('blog.show', $yazi))->assertSee('content="noindex,follow"', false);
    }

    public function test_the_sitemap_never_lists_what_the_page_tells_crawlers_to_skip(): void
    {
        // Harita "indeksle" derken sayfa "indeksleme" diyorsa tarayıcıya
        // çelişkili sinyal gidiyor.
        $ince = $this->yazi(['body' => 'Kısa.', 'slug' => 'ince-yazi']);
        $saglam = $this->yazi(['slug' => 'saglam-yazi']);

        $this->get(route('sitemap.blog'))
            ->assertOk()
            ->assertSee($saglam->slug)
            ->assertDontSee($ince->slug);
    }

    public function test_links_are_still_followed_so_the_site_graph_survives(): void
    {
        // noindex evet, nofollow hayır: sayfadaki bağlantılar taranmaya
        // devam etsin.
        $yazi = $this->yazi(['body' => 'Kısa.']);

        $this->get(route('blog.show', $yazi))->assertDontSee('nofollow', false);
    }

    /** @param array<string, mixed> $ustuneYaz */
    private function yazi(array $ustuneYaz = []): BlogPost
    {
        return BlogPost::query()->create(array_merge([
            'title' => 'Kıbrıs mutfağında zeytinyağı',
            'slug' => 'kibris-zeytinyagi-'.uniqid(),
            'excerpt' => 'Yerel zeytinyağının sofradaki yeri.',
            'body' => str_repeat('Zeytinyağının sofradaki yeri üzerine bir not. ', 80),
            'status' => 'published',
            'fact_check_status' => 'passed',
            'originality_status' => 'passed',
            'published_at' => now()->subDay(),
        ], $ustuneYaz));
    }
}
