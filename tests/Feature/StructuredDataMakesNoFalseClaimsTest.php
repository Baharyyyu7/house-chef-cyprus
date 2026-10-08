<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Models\Recipe;
use App\Services\Seo\SeoSchemaService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Yapısal veri yalnız doğru ve geçerli şey söyler.
 *
 * 8 Ekim 2026 denetimi: 345/345 tarifte Lefkoşa koordinatlı geo vardı,
 * schema.org'da olmayan "difficulty" alanı basılıyordu, Organization @id
 * dile göre değişiyordu, kalıp SSS "…Recipe recipe make?" gibi bozuk metin
 * üretiyordu, WebSite SearchAction (Google Kasım 2024'te kaldırdı) ve
 * speakable basılıyordu.
 */
final class StructuredDataMakesNoFalseClaimsTest extends TestCase
{
    use RefreshDatabase;

    public function test_kurulus_kimligi_her_dilde_ayni_ve_arama_eylemi_yok(): void
    {
        $kimlikler = [];

        foreach (['/', '/en', '/de'] as $adres) {
            $html = (string) $this->get($adres)->assertOk()->getContent();
            $this->assertStringNotContainsString('SearchAction', $html);
            $this->assertStringNotContainsString('SpeakableSpecification', $html);

            preg_match_all('#"@type":"Organization","@id":"([^"]+)"#', $html, $eslesme);
            $this->assertNotEmpty($eslesme[1], $adres);
            $kimlikler = array_merge($kimlikler, $eslesme[1]);
        }

        $this->assertSame([url('/').'#organization'], array_values(array_unique($kimlikler)));
    }

    public function test_tarife_ozgu_veri_yoksa_kalip_sss_basilmaz(): void
    {
        $tarif = Recipe::query()->create([
            'title' => 'Teriyaki Chicken Recipe', 'slug' => 'teriyaki', 'excerpt' => 'Özet.', 'description' => 'Açıklama.',
            'servings' => 4, 'prep_minutes' => 10, 'cook_minutes' => 20, 'difficulty' => 'easy',
            'status' => 'published', 'verification_status' => 'verified', 'published_at' => now(),
        ]);

        $servis = app(SeoSchemaService::class);

        foreach (['tr', 'en'] as $dil) {
            $this->assertSame([], $servis->generateRecipeFaqs($tarif, $dil), $dil);
        }

        $graf = $servis->recipe($tarif, ['1 kg tavuk'], 'https://example.test/a.jpg');
        $dugum = collect($graf['@graph'])->firstWhere('@type', 'Recipe');

        $this->assertArrayNotHasKey('geo', $dugum);
        $this->assertArrayNotHasKey('difficulty', $dugum);
        $this->assertArrayNotHasKey('speakable', $dugum);
        $this->assertNull(collect($graf['@graph'])->firstWhere('@type', 'FAQPage'));
        $this->assertStringNotContainsString('recipe make', json_encode($graf));
    }
}
