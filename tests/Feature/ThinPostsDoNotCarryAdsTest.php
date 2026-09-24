<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Models\BlogPost;
use App\Services\Seo\BlogIndexability;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * İnce yazı reklam taşımaz.
 *
 * Reklam kapısı yalnız noindex sayfalara bakıyordu ve "indekslenebilir"
 * ile "reklam taşımaya uygun" aynı eşiğe bağlıydı. İkisi aynı şey değil:
 * 2000 karakter (~350 kelime) bir yazının aramada görünmesi için yeterli
 * olabilir, AdSense politikası "düşük değerli içerik" derken bundan
 * fazlasını kastediyor.
 *
 * 2026-09-24'te ölçüldü: yayındaki 156 yazının 100'ü 700 kelimenin
 * altındaydı ve hepsi reklam taşıyordu. Hesap o sırada incelemedeydi ve
 * bu, reddedilme gerekçelerinin başında geliyor.
 *
 * Sayfa yayında ve okunabilir kalır; yalnız reklam basılmaz.
 */
final class ThinPostsDoNotCarryAdsTest extends TestCase
{
    use RefreshDatabase;

    private function reklamiAc(): void
    {
        config([
            'integrations.google.adsense.enabled' => true,
            'integrations.google.adsense.client_id' => 'ca-pub-1614195335767159',
            'integrations.google.adsense.certified_cmp_enabled' => true,
            'integrations.google.adsense.cmp_provider' => 'google',
        ]);
    }

    private function yazi(string $slug, int $karakter): BlogPost
    {
        $govde = "## Bölüm\n\n".str_repeat('Bu yazının gövdesi konuyu ölçülerle anlatıyor. ', (int) ceil($karakter / 46));

        $yazi = BlogPost::create([
            'title' => 'Yazı '.$slug,
            'slug' => $slug,
            'excerpt' => 'Özgün kısa özet.',
            'body' => $govde,
            'status' => 'published',
            'published_at' => now()->subDay(),
            'fact_check_status' => 'passed',
            'originality_status' => 'passed',
            'editorial_status' => 'approved',
        ]);

        return $yazi->fresh();
    }

    public function test_indekslenebilirlik_reklam_uygunlugundan_ayridir(): void
    {
        $kapi = app(BlogIndexability::class);

        // Eşiğin iki yanı: indeks için yeterli, reklam için değil.
        $ince = $this->yazi('ince-ama-indekste', BlogIndexability::ASGARI_KARAKTER + 200);

        $this->assertTrue($kapi->indekslenebilir($ince), 'Yazı aramada görünmeye devam etmeli.');
        $this->assertFalse($kapi->reklamTasiyabilir($ince), 'Aynı yazı reklam taşımamalı.');
    }

    public function test_ince_yazida_reklam_basilmaz(): void
    {
        $this->reklamiAc();
        $ince = $this->yazi('ince-yazi', BlogIndexability::ASGARI_KARAKTER + 200);

        $cevap = $this->get(route('blog.show', $ince));
        $cevap->assertOk();
        // Sayfanın kendisi kapanmıyor: yalnız reklam basılmıyor.
        $cevap->assertSee('Yazı ince-yazi', false);
        $cevap->assertDontSee('adsbygoogle', false);
    }

    public function test_dolu_yazida_reklam_basilir(): void
    {
        $this->reklamiAc();
        $dolu = $this->yazi('dolu-yazi', BlogIndexability::REKLAM_ASGARI_KARAKTER + 1000);

        $this->get(route('blog.show', $dolu))->assertOk()->assertSee('adsbygoogle', false);
    }
}
