<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Models\BlogCategory;
use App\Models\BlogPost;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Tests\TestCase;

/**
 * Ortak blog kapağı 2,79 MB'lık 1600 px PNG'ydi ve 400 px'lik kartta tam
 * boy iniyordu. Varyantlar üretildiyse kart AVIF/WebP srcset sunar.
 */
final class BlogFallbackCoverVariantsTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        Storage::fake('public');
        $resim = imagecreatetruecolor(1600, 1200);
        imagefill($resim, 0, 0, imagecolorallocate($resim, 200, 120, 40));
        ob_start();
        imagepng($resim);
        Storage::disk('public')->put('media/system/blog-fallback-cover.png', (string) ob_get_clean());
    }

    public function test_without_variants_the_card_keeps_the_png(): void
    {
        $this->yazi();

        $this->get(route('blog.index'))
            ->assertOk()
            ->assertSee(asset('storage/media/system/blog-fallback-cover.png'), false)
            ->assertDontSee('blog-fallback-cover-400.avif', false);
    }

    public function test_the_command_writes_avif_and_webp_variants_and_the_card_uses_them(): void
    {
        $this->artisan('media:blog-fallback-variants')->assertSuccessful();

        foreach ([400, 800, 1600] as $genislik) {
            foreach (['avif', 'webp'] as $bicim) {
                $yol = "media/system/blog-fallback-cover-{$genislik}.{$bicim}";
                Storage::disk('public')->assertExists($yol);
                $this->assertSame($genislik, getimagesizefromstring((string) Storage::disk('public')->get($yol))[0], $yol);
            }
        }

        // 400 px'lik webp kaynağın küçük bir kesri olmalı.
        $this->assertLessThan(Storage::disk('public')->size('media/system/blog-fallback-cover.png'), Storage::disk('public')->size('media/system/blog-fallback-cover-400.webp'));

        $yazi = $this->yazi();

        $this->get(route('blog.index'))
            ->assertOk()
            ->assertSee('<source type="image/avif" srcset="'.asset('storage/media/system/blog-fallback-cover-400.avif').' 400w', false)
            ->assertSee(asset('storage/media/system/blog-fallback-cover-800.webp').' 800w', false);

        $this->get(route('blog.show', $yazi))
            ->assertOk()
            ->assertSee(asset('storage/media/system/blog-fallback-cover-1600.avif').' 1600w', false);
    }

    public function test_a_missing_source_fails_loudly(): void
    {
        Storage::disk('public')->delete('media/system/blog-fallback-cover.png');

        $this->artisan('media:blog-fallback-variants')->assertFailed();
    }

    private function yazi(): BlogPost
    {
        return BlogPost::query()->create([
            'author_id' => User::factory()->create()->id,
            'blog_category_id' => BlogCategory::query()->create(['name' => 'Pişirme', 'slug' => 'pisirme-'.uniqid()])->id,
            'title' => 'Çorba Kıvamı',
            'slug' => Str::slug('Çorba Kıvamı').'-'.uniqid(),
            'excerpt' => 'Kısa özet.',
            'body' => str_repeat('Yazının özgün gövde metni; konusunu taşıyacak uzunlukta. ', 40), 'fact_check_status' => 'passed', 'originality_status' => 'passed',
            'status' => 'published',
            'published_at' => now()->subDay(),
        ]);
    }
}
