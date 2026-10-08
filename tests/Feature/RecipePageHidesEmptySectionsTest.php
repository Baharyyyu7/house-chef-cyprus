<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Models\Recipe;
use App\Services\Seo\SeoSchemaService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use Tests\TestCase;

/**
 * 109 tarifte "Servis önerisi henüz eklenmedi" basılıyordu; saklama notu
 * yokken SSS "3-4 gün" diyordu; "Son kontrol::" çift iki nokta; yapay zekâ
 * görsel beyanı iki kez.
 */
final class RecipePageHidesEmptySectionsTest extends TestCase
{
    use RefreshDatabase;

    private function tarif(array $ek = []): Recipe
    {
        return Recipe::create(array_merge(['title' => 'Mercimek çorbası', 'slug' => 'mercimek-'.Str::lower(Str::random(5)), 'excerpt' => 'Özet.', 'servings' => 4, 'prep_minutes' => 5, 'cook_minutes' => 20, 'rest_minutes' => 0, 'difficulty' => 'Kolay', 'status' => 'published', 'verification_status' => 'verified', 'published_at' => now()->subDay()], $ek));
    }

    public function test_bos_servis_ve_saklama_bolumu_basilmaz(): void
    {
        $sayfa = $this->get(route('recipes.show', $this->tarif()))->assertOk();

        $sayfa->assertDontSee('henüz eklenmedi')
            ->assertDontSee('data-storage-notes', false)
            ->assertDontSee('href="#storage"', false)
            ->assertDontSee('Son kontrol::')
            ->assertSee('Son kontrol:');
    }

    public function test_saklama_notu_varsa_kart_ve_sss_birlikte_gelir(): void
    {
        $tarif = $this->tarif(['storage_notes' => ['buzdolabi_gun' => 3, 'uyari' => 'Kapaklı kapta saklayın.'], 'serving_suggestions' => 'Limonla servis edin.']);

        $this->get(route('recipes.show', $tarif))->assertOk()
            ->assertSee('data-storage-notes', false)
            ->assertSee('Buzdolabında 3 güne kadar.')
            ->assertSee('Kapaklı kapta saklayın.')
            ->assertSee('Limonla servis edin.');

        $sorular = collect(app(SeoSchemaService::class)->generateRecipeFaqs($tarif, 'tr'))->pluck('name')->implode(' | ');
        $this->assertStringContainsString('nasıl saklanır', $sorular);
    }

    public function test_saklama_notu_yoksa_sss_saklama_cevabi_uydurmaz(): void
    {
        $tarif = $this->tarif(['storage_notes' => ['uyari' => '']]);

        $sorular = collect(app(SeoSchemaService::class)->generateRecipeFaqs($tarif, 'tr'))->pluck('name')->implode(' | ');
        $this->assertStringNotContainsString('nasıl saklanır', $sorular);
    }
}
