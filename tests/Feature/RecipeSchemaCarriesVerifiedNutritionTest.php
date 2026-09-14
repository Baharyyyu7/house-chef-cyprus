<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Models\Recipe;
use App\Models\RecipeNutritionSnapshot;
use App\Services\Seo\SeoSchemaService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Tarif şeması doğrulanmış beslenme bilgisini taşır, eksik olanı taşımaz.
 *
 * 236 geçerli beslenme kaydı vardı ama şema hiçbirini okumuyordu; Google tarif
 * sonucunda kalori gösteremiyordu. Kayıtların bir kısmı ise eksik malzemeyle
 * hesaplanmıştı (tamlık %0-20). Onları basmak yanlış kalori demekti.
 */
final class RecipeSchemaCarriesVerifiedNutritionTest extends TestCase
{
    use RefreshDatabase;

    private function tarif(): Recipe
    {
        return Recipe::query()->create([
            'title' => 'Mercimek Çorbası',
            'slug' => 'mercimek-corbasi-'.uniqid(),
            'excerpt' => 'Özet.',
            'description' => 'Açıklama.',
            'status' => 'published',
            'verification_status' => 'verified',
            'published_at' => now(),
            'servings' => 4,
            'prep_minutes' => 10,
            'cook_minutes' => 30,
        ]);
    }

    private function kayit(Recipe $tarif, float $tamlik, float $guven): void
    {
        RecipeNutritionSnapshot::query()->forceCreate([
            'recipe_id' => $tarif->id,
            'servings' => 4,
            'payload' => [
                'nutrients' => [
                    'energy_kcal' => ['unit' => 'kcal', 'amount' => '990', 'per_serving' => '247.5'],
                    'protein' => ['unit' => 'g', 'amount' => '52', 'per_serving' => '13'],
                    'fat' => ['unit' => 'g', 'amount' => '24', 'per_serving' => '6'],
                    'sodium' => ['unit' => 'mg', 'amount' => '2400', 'per_serving' => '600'],
                ],
            ],
            'completeness_percentage' => $tamlik,
            'confidence_score' => $guven,
            'source_version' => 'test',
            'calculated_at' => now(),
        ]);
    }

    /** @return array<string, mixed> */
    private function dugum(Recipe $tarif): array
    {
        $tarif = $tarif->fresh(['steps', 'cuisine', 'category']);

        return app(SeoSchemaService::class)->recipe($tarif, ['200 g mercimek'], 'https://example.com/corba.jpg')['@graph'][0];
    }

    public function test_tam_ve_guvenilir_kayit_porsiyon_basi_basilir(): void
    {
        $tarif = $this->tarif();
        $this->kayit($tarif, 100, 90);

        $beslenme = $this->dugum($tarif)['nutrition'] ?? null;

        $this->assertIsArray($beslenme);
        $this->assertSame('NutritionInformation', $beslenme['@type']);
        $this->assertSame('247.5 kcal', $beslenme['calories'], 'Toplam değil porsiyon başı değer basılmalı.');
        $this->assertSame('13 g', $beslenme['proteinContent']);
        $this->assertSame('600 mg', $beslenme['sodiumContent']);
        $this->assertArrayNotHasKey('fiberContent', $beslenme, 'Kayıtta olmayan besin uydurulmamalı.');
    }

    public function test_eksik_hesaplanmis_kayit_basilmaz(): void
    {
        $tarif = $this->tarif();
        $this->kayit($tarif, 20, 90);

        $this->assertArrayNotHasKey('nutrition', $this->dugum($tarif));
    }

    public function test_guveni_dusuk_kayit_basilmaz(): void
    {
        $tarif = $this->tarif();
        $this->kayit($tarif, 100, 40);

        $this->assertArrayNotHasKey('nutrition', $this->dugum($tarif));
    }

    public function test_kaydi_olmayan_tarifte_alan_hic_yok(): void
    {
        $this->assertArrayNotHasKey('nutrition', $this->dugum($this->tarif()));
    }
}
