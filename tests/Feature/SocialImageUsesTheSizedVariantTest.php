<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Models\Media;
use App\Models\Recipe;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * og:image, twitter:image ve tarif şeması ham kaynağı gösteriyordu; bir
 * tarifte 2,77 MB'lık PNG. O dosyayı WhatsApp, Facebook ve X her paylaşım
 * önizlemesinde yeniden çekiyor, Google zengin sonuç için okuyor.
 *
 * Hattın ürettiği open_graph varyantı (1200x630) tam bu iş için var ve
 * kullanılmıyordu. 140 görsel daha yüklenmeden önce sabitlemek gerekiyor:
 * aksi hâlde her yeni tarif megabaytlarca paylaşım görseli yayınlar.
 */
final class SocialImageUsesTheSizedVariantTest extends TestCase
{
    use RefreshDatabase;

    public function test_it_prefers_the_open_graph_variant(): void
    {
        $medya = $this->medya([
            ['name' => 'hero', 'jpeg' => 'media/o/1600.jpg', 'width' => 1600, 'height' => 1000],
            ['name' => 'open_graph', 'jpeg' => 'media/o/1200x630.jpg', 'width' => 1200, 'height' => 630],
        ]);

        $sonuc = $medya->socialImage();

        $this->assertStringContainsString('1200x630.jpg', $sonuc['url']);
        $this->assertSame(1200, $sonuc['width']);
        $this->assertSame(630, $sonuc['height']);
    }

    public function test_it_never_returns_an_avif_or_webp_for_sharing(): void
    {
        // Paylaşım tarayıcılarının bir kısmı bu biçimleri okumuyor;
        // okumadığında önizleme boş çıkar.
        $medya = $this->medya([
            ['name' => 'open_graph', 'jpeg' => 'media/o/og.jpg', 'webp' => 'media/o/og.webp', 'avif' => 'media/o/og.avif', 'width' => 1200, 'height' => 630],
        ]);

        $url = $medya->socialImage()['url'];

        $this->assertStringEndsWith('.jpg', $url);
    }

    public function test_without_variants_it_falls_back_to_the_source(): void
    {
        $medya = $this->medya([]);

        $this->assertStringContainsString('source.png', $medya->socialImage()['url']);
    }

    /** @param list<array<string,mixed>> $varyantlar */
    private function medya(array $varyantlar): Media
    {
        $recipe = Recipe::query()->create([
            'title' => 'Ali Nazik Kebabı',
            'slug' => 'ali-nazik-'.uniqid(),
            'status' => 'published',
            'verification_status' => 'verified',
            'published_at' => now(),
        ]);

        return $recipe->media()->create([
            'kind' => 'recipe_synthetic',
            'disk' => 'public',
            'path' => 'media/approved-synthetic/recipe/X/source.png',
            'visibility' => 'public',
            'media_status' => 'approved_real_media',
            'fidelity_status' => 'strong_match',
            // isApprovedRealMedia() bunların hepsini istiyor; eksikse varyant
            // yolu hiç denenmez ve ham kaynağa düşülür.
            'verification_status' => 'verified',
            'license_review_status' => 'approved',
            'attribution_review_status' => 'approved',
            'is_placeholder' => false,
            'width' => 1600,
            'height' => 1350,
            'responsive_variants' => $varyantlar,
        ]);
    }
}
