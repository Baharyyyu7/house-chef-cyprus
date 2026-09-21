<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Models\Recipe;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

final class RecipeMetaDescriptionPrefersFullTextTest extends TestCase
{
    use RefreshDatabase;

    private const DOLGUN = 'Pirinç soğanla zeytinyağında yumuşatılır, haşlanmış nohutla birlikte pişer. Ocak kapandıktan sonra kapalı tencerede dinlenmek taneleri birbirinden ayırır. Sıcakken limon sıkılarak servis edilir.';

    public function test_the_meta_description_uses_the_full_description_not_the_teaser(): void
    {
        // Sayfa `excerpt ?: description` kullanıyordu. Özet bir cümlelik
        // teaser ("Yoğurtlu acı meze." - 18 karakter), açıklama ise asıl
        // metin. Google snippet'i ~155 karakter gösteriyor; teaser kazanınca
        // arama sonucundaki alanın neredeyse tamamı boşa gidiyordu.
        // Denetim (SeoAuditService) zaten ters sırayı kullanıyordu, yani iki
        // taraf birbiriyle çelişiyordu.
        $tarif = $this->tarif(['excerpt' => 'Kısa teaser.', 'description' => self::DOLGUN]);
        $this->makeRecipePubliclyEligible($tarif);

        $icerik = (string) $this->get(route('recipes.show', $tarif))->assertOk()->getContent();

        $this->assertStringContainsString('Pirinç soğanla zeytinyağında', $icerik);
        $this->assertStringNotContainsString('name="description" content="Kısa teaser."', $icerik);
    }

    public function test_the_teaser_is_still_used_when_there_is_no_description(): void
    {
        // Daralma değil, sıralama düzeltmesi: açıklaması olmayan kayıt
        // meta açıklamasız kalmamalı.
        $tarif = $this->tarif(['excerpt' => 'Yalnızca özet var.', 'description' => null]);

        // Meta açıklaması artık porsiyon ve süre gibi tıklanmayı artıran
        // bir ön ek taşıyor; sabitlenen şey özetin yine de kullanıldığıdır,
        // metnin birebir tamamı değil.
        $icerik = (string) $this->get(route('recipes.show', $tarif))->assertOk()->getContent();

        $this->assertMatchesRegularExpression('/name="description" content="[^"]*Yalnızca özet var\./', $icerik);
    }

    public function test_the_recipe_schema_carries_the_full_description_too(): void
    {
        // JSON-LD ile sayfanın meta etiketi ayrışırsa Google iki farklı
        // açıklama görür; ikisi de aynı kaynaktan gelmeli.
        $tarif = $this->tarif(['excerpt' => 'Kısa teaser.', 'description' => self::DOLGUN]);
        $this->makeRecipePubliclyEligible($tarif);

        $icerik = (string) $this->get(route('recipes.show', $tarif))->assertOk()->getContent();

        $this->assertStringContainsString('"description":"Pirin', $icerik);
    }

    /** @param array<string, mixed> $alanlar */
    private function tarif(array $alanlar): Recipe
    {
        return Recipe::query()->create($alanlar + [
            'title' => 'Zeytinyağlı Nohutlu Pilav',
            'slug' => 'nohutlu-pilav-'.uniqid(),
            'status' => 'published',
            'verification_status' => 'verified',
            'published_at' => now(),
        ]);
    }
}
