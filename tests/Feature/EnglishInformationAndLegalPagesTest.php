<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Http\Controllers\InformationPageController;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Mail;
use Tests\TestCase;

/**
 * İngilizce bilgi sayfaları, politikalar, yasal merkez ve iletişim.
 *
 * Çeviri makine destekli; hukuki metinler sahibi tarafından gözden geçirilmeden
 * dayanak yapılmamalı. Bu dosya çevirinin yapısını sabitler: aynı bölümler,
 * aynı paragraf sayıları, İngilizce başlıklar, Türkçe kalmayan sabit metin ve
 * her sayfanın kendi dilinde kanonik adresi.
 */
final class EnglishInformationAndLegalPagesTest extends TestCase
{
    use RefreshDatabase;

    /** Çevirisi lang/en/information_pages.php'de olan ve yönlendirilen bilgi sayfaları. */
    private const BILGI_SAYFALARI = [
        'hakkimizda', 'nasil-calisir', 'sss', 'editorial-ilkeler', 'tarif-dogrulama-politikasi',
        'besin-degeri-aciklamasi', 'alerjen-uyarisi', 'gorsel-ve-medya-politikasi',
        'icerik-duzeltme-politikasi', 'hata-bildir',
    ];

    protected function tearDown(): void
    {
        // reklamDurumu() ortam değişkenini değiştirir; sonraki testler temiz başlasın.
        putenv('GOOGLE_ADSENSE_ENABLED');
        unset($_ENV['GOOGLE_ADSENSE_ENABLED']);

        parent::tearDown();
    }

    public function test_english_information_page_is_english_with_its_own_canonical(): void
    {
        $this->get('/en/hakkimizda')
            ->assertOk()
            ->assertSee('About Us')
            ->assertSee('What do we do?')
            ->assertSee('Home')
            ->assertDontSee('Hakkımızda')
            ->assertDontSee('Ana sayfa')
            ->assertSee('rel="canonical" href="'.url('/en/hakkimizda').'"', false);
    }

    public function test_turkish_information_page_is_unchanged_and_points_hreflang_at_english(): void
    {
        $this->get('/hakkimizda')
            ->assertOk()
            ->assertSee('Hakkımızda')
            ->assertSee('Ne yapıyoruz?')
            ->assertSee('rel="canonical" href="'.url('/hakkimizda').'"', false)
            ->assertSee('hreflang="en" href="'.url('/en/hakkimizda').'"', false);
    }

    public function test_every_english_information_page_renders_its_english_title_and_first_section(): void
    {
        $en = require lang_path('en/information_pages.php');

        foreach (self::BILGI_SAYFALARI as $sayfa) {
            $this->get('/en/'.$sayfa)
                ->assertOk()
                ->assertSee($en[$sayfa]['title'])
                ->assertSee($en[$sayfa]['sections'][0]['title']);
        }
    }

    public function test_legacy_media_alias_keeps_its_own_canonical_in_both_languages(): void
    {
        // gorsel-ve-kaynak-politikasi aynı içeriği sunar; canonical kendi adresi kalır.
        $this->get('/gorsel-ve-kaynak-politikasi')
            ->assertOk()
            ->assertSee('rel="canonical" href="'.url('/gorsel-ve-kaynak-politikasi').'"', false);

        $this->get('/en/gorsel-ve-kaynak-politikasi')
            ->assertOk()
            ->assertSee('rel="canonical" href="'.url('/en/gorsel-ve-kaynak-politikasi').'"', false);
    }

    public function test_english_policy_pages_use_english_titles_and_no_turkish_chrome(): void
    {
        $this->get('/en/gizlilik')
            ->assertOk()
            ->assertSee('Privacy and Personal Data')
            ->assertSee('User Rights')
            ->assertSee('Other policies')
            ->assertSee('Print')
            ->assertDontSee('Yazdır')
            ->assertDontSee('Diğer politikalar')
            ->assertDontSee('Sayfa bağlantısını kopyala')
            ->assertSee('rel="canonical" href="'.url('/en/gizlilik').'"', false);

        $this->get('/en/cerezler')
            ->assertOk()
            ->assertSee('Cookie Policy')
            ->assertSee('rel="canonical" href="'.url('/en/cerezler').'"', false);

        $this->get('/en/kullanim-kosullari')
            ->assertOk()
            ->assertSee('Terms of Use')
            ->assertSee('rel="canonical" href="'.url('/en/kullanim-kosullari').'"', false);
    }

    public function test_english_policy_pages_list_the_other_policies_in_english(): void
    {
        $this->get('/en/gizlilik')
            ->assertOk()
            ->assertSee('Cookie Policy')
            ->assertSee('Which cookies are essential, which ones depend on your consent')
            ->assertSee('Terms of Use')
            ->assertDontSee('Çerez Politikası')
            ->assertDontSee('Kullanım Koşulları')
            ->assertDontSee('Hangi çerezler zorunlu');

        $this->get('/en/kullanim-kosullari')
            ->assertOk()
            ->assertSee('Privacy and Personal Data')
            ->assertDontSee('Gizlilik ve Kişisel Veriler')
            ->assertDontSee('Hangi verileri neden işlediğimiz');
    }

