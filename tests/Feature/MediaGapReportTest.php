<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Models\Cuisine;
use App\Models\Ingredient;
use App\Models\Recipe;
use App\Services\Integrity\MediaGapReport;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use Tests\TestCase;

/**
 * Görsel üretimi bekleyen tariflerin öncelik sırası.
 *
 * Yayındaki tariflerin yarısına yakını onaylı görseli olmadığı için yer
 * tutucuyla gösteriliyor; bu ziyaretçinin gördüğü en büyük eksiklik.
 * Görsel üretimi başka ajanlara veriliyor, bu yüzden sıra onlara doğrudan
 * verilebilir olmalı ve tahmine değil sitenin kendi verisine dayanmalı.
 */
final class MediaGapReportTest extends TestCase
{
    use RefreshDatabase;

    public function test_a_recipe_with_approved_media_is_not_queued(): void
    {
        $tarif = $this->tarif('Görselli Tarif');
        $this->onayliGorsel($tarif);

        $rapor = $this->rapor();

        $this->assertSame(0, $rapor['missing_media']);
        $this->assertSame('complete', $rapor['status']);
    }

    public function test_a_recipe_without_media_is_queued(): void
    {
        $this->tarif('Görselsiz Tarif');

        $this->assertSame(1, $this->rapor()['missing_media']);
    }

    public function test_a_placeholder_only_recipe_still_counts_as_missing(): void
    {
        // Yer tutucu görsel "görseli var" saymamalı; ziyaretçi gri kare
        // görüyor.
        $tarif = $this->tarif('Yer Tutuculu Tarif');
        $tarif->media()->create([
            'kind' => 'recipe_synthetic',
            'disk' => 'public',
            'path' => 'fallbacks/recipe/ABC.svg',
            'mime_type' => 'image/svg+xml',
            'visibility' => 'public',
            'media_status' => 'pending_review',
        ]);

        $this->assertSame(1, $this->rapor()['missing_media']);
    }

    public function test_cypriot_recipes_outrank_others(): void
    {
        // Kıbrıs mutfağı bu sitenin ayırt edici tarafı ve o sorgularda
        // gerçek talep ölçüldü: "kıbrıs mutfağı" 21 kez arandı ve sıfır
        // sonuç döndü.
        $kibris = Cuisine::query()->create(['name' => 'Kıbrıs Mutfağı', 'slug' => 'kibris-mutfagi']);
        $this->tarif('Sıradan Tarif');
        $this->tarif('Hellimli Gözleme', ['cuisine_id' => $kibris->id]);

        $sira = collect($this->rapor()['queue'])->pluck('title');

        $this->assertSame('Hellimli Gözleme', $sira->first());
    }

    public function test_a_featured_recipe_comes_first(): void
    {
        // Öne çıkarılan tarif ana sayfada görünüyor; oradaki yer tutucu en
        // çok göze batan.
        $this->tarif('Sıradan Tarif');
        $this->tarif('Öne Çıkan Tarif', ['is_featured' => true]);

        $this->assertSame('Öne Çıkan Tarif', collect($this->rapor()['queue'])->pluck('title')->first());
    }

    public function test_the_reason_explains_the_ranking(): void
    {
        // Sıra başka bir ajana veriliyor; neden o sırada olduğu okunabilir
        // olmalı.
        $this->tarif('Öne Çıkan Tarif', ['is_featured' => true]);

        $this->assertStringContainsString('öne çıkarılmış', $this->rapor()['queue'][0]['reason']);
    }

    public function test_the_limit_is_respected(): void
    {
        for ($i = 0; $i < 5; $i++) {
            $this->tarif('Tarif '.$i);
        }

        $rapor = $this->rapor(2);

        $this->assertCount(2, $rapor['queue']);
        $this->assertSame(5, $rapor['missing_media'], 'Sınır listeyi kısaltır ama sayımı değiştirmemeli.');
    }

    public function test_draft_recipes_are_not_queued(): void
    {
        // Yayında olmayan tarif ziyaretçiye görünmüyor; görsel üretimi
        // önce yayındakiler için.
        $this->tarif('Taslak Tarif', ['status' => 'draft']);

        $this->assertSame(0, $this->rapor()['missing_media']);
    }

    public function test_a_finished_image_waiting_for_human_approval_is_counted(): void
    {
        // Bitmiş ama görünmeyen görsel, eksik görselle aynı şey değil:
        // birinin çözümü üretim, diğerininki onay. Üretimde 26 tamamlanmış
        // görsel insan onayı beklerken bu hiçbir yerde görünmüyordu.
        // Kurgu üretimdeki durumu birebir taklit ediyor: görsel içeri alınmış,
        // kimlik eşleşmesi doğrulanmış; eksik olan tek şey insan onayı.
        $tarif = $this->tarif('Onay Bekleyen');
        $tarif->media()->create([
            'kind' => 'recipe_synthetic',
            'disk' => 'public',
            'path' => 'media/approved-synthetic/recipe/ONAY/source.jpg',
            'mime_type' => 'image/jpeg',
            'visibility' => 'public',
            'media_status' => 'approved_real_media',
            'verification_status' => 'verified',
            'fidelity_status' => 'strong_match',
            'fidelity_confidence' => 100,
            'license_review_status' => 'approved',
            'attribution_review_status' => 'approved',
            'is_placeholder' => false,
        ]);

        $this->assertSame(1, $this->rapor()['awaiting_approval']);
    }

    public function test_a_recipe_with_no_media_is_not_counted_as_awaiting_approval(): void
    {
        // İki gösterge karışmamalı.
        $this->tarif('Görselsiz Tarif');

        $rapor = $this->rapor();

        $this->assertSame(1, $rapor['missing_media']);
        $this->assertSame(0, $rapor['awaiting_approval']);
    }

    /** @return array<string, mixed> */
    private function rapor(int $limit = 25): array
    {
        return app(MediaGapReport::class)->build($limit);
    }

    private function onayliGorsel(Recipe $tarif): void
    {
        $tarif->media()->create([
            'kind' => 'recipe_synthetic',
            'disk' => 'public',
            'path' => 'media/approved-synthetic/recipe/ABC/source.jpg',
            'mime_type' => 'image/jpeg',
            'visibility' => 'public',
            'media_status' => 'approved_real_media',
        ]);
    }

    /** @param array<string, mixed> $alanlar */
    private function tarif(string $baslik, array $alanlar = []): Recipe
    {
        $tarif = Recipe::query()->create($alanlar + [
            'title' => $baslik,
            'slug' => Str::slug($baslik).'-'.uniqid(),
            'excerpt' => 'Özet.',
            'description' => 'Açıklama.',
            'status' => 'published',
            'verification_status' => 'verified',
            'published_at' => now(),
        ]);

        $malzeme = Ingredient::query()->firstOrCreate(['name' => 'Un'], ['slug' => 'un-'.uniqid()]);
        $tarif->recipeIngredients()->create([
            'ingredient_id' => $malzeme->id,
            'display_quantity' => 1,
            'display_measure_code' => 'g',
            'sort_order' => 1,
        ]);

        return $tarif;
    }
}
