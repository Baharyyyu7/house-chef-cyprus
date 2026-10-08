<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Models\BlogPost;
use App\Services\Seo\RankedUrlGuardService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Google'ın gösterdiği bir sayfa kapanırsa fark edilir.
 *
 * 18 Eylül 2026: gösterimlerin dörtte biri, bir gün önce taslağa çekilmiş
 * 10 yazıya aitti ve kimse fark etmemişti.
 */
final class RankedUrlGuardTest extends TestCase
{
    use RefreshDatabase;

    private function yazi(string $slug, string $durum): BlogPost
    {
        return BlogPost::query()->create([
            'title' => 'Yazı '.$slug, 'slug' => $slug, 'excerpt' => 'Özet.',
            'body' => $this->cesitliMetin('Özgün editoryal bilgi ve gerçek teknik anlatım. ', 60),
            'status' => $durum, 'published_at' => now()->subDays(3),
            'fact_check_status' => 'passed', 'originality_status' => 'passed',
        ]);
    }

    public function test_taslaga_cekilmis_siralanan_yazi_raporlanir(): void
    {
        $this->yazi('firin-kac-dakikada-isinir', 'draft');
        $this->yazi('acik-yazi', 'published');

        $kapananlar = app(RankedUrlGuardService::class)->kapananlar([
            ['page' => 'https://housechefcyprus.com/blog/firin-kac-dakikada-isinir', 'impressions' => 190],
            ['page' => 'https://housechefcyprus.com/blog/acik-yazi', 'impressions' => 40],
        ]);

        $this->assertCount(1, $kapananlar);
        $this->assertSame('yayinda_degil', $kapananlar[0]['reason']);
        $this->assertSame(190, $kapananlar[0]['impressions']);
    }

    public function test_silinmis_kayit_ve_dil_onekli_adres_yakalanir(): void
    {
        $kapananlar = app(RankedUrlGuardService::class)->kapananlar([
            ['page' => 'https://housechefcyprus.com/en/blog/olmayan-yazi', 'impressions' => 11],
        ]);

        $this->assertSame('kayit_yok', $kapananlar[0]['reason'] ?? null);
    }

    public function test_az_gosterimli_adres_gurultu_sayilmaz(): void
    {
        $this->yazi('taslak-yazi', 'draft');

        $kapananlar = app(RankedUrlGuardService::class)->kapananlar([
            ['page' => 'https://housechefcyprus.com/blog/taslak-yazi', 'impressions' => 2],
        ]);

        $this->assertSame([], $kapananlar);
    }

    public function test_yonlendirilen_eski_adres_kapali_sayilmaz(): void
    {
        $yazi = $this->yazi('sogan-karamelizasyonu', 'published');
        $yazi->update(['slug' => 'sogan-karamelizasyonu-ATNFE']);

        $kapananlar = app(RankedUrlGuardService::class)->kapananlar([
            ['page' => 'https://housechefcyprus.com/en/blog/sogan-karamelizasyonu', 'impressions' => 29],
        ]);

        $this->assertSame([], $kapananlar);
    }

    public function test_esine_kanonikle_baglanan_yazi_kapali_sayilmaz(): void
    {
        $asil = BlogPost::query()->create([
            'title' => 'Asıl Yazı', 'slug' => 'asil-yazi', 'excerpt' => 'Özet', 'body' => '<p>Gövde</p>',
            'status' => 'published', 'published_at' => now()->subDay(),
            'fact_check_status' => 'passed', 'originality_status' => 'passed', 'editorial_status' => 'approved',
        ]);
        $kopya = BlogPost::query()->create([
            'title' => 'Kopya Yazı', 'slug' => 'asil-yazi-ENGZ3', 'excerpt' => 'Özet', 'body' => '<p>Gövde</p>',
            'status' => 'published', 'published_at' => now()->subDay(),
            'fact_check_status' => 'passed', 'originality_status' => 'passed', 'editorial_status' => 'approved',
        ]);
        $kopya->seoMetadata()->updateOrCreate([], [
            'robots' => 'noindex,follow',
            'canonical_override' => route('blog.show', $asil),
        ]);

        $kapananlar = app(RankedUrlGuardService::class)->kapananlar([
            ['page' => route('blog.show', $kopya), 'impressions' => 30, 'clicks' => 0, 'position' => 8.0],
        ]);

        $this->assertSame([], $kapananlar);
    }

    public function test_yayinda_olmayan_nusha_yonlendiriliyorsa_kapali_sayilmaz(): void
    {
        $canli = BlogPost::query()->create([
            'title' => 'Canlı Yazı', 'slug' => 'canli-yazi', 'excerpt' => 'Ö', 'body' => '<p>G</p>',
            'status' => 'published', 'published_at' => now()->subDay(),
            'fact_check_status' => 'passed', 'originality_status' => 'passed', 'editorial_status' => 'approved',
        ]);
        $taslak = BlogPost::query()->create([
            'title' => 'Canlı Yazı', 'slug' => 'canli-yazi-09MMS', 'excerpt' => 'Ö', 'body' => '<p>G</p>',
            'status' => 'draft',
        ]);
        \App\Models\SlugRedirect::query()->create([
            'model_type' => $taslak->getMorphClass(),
            'old_slug' => $taslak->slug,
            'model_id' => $canli->id,
        ]);

        $kapananlar = app(RankedUrlGuardService::class)->kapananlar([
            ['page' => route('blog.show', $taslak), 'impressions' => 46, 'clicks' => 0, 'position' => 8.0],
        ]);

        $this->assertSame([], $kapananlar);
    }
}
