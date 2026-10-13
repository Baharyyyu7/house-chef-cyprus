<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Models\BlogPost;
use App\Models\Recipe;
use App\Models\SlugRedirect;
use App\Services\Integrity\SiteInvariants;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * 13 Ekim 2026: yayındaki 100 yazının gövdesinde 50 adres 404'tü (taslağa
 * alınmış tariflere), çeviri gövdeleri Türkçe sayfalara gidiyordu ve hiçbir
 * gece denetimi gövdeye bakmıyordu.
 */
final class BlogIcBaglantiOnarTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        config()->set('locales.supported.en.enabled', true);
    }

    private function tarif(string $slug, string $durum = 'published', bool $ingilizce = false): Recipe
    {
        $tarif = Recipe::query()->create([
            'title' => 'Tarif '.$slug,
            'slug' => $slug,
            'excerpt' => 'Özet.',
            'description' => 'Açıklama.',
            'status' => $durum,
            'verification_status' => 'verified',
            'published_at' => now()->subDay(),
        ]);

        if ($ingilizce) {
            foreach (['title' => 'Recipe', 'excerpt' => 'Summary.', 'description' => 'Description.'] as $alan => $deger) {
                $tarif->translations()->create(['locale' => 'en', 'field' => $alan, 'value' => $deger]);
            }
        }

        return $tarif;
    }

    private function yazi(string $govde, ?string $ingilizceGovde = null): BlogPost
    {
        $yazi = BlogPost::query()->create([
            'title' => 'Bağlantılı Yazı',
            'slug' => 'baglantili-yazi-'.uniqid(),
            'excerpt' => 'Özet.',
            'body' => $govde,
            'status' => 'published',
            'published_at' => now()->subDay(),
        ]);

        if ($ingilizceGovde !== null) {
            foreach (['title' => 'Linked Post', 'excerpt' => 'Summary.', 'body' => $ingilizceGovde] as $alan => $deger) {
                $yazi->translations()->create(['locale' => 'en', 'field' => $alan, 'value' => $deger]);
            }
        }

        return $yazi;
    }

    private function kuralVar(): bool
    {
        return in_array('yazida_kirik_ic_baglanti', array_column(app(SiteInvariants::class)->ihlaller(), 'kural'), true);
    }

    public function test_taslaga_alinmis_tarife_giden_baglanti_yakalanir_ve_metin_kalarak_kaldirilir(): void
    {
        $this->tarif('manti', 'draft');
        $yazi = $this->yazi('Hamuru incecik açın, [Mantı tarifimiz](/tarifler/manti) adım adım anlatıyor.');

        $this->assertTrue($this->kuralVar());

        $this->artisan('blog:ic-baglanti-onar', ['--apply' => true])->assertSuccessful();

        $this->assertSame('Hamuru incecik açın, Mantı tarifimiz adım adım anlatıyor.', $yazi->fresh()->getRawOriginal('body'));
        $this->assertFalse($this->kuralVar());
    }

    public function test_yonlendirmeli_ve_buyuk_harfli_adres_canli_adrese_yazilir(): void
    {
        $hedef = $this->tarif('su-boregi-5m4wg');
        SlugRedirect::query()->create(['model_type' => Recipe::class, 'old_slug' => 'su-boregi', 'model_id' => $hedef->id]);
        $yazi = $this->yazi('[Su böreği](/tarifler/su-boregi) ve [aynısı](https://housechefcyprus.com/tarifler/su-boregi-5M4WG "Su böreği").');

        $this->artisan('blog:ic-baglanti-onar', ['--apply' => true])->assertSuccessful();

        $this->assertSame('[Su böreği](/tarifler/su-boregi-5m4wg) ve [aynısı](/tarifler/su-boregi-5m4wg "Su böreği").', $yazi->fresh()->getRawOriginal('body'));
    }

    public function test_ceviri_govdesi_kendi_dilindeki_sayfaya_baglanir_cevirisi_yoksa_kaldirilir(): void
    {
        $this->tarif('sutlac', ingilizce: true);
        $this->tarif('humus');
        $yazi = $this->yazi('[Sütlaç](/tarifler/sutlac) ve [humus](/tarifler/humus).', 'Try [rice pudding](/tarifler/sutlac) or [hummus](/en/tarifler/humus).');

        $this->assertTrue($this->kuralVar());

        $this->artisan('blog:ic-baglanti-onar', ['--apply' => true])->assertSuccessful();

        $this->assertSame('[Sütlaç](/tarifler/sutlac) ve [humus](/tarifler/humus).', $yazi->fresh()->getRawOriginal('body'));
        $this->assertSame('Try [rice pudding](/en/recipes/sutlac) or hummus.', $yazi->translations()->where('locale', 'en')->where('field', 'body')->value('value'));
        $this->assertFalse($this->kuralVar());
    }

    public function test_harita_ayni_yemegin_yayindaki_adresine_yazar_ve_baska_yolu_kaldirir(): void
    {
        $this->tarif('adana-kebabi-mt5xa');
        $yazi = $this->yazi('[Adana](/tarifler/adana-kebab), [şef](/private-chef), [mutfak](/mutfaklar/kibris-mutfagi).');
        $harita = tempnam(sys_get_temp_dir(), 'harita');
        file_put_contents($harita, json_encode(['/tarifler/adana-kebab' => '/tarifler/adana-kebabi-mt5xa', '/private-chef' => null]));

        $this->artisan('blog:ic-baglanti-onar', ['--harita' => $harita, '--apply' => true])->assertSuccessful();

        $this->assertSame('[Adana](/tarifler/adana-kebabi-mt5xa), şef, [mutfak](/mutfaklar/kibris-mutfagi).', $yazi->fresh()->getRawOriginal('body'));
    }

    public function test_gorsel_ve_canli_baglanti_dokunulmaz_kuru_calistirma_yazmaz(): void
    {
        $this->tarif('canli');
        $this->tarif('olu', 'draft');
        $govde = '![Kapak](/tarifler/olu) [Canlı](/tarifler/canli) [Ölü](/tarifler/olu)';
        $yazi = $this->yazi($govde);

        $this->artisan('blog:ic-baglanti-onar')->assertSuccessful();
        $this->assertSame($govde, $yazi->fresh()->getRawOriginal('body'));

        $this->artisan('blog:ic-baglanti-onar', ['--apply' => true])->assertSuccessful();
        $this->assertSame('![Kapak](/tarifler/olu) [Canlı](/tarifler/canli) Ölü', $yazi->fresh()->getRawOriginal('body'));
    }
}
