<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Models\Ingredient;
use App\Models\Recipe;
use App\Services\HomeMatchShowcaseService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Ana sayfa hero'sundaki eşleştirme vitrini gerçek veri gösterir.
 *
 * İlk denemede vitrin sitenin en kötü verisini ön sayfaya taşıyacaktı:
 * pirinç, bulgur, makarna ve undan yapılmış bir "fasulye salatası" ile aynı
 * dört malzemeyi taşıyan bir "mantarlı risotto". Aynı zorunlu malzeme
 * kümesini paylaşan tarifler şablon üretiminden kalma ve vitrine girmez.
 *
 * Gösteri bir huni değil: motor bir dolap eşleştiricisi, malzeme ekledikçe
 * pişirilebilecek tarif artar. Yüzde tarifin kapsanma oranı, sayı o dolapla
 * eksiksiz pişirilebilecek tariflerin sayısı.
 */
final class HomeMatchShowcaseTest extends TestCase
{
    use RefreshDatabase;

    private function malzeme(string $ad): Ingredient
    {
        return Ingredient::query()->create(['name' => $ad, 'canonical_name' => $ad, 'slug' => str($ad)->slug()->toString(), 'is_active' => true]);
    }

    /** @param list<Ingredient> $malzemeler */
    private function tarif(string $baslik, array $malzemeler, int $dakika = 20): Recipe
    {
        $tarif = Recipe::query()->create([
            'title' => $baslik,
            'slug' => str($baslik)->slug()->toString(),
            'excerpt' => 'Özet.',
            'description' => 'Açıklama.',
            'status' => 'published',
            'verification_status' => 'verified',
            'published_at' => now()->subDay(),
            'prep_minutes' => $dakika,
            'cook_minutes' => 0,
            'servings' => 2,
        ]);

        foreach ($malzemeler as $sira => $m) {
            $tarif->recipeIngredients()->create([
                'ingredient_id' => $m->id,
                'quantity' => 1,
                'unit' => 'adet',
                'is_required' => true,
                'is_optional' => false,
                'sort_order' => $sira,
            ]);
        }

        return $tarif;
    }

    public function test_each_step_is_the_real_coverage_and_the_real_cookable_count(): void
    {
        [$hellim, $domates, $nane] = [$this->malzeme('Hellim'), $this->malzeme('Domates'), $this->malzeme('Nane')];
        $this->tarif('Hellimli Tava', [$hellim, $domates, $nane]);
        // Dolabın alt kümesiyle pişirilebilen ikinci bir tarif: son adımda
        // sayı 2 olmalı, çünkü iki tarifin de her zorunlu malzemesi dolapta.
        $this->tarif('Domatesli Hellim', [$hellim, $domates], 10);
        $this->tarif('Naneli Hellim Salatası', [$hellim, $nane, $domates, $this->malzeme('Limon')]);

        $senaryo = collect(app(HomeMatchShowcaseService::class)->senaryolar())
            ->firstWhere('recipe.title', 'Hellimli Tava');

        $this->assertNotNull($senaryo);
        $this->assertSame(['Hellim', 'Domates', 'Nane'], array_column($senaryo['steps'], 'ingredient'));
        $this->assertSame([33, 67, 100], array_column($senaryo['steps'], 'percent'));
        $this->assertSame([0, 1, 2], array_column($senaryo['steps'], 'can_cook'));
    }

    public function test_recipes_sharing_an_identical_ingredient_set_never_reach_the_showcase(): void
    {
        [$pirinc, $bulgur, $makarna] = [$this->malzeme('Pirinç'), $this->malzeme('Bulgur'), $this->malzeme('Makarna')];
        $this->tarif('Fasulye Salatası', [$pirinc, $bulgur, $makarna]);
        $this->tarif('Mantarlı Risotto', [$pirinc, $bulgur, $makarna]);

        $basliklar = array_column(array_column(app(HomeMatchShowcaseService::class)->senaryolar(), 'recipe'), 'title');

        $this->assertNotContains('Fasulye Salatası', $basliklar);
        $this->assertNotContains('Mantarlı Risotto', $basliklar);
    }

    public function test_a_recipe_without_a_cooking_time_is_not_shown(): void
    {
        $this->tarif('Süresiz Tarif', [$this->malzeme('Un'), $this->malzeme('Su'), $this->malzeme('Tuz')], 0);

        $this->assertSame([], app(HomeMatchShowcaseService::class)->senaryolar());
    }

    public function test_the_home_page_renders_the_showcase_only_when_there_is_a_clean_scenario(): void
    {
        $this->get(route('home'))->assertOk()->assertDontSee('hcMatchShowcase', false);

        $this->tarif('Hellimli Tava', [$this->malzeme('Hellim'), $this->malzeme('Domates'), $this->malzeme('Nane')]);
        cache()->flush();

        $this->get(route('home'))->assertOk()
            ->assertSee('hcMatchShowcase', false)
            ->assertSee('Hellimli Tava', false);
    }
}
