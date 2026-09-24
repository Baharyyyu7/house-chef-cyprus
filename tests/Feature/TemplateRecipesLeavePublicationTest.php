<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Models\Recipe;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Yayındaki 678 tarifin 463'ünün adı bir yemeği tarif etmiyor. Hepsi noindex,
 * yani Google göstermiyor -- ama siteyi gezen bir insan ve AdSense incelemesi
 * görüyor; "düşük değerli içerik" reddinin tipik deseni budur.
 *
 * Kayıt silinmez, taslağa çekilir. Testler asıl riski sabitler: gerçek bir
 * tarifin yanlışlıkla yayından kalkması.
 */
final class TemplateRecipesLeavePublicationTest extends TestCase
{
    use RefreshDatabase;

    public function test_a_template_recipe_leaves_publication(): void
    {
        $sablon = $this->publish('Akdeniz Mutfağı Usulü Limonlu', 'akdeniz-usulu-limonlu');

        $this->artisan('recipes:unpublish-templates', ['--apply' => true])->assertSuccessful();

        $this->assertSame('draft', $sablon->fresh()->status);
    }

    public function test_a_real_recipe_is_never_touched(): void
    {
        $gercek = $this->publish('Adana Kebabı', 'adana-kebabi');
        $tekKelime = $this->publish('Menemen', 'menemen');
        $humus = $this->publish('Humus', 'humus');

        $this->artisan('recipes:unpublish-templates', ['--apply' => true])->assertSuccessful();

        foreach ([$gercek, $tekKelime, $humus] as $recipe) {
            $this->assertSame('published', $recipe->fresh()->status, $recipe->title.' yayından kalkmamalıydı.');
        }
    }

    public function test_a_title_cut_off_at_an_adjective_leaves_publication(): void
    {
        $kesik = $this->publish('Peynirli', 'peynirli');

        $this->artisan('recipes:unpublish-templates', ['--apply' => true])->assertSuccessful();

        $this->assertSame('draft', $kesik->fresh()->status);
    }

    public function test_without_apply_nothing_changes(): void
    {
        $sablon = $this->publish('Türk Mutfağı Usulü Limonlu Bulgur', 'turk-usulu-limonlu-bulgur');

        $this->artisan('recipes:unpublish-templates')->assertSuccessful();

        $this->assertSame('published', $sablon->fresh()->status);
    }

    public function test_the_publication_date_survives_so_the_change_can_be_undone(): void
    {
        $sablon = $this->publish('Arap Mutfağı Usulü Limonlu', 'arap-usulu-limonlu');
        $tarih = $sablon->published_at;

        $this->artisan('recipes:unpublish-templates', ['--apply' => true])->assertSuccessful();

        $this->assertNotNull($sablon->fresh()->published_at);
        $this->assertTrue($tarih->equalTo($sablon->fresh()->published_at));
    }

    public function test_an_unpublished_recipe_no_longer_appears_in_the_sitemap(): void
    {
        $sablon = $this->publish('Yunan Mutfağı Usulü Limonlu Nohut', 'yunan-usulu-limonlu-nohut');
        $gercek = $this->publish('Mercimek Çorbası', 'mercimek-corbasi');

        $this->artisan('recipes:unpublish-templates', ['--apply' => true])->assertSuccessful();

        $this->get('/sitemaps/recipes.xml')
            ->assertOk()
            ->assertSee($gercek->slug, false)
            ->assertDontSee($sablon->slug, false);
    }

    private function publish(string $title, string $slug): Recipe
    {
        $recipe = Recipe::query()->create([
            'title' => $title,
            'slug' => $slug,
            'status' => 'published',
            'verification_status' => 'verified',
            'published_at' => now()->subDays(3),
        ]);
        $this->makeRecipePubliclyEligible($recipe);

        return $recipe;
    }
}
