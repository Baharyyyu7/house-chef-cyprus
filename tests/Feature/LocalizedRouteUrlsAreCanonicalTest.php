<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Cuisine;
use App\Models\Recipe;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Route;
use Tests\TestCase;

/**
 * Her dilde route() kanonik, yönlendirmesiz adres üretir.
 *
 * 8 Ekim 2026 denetimi: SetLocale'deki adres biçimleyici yalnız dil önekini
 * ekliyordu, yol yazımını (tarifler → recipes, mutfaklar → kuechen)
 * uygulamıyordu. route() ile üretilen her yabancı adres 301'e gidiyordu:
 * 4.696 iç bağlantı, 217 Recipe şeması ve 23 kanonik. /de/kuechen sayfasının
 * kanoniği /de/mutfaklar'dı, o da geri /de/kuechen'e dönüyordu.
 */
final class LocalizedRouteUrlsAreCanonicalTest extends TestCase
{
    use RefreshDatabase;

    private const DILLER = ['en', 'de', 'ru'];

    protected function setUp(): void
    {
        parent::setUp();

        // Biçimleyici yalnız istek içinde etkin; adresleri gerçek bir web
        // isteğinin içinden okumak için ad listesinde olan bir test rotası.
        Route::middleware('web')->get('/_adres-olcumu', fn () => implode('|', [
            route('recipes.index'),
            route('recipes.show', 'ispanakli-gozleme'),
            route('cuisines.index'),
            route('cuisines.show', 'akdeniz-mutfagi'),
            route('blog.show', 'bir-yazi'),
            route('home'),
        ]))->name('recipes.adres-olcumu');
        app('router')->getRoutes()->refreshNameLookups();
    }

    public function test_route_uretimi_her_dilde_yerellesmis_yolu_kullanir(): void
    {
        $beklenen = [
            'en' => ['/en/recipes', '/en/recipes/ispanakli-gozleme', '/en/cuisines', '/en/cuisines/akdeniz-mutfagi', '/en/blog/bir-yazi', '/en'],
            'de' => ['/de/rezepte', '/de/rezepte/ispanakli-gozleme', '/de/kuechen', '/de/kuechen/akdeniz-mutfagi', '/de/blog/bir-yazi', '/de'],
            'ru' => ['/ru/recepty', '/ru/recepty/ispanakli-gozleme', '/ru/kuhni', '/ru/kuhni/akdeniz-mutfagi', '/ru/blog/bir-yazi', '/ru'],
        ];

        foreach ($beklenen as $dil => $yollar) {
            $uretilen = explode('|', (string) $this->get('/'.$dil.'/_adres-olcumu')->assertOk()->getContent());

            $this->assertSame(array_map(static fn (string $yol): string => url($yol), $yollar), $uretilen, $dil);

            // Liste sayfaları kendileri açılır; 301 zinciri yok.
            foreach ([$uretilen[0], $uretilen[2]] as $adres) {
                $this->get($adres)->assertOk();
            }
        }

        $this->assertSame(
            url('/tarifler').'|'.url('/tarifler/ispanakli-gozleme').'|'.url('/mutfaklar').'|'.url('/mutfaklar/akdeniz-mutfagi').'|'.url('/blog/bir-yazi').'|'.url('/'),
            (string) $this->get('/_adres-olcumu')->getContent(),
            'Türkçe adresler değişmemeli.'
        );
    }

