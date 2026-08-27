<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Models\Ingredient;
use App\Models\Recipe;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use Tests\TestCase;

final class IngredientHubPagesTest extends TestCase
{
    use RefreshDatabase;

    public function test_an_ingredient_with_enough_recipes_gets_a_page(): void
    {
        $malzeme = $this->malzemeIle(5);

        $this->get(route('ingredients.hub.show', $malzeme->slug))
            ->assertOk()
            ->assertSee($malzeme->name.' ile ne yapılır?');
    }

    public function test_a_thin_ingredient_has_no_page_at_all(): void
    {
        // 180 malzeme yayındaki tariflerde geçiyor ama yalnız 39'unun beşten
        // fazla tarifi var. Hepsine sayfa açmak 102 tane tek tarifli ince
        // sayfa üretmek olurdu; yardımcı içerik sistemi site geneli çalıştığı
        // için bu, güçlü sayfaların sıralamasını da aşağı çeker.
        $malzeme = $this->malzemeIle(2);

        $this->get(route('ingredients.hub.show', $malzeme->slug))->assertNotFound();
    }

    public function test_the_index_lists_only_ingredients_that_have_a_page(): void
    {
        $guclu = $this->malzemeIle(6, 'Hellim Peyniri');
        $zayif = $this->malzemeIle(1, 'Defne Yaprağı');

        $this->get(route('ingredients.hub.index'))
            ->assertOk()
            ->assertSee($guclu->name)
            ->assertDontSee($zayif->name);
    }

    public function test_the_sitemap_and_the_listing_share_one_threshold(): void
    {
        // Ayrışırlarsa sitemap 404 veren adresleri ilan eder ve tarama
        // bütçesi boşa gider.
        $guclu = $this->malzemeIle(5, 'Kuru Fasulye');
        $zayif = $this->malzemeIle(1, 'Karanfil');

        $icerik = (string) $this->get(route('sitemap.pages'))->assertOk()->getContent();

        $this->assertStringContainsString($guclu->slug, $icerik);
        $this->assertStringNotContainsString($zayif->slug, $icerik);
    }

    public function test_every_sitemap_ingredient_url_actually_resolves(): void
    {
        $this->malzemeIle(5, 'Zeytinyağı');

        $icerik = (string) $this->get(route('sitemap.pages'))->assertOk()->getContent();

        preg_match_all('#<loc>([^<]*/malzemeler/[^<]+)</loc>#', $icerik, $eslesme);

        $this->assertNotEmpty($eslesme[1]);

        foreach ($eslesme[1] as $adres) {
            $this->get(parse_url($adres, PHP_URL_PATH))->assertOk();
        }
    }

    public function test_an_unknown_ingredient_is_not_found(): void
    {
        $this->get(route('ingredients.hub.show', 'olmayan-malzeme'))->assertNotFound();
    }


    public function test_a_pantry_staple_never_gets_a_page_however_many_recipes_it_has(): void
    {
        // Eşik veri hacmini filtreliyor ama arama niyetini filtrelemiyor ve
        // sinyal burada ters işliyor: tuz 73 tarifte geçiyor, tam da her
        // yemekte olduğu için. Kimse "tuz ile ne yapılır" diye aramaz.
        $tuz = Ingredient::query()->create(['name' => 'Tuz', 'slug' => 'tuz']);

        for ($i = 0; $i < 30; $i++) {
            $tarif = Recipe::query()->create([
                'title' => 'Tuzlu Tarif '.$i,
                'slug' => 'tuzlu-tarif-'.$i.'-'.uniqid(),
                'excerpt' => 'Özet.', 'description' => 'Açıklama.',
                'status' => 'published', 'verification_status' => 'verified',
                'published_at' => now(),
            ]);
            $tarif->recipeIngredients()->create([
                'ingredient_id' => $tuz->id,
                'display_quantity' => 1, 'display_measure_code' => 'g', 'sort_order' => 1,
            ]);
        }

        $this->get(route('ingredients.hub.show', 'tuz'))->assertNotFound();
        $this->get(route('ingredients.hub.index'))->assertOk()->assertDontSee('>Tuz<', false);
    }

    public function test_the_placeholder_label_is_not_treated_as_an_ingredient(): void
    {
        // "Ana Malzeme" veri tarafında kalmış bir etiket, malzeme değil.
        Ingredient::query()->create(['name' => 'Ana Malzeme', 'slug' => 'ana-malzeme']);

        $this->get(route('ingredients.hub.show', 'ana-malzeme'))->assertNotFound();
    }

    private function malzemeIle(int $tarifSayisi, string $ad = 'Domates'): Ingredient
    {
        $malzeme = Ingredient::query()->create([
            'name' => $ad,
            'slug' => Str::slug($ad).'-'.uniqid(),
        ]);

        for ($i = 0; $i < $tarifSayisi; $i++) {
            $tarif = Recipe::query()->create([
                'title' => $ad.' Tarifi '.$i,
                'slug' => Str::slug($ad).'-tarif-'.$i.'-'.uniqid(),
                'excerpt' => 'Özet.',
                'description' => 'Açıklama.',
                'status' => 'published',
                'verification_status' => 'verified',
                'published_at' => now(),
            ]);

            $tarif->recipeIngredients()->create([
                'ingredient_id' => $malzeme->id,
                'display_quantity' => 1,
                'display_measure_code' => 'g',
                'sort_order' => 1,
            ]);
        }

        return $malzeme;
    }
}
