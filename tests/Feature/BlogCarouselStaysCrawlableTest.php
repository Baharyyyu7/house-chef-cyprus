<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Models\BlogPost;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

final class BlogCarouselStaysCrawlableTest extends TestCase
{
    use RefreshDatabase;

    public function test_carousel_items_are_links_not_buttons(): void
    {
        $yazi = $this->yazi('Fırın Kaç Dakikada Isınır');

        $icerik = (string) $this->get(route('blog.index'))->assertOk()->getContent();

        // Kaynak React bileşeni her kartı <button> olarak basıyor. Bu bir SEO
        // sayfası; arama motoru <button> takip edemez ve yazılar keşfedilmez.
        $this->assertStringContainsString('<a', $icerik);
        $this->assertStringContainsString(route('blog.show', $yazi), $icerik);
        $this->assertDoesNotMatchRegularExpression(
            '/<button[^>]*class="[^"]*hc-karusel__kart/',
            $icerik
        );
    }

    public function test_the_blog_list_no_longer_renders_post_images(): void
    {
        $this->yazi('Kıbrıs Kebabı');

        $icerik = (string) $this->get(route('blog.index'))->assertOk()->getContent();

        // Blog fotoğrafsız yayımlanıyor; olmayan görselin yerine yer tutucu
        // koymak sayfayı ağırlaştırmaktan başka bir işe yaramıyordu.
        $this->assertStringNotContainsString('<picture>', $icerik);
        $this->assertStringNotContainsString('x-blog-cover', $icerik);
    }

    public function test_every_post_stays_reachable_even_though_the_carousel_shows_a_few(): void
    {
        $yazilar = collect(range(1, 12))->map(fn (int $i): BlogPost => $this->yazi('Yazı '.$i));

        $icerik = (string) $this->get(route('blog.index'))->assertOk()->getContent();

        // Karusel aynı anda birkaç kart gösteriyor. Tam listeyi ona bırakmak
        // geri kalan yazıları gömerdi; hepsi sayfada bulunabilir olmalı.
        foreach ($yazilar as $yazi) {
            $this->assertStringContainsString(route('blog.show', $yazi), $icerik);
        }
    }

    private function yazi(string $baslik): BlogPost
    {
        return BlogPost::query()->create([
            'title' => $baslik,
            'slug' => str($baslik)->slug().'-'.uniqid(),
            'excerpt' => 'Kısa özet metni.',
            'body' => 'Gövde metni.',
            'status' => 'published',
            'published_at' => now()->subMinutes(random_int(1, 500)),
        ]);
    }
}
