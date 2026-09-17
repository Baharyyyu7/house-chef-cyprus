<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Models\BlogPost;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * "Taslak İnceleme" kopyaları arama sonucunda eşiyle yarışmaz.
 *
 * 17 Eylül 2026: yayındaki 20 yazının adresi "-taslak-inceleme-XXXXX" ile
 * bitiyordu, 14'ünün temiz adresli eşi de yayındaydı.
 */
final class ReviewDraftCleanupTest extends TestCase
{
    use RefreshDatabase;

    private function yazi(string $slug, string $baslik): BlogPost
    {
        return BlogPost::query()->create([
            'title' => $baslik, 'slug' => $slug, 'excerpt' => 'Özet.',
            'body' => str_repeat('Özgün editoryal bilgi. ', 30),
            'status' => 'published', 'published_at' => now()->subDay(),
        ]);
    }

    public function test_esi_olan_kopya_indeks_disina_alinir_ve_esini_kanonik_gosterir(): void
    {
        $es = $this->yazi('alerjen-etiketleri-nasil-okunur', 'Alerjen Etiketleri Nasıl Okunur?');
        $kopya = $this->yazi('alerjen-etiketleri-nasil-okunur-taslak-inceleme-Y8HZ5', 'Alerjen Etiketleri Nasıl Okunur? (Taslak İnceleme)');

        $this->artisan('seo:review-draft-cleanup --apply')->assertSuccessful();

        $kopya->refresh()->load('seoMetadata');
        $this->assertStringContainsString('noindex', (string) $kopya->seoMetadata?->robots);
        $this->assertSame(route('blog.show', $es), $kopya->seoMetadata?->canonical_override);
        $this->assertSame('published', $kopya->status, 'İçerik silinmemeli.');
        // Not başlıkta kalırsa ilgili yazı kartlarında ziyaretçiye görünür.
        $this->assertSame('Alerjen Etiketleri Nasıl Okunur?', $kopya->title);
        $this->assertNull($es->fresh('seoMetadata')->seoMetadata?->robots);
    }

    public function test_esi_olmayan_yazinin_yalniz_baslik_notu_temizlenir(): void
    {
        $tek = $this->yazi('kendi-basina-konu-taslak-inceleme-3C0KN', 'Kendi Başına Konu (Taslak İnceleme)');

        $this->artisan('seo:review-draft-cleanup --apply')->assertSuccessful();

        $tek->refresh()->load('seoMetadata');
        $this->assertSame('Kendi Başına Konu', $tek->title);
        $this->assertNull($tek->seoMetadata?->robots, 'Eşi yoksa yazı indekste kalmalı.');
    }

    public function test_rapor_modu_hicbir_seyi_yazmaz(): void
    {
        $this->yazi('alerjen-etiketleri-nasil-okunur', 'Alerjen Etiketleri Nasıl Okunur?');
        $kopya = $this->yazi('alerjen-etiketleri-nasil-okunur-taslak-inceleme-Y8HZ5', 'Alerjen Etiketleri Nasıl Okunur? (Taslak İnceleme)');

        $this->artisan('seo:review-draft-cleanup')->assertSuccessful();

        $this->assertNull($kopya->fresh('seoMetadata')->seoMetadata?->robots);
    }

    public function test_normal_yaziya_dokunulmaz(): void
    {
        $normal = $this->yazi('kibris-mezeleri-rehberi', 'Kıbrıs Mezeleri Rehberi');

        $this->artisan('seo:review-draft-cleanup --apply')->assertSuccessful();

        $this->assertSame('Kıbrıs Mezeleri Rehberi', $normal->fresh()->title);
        $this->assertNull($normal->fresh('seoMetadata')->seoMetadata?->robots);
    }
}
