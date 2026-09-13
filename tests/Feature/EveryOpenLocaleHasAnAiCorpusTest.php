<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Models\Recipe;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Her açık dilin bir AI korpusu (llms.txt) vardır.
 *
 * Perplexity, ChatGPT ve Claude siteyi bu dosyadan tanıyor. Eskiden her dil
 * için ayrı bir metot yazmak gerekiyordu; yani dil açmak bir satır, o dilin
 * üretken arama motorlarına görünmesi ayrı bir işti ve unutulabilirdi.
 *
 * Korpus yalnız o dilde TAM çevirisi olan içeriği listeler: AI'a Türkçe
 * başlık göstermek o dilin korpusunu kirletmekten başka bir şey değil.
 */
final class EveryOpenLocaleHasAnAiCorpusTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        config()->set('locales.supported.de.enabled', true);
    }

    private function tarif(string $slug, ?string $almanca): Recipe
    {
        $tarif = Recipe::query()->create([
            'title' => 'Hellim Izgara',
            'slug' => $slug,
            'excerpt' => 'Türkçe özet.',
            'description' => 'Türkçe açıklama.',
            'status' => 'published',
            'verification_status' => 'verified',
            'published_at' => now(),
        ]);

        if ($almanca !== null) {
            foreach (['title' => $almanca, 'excerpt' => 'Deutsche Zusammenfassung.', 'description' => 'Deutsche Beschreibung.'] as $alan => $deger) {
                $tarif->translations()->create(['locale' => 'de', 'field' => $alan, 'value' => $deger]);
            }
        }

        return $tarif;
    }

    public function test_acik_dil_korpus_verir(): void
    {
        $this->tarif('gegrillter-halloumi', 'Gegrillter Halloumi');

        $icerik = $this->get('/de/llms.txt')->assertOk()
            ->assertHeader('Content-Type', 'text/plain; charset=UTF-8')
            ->getContent();

        $this->assertStringContainsString('Gegrillter Halloumi', $icerik);
        $this->assertStringContainsString(url('/de/tarifler/gegrillter-halloumi'), $icerik);
    }

    public function test_cevrilmemis_icerik_korpusa_girmez(): void
    {
        $this->tarif('cevrisiz', null);

        $icerik = $this->get('/de/llms.txt')->assertOk()->getContent();

        $this->assertStringNotContainsString('Hellim Izgara', $icerik, 'Türkçe başlık Almanca korpusa sızdı.');
        $this->assertStringNotContainsString('/de/tarifler/cevrisiz', $icerik);
    }

    public function test_icerik_yokken_dogru_soyler(): void
    {
        // Boş bölüm, korpusu okuyan modele "burada içerik yok" demenin dürüst
        // yolu. Türkçeye düşmek yanlış bilgi olurdu.
        $icerik = $this->get('/de/llms.txt')->assertOk()->getContent();

        $this->assertStringContainsString('No recipe has been fully translated', $icerik);
    }

    public function test_kapali_dil_korpus_vermez(): void
    {
        config()->set('locales.supported.pl.enabled', false);

        $this->get('/pl/llms.txt')->assertNotFound();
    }

    public function test_yeni_dil_acmak_kod_gerektirmez(): void
    {
        // Altyapının kendi kendine büyümesi tam olarak bu: yapılandırmada
        // bayrağı çevirmek, o dilin korpusunu var etmeye yetiyor.
        config()->set('locales.supported.fa.enabled', true);

        $this->get('/fa/llms.txt')->assertOk()->assertSee('Language: fa', false);
    }
}
