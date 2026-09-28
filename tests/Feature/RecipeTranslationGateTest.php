<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Models\Category;
use App\Models\ConiAction;
use App\Models\Cuisine;
use App\Models\Ingredient;
use App\Models\IntegrityReport;
use App\Models\Recipe;
use App\Services\Seo\RecipeTranslationGate;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Queue;
use Tests\TestCase;

/**
 * Yabancı dildeki tarif ancak adımları gerçekten çevrilmişse aranabilir.
 *
 * 24 Eylül 2026: altı dilin her birinde 588 adım çevirisinin 588'i aynı
 * kalıptı ("In step 1, carefully proceed with the culinary preparation...").
 */
final class RecipeTranslationGateTest extends TestCase
{
    use RefreshDatabase;

    private const KALIP = [
        'In step 1, carefully proceed with the culinary preparation: Ensure appropriate heat control. Şeker ve suyu kaynatın.',
        'In step 2, carefully proceed with the culinary preparation: Ensure appropriate heat control. Kadayıfı ekleyin.',
        'In step 3, carefully proceed with the culinary preparation: Ensure appropriate heat control. Fıstık serpin.',
    ];

    private const GERCEK = [
        'Boil the sugar and water for 5 minutes until the syrup thickens slightly.',
        'Pull the kadayif apart and toast it in the butter over low heat without letting it brown.',
        'Pour in the syrup, stir until absorbed and finish with ground pistachio.',
    ];

    protected function setUp(): void
    {
        parent::setUp();

        config()->set('locales.supported.en.enabled', true);
    }

    /** @param list<string>|null $adimlar */
    private function tarif(?array $adimlar, string $slug = 'cennet-camuru'): Recipe
    {
        // Mutfağı ve kategorisi olmayan tarif her dilde noindex; kapının
        // etkisi ancak eksiksiz bir tarifte görülür.
        $mutfak = Cuisine::query()->firstOrCreate(['slug' => 'turk-mutfagi'], ['name' => 'Türk Mutfağı']);
        $kategori = Category::query()->firstOrCreate(['slug' => 'tatli'], ['name' => 'Tatlı']);
        $tarif = Recipe::query()->create([
            'cuisine_id' => $mutfak->id, 'category_id' => $kategori->id,
            'title' => 'Cennet Çamuru', 'slug' => $slug, 'excerpt' => 'Özet.',
            'description' => 'Kilis usulü kadayıflı tatlı.', 'servings' => 4, 'prep_minutes' => 10, 'cook_minutes' => 25,
            'status' => 'published', 'verification_status' => 'verified', 'published_at' => now(),
        ]);

        foreach (['title' => 'Cennet Çamuru', 'excerpt' => 'Summary.', 'description' => 'Kilis-style kadayif dessert.'] as $alan => $deger) {
            $tarif->translations()->create(['locale' => 'en', 'field' => $alan, 'value' => $deger]);
        }

        foreach (['Şeker ve suyu kaynatın.', 'Kadayıfı tereyağında kavurun.', 'Şerbeti ekleyip fıstık serpin.'] as $sira => $talimat) {
            $adim = $tarif->steps()->create(['position' => $sira + 1, 'title' => 'Adım '.($sira + 1), 'instruction' => $talimat]);

            if ($adimlar !== null) {
                $adim->translations()->create(['locale' => 'en', 'field' => 'title', 'value' => 'Step '.($sira + 1)]);
                $adim->translations()->create(['locale' => 'en', 'field' => 'instruction', 'value' => $adimlar[$sira]]);
            }
        }

        return $tarif;
    }

    public function test_kalip_eksik_ve_gercek_ceviri_ayrilir(): void
    {
        $kapi = new RecipeTranslationGate;

        $kalip = $this->tarif(self::KALIP);
        $this->assertSame('sablon', $kapi->engel($kalip, 'en'));
        $this->assertNull($kapi->engel($kalip, 'tr'));

        $this->assertSame('eksik', (new RecipeTranslationGate)->engel($this->tarif(null, 'cennet-camuru-2'), 'en'));
        $this->assertNull((new RecipeTranslationGate)->engel($this->tarif(self::GERCEK, 'cennet-camuru-3'), 'en'));
    }

    /**
     * 27 Eylül 2026'ya kadar kalıp çevirili sayfa açık kalıyor, yalnız
     * noindex oluyordu. AdSense siteyi "düşük değerli içerik" diye reddetti;
     * inceleme indekse değil siteye bakıyor ve altı dilde ~530 sayfa bu
     * çöpü gösteriyordu. Artık o dilde sayfa yok: 404, hreflang ve
     * listelemeler de onu göstermez.
     */
    public function test_kalip_cevirili_sayfa_o_dilde_yok_ve_hicbir_yerden_gosterilmez(): void
    {
        $this->tarif(self::KALIP);

        $this->get('/en/recipes/cennet-camuru')->assertNotFound();
        $tr = (string) $this->get('/tarifler/cennet-camuru')->assertOk()->assertDontSee('<meta name="robots" content="noindex', false)->getContent();
        $this->assertStringNotContainsString('hreflang="en"', $tr);
        $this->assertStringNotContainsString('hreflang="en"', (string) $this->get(route('sitemap.recipes'))->getContent());
        $this->assertSame(0, Recipe::query()->translatedInto('en')->count());
        $this->assertSame(1, Recipe::query()->translatedInto('tr')->count());
    }

