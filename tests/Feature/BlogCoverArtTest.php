<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Models\BlogCategory;
use App\Models\BlogPost;
use App\Models\User;
use App\Services\Blog\CoverArtService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use Tests\TestCase;

/**
 * Rehber yazılarında fotoğraf bilgi taşımıyordu ve onaylı görsel bulunamadığı
 * için her kartta "görsel hazırlanıyor" yer tutucusu çıkıyordu. Yerine
 * başlıktan üretilen tipografik kapak kullanılır.
 */
final class BlogCoverArtTest extends TestCase
{
    use RefreshDatabase;

    public function test_the_listing_shows_a_generated_cover_instead_of_a_placeholder(): void
    {
        $this->publish('Pilavın Tane Tane Olması');

        $this->get(route('blog.index'))
            ->assertOk()
            ->assertSee('Pilavın Tane Tane Olması', false)
            ->assertDontSee('görsel hazırlanıyor', false);
    }

    public function test_each_post_gets_its_own_social_image(): void
    {
        $post = $this->publish('Çorba Kıvamı Nasıl Ayarlanır');

        $this->get(route('blog.show', $post))
            ->assertOk()
            ->assertSee(route('blog.cover', $post), false)
            ->assertDontSee('brand/og-default.jpg', false);
    }

    public function test_the_cover_endpoint_returns_a_real_png(): void
    {
        $response = $this->get(route('blog.cover', $this->publish('Ev Ölçüleri Rehberi')));

        $response->assertOk()->assertHeader('Content-Type', 'image/png');

        $image = imagecreatefromstring((string) $response->getContent());

        $this->assertNotFalse($image);
        $this->assertSame(1200, imagesx($image));
        $this->assertSame(630, imagesy($image));
    }

    public function test_a_draft_post_has_no_public_cover(): void
    {
        $post = BlogPost::query()->create([
            'author_id' => User::factory()->create()->id,
            'title' => 'Taslak',
            'slug' => 'taslak-kapak',
            'excerpt' => 'Özet.',
            'body' => 'Gövde.',
            'status' => 'draft',
        ]);

        $this->get(route('blog.cover', $post))->assertNotFound();
    }

    public function test_the_palette_is_stable_for_a_given_post(): void
    {
        $post = $this->publish('Kıbrıs Mutfağına Başlangıç');
        $cover = app(CoverArtService::class);

        $this->assertSame($cover->palette($post), $cover->palette($post->fresh()));
    }

    public function test_a_long_title_is_wrapped_and_never_overflows(): void
    {
        $post = $this->publish('Fırın Yemeklerinde Sıcaklık ve Süre Dengesini Kurmanın Bütün Ayrıntıları ve İpuçları');
        $lines = app(CoverArtService::class)->titleLines($post, 84, 1024);

        $this->assertLessThanOrEqual(4, count($lines));
        $this->assertGreaterThan(1, count($lines));
    }

    private function publish(string $title): BlogPost
    {
        return BlogPost::query()->create([
            'author_id' => User::factory()->create()->id,
            'blog_category_id' => BlogCategory::query()->create(['name' => 'Pişirme Teknikleri', 'slug' => 'pisirme-teknikleri-'.uniqid()])->id,
            'title' => $title,
            'slug' => Str::slug($title).'-'.uniqid(),
            'excerpt' => 'Kısa özet.',
            'body' => 'Gövde metni.',
            'status' => 'published',
            'published_at' => now()->subDay(),
        ]);
    }
}
