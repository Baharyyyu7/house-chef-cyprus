<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Contracts\Search\RecipeSearchInterface;
use App\Models\Recipe;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Kalite kapısının kapattığı tarif site içi aramada da çıkmaz.
 *
 * 20 Eylül 2026'da ölçüldü: "vegan" aramasının ilk sayfasındaki 20 sonucun
 * tamamı, malzemesi "sebze karışımı" olan şablon kayıtlardı. Arama motoruna
 * "bu sayfa gösterilmeye değmez" deyip aynı sayfayı ziyaretçiye ilk sonuç
 * olarak sunmak kendi ölçümümüzle çelişiyor.
 */
final class ClosedRecipesStayOutOfSiteSearchTest extends TestCase
{
    use RefreshDatabase;

    private function tarif(string $slug, ?string $robots): Recipe
    {
        $tarif = Recipe::query()->create([
            'title' => 'Vegan '.$slug,
            'slug' => $slug,
            'excerpt' => 'Vegan tarif özeti.',
            'description' => 'Bu tarifin açıklaması testte kullanılmak üzere yazıldı.',
            'status' => 'published',
            'verification_status' => 'verified',
            'published_at' => now()->subDay(),
            'servings' => 4,
            'prep_minutes' => 10,
            'cook_minutes' => 20,
        ]);

        if ($robots !== null) {
            $tarif->seoMetadata()->create(['robots' => $robots]);
        }

        $this->makeRecipePubliclyEligible($tarif);

        return $tarif->refresh();
    }

    public function test_noindex_tarif_arama_sonucunda_cikmaz(): void
    {
        $acik = $this->tarif('vegan-gercek-tarif', 'index,follow');
        $kapali = $this->tarif('vegan-sablon-tarif', 'noindex,follow');

        $sonuc = app(RecipeSearchInterface::class)->search(['q' => 'Vegan']);
        $slugler = $sonuc->pluck('slug')->all();

        $this->assertContains($acik->slug, $slugler);
        $this->assertNotContains($kapali->slug, $slugler);
    }

    public function test_metadatasiz_tarif_aramada_kalir(): void
    {
        // Metadata kaydı hiç olmayan tarif varsayılan olarak açıktır.
        $tarif = $this->tarif('vegan-metadatasiz', null);

        $this->assertContains($tarif->slug, app(RecipeSearchInterface::class)->search(['q' => 'Vegan'])->pluck('slug')->all());
    }
}
