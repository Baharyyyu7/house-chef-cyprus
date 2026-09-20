<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Models\BlogPost;
use App\Services\Seo\BlogIndexability;
use App\Services\Seo\BlogOriginalityService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Blog özgünlüğü ölçülür, varsayılmaz.
 *
 * 13 Eylül 2026'da 100 şablon yazı "özgün" işaretiyle yayına girdi: otomatik
 * yayın kapısı alanı ölçmeden "passed" yapıyordu. Paragraflarının çoğu başka
 * yazılarda birebir geçiyordu. Bu test ölçümün gerçekten yapıldığını ve
 * tekrar içeriğin indeksten çıktığını sabitler.
 */
final class BlogOriginalityIsMeasuredTest extends TestCase
{
    use RefreshDatabase;

    private const SABLON_1 = 'Kıbrıs bölgesinin asırlık gastronomi mirası geleneksel hazırlama yöntemleriyle nesilden nesile aktarılmış ve bugün de köy mutfaklarında aynı özenle sürdürülmektedir.';

    private const SABLON_2 = 'Bu tarifte kullanılan malzemelerin taze ve mevsiminde seçilmesi lezzetin doğal dengesini korur ve sofraya gelen tabağın aromasını belirgin biçimde güçlendirir.';

    private const SABLON_3 = 'Pişirme sırasında ateşin kısık tutulması dokunun yumuşak kalmasını sağlar ve malzemelerin kendi suyunu salarak birbirine karışmasına yeterli zaman tanır.';

    private function yazi(string $slug, string $govde): BlogPost
    {
        return BlogPost::query()->create([
            'title' => 'Başlık '.$slug,
            'slug' => $slug,
            'excerpt' => 'Özet '.$slug,
            'body' => $govde,
            'status' => 'published',
            'published_at' => now()->subDay(),
            'fact_check_status' => 'passed',
            'originality_status' => 'passed',
        ]);
    }

    private function sablonYazi(string $slug): BlogPost
    {
        $ozel = 'Bu yazıya özgü tek paragraf '.$slug.' için yazıldı ve başka hiçbir yazıda geçmeyen ayrıntılar, ölçüler ve kişisel gözlemler içeriyor, uzunluğu da yeterli.';

        return $this->yazi($slug, '<p>'.self::SABLON_1.'</p><p>'.self::SABLON_2.'</p><p>'.self::SABLON_3.'</p><p>'.$ozel.'</p>');
    }

    private function ozgunYazi(string $slug): BlogPost
    {
        $paragraflar = collect(range(1, 4))->map(fn (int $i): string => '<p>'.$slug.' yazısının '.$i.'. paragrafı kendi ayrıntılarını anlatıyor: bu bölümde yalnız bu yazıya ait gözlemler, ölçüler, denemeler ve sonuçlar yer alıyor, tekrar yok.</p>')->implode('');

        return $this->yazi($slug, $paragraflar);
    }

    public function test_sablon_yazilar_tekrar_olarak_olculur_ozgun_yazi_olculmez(): void
    {
        $a = $this->sablonYazi('sablon-a');
        $b = $this->sablonYazi('sablon-b');
        $ozgun = $this->ozgunYazi('ozgun-yazi');

        $servis = app(BlogOriginalityService::class);
        $olcum = $servis->olc();

        // İlk yayınlanan paragrafların sahibidir; tekrar sonraki yazıdır.
        $this->assertFalse($servis->tekrarMi($olcum[$a->id]));
        $this->assertTrue($servis->tekrarMi($olcum[$b->id]));
        $this->assertSame(0.75, $olcum[$b->id]['share']);
        $this->assertFalse($servis->tekrarMi($olcum[$ozgun->id]));
        $this->assertSame(0.0, $olcum[$ozgun->id]['share']);
    }

    public function test_birebir_kopyada_orijinal_indekste_kalir(): void
    {
        $orijinal = $this->yazi('ev-olculeri-rehberi', '<p>'.self::SABLON_1.'</p><p>'.self::SABLON_2.'</p><p>'.self::SABLON_3.'</p>');
        $orijinal->forceFill(['published_at' => now()->subDays(10)])->save();
        $kopya = $this->yazi('ev-olculeri-rehberi-0JCY7', $orijinal->body);

        $this->artisan('seo:originality-scan --apply')->assertSuccessful();

        $this->assertSame('passed', $orijinal->fresh()->originality_status);
        $this->assertSame('duplicate', $kopya->fresh()->originality_status);
    }