    public function test_english_legal_centre_has_english_sections_and_links_to_the_moved_policies(): void
    {
        $this->get('/en/yasal-bilgilendirme')
            ->assertOk()
            ->assertSee('House Chef Cyprus Terms of Use, Privacy, Cookie and Content Policy')
            ->assertSee('Last updated: 3 August 2026')
            ->assertSee('Recipe and Health Warnings')
            ->assertSee('Content and Source Policy')
            ->assertSee('Send message')
            ->assertSee('Legal information')
            ->assertDontSee('Yasal Bilgilendirme')
            ->assertDontSee('Son güncelleme')
            ->assertDontSee('Mesajı gönder')
            ->assertDontSee('Yazdır')
            ->assertSee('href="'.url('/en/gizlilik').'"', false)
            ->assertSee('rel="canonical" href="'.url('/en/yasal-bilgilendirme').'"', false)
            ->assertSee('action="'.url('/en/yasal-bilgilendirme/iletisim').'"', false);
    }

    public function test_english_contact_page_is_english_and_its_form_posts_to_the_english_legal_form(): void
    {
        $this->get('/en/iletisim')
            ->assertOk()
            ->assertSee('Write to us')
            ->assertSee('Send message')
            ->assertSee('Recipe error')
            ->assertSee('Frequently asked questions')
            ->assertDontSee('Mesajı gönder')
            ->assertDontSee('Tarif Hatası')
            ->assertDontSee('Sıkça Sorulan Sorular')
            ->assertSee('rel="canonical" href="'.url('/en/iletisim').'"', false)
            ->assertSee('action="'.url('/en/yasal-bilgilendirme/iletisim').'"', false);
    }

    public function test_english_contact_submission_is_acknowledged_in_english(): void
    {
        Mail::fake();

        $this->post('/en/yasal-bilgilendirme/iletisim', [
            'first_name' => 'Ada',
            'last_name' => 'Yilmaz',
            'email' => 'contact@housechefcyprus.com',
            'subject' => 'general_feedback',
            'message' => 'Safe English contact test message.',
            'privacy_consent' => '1',
        ])
            ->assertRedirect(url('/en/yasal-bilgilendirme').'#iletisim-ve-geri-bildirim')
            ->assertSessionHas('status', fn (string $mesaj): bool => str_starts_with($mesaj, 'Your message has been received. Your request number: '));
    }

    public function test_schema_declares_the_language_of_the_page(): void
    {
        $this->get('/en/gizlilik')->assertSee('"inLanguage":"en"', false);
        $this->get('/en/yasal-bilgilendirme')->assertSee('"inLanguage":"en"', false);
        $this->get('/yasal-bilgilendirme')->assertSee('"inLanguage":"tr"', false);
    }

    public function test_english_legal_sections_keep_the_turkish_ids_and_paragraph_counts(): void
    {
        // Reklam bölümü yalnız reklam açıkken var; iki durumu da kapsamak için açık tutulur.
        $this->reklamDurumu(true);

        $en = require lang_path('en/legal_center.php');
        $tr = collect(config('legal-center.sections'))->keyBy('id');

        $this->assertSame(
            $tr->keys()->sort()->values()->all(),
            collect($en['sections'])->keys()->sort()->values()->all(),
        );

        foreach ($tr as $id => $bolum) {
            $this->assertCount(count($bolum['paragraphs']), $en['sections'][$id]['paragraphs'], $id);
        }

        $this->assertSame(array_keys(config('legal-center.standalone_pages')), array_keys($en['pages']));
    }

    public function test_english_information_pages_keep_the_turkish_section_counts(): void
    {
        $tr = (new \ReflectionClassConstant(InformationPageController::class, 'PAGES'))->getValue();
        $en = require lang_path('en/information_pages.php');

        $this->assertSame(self::BILGI_SAYFALARI, array_keys($en));

        foreach ($en as $sayfa => $icerik) {
            $this->assertCount(count($tr[$sayfa]['sections']), $icerik['sections'], $sayfa);
        }
    }

    public function test_english_source_files_contain_no_turkish_letters(): void
    {
        foreach (['lang/en/information_pages.php', 'lang/en/legal_center.php'] as $dosya) {
            $this->assertDoesNotMatchRegularExpression('/[ğüşıöçĞÜŞİÖÇ]/u', (string) file_get_contents(base_path($dosya)), $dosya);
        }
    }

    private function reklamDurumu(bool $acik): void
    {
        putenv('GOOGLE_ADSENSE_ENABLED='.($acik ? 'true' : 'false'));
        $_ENV['GOOGLE_ADSENSE_ENABLED'] = $acik ? 'true' : 'false';

        // Yapılandırma dosyası env'i okuma anında değerlendirir.
        config()->set('legal-center', require base_path('config/legal-center.php'));
    }
}
