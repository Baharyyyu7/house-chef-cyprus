<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Models\Cuisine;
use App\Models\Recipe;
use App\Services\Integrity\LinkIntegrityProbe;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\Client\Request;
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

    public function test_rate_limit_stops_probe_and_does_not_mark_unchecked_pages_broken(): void
    {
        Http::fake(['*' => Http::response('', 429, ['Retry-After' => '600'])]);
        $probe = app(LinkIntegrityProbe::class);
        $report = $probe->probe([], 60, [url('/a'), url('/b')]);
        $this->assertSame('incomplete', $report['status']);
        $this->assertSame(0, $report['broken']);
        $this->assertSame(1, $report['checked']);
        $this->assertSame(1, $report['deferred']);
        $this->assertNull($probe->orphanPages(url('/sitemap.xml'), [])['orphaned']);
        Http::assertSentCount(1);
    }

    public function test_missing_sitemap_is_unknown_not_zero_orphans(): void
    {
        Http::fake(['*' => Http::response('', 503)]);
        $report = app(LinkIntegrityProbe::class)->orphanPages(url('/sitemap.xml'), []);
        $this->assertNull($report['orphaned']);
        $this->assertFalse($report['sitemap_complete']);
    }

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

    public function test_it_reports_sitemap_pages_that_receive_no_link_from_any_hub(): void
    {
        $base = rtrim((string) config('app.url'), '/');
        Http::fake([
            $base.'/sitemaps/pages.xml' => Http::response(
                '<urlset><url><loc>'.$base.'/bagli</loc></url><url><loc>'.$base.'/oksuz</loc></url></urlset>',
                200
            ),
            $base.'/hub-a' => Http::response('<a href="'.$base.'/bagli">Bağlı</a>', 200),
            $base.'/hub-b' => Http::response('<a href="'.$base.'/bagli#tekrar">Bağlı yine</a>', 200),
        ]);

        $rapor = app(LinkIntegrityProbe::class)->orphanPages(
            $base.'/sitemaps/pages.xml',
            [$base.'/hub-a', $base.'/hub-b'],
        );

        $this->assertSame(2, $rapor['sitemap_count']);
        $this->assertSame(1, $rapor['orphaned']);
        $this->assertSame([$base.'/oksuz'], $rapor['orphan_urls']);
        $this->assertSame(2, $rapor['backlinks'][$base.'/bagli']);
    }

    public function test_the_command_checks_each_existing_duration_and_difficulty_collection_directly(): void
    {
        for ($i = 0; $i < 4; $i++) {
            Recipe::query()->create([
                'title' => 'Koleksiyon Tarif '.$i, 'slug' => 'koleksiyon-tarif-'.$i,
                'status' => 'published', 'verification_status' => 'verified',
                'difficulty' => 'easy', 'prep_minutes' => 5, 'cook_minutes' => 15, 'rest_minutes' => 0,
            ]);
        }

        $istenenler = [];
        Http::fake(function (Request $istek) use (&$istenenler) {
            $istenenler[] = $istek->url();

            return Http::response('<html></html>', 200);
        });

        $this->artisan('content:link-integrity --json')->assertSuccessful();

        $this->assertContains(route('collections.sure', '20-dakika'), $istenenler);
        $this->assertContains(route('collections.sure', '30-dakika'), $istenenler);
        $this->assertContains(route('collections.zorluk', 'kolay'), $istenenler);
        $this->assertNotContains(route('collections.zorluk', 'zor'), $istenenler);
    }

    /**
     * @param  list<string>  $adresler
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
        Http::fake(function (Request $istek) use ($adresler) {
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