    public function test_kanonigi_baska_yaziyi_gosteren_eski_kopya_sahipligi_birakir(): void
    {
        // Üretimdeki durum: son ekli kopya daha eski ama kanoniği temiz adresi gösteriyor.
        $kopya = $this->yazi('kuru-bakliyat-nasil-saklanir-5F16S', '<p>'.self::SABLON_1.'</p><p>'.self::SABLON_2.'</p><p>'.self::SABLON_3.'</p>');
        $kopya->forceFill(['published_at' => now()->subDays(30), 'canonical_url' => 'https://housechefcyprus.com/blog/kuru-bakliyat-nasil-saklanir'])->save();
        $temiz = $this->yazi('kuru-bakliyat-nasil-saklanir', $kopya->body);

        $this->artisan('seo:originality-scan --apply')->assertSuccessful();

        $this->assertSame('passed', $temiz->fresh()->originality_status);
        $this->assertSame('duplicate', $kopya->fresh()->originality_status);
    }

    public function test_tarama_tekrar_yaziyi_indeksten_cikarir_icerigi_silmez(): void
    {
        $this->sablonYazi('sablon-a');
        $b = $this->sablonYazi('sablon-b');
        $ozgun = $this->ozgunYazi('ozgun-yazi');

        $this->artisan('seo:originality-scan --apply')->assertSuccessful();

        $b->refresh();
        $this->assertSame('duplicate', $b->originality_status);
        $this->assertSame('published', $b->status, 'Yazı silinmemeli ya da yayından kaldırılmamalı.');
        $this->assertStringContainsString('noindex', app(BlogIndexability::class)->robots($b));
        $this->assertSame('passed', $ozgun->fresh()->originality_status);
    }

    public function test_rapor_modu_hicbir_seyi_yazmaz(): void
    {
        $this->sablonYazi('sablon-a');
        $b = $this->sablonYazi('sablon-b');

        $this->artisan('seo:originality-scan')->assertSuccessful();

        $this->assertSame('passed', $b->fresh()->originality_status);
    }

    public function test_tekrari_duzelen_yazi_kendiliginden_onaylanmaz(): void
    {
        // Özgünlük onayı insan kararı; komutun yalnız reddetme yetkisi var.
        $a = $this->sablonYazi('sablon-a');
        $b = $this->sablonYazi('sablon-b');
        $this->artisan('seo:originality-scan --apply')->assertSuccessful();
        $this->assertSame('duplicate', $b->fresh()->originality_status);

        $a->forceFill(['status' => 'draft'])->save();
        $this->artisan('seo:originality-scan --apply')->assertSuccessful();

        $this->assertSame('pending', $b->fresh()->originality_status);
    }

    public function test_yayin_kapisi_tekrar_taslagi_olcer(): void
    {
        $this->sablonYazi('yayindaki-sablon');
        $taslak = BlogPost::query()->create([
            'title' => 'Taslak',
            'slug' => 'taslak-sablon',
            'excerpt' => 'Özet',
            'body' => '<p>'.self::SABLON_1.'</p><p>'.self::SABLON_2.'</p><p>'.self::SABLON_3.'</p>',
            'status' => 'draft',
        ]);

        $servis = app(BlogOriginalityService::class);

        $this->assertTrue($servis->tekrarMi($servis->adayiOlc($taslak)));
    }

    public function test_meta_kaydindaki_kanonik_de_sahipligi_birakir(): void
    {
        // Yineleme birleştirmesi kanoniği SeoMetadata'ya yazıyor; servis
        // yalnız yazının kendi alanına bakınca temiz adres kendi eski
        // kopyasının tekrarı sayılıyordu.
        $kopya = $this->yazi('firin-dereceleri-T0ZQ1', '<p>'.self::SABLON_1.'</p><p>'.self::SABLON_2.'</p><p>'.self::SABLON_3.'</p>');
        $kopya->forceFill(['published_at' => now()->subDays(30)])->save();
        $temiz = $this->yazi('firin-dereceleri', $kopya->body);
        $kopya->seoMetadata()->updateOrCreate([], [
            'robots' => 'noindex,follow',
            'canonical_override' => route('blog.show', $temiz),
        ]);

        $servis = app(BlogOriginalityService::class);
        $olcum = $servis->olc();

        $this->assertFalse($servis->tekrarMi($olcum[$temiz->id]));
        $this->assertTrue($servis->tekrarMi($olcum[$kopya->id]));
    }
}
