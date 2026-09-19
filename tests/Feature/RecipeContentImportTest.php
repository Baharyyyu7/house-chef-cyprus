<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Models\Ingredient;
use App\Models\Recipe;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\File;
use Tests\TestCase;

/**
 * Teslim edilen tarif içeriği kalite kapısını geçmeden yayına girmez.
 *
 * 678 şablon tarifi düzeltmenin yolu bu komut. Şablonu şablonla değiştirmek
 * bir şey kazandırmaz, o yüzden kabul ölçütü kapının kendisi.
 */
final class RecipeContentImportTest extends TestCase
{
    use RefreshDatabase;

    private const SABLON = 'Malzemeleri yikayin, dograyin ve tezgahta sirayla dizerek hazirlik yapin.';

    private string $kok;

    protected function setUp(): void
    {
        parent::setUp();
        $this->kok = storage_path('app/content-import/recipes');
        File::ensureDirectoryExists($this->kok);
        File::cleanDirectory($this->kok);
    }

    protected function tearDown(): void
    {
        File::cleanDirectory($this->kok);
        parent::tearDown();
    }

    private function tarif(string $slug, string $aciklama): Recipe
    {
        $tarif = Recipe::query()->create([
            'title' => 'Tarif '.$slug, 'slug' => $slug, 'excerpt' => 'Özet.', 'description' => $aciklama,
            'status' => 'published', 'verification_status' => 'verified', 'published_at' => now()->subDay(),
            'servings' => 4, 'prep_minutes' => 10, 'cook_minutes' => 20,
        ]);

        for ($i = 1; $i <= 3; $i++) {
            $tarif->steps()->create(['position' => $i, 'title' => 'Adım '.$i, 'instruction' => self::SABLON]);
        }

        $malzeme = Ingredient::query()->firstOrCreate(['slug' => 'ana-malzeme'], ['name' => 'Ana Malzeme']);
        $tarif->recipeIngredients()->create(['ingredient_id' => $malzeme->id, 'quantity' => 1, 'unit' => 'adet', 'sort_order' => 1]);

        return $tarif;
    }

    /** @param array<string, mixed> $veri */
    private function teslim(string $ad, array $veri): void
    {
        File::put($this->kok.'/'.$ad.'.json', json_encode($veri, JSON_UNESCAPED_UNICODE));
    }

    /** @return array<string, mixed> */
    private function gercekIcerik(string $slug): array
    {
        return [
            'slug' => $slug,
            'excerpt' => 'Hellim, taze nane ve zeytinyağıyla hazırlanan, tavada iki dakikada kızaran köy kahvaltısı.',
            'description' => 'Hellimin tuzu tavada yoğunlaşır, bu yüzden peyniri kızartmadan önce soğuk suda beş dakika bekletmek dengeyi kurar. Domates suyunu bırakmadan alınır.',
            'steps' => [
                ['title' => 'Hellimi hazırla', 'instruction' => 'Hellimi bir santim kalınlığında dilimleyip soğuk suda beş dakika bekletin, ardından kağıt havluyla kurulayın.'],
                ['title' => 'Tavayı ısıt', 'instruction' => 'Yapışmaz tavayı zeytinyağıyla orta ateşte ısıtın; yağ dumanlanmadan hellimi yerleştirin.'],
                ['title' => 'Kızart', 'instruction' => 'Her yüzü altın rengi olana kadar kırk beş saniye kızartın, kenarları kabarınca çevirin.'],
                ['title' => 'Servis', 'instruction' => 'Dilimlenmiş domates ve taze naneyle sıcak servis edin, üzerine bir tutam karabiber öğütün.'],
            ],
            'ingredients' => [
                ['name' => 'Hellim', 'quantity' => 250, 'unit' => 'g'],
                ['name' => 'Domates', 'quantity' => 2, 'unit' => 'adet'],
                ['name' => 'Zeytinyağı', 'quantity' => 2, 'unit' => 'yemek kaşığı'],
                ['name' => 'Taze Nane', 'quantity' => 5, 'unit' => 'dal'],
            ],
        ];
    }

    public function test_gercek_icerik_iceri_alinir_ve_kapiyi_gecer(): void
    {
        $tarif = $this->tarif('hellim-tavasi', 'Şablon açıklama metni burada duruyor ve hiçbir şey anlatmıyor.');
        $this->teslim('hellim', $this->gercekIcerik('hellim-tavasi'));

        $this->artisan('content:import-recipes --apply')->assertSuccessful();

        $tarif->refresh()->load(['steps', 'ingredients']);
        $this->assertSame(4, $tarif->steps->count());
        $this->assertStringContainsString('soğuk suda beş dakika', (string) $tarif->steps[0]->instruction);
        $this->assertSame(4, $tarif->ingredients->count());
        $this->assertStringNotContainsString('Ana Malzeme', $tarif->ingredients->pluck('name')->implode(','));
    }

