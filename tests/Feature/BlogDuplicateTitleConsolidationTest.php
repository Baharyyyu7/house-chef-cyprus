<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Models\BlogPost;
use App\Services\Seo\BlogIndexability;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Aynı başlıklı iki yazı tek adreste toplanır.
 *
 * 20 Eylül 2026'da 26 konuda aynı SEO başlığıyla iki sayfa yayındaydı ve
 * ikisi de indeksteydi; aynı sorgu için kendi sayfalarımız yarışıyordu.
 */
final class BlogDuplicateTitleConsolidationTest extends TestCase
{
    use RefreshDatabase;

    private function yazi(string $slug, string $baslik, string $govde, string $yayin = '-5 days'): BlogPost
    {
        return BlogPost::query()->create([
            'title' => $baslik,
            'slug' => $slug,
            'excerpt' => 'Özet '.$slug,
            'body' => $govde,
            'status' => 'published',
            'published_at' => now()->modify($yayin),
            'meta_title' => $baslik,
            'meta_description' => 'Açıklama '.$slug,
            'fact_check_status' => 'passed',
            'originality_status' => 'passed',
            'editorial_status' => 'approved',
        ]);
    }

    public function test_son_ekli_nusha_temiz_adrese_baglanir_ve_uzun_govde_tasinir(): void
    {
        $temiz = $this->yazi('balik-pisirme-rehberi', 'Balık Pişirme Rehberi', '<p>Kısa gövde.</p>');
        $kopya = $this->yazi('balik-pisirme-rehberi-ENGZ3', 'Balık Pişirme Rehberi', '<p>Bu nüshanın gövdesi belirgin biçimde daha uzun ve ayrıntılıdır. '.str_repeat('Metin yeterli uzunluğa ulaşsın diye ayrıntı eklendi. ', 60).'</p>', '-20 days');

        $this->artisan('seo:blog-duplicate-title-consolidation --apply')->assertSuccessful();

        $temiz->refresh();
        $kopya->refresh();

        $this->assertStringContainsString('daha uzun', (string) $temiz->body);
        $this->assertNull($kopya->meta_title);
        $this->assertSame('noindex,follow', (string) $kopya->seoMetadata?->robots);
        $this->assertSame(route('blog.show', $temiz), (string) $kopya->seoMetadata?->canonical_override);
        $this->assertStringNotContainsString('noindex', app(BlogIndexability::class)->robots($temiz));
    }

    public function test_rapor_modu_hicbir_seyi_yazmaz(): void
    {
        $kopya = $this->yazi('konu-XY12Z', 'Ortak Başlık', '<p>Gövde.</p>');
        $this->yazi('konu', 'Ortak Başlık', '<p>Gövde.</p>');

        $this->artisan('seo:blog-duplicate-title-consolidation')->assertSuccessful();

        $this->assertSame('Ortak Başlık', $kopya->fresh()->meta_title);
    }

    public function test_tek_basina_duran_baslik_dokunulmaz(): void
    {
        $yalniz = $this->yazi('tek-yazi', 'Tek Başlık', '<p>Gövde.</p>');

        $this->artisan('seo:blog-duplicate-title-consolidation --apply')->assertSuccessful();

        $this->assertSame('Tek Başlık', $yalniz->fresh()->meta_title);
        $this->assertNull($yalniz->fresh()->seoMetadata?->canonical_override);
    }

    public function test_ayni_adres_kokunu_paylasan_nushalar_baslik_farkli_olsa_da_birlesir(): void
    {
        // 20 Eylül 2026: sitenin en çok gösterim alan iki sayfasının üçer
        // nüshası birden indeksteydi; başlıkları farklı olduğu için başlık
        // kuralına takılmıyorlardı.
        $temiz = $this->yazi('firin-kac-dakikada-isinir', 'Fırın Kaç Dakikada Isınır?', '<p>Kısa gövde.</p>');
        $eski = $this->yazi('firin-kac-dakikada-isinir-BH3DY', 'Fırın Ne Kadar Sürede Isınır?', '<p>Bu nüshanın gövdesi daha uzun. '.str_repeat('Ayrıntı eklendi. ', 80).'</p>', '-40 days');

        $this->artisan('seo:blog-duplicate-title-consolidation --apply')->assertSuccessful();

        $temiz->refresh();
        $eski->refresh();

        $this->assertStringContainsString('daha uzun', (string) $temiz->body);
        $this->assertSame('noindex,follow', (string) $eski->seoMetadata?->robots);
        $this->assertSame(route('blog.show', $temiz), (string) $eski->seoMetadata?->canonical_override);
    }
}