    public function test_gercek_ceviri_yazilinca_sayfa_acilir_ve_geri_alinabilir(): void
    {
        $this->tarif(self::KALIP);
        $dosya = tempnam(sys_get_temp_dir(), 'coni');
        file_put_contents($dosya, (string) json_encode(['title' => 'Cennet Çamuru: Kilis Kadayif Dessert', 'steps' => self::GERCEK, 'step_titles' => ['Make the syrup', 'Toast the kadayif', 'Finish']]));

        $this->artisan('coni:ceviri', ['slug' => 'cennet-camuru', 'dil' => 'en', 'dosya' => $dosya, '--neden' => 'Kalıp çeviri; 106 gösterim.'])->assertSuccessful();

        $this->get('/en/recipes/cennet-camuru')->assertOk()
            ->assertSee('Pull the kadayif apart')
            ->assertSee('Toast the kadayif')
            ->assertDontSee('<meta name="robots" content="noindex', false);
        $this->assertStringContainsString('hreflang="en"', (string) $this->get(route('sitemap.recipes'))->getContent());

        $is = ConiAction::query()->where('kind', ConiAction::CEVIRI)->firstOrFail();
        $this->assertSame('sablon', $is->evidence['engel_once']);

        $this->artisan('coni:geri-al', ['id' => $is->id])->assertSuccessful();

        $this->assertSame('sablon', (new RecipeTranslationGate)->engel(Recipe::query()->firstOrFail(), 'en'));
        $this->assertSame('Cennet Çamuru', Recipe::query()->firstOrFail()->translated('title', 'en'));
        $this->assertSame('Step 2', Recipe::query()->firstOrFail()->steps()->orderBy('position')->get()[1]->translated('title', 'en'));
        unlink($dosya);
    }

    /**
     * Malzeme adları kelime parçası değiştirilerek üretilmişti: "Taze
     * Fasulye" → "Taze Fawasserlye". Düzeltme kuyruğu da tıkamamalı.
     */
    public function test_malzeme_adlari_olaysiz_duzeltilir_ve_geri_alinir(): void
    {
        $malzeme = Ingredient::query()->create(['name' => 'Taze Fasulye', 'slug' => 'taze-fasulye']);
        $malzeme->translations()->create(['locale' => 'de', 'field' => 'name', 'value' => 'Taze Fawasserlye']);
        $dosya = tempnam(sys_get_temp_dir(), 'coni');
        file_put_contents($dosya, (string) json_encode([(string) $malzeme->id => ['de' => 'Grüne Bohnen', 'en' => 'Green beans', 'tr' => 'yok sayılır']]));
        Queue::fake();

        $this->artisan('coni:malzeme', ['dosya' => $dosya])->assertSuccessful();

        $this->assertSame('Grüne Bohnen', $malzeme->fresh()->translated('name', 'de'));
        $this->assertSame('Green beans', $malzeme->fresh()->translated('name', 'en'));
        $this->assertSame('Taze Fasulye', $malzeme->fresh()->getRawOriginal('name'));
        Queue::assertNothingPushed();

        $rapor = IntegrityReport::query()->where('guard', 'coni:malzeme')->firstOrFail();
        $this->artisan('coni:malzeme', ['--geri-al' => $rapor->id])->assertSuccessful();

        $this->assertSame('Taze Fawasserlye', $malzeme->fresh()->translated('name', 'de'));
        $this->assertNull($malzeme->fresh()->translated('name', 'en'));
        unlink($dosya);
    }

    public function test_toplu_ceviri_kalibi_acar_gercek_ceviriyi_ezmez(): void
    {
        $this->tarif(self::KALIP);
        $this->tarif(self::GERCEK, 'cennet-camuru-2');
        $klasor = sys_get_temp_dir().'/coni-toplu-'.uniqid();
        mkdir($klasor);
        file_put_contents($klasor.'/cennet-camuru.json', (string) json_encode(['en' => ['steps' => self::GERCEK]]));
        file_put_contents($klasor.'/cennet-camuru-2.json', (string) json_encode(['en' => ['steps' => ['Bir.', 'İki farklı adım.', 'Üçüncü başka adım.']]]));

        $this->artisan('coni:ceviri-toplu', ['klasor' => $klasor])->expectsOutputToContain('1 yazıldı, 1 dizine açıldı, 1 atlandı')->assertSuccessful();

        $this->assertNull((new RecipeTranslationGate)->engel(Recipe::query()->where('slug', 'cennet-camuru')->firstOrFail(), 'en'));
        $this->assertSame(self::GERCEK[0], Recipe::query()->where('slug', 'cennet-camuru-2')->firstOrFail()->steps()->orderBy('position')->first()->translated('instruction', 'en'));
        array_map('unlink', glob($klasor.'/*.json'));
        rmdir($klasor);
    }

    public function test_kalip_ya_da_eksik_ceviri_kabul_edilmez(): void
    {
        $this->tarif(self::KALIP);
        $dosya = tempnam(sys_get_temp_dir(), 'coni');

        file_put_contents($dosya, (string) json_encode(['steps' => array_slice(self::GERCEK, 0, 2)]));
        $this->artisan('coni:ceviri', ['slug' => 'cennet-camuru', 'dil' => 'en', 'dosya' => $dosya, '--neden' => 'x'])->assertFailed();

        file_put_contents($dosya, (string) json_encode(['steps' => self::KALIP]));
        $this->artisan('coni:ceviri', ['slug' => 'cennet-camuru', 'dil' => 'en', 'dosya' => $dosya, '--neden' => 'x'])->assertFailed();

        $this->assertSame(0, ConiAction::query()->count());
        unlink($dosya);
    }
}
