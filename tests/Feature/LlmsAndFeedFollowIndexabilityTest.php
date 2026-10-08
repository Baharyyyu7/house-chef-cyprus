<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Models\Recipe;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * llms.txt ve RSS site haritasının indekslenebilirlik süzgecini uygular.
 *
 * 8 Ekim 2026 denetimi: ikisi de noindex bir nüsha tarifi ve iki 404
 * adresi yayıyordu; /en/feed Türkçe adres veriyordu. llms önbelleği bir gün
 * yaşıyordu ve hiçbir yayın/güncelleme onu temizlemiyordu.
 */
final class LlmsAndFeedFollowIndexabilityTest extends TestCase
{
    use RefreshDatabase;

    private function tarif(string $slug, array $diller = []): Recipe
    {
        $tarif = Recipe::query()->create([
            'title' => 'Tarif '.$slug, 'slug' => $slug, 'excerpt' => 'Özet.', 'description' => 'Açıklama metni.',
            'status' => 'published', 'verification_status' => 'verified', 'published_at' => now()->subDay(),
            'servings' => 2, 'prep_minutes' => 5, 'cook_minutes' => 10,
        ]);

        foreach ($diller as $dil) {
            foreach (['title' => 'Recipe '.$slug, 'excerpt' => 'Summary.', 'description' => 'Description text.'] as $alan => $deger) {
                $tarif->translations()->create(['locale' => $dil, 'field' => $alan, 'value' => $deger]);
            }
        }

        return $tarif;
    }

    public function test_noindex_tarif_llms_ve_beslemede_yer_almaz(): void
    {
        $this->tarif('acik-tarif');
        $this->tarif('nusha-tarif')->seoMetadata()->create(['robots' => 'noindex,follow']);

        foreach (['/llms.txt', '/llms-full.txt', '/feed'] as $adres) {
            $icerik = (string) $this->get($adres)->assertOk()->getContent();
            $this->assertStringContainsString('/tarifler/acik-tarif', $icerik, $adres);
            $this->assertStringNotContainsString('nusha-tarif', $icerik, $adres);
        }
    }

    public function test_ingilizce_besleme_ve_korpus_yalniz_ingilizce_adres_verir(): void
    {
        $this->tarif('cevrilmis', ['en']);
        $this->tarif('cevrilmemis');

        foreach (['/en/feed', '/en/llms.txt', '/en/llms-full.txt'] as $adres) {
            $icerik = (string) $this->get($adres)->assertOk()->getContent();
            $this->assertStringContainsString(url('/en/recipes/cevrilmis'), $icerik, $adres);
            $this->assertStringNotContainsString('cevrilmemis', $icerik, $adres);
            $this->assertStringNotContainsString('/tarifler/', $icerik, $adres);
        }

        $this->get('/en/recipes/cevrilmis')->assertOk();
    }

    public function test_tarif_noindexe_alininca_llms_onbellegi_temizlenir(): void
    {
        $tarif = $this->tarif('sonradan-kapanan');

        $this->assertStringContainsString('sonradan-kapanan', (string) $this->get('/llms.txt')->getContent());

        $tarif->seoMetadata()->create(['robots' => 'noindex,follow']);

        $this->assertStringNotContainsString('sonradan-kapanan', (string) $this->get('/llms.txt')->getContent());
    }
}
