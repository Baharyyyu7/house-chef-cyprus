<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Models\Recipe;
use App\Services\DietaryCompatibilityService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

final class DietaryClaimsAreHonestTest extends TestCase
{
    use RefreshDatabase;

    public function test_missing_nutrition_data_is_not_reported_as_unsuitable(): void
    {
        // Beslenme verisi yokken data_get '0' döndürüyordu ve bu eşiğin
        // altında kalan gerçek bir ölçüm gibi değerlendiriliyordu: veri
        // taşımayan tarif "Yüksek protein: uygun değil" diye etiketleniyordu.
        // Veri yokluğundan olumsuz sonuç çıkarmak, bilinmediğini söylemekten
        // daha kötü; okuyan bunu ölçülmüş bir sonuç sanıyor.
        $sonuc = app(DietaryCompatibilityService::class)->assess($this->tarif());

        $this->assertSame('uncertain', $sonuc['high_protein']['status']);
        $this->assertSame('uncertain', $sonuc['low_calorie']['status']);
    }

    public function test_every_row_carries_its_turkish_label(): void
    {
        // Türkçe adlar dietary_tags tablosunda zaten duruyordu; şablon
        // onları yok sayıp ham kodu başlık formatına çeviriyordu ve Türkçe
        // bir sitede "Gluten Free", "High Protein" yazıyordu.
        $sonuc = app(DietaryCompatibilityService::class)->assess($this->tarif());

        foreach ($sonuc as $kod => $satir) {
            $this->assertArrayHasKey('label', $satir, $kod.' için etiket yok.');
            $this->assertNotSame('', trim((string) $satir['label']));
        }
    }

    public function test_the_page_does_not_print_english_dietary_labels(): void
    {
        $tarif = $this->tarif();

        $icerik = (string) $this->get(route('recipes.show', $tarif))->assertOk()->getContent();

        foreach (['Gluten Free', 'High Protein', 'Low Calorie', 'Halal Suitable', 'Dairy Free'] as $ingilizce) {
            $this->assertStringNotContainsString($ingilizce, $icerik);
        }
    }

    public function test_unknown_rows_are_summarised_instead_of_repeated(): void
    {
        // Dokuz satırın dokuzu da "belirsiz" diyordu ve bilgi taşıyan
        // satırları boğuyordu. Bilinmeyen gizlenmiyor, tek satırda toplanıyor.
        $tarif = $this->tarif();

        $icerik = (string) $this->get(route('recipes.show', $tarif))->assertOk()->getContent();

        $this->assertStringContainsString('doğrulanmış veri yok', $icerik);
        $this->assertSame(0, mb_substr_count($icerik, ': belirsiz'));
    }

    private function tarif(): Recipe
    {
        return Recipe::query()->create([
            'title' => 'Çiğ Köfte',
            'slug' => 'cig-kofte',
            'excerpt' => 'Özet.',
            'description' => 'Açıklama.',
            'status' => 'published',
            'verification_status' => 'verified',
            'published_at' => now()->subDay(),
        ]);
    }
}
