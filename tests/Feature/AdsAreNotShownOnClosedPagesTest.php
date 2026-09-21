<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Models\Recipe;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Kendi kapattığımız sayfada reklam gösterilmez.
 *
 * 20 Eylül 2026'da ölçüldü: kalite kapısının kapattığı 868 tarif noindex'ti
 * ama hepsi halka açık ve reklam yuvalarıyla birlikte yayındaydı. AdSense
 * incelemesi arama indeksine değil sayfanın kendisine bakar; "aramada
 * gösterilmeye değmez" dediğimiz sayfada reklam yayınlamak kendi ölçümümüzle
 * çelişir ve düşük değerli içerik olarak okunur.
 */
final class AdsAreNotShownOnClosedPagesTest extends TestCase
{
    use RefreshDatabase;

    private function reklamiAc(): void
    {
        config([
            'integrations.google.adsense.enabled' => true,
            'integrations.google.adsense.client_id' => 'ca-pub-1614195335767159',
            'integrations.google.adsense.certified_cmp_enabled' => true,
            'integrations.google.adsense.cmp_provider' => 'google',
        ]);
    }

    private function tarif(string $slug, string $robots): Recipe
    {
        $tarif = Recipe::query()->create([
            'title' => 'Tarif '.$slug,
            'slug' => $slug,
            'excerpt' => 'Özet.',
            'description' => 'Bu tarifin açıklaması testte kullanılmak üzere yazıldı ve yeterlidir.',
            'status' => 'published',
            'verification_status' => 'verified',
            'published_at' => now()->subDay(),
            'servings' => 4,
            'prep_minutes' => 10,
            'cook_minutes' => 20,
        ]);

        $tarif->seoMetadata()->create(['robots' => $robots]);
        $this->makeRecipePubliclyEligible($tarif);

        return $tarif->refresh();
    }

    public function test_noindex_sayfada_reklam_basilmaz(): void
    {
        $this->reklamiAc();
        $kapali = $this->tarif('kapali-tarif', 'noindex,follow');

        $this->get(route('recipes.show', $kapali))->assertOk()->assertDontSee('adsbygoogle', false);
    }

    public function test_indekse_acik_sayfada_reklam_basilir(): void
    {
        $this->reklamiAc();
        $acik = $this->tarif('acik-tarif', 'index,follow');

        $this->get(route('recipes.show', $acik))->assertOk()->assertSee('adsbygoogle', false);
    }
}
