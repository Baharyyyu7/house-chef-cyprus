<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Models\BlogPost;
use App\Models\ContentOpportunity;
use App\Models\IntegrityReport;
use App\Models\Recipe;
use App\Models\SlugRedirect;
use App\Services\Integrity\SiteInvariants;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Bu turda yaşanan kusurların her biri bir kurala çevrildi.
 *
 * Hiçbiri testleri kırmadan üretimde oluştu: yönlendirmeler dokuz hedefe
 * yığıldı, muhafız günlüğüne günde 9.899 satır yazıldı, kapalı sayfalara
 * 5.207 çeviri işi açıldı. Buradaki testler, kuralların o durumları
 * gerçekten yakaladığını sabitler.
 */
final class SiteInvariantsCatchRealBreakagesTest extends TestCase
{
    use RefreshDatabase;

    private function yazi(string $slug, array $ek = []): BlogPost
    {
        return BlogPost::query()->create(array_merge([
            'title' => 'Yazı '.$slug,
            'slug' => $slug,
            'excerpt' => 'Özet.',
            'body' => $this->cesitliMetin('Bu yazının gövdesi kapıdan geçecek uzunlukta yazıldı. ', 60),
            'status' => 'published',
            'published_at' => now()->subDay(),
            'fact_check_status' => 'passed',
            'originality_status' => 'passed',
            'editorial_status' => 'approved',
        ], $ek));
    }

    /** @return list<string> */
    private function kurallar(): array
    {
        return array_column(app(SiteInvariants::class)->ihlaller(), 'kural');
    }

    public function test_temiz_sitede_ihlal_yok(): void
    {
        $this->yazi('temiz-yazi');

        $this->assertSame([], $this->kurallar());
    }

    public function test_yonlendirme_yigilmasi_yakalanir(): void
    {
        $hedef = $this->yazi('hedef');

        for ($i = 1; $i <= 7; $i++) {
            SlugRedirect::query()->create([
                'model_type' => $hedef->getMorphClass(),
                'old_slug' => 'eski-'.$i,
                'model_id' => $hedef->id,
            ]);
        }

        $this->assertContains('yonlendirme_yigilmasi', $this->kurallar());
    }

    public function test_rapor_seli_yakalanir(): void
    {
        for ($i = 0; $i < SiteInvariants::GUNLUK_RAPOR_TAVANI + 1; $i++) {
            IntegrityReport::query()->create(['guard' => 'seo:content-growth', 'status' => 'ok', 'headline' => 0, 'payload' => []]);
        }

        $this->assertContains('rapor_seli', $this->kurallar());
    }

    public function test_kapali_sayfaya_acik_ceviri_isi_yakalanir(): void
    {
        $yazi = $this->yazi('kapali-yazi', ['originality_status' => 'duplicate']);

        ContentOpportunity::query()->create([
            'kind' => 'translation_gap',
            'title' => 'en: Kapalı Yazı',
            'normalized_key' => 'translation-blog-'.$yazi->id.'-en',
            'search_intent' => 'informational',
            'topic_cluster' => 'Localization',
            'risk_level' => 'medium',
            'priority' => 60,
            'status' => 'open',
            'related_public_ids' => [$yazi->public_id],
            'evidence' => ['locale' => 'en'],
        ]);

        $this->assertContains('kapali_sayfaya_ceviri_isi', $this->kurallar());
    }

    public function test_indekste_kusurlu_tarif_yakalanir(): void
    {
        $tarif = Recipe::query()->create([
            'title' => 'Ölçüsüz Tarif',
            'slug' => 'olcusuz-tarif',
            'excerpt' => 'Özet.',
            'description' => 'Bu tarifin açıklaması testte kullanılmak üzere yazıldı ve yeterlidir.',
            'status' => 'published',
            'verification_status' => 'verified',
            'published_at' => now()->subDay(),
            'servings' => 4,
        ]);
        $tarif->steps()->create(['position' => 1, 'title' => 'Adım', 'instruction' => 'Tek adım.']);

        $this->assertContains('indekste_kusurlu_tarif', $this->kurallar());
    }

    public function test_kapidan_gecmeden_yayinda_kalan_yazi_yakalanir(): void
    {
        $this->yazi('ince-yazi', ['body' => '<p>Çok kısa.</p>']);

        $this->assertContains('kapidan_gecmeden_yayinda', $this->kurallar());
    }

    public function test_esine_kanonikle_baglanan_nusha_ihlal_sayilmaz(): void
    {
        // Birleştirme kararı kayıtlı: bu nüshanın kapıdan geçmemesi kaza değil.
        $asil = $this->yazi('asil-yazi');
        $kopya = $this->yazi('asil-yazi-ENGZ3', ['originality_status' => 'duplicate']);
        $kopya->seoMetadata()->create([
            'robots' => 'noindex,follow',
            'canonical_override' => route('blog.show', $asil),
        ]);

        $this->assertNotContains('kapidan_gecmeden_yayinda', $this->kurallar());
    }

    public function test_ayni_konuda_cokla_nusha_yakalanir(): void
    {
        $this->yazi('firin-kac-dakikada-isinir');
        $this->yazi('firin-kac-dakikada-isinir-BH3DY');

        $this->assertContains('ayni_konuda_cokla_nusha', $this->kurallar());
    }
}
