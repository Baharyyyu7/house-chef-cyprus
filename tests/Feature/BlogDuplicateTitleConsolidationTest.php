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

    public function test_uzun_ama_sablon_olan_govde_gercek_makalenin_yerine_gecmez(): void
    {
        // 2026-09-26'da ölçüldü: temiz adreslerdeki şablon sürümler %88'i
        // ortak paragraf olan dolguyla daha uzundu, gerçek makaleler son
        // ekli "taslak-inceleme" adreslerinde duruyordu. Yalnız uzunluğa
        // bakan kural şablonu seçti ve gerçek makaleleri noindex'e itti.
        $sablonParagraflari = collect(range(1, 8))->map(fn (int $i): string => '<p>Bu ortak şablon paragrafı her makine yazısında aynen tekrar ediyor ve hiçbir konuya ait değil; numarası '.$i.' ama geri kalanı birebir aynı, ne okuyana ne arama motoruna yeni bir şey söylüyor.</p>')->implode('');

        // Aynı şablonu taşıyan iki yazı daha: şablon paragrafları korpusta
        // tekrar ediyor, dolayısıyla ölçülebilir biçimde özgün değil.
        $this->yazi('baska-sablon-yazi-bir', 'Başka Konu Bir', $sablonParagraflari, '-40 days');
        $this->yazi('baska-sablon-yazi-iki', 'Başka Konu İki', $sablonParagraflari, '-41 days');

        $sablon = $this->yazi('kuru-bakliyat-saklama', 'Kuru Bakliyat Saklama', $sablonParagraflari.$sablonParagraflari);
        $gercek = $this->yazi('kuru-bakliyat-saklama-Q9VS4', 'Kuru Bakliyat Saklama',
            '<p>Kuru baklagiller nemden uzak, hava almayan cam kavanozda ve serin bir dolapta altı ila on iki ay dayanır; kavanozun dibine bir defne yaprağı koymak böcek riskini azaltır.</p>'
            .'<p>Satın aldıktan sonra paketi açıp bir hafta dondurucuda bekletmek, içinde kalmış olabilecek yumurtaları öldürür; bu adım özellikle nohut ve barbunya için önemlidir.</p>'
            .'<p>Eski ve yeni bakliyatı aynı kavanozda karıştırmayın: pişme süreleri farklı olur ve bir kısmı lapa olurken diğeri sert kalır.</p>',
            '-20 days');

        $this->assertGreaterThan(mb_strlen((string) $gercek->body), mb_strlen((string) $sablon->body), 'Test kurgusu: şablon daha uzun olmalı.');

        $this->artisan('seo:blog-duplicate-title-consolidation --apply')->assertSuccessful();

        $this->assertStringContainsString('defne yaprağı', (string) $sablon->fresh()->body, 'Temiz adrese gerçek makale taşınmalıydı.');
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
