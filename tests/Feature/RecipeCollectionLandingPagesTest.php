<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Models\Recipe;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use Tests\TestCase;

final class RecipeCollectionLandingPagesTest extends TestCase
{
    use RefreshDatabase;

    public function test_a_duration_collection_with_enough_verified_recipes_is_indexable_and_self_canonical(): void
    {
        $this->tarifler(4, ['prep_minutes' => 5, 'cook_minutes' => 15]);

        $url = url('/sure/20-dakika');
        $icerik = (string) $this->get($url)->assertOk()->getContent();

        $this->assertStringContainsString('20 Dakikada Hazır Tarifler', $icerik);
        $this->assertStringContainsString('rel="canonical" href="'.$url.'"', $icerik);
        $this->assertStringContainsString('name="robots" content="index,follow"', $icerik);
        $this->assertStringNotContainsString('noindex', $icerik);
    }

    public function test_a_thin_duration_collection_returns_not_found(): void
    {
        $this->tarifler(3, ['prep_minutes' => 10, 'cook_minutes' => 20]);

        $this->get('/sure/30-dakika')->assertNotFound();
    }

    public function test_an_empty_difficulty_collection_returns_not_found(): void
    {
        $this->get('/zorluk/kolay')->assertNotFound();
    }

    public function test_drafts_and_unverified_recipes_do_not_fill_the_threshold(): void
    {
        $this->tarifler(2, ['difficulty' => 'easy']);
        $this->tarifler(2, ['difficulty' => 'easy', 'status' => 'draft']);
        $this->tarifler(2, ['difficulty' => 'easy', 'verification_status' => 'needs_review']);

        $this->get('/zorluk/kolay')->assertNotFound();
    }

    public function test_a_difficulty_collection_only_shows_matching_verified_recipes(): void
    {
        $this->tarifler(4, ['difficulty' => 'easy']);
        $this->tarifler(1, ['difficulty' => 'hard', 'title' => 'Gizli Zor Tarif']);

        $this->get('/zorluk/kolay')
            ->assertOk()
            ->assertSee('Kolay Tarifler')
            ->assertDontSee('Gizli Zor Tarif');
    }

    public function test_collection_schema_lists_exactly_the_visible_recipe_cards(): void
    {
        $this->tarifler(4, ['difficulty' => 'easy']);
        $this->tarifler(1, ['difficulty' => 'hard', 'title' => 'Şemaya Girmeyen Zor Tarif']);

        $icerik = (string) $this->get('/zorluk/kolay')->assertOk()->getContent();

        $this->assertStringContainsString('"@type":"CollectionPage"', $icerik);
        $this->assertStringContainsString('"@type":"ItemList"', $icerik);
        $this->assertStringContainsString('"numberOfItems":4', $icerik);
        $this->assertStringNotContainsString('Şemaya Girmeyen Zor Tarif', $icerik);
        $schema = json_decode($this->collectionSchema($icerik), true, flags: JSON_THROW_ON_ERROR);
        $collection = collect($schema['@graph'])->firstWhere('@type', 'CollectionPage');
        foreach ($collection['mainEntity']['itemListElement'] as $item) {
            $this->assertSame(['@type', 'position', 'url'], array_keys($item));
            $this->assertArrayNotHasKey('item', $item, 'Özet sayfası ListItem içinde Recipe ayrıntısı ilan etmez.');
            $this->assertArrayNotHasKey('image', $item, 'Kart özeti görsel uygunluğunu yapılandırılmış veride yeniden yorumlamaz.');
        }
    }

    public function test_collection_pages_emit_no_hreflang_while_only_turkish_is_enabled(): void
    {
        $this->tarifler(4, ['difficulty' => 'easy']);

        $this->get('/zorluk/kolay')->assertOk()->assertDontSee('rel="alternate" hreflang', false);
    }

    public function test_collection_hreflang_uses_the_same_path_when_a_second_locale_is_enabled(): void
    {
        config(['locales.supported.en.enabled' => true]);
        $this->tarifler(4, ['difficulty' => 'easy']);

        $this->get('/zorluk/kolay')
            ->assertOk()
            ->assertSee('hreflang="tr" href="'.url('/zorluk/kolay').'"', false)
            ->assertSee('hreflang="en" href="'.url('/en/zorluk/kolay').'"', false)
            ->assertSee('hreflang="x-default" href="'.url('/zorluk/kolay').'"', false);
    }