    public function test_mutfak_sayfalari_kendi_dil_yazimini_kanonik_ve_baglanti_olarak_basar(): void
    {
        $this->mutfakVeTarifler();

        foreach (['de' => 'kuechen', 'en' => 'cuisines', 'ru' => 'kuhni'] as $dil => $yazim) {
            $tarifYazimi = config("locales.path_aliases.{$dil}.tarifler");

            $liste = (string) $this->get("/{$dil}/{$yazim}")->assertOk()->getContent();
            $this->assertStringContainsString('rel="canonical" href="'.url("/{$dil}/{$yazim}").'"', $liste);
            $this->assertStringContainsString('href="'.url("/{$dil}/{$yazim}/akdeniz-mutfagi").'"', $liste);
            $this->assertStringNotContainsString('/'.$dil.'/mutfaklar', $liste);

            $sayfa = (string) $this->get("/{$dil}/{$yazim}/akdeniz-mutfagi")->assertOk()->getContent();
            $this->assertStringContainsString('rel="canonical" href="'.url("/{$dil}/{$yazim}/akdeniz-mutfagi").'"', $sayfa);
            $this->assertStringContainsString(url("/{$dil}/{$tarifYazimi}/akdeniz-1"), $sayfa);
            $this->assertStringNotContainsString('/'.$dil.'/tarifler/', $sayfa);
            $this->assertStringNotContainsString('/'.$dil.'/mutfaklar', $sayfa);

            $kirintilar = collect($this->jsonLd($sayfa))->firstWhere('@type', 'BreadcrumbList');
            $this->assertSame(url("/{$dil}/{$yazim}"), $kirintilar['itemListElement'][1]['item']);
        }
    }

    public function test_tarif_sayfasi_semasi_ve_kanonigi_yerellesmis_adresi_gosterir(): void
    {
        $this->mutfakVeTarifler();

        foreach (self::DILLER as $dil) {
            $yazim = config("locales.path_aliases.{$dil}.tarifler");
            $adres = url("/{$dil}/{$yazim}/akdeniz-1");
            $sayfa = (string) $this->get($adres)->assertOk()->getContent();

            $this->assertStringContainsString('rel="canonical" href="'.$adres.'"', $sayfa);
            $this->assertStringNotContainsString('/'.$dil.'/tarifler/', $sayfa, $dil.' sayfasında yönlendiren tarif adresi var');
            $this->assertStringNotContainsString('/'.$dil.'/mutfaklar', $sayfa, $dil.' sayfasında yönlendiren mutfak adresi var');

            $recipe = collect($this->jsonLd($sayfa))->firstWhere('@type', 'Recipe');
            $this->assertNotNull($recipe, $dil);
            $this->assertSame($adres, $recipe['url']);
            $this->assertSame($adres, $recipe['mainEntityOfPage']['@id']);
        }
    }

    private function mutfakVeTarifler(): void
    {
        $mutfak = Cuisine::query()->create(['name' => 'Akdeniz Mutfağı', 'slug' => 'akdeniz-mutfagi']);
        $kategori = Category::query()->create(['name' => 'Çorba', 'slug' => 'corba']);

        foreach (range(1, 4) as $i) {
            $tarif = Recipe::query()->create([
                'title' => 'Akdeniz '.$i, 'slug' => 'akdeniz-'.$i,
                'excerpt' => 'Özet.', 'description' => 'Açıklama.',
                'status' => 'published', 'verification_status' => 'verified', 'published_at' => now()->subDay(),
                'cuisine_id' => $mutfak->id, 'category_id' => $kategori->id,
                'prep_minutes' => 10, 'cook_minutes' => 10, 'servings' => 2,
            ]);
            $this->makeRecipePubliclyEligible($tarif);

            foreach (self::DILLER as $dil) {
                foreach (['title' => 'Mediterranean '.$i, 'excerpt' => 'Summary.', 'description' => 'Description.'] as $alan => $deger) {
                    $tarif->translations()->create(['locale' => $dil, 'field' => $alan, 'value' => $deger]);
                }
            }
        }
    }

    /** @return list<array<string, mixed>> */
    private function jsonLd(string $html): array
    {
        preg_match_all('#<script type="application/ld\+json">(.*?)</script>#s', $html, $eslesmeler);

        return collect($eslesmeler[1])
            ->map(fn (string $json): array => (array) json_decode($json, true))
            ->flatMap(fn (array $belge): array => $belge['@graph'] ?? [$belge])
            ->values()
            ->all();
    }
}
