<?php

declare(strict_types=1);

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Bu bölüm dokuz uydurma kişiyi dokuz uydurma alıntıyla gösteriyordu ve bu
 * test onların ekranda olduğunu doğruluyordu. Altındaki "temsili içeriktir"
 * notu iyi niyetliydi ama yeterli değil: uydurma isim ve uydurma alıntı,
 * altına ne yazılırsa yazılsın sayfada sosyal kanıt gibi okunur.
 *
 * Bölümün tasarımı ve hareketi korundu; içeriği sitede doğrulanabilir
 * taahhütlerle değiştirildi. Test artık bunu koruyor.
 */
final class HomeTestimonialsTest extends TestCase
{
    use RefreshDatabase;

    public function test_the_section_renders_its_commitments(): void
    {
        $this->get(route('home'))
            ->assertOk()
            ->assertSee('Neye güvenebilirsiniz?')
            ->assertSee('Onaysız görsel yayınlanmaz')
            ->assertDontSee('data-testimonial-avatar', false);
    }

    public function test_the_commitments_are_not_an_auto_scrolling_marquee(): void
    {
        // Taahhütler yorum karuselinde duruyordu: akış döngüsü için her
        // madde iki kez basılıyor ve bölüm anasayfanın en büyük metin bloğu
        // oluyordu (5.139 karakter, sıfır bağlantı). Biçim içerikle
        // çelişiyordu - yorum gibi görünen bir şeyin yorum olmadığını
        // söyleyen bir açıklama satırı gerekmesi de bunun işaretiydi.
        $icerik = (string) $this->get(route('home'))->assertOk()->getContent();

        $this->assertStringNotContainsString('hc-testimonials-track', $icerik);
        $this->assertStringNotContainsString('Yorum akışını duraklat', $icerik);
    }

    public function test_each_commitment_links_to_the_page_that_proves_it(): void
    {
        // İddia edip kanıtını göstermemek iddiayı zayıflatır. Bu sayfalar
        // zaten vardı ama anasayfadan hiçbirine bağlantı yoktu.
        $this->get(route('home'))
            ->assertOk()
            ->assertSee(route('information.show', 'gorsel-ve-medya-politikasi'), false)
            ->assertSee(route('information.show', 'besin-degeri-aciklamasi'), false);
    }

    public function test_every_commitment_carries_a_proof_page(): void
    {
        $maddeler = config('testimonials.home', []);

        $this->assertNotEmpty($maddeler);

        foreach ($maddeler as $madde) {
            $this->assertArrayHasKey('proof', $madde, $madde['name'].' için kanıt sayfası yok.');
            $this->assertNotEmpty($madde['proof']['page'] ?? null);
        }
    }

    public function test_no_commitment_is_printed_twice(): void
    {
        // Karusel her maddeyi iki kez basıyordu; ızgarada bir kez basılmalı.
        $icerik = (string) $this->get(route('home'))->assertOk()->getContent();

        $this->assertSame(1, substr_count($icerik, 'Onaysız görsel yayınlanmaz'));
    }

    public function test_the_disclosure_says_these_are_commitments_not_reviews(): void
    {
        $aciklama = (string) config('testimonials.disclosure');

        $this->assertNotEmpty($aciklama);
        $this->assertStringContainsString('kullanıcı yorumu değil', $aciklama);

        $this->get(route('home'))->assertOk()->assertSee('kullanıcı yorumu değil');
    }

    public function test_every_item_is_a_checkable_claim_and_never_a_person(): void
    {
        $maddeler = collect(config('testimonials.home'));

        $this->assertNotEmpty($maddeler);

        $maddeler->each(function (array $madde): void {
            $this->assertNotEmpty($madde['name']);
            $this->assertNotEmpty($madde['role']);
            $this->assertNotEmpty($madde['text']);
            // Yüz yok: fotoğraflı sahte kişi hiç oluşmasın.
            $this->assertNull($madde['image']);
            // Başlık bir iddia olmalı, bir insan adı değil. Ayırt edici:
            // "Elif Karaca" gibi adlarda iki kelime de büyük harfle başlar;
            // "Elinizdekilerle eşleştirme" gibi bir iddiada başlamaz.
            $this->assertDoesNotMatchRegularExpression(
                '/^\p{Lu}\p{L}+\s+\p{Lu}\p{L}+$/u',
                $madde['name'],
                'Bölüme insan adına benzeyen bir başlık girmiş: '.$madde['name']
            );
        });
    }

    public function test_the_commitments_section_is_translated_for_all_enabled_locales(): void
    {
        $locales = config('locales.supported', []);
        $localeUrlService = app(\App\Services\Seo\LocaleUrlService::class);

        foreach ($locales as $code => $config) {
            if (($config['enabled'] ?? false) !== true) {
                continue;
            }

            $url = $localeUrlService->urlFor((string) $code, '/');
            $expectedTitle = __('home.trust_title', [], (string) $code);
            $expectedKicker = __('home.trust_kicker', [], (string) $code);

            $this->get($url)
                ->assertOk()
                ->assertSee($expectedTitle)
                ->assertSee($expectedKicker);
        }
    }
}