    public function test_the_sitemap_lists_exactly_the_duration_and_difficulty_pages_that_exist(): void
    {
        $this->tarifler(4, ['prep_minutes' => 5, 'cook_minutes' => 15, 'difficulty' => 'easy']);

        $xml = (string) $this->get('/sitemaps/pages.xml')->assertOk()->getContent();

        $this->assertStringContainsString(url('/sure/20-dakika'), $xml);
        // 20 dakikanın altındaki dört tarif doğal olarak 30 dakika seçkisini
        // de doldurur; iki sayfanın da sitemap'te olması gerekir.
        $this->assertStringContainsString(url('/sure/30-dakika'), $xml);
        $this->assertStringContainsString(url('/zorluk/kolay'), $xml);
        $this->assertStringNotContainsString(url('/zorluk/zor'), $xml);
    }

    public function test_collection_sitemap_lastmod_is_the_latest_published_recipe_date(): void
    {
        $this->tarifler(4, [
            'difficulty' => 'easy',
            'published_at' => '2026-08-20 12:00:00',
        ]);
        $this->tarifler(1, [
            'difficulty' => 'easy',
            'published_at' => '2026-08-29 12:00:00',
        ]);

        $xml = (string) $this->get('/sitemaps/pages.xml')->assertOk()->getContent();

        $this->assertMatchesRegularExpression(
            '#<loc>'.preg_quote(url('/zorluk/kolay'), '#').'</loc><lastmod>2026-08-29</lastmod>#',
            $xml,
        );
    }

    /** @param array<string, mixed> $attributes */
    private function tarifler(int $adet, array $attributes = []): void
    {
        for ($i = 0; $i < $adet; $i++) {
            $baslik = (string) ($attributes['title'] ?? 'Koleksiyon Tarifi').' '.$i.' '.Str::random(6);

            Recipe::query()->create(array_merge([
                'title' => $baslik,
                'slug' => Str::slug($baslik),
                'excerpt' => 'Doğrulanmış tarif özeti.',
                'description' => 'Doğrulanmış tarif açıklaması.',
                'prep_minutes' => 5,
                'cook_minutes' => 10,
                'rest_minutes' => 0,
                'servings' => 4,
                'difficulty' => 'medium',
                'status' => 'published',
                'verification_status' => 'verified',
                'published_at' => now(),
            ], $attributes, ['title' => $baslik, 'slug' => Str::slug($baslik)]));
        }
    }

    public function test_the_home_chips_point_at_the_collection_pages(): void
    {
        // Sayfalar kuruldu ama hiçbir yerden bağlantı yoktu ve çipler hâlâ
        // noindex filtreli görünüme gidiyordu - Kıbrıs çipiyle aynı hata
        // sınıfı. Öksüz sayfa taranmaz.
        for ($i = 0; $i < 4; $i++) {
            $this->kolayVeHizliTarif('Çip Tarifi '.$i);
        }
        cache()->forget('koleksiyon-baglantilari');

        $this->get(route('home'))
            ->assertOk()
            ->assertSee(route('collections.sure', '20-dakika'), false)
            ->assertSee(route('collections.zorluk', 'kolay'), false);
    }

    public function test_a_chip_falls_back_to_the_filter_when_the_page_is_thin(): void
    {
        // Anasayfadan 404'e bağlanmak, filtreye bağlanmaktan kötü.
        cache()->forget('koleksiyon-baglantilari');

        $this->get(route('home'))
            ->assertOk()
            ->assertSee(route('recipes.index', ['max_minutes' => 20]), false)
            ->assertDontSee(route('collections.sure', '20-dakika'), false);
    }

    private function kolayVeHizliTarif(string $baslik): Recipe
    {
        return Recipe::query()->create([
            'title' => $baslik,
            'slug' => Str::slug($baslik).'-'.uniqid(),
            'excerpt' => 'Özet.',
            'description' => 'Açıklama.',
            'status' => 'published',
            'verification_status' => 'verified',
            'published_at' => now(),
            'difficulty' => 'easy',
            'prep_minutes' => 5,
            'cook_minutes' => 10,
            'rest_minutes' => 0,
        ]);
    }

    private function collectionSchema(string $icerik): string
    {
        preg_match_all('#<script type="application/ld\+json">(.*?)</script>#s', $icerik, $eslesmeler);

        return collect($eslesmeler[1] ?? [])->first(fn (string $json): bool => str_contains($json, 'CollectionPage'), '');
    }
}
