<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Models\Cuisine;
use App\Models\Recipe;
use App\Services\Integrity\LinkIntegrityProbe;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;
use Tests\TestCase;

/**
 * İç bağlantılar gerçekten bir yere götürüyor mu?
 *
 * Üretimde aylarca fark edilmeyen kusur şuydu: anasayfadaki "Kıbrıs mutfağı"
 * çipi `?cuisine=kibris` kullanıyordu, o slug hiçbir mutfağa karşılık
 * gelmiyordu ve bağlantı sıfır tarif listeliyordu. HTTP 200 döndüğü için
 * hiçbir sağlık kontrolüne takılmadı - sitenin amiral konusundaki iki öne
 * çıkan bağlantı boş sayfaya götürüyordu.
 */
final class LinkIntegrityProbeTest extends TestCase
{
    use RefreshDatabase;

    public function test_a_filter_that_lists_nothing_is_a_finding(): void
    {
        // Asıl yakalanmak istenen kusur bu: 200 dönen ama boş olan sayfa.
        $this->tarif('Hellimli Gözleme', $this->mutfak('Kıbrıs Mutfağı', 'kibris-mutfagi'));
        $adres = route('recipes.index', ['cuisine' => 'kibris']);

        $rapor = $this->probe([$adres]);

        $this->assertSame(1, $rapor['broken']);
        $this->assertSame('EMPTY_FILTER', $rapor['findings'][0]['reason']);
    }

    public function test_a_filter_that_lists_recipes_is_not_a_finding(): void
    {
        $mutfak = $this->mutfak('Kıbrıs Mutfağı', 'kibris-mutfagi');
        $this->tarif('Hellimli Gözleme', $mutfak);

        $rapor = $this->probe([route('recipes.index', ['cuisine' => 'kibris-mutfagi'])]);

        $this->assertSame(0, $rapor['broken']);
    }

    public function test_an_unfiltered_catalogue_link_is_not_checked_for_emptiness(): void
    {
        // Filtresiz katalog boş olabilir; bu bir bağlantı kusuru değil,
        // içerik durumu.
        $rapor = $this->probe([route('recipes.index')]);

        $this->assertSame(0, $rapor['broken']);
    }

    public function test_a_link_that_returns_404_is_a_finding(): void
    {
        $rapor = $this->probe([url('/mutfaklar/olmayan-mutfak')]);

        $this->assertSame(1, $rapor['broken']);
        $this->assertSame('HTTP_404', $rapor['findings'][0]['reason']);
    }

    public function test_external_links_are_not_our_responsibility(): void
    {
        Http::fake(['*' => Http::response('<a href="https://baska-site.test/sayfa">dış</a><a href="'.url('/tarifler').'">iç</a>', 200)]);

        $baglantilar = app(LinkIntegrityProbe::class)->baglantilar('https://ornek.test/sayfa');

        $this->assertSame([url('/tarifler')], $baglantilar);
    }

    public function test_the_same_link_is_only_checked_once(): void
    {
        Http::fake(['*' => Http::response('<a href="'.url('/tarifler').'">a</a><a href="'.url('/tarifler').'#alt">b</a>', 200)]);

        $this->assertCount(1, app(LinkIntegrityProbe::class)->baglantilar('https://ornek.test/sayfa'));
    }

    /**
     * @param list<string> $adresler
     * @return array<string, mixed>
     */
    private function probe(array $adresler): array
    {
        // Hub taramasını atlayıp doğrudan verilen adresleri denetlemek için
        // sahte bir hub sayfası kuruluyor.
        Http::fake([
            'ornek.test/*' => Http::response(implode('', array_map(fn (string $a): string => '<a href="'.$a.'">x</a>', $adresler)), 200),
        ]);
        Http::preventStrayRequests();

        // Gerçek uygulama isteklerini test istemcisine yönlendir.
        Http::fake(function (\Illuminate\Http\Client\Request $istek) use ($adresler) {
            if (str_contains($istek->url(), 'ornek.test')) {
                return Http::response(implode('', array_map(fn (string $a): string => '<a href="'.$a.'">x</a>', $adresler)), 200);
            }

            $cevap = $this->get($istek->url());

            return Http::response($cevap->getContent(), $cevap->getStatusCode());
        });

        return app(LinkIntegrityProbe::class)->probe(['https://ornek.test/hub']);
    }

    private function mutfak(string $ad, string $slug): Cuisine
    {
        return Cuisine::query()->create(['name' => $ad, 'slug' => $slug]);
    }

    private function tarif(string $baslik, Cuisine $mutfak): Recipe
    {
        return Recipe::query()->create([
            'title' => $baslik,
            'slug' => Str::slug($baslik).'-'.uniqid(),
            'excerpt' => 'Özet.',
            'description' => 'Açıklama.',
            'status' => 'published',
            'verification_status' => 'verified',
            'published_at' => now(),
            'cuisine_id' => $mutfak->id,
        ]);
    }
}