    public function test_rapor_modu_hicbir_seyi_yazmaz(): void
    {
        $tarif = $this->tarif('hellim-tavasi', 'Şablon açıklama metni burada duruyor ve hiçbir şey anlatmıyor.');
        $this->teslim('hellim', $this->gercekIcerik('hellim-tavasi'));

        $this->artisan('content:import-recipes')->assertSuccessful();

        $this->assertSame(3, $tarif->fresh()->steps()->count());
    }

    public function test_baska_tariften_kopyalanan_metin_reddedilir(): void
    {
        $kaynak = $this->tarif('kaynak-tarif', 'Kaynak tarifin kendi açıklaması burada yer alıyor efendim.');
        $kaynak->steps()->delete();
        $icerik = $this->gercekIcerik('kaynak-tarif');
        foreach ($icerik['steps'] as $sira => $adim) {
            $kaynak->steps()->create(['position' => $sira + 1, 'title' => $adim['title'], 'instruction' => $adim['instruction']]);
        }
        $kaynak->forceFill(['description' => $icerik['description'], 'excerpt' => $icerik['excerpt']])->save();

        $hedef = $this->tarif('kopya-hedef', 'Hedefin şablon açıklaması burada duruyor ve bir şey anlatmıyor.');
        $this->teslim('kopya', $this->gercekIcerik('kopya-hedef'));

        $this->artisan('content:import-recipes --apply')->assertSuccessful();

        $this->assertSame(3, $hedef->fresh()->steps()->count(), 'Kopya içerik yazılmamalı.');
    }

    public function test_olcusuz_malzeme_reddedilir(): void
    {
        $tarif = $this->tarif('olcusuz-tarif', 'Şablon açıklama metni burada duruyor ve hiçbir şey anlatmıyor.');
        $icerik = $this->gercekIcerik('olcusuz-tarif');
        $icerik['ingredients'][0] = ['name' => 'Hellim'];
        $this->teslim('olcusuz', $icerik);

        $this->artisan('content:import-recipes --apply')->assertSuccessful();

        $this->assertSame(3, $tarif->fresh()->steps()->count());
    }

    public function test_duzelen_tarifin_makine_damgasi_kalkar(): void
    {
        $tarif = $this->tarif('hellim-tavasi', 'Şablon açıklama metni burada duruyor ve hiçbir şey anlatmıyor.');
        app(\App\Services\Seo\SeoMetadataService::class)->update($tarif, ['robots' => 'noindex,follow']);
        $this->teslim('hellim', $this->gercekIcerik('hellim-tavasi'));

        $this->artisan('content:import-recipes --apply')->assertSuccessful();

        $this->assertSame('index,follow', $tarif->fresh('seoMetadata')->seoMetadata?->robots);
    }

    public function test_insanin_koydugu_robots_degeri_korunur(): void
    {
        $tarif = $this->tarif('hellim-tavasi', 'Şablon açıklama metni burada duruyor ve hiçbir şey anlatmıyor.');
        app(\App\Services\Seo\SeoMetadataService::class)->update($tarif, ['robots' => 'noindex,nofollow']);
        $this->teslim('hellim', $this->gercekIcerik('hellim-tavasi'));

        $this->artisan('content:import-recipes --apply')->assertSuccessful();

        $this->assertSame('noindex,nofollow', $tarif->fresh('seoMetadata')->seoMetadata?->robots);
    }

    public function test_gecersiz_kalan_ceviri_uyarisi_verilir(): void
    {
        config(['locales.supported.en.enabled' => true]);
        $tarif = $this->tarif('hellim-tavasi', 'Şablon açıklama metni burada duruyor ve hiçbir şey anlatmıyor.');
        foreach (['title' => 'Halloumi Pan', 'excerpt' => 'Summary.', 'description' => 'Description.'] as $alan => $deger) {
            $tarif->translations()->create(['locale' => 'en', 'field' => $alan, 'value' => $deger]);
        }
        foreach ($tarif->steps as $adim) {
            $adim->translations()->create(['locale' => 'en', 'field' => 'title', 'value' => 'Step']);
            $adim->translations()->create(['locale' => 'en', 'field' => 'instruction', 'value' => 'Old instruction text.']);
        }
        foreach ($tarif->ingredients as $malzeme) {
            $malzeme->translations()->create(['locale' => 'en', 'field' => 'name', 'value' => 'Placeholder']);
        }
        $this->assertTrue($tarif->fresh(['steps', 'ingredients'])->hasTranslation('en'));

        $this->teslim('hellim', $this->gercekIcerik('hellim-tavasi'));

        $this->artisan('content:import-recipes --apply')
            ->expectsOutputToContain('en çevirisi artık eksik')
            ->assertSuccessful();
    }
}
