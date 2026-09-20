<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Models\Recipe;
use App\Services\Seo\SeoAuditService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Bilinçli kanonik birleştirme kusur sayılmaz.
 *
 * 20 Eylül 2026'da ölçüldü: 227 teknik uyarının 191'i, kopya tarifin gerçek
 * eşine bağlanmasıydı. Sinyal kaybolmuyordu, eşine taşınıyordu. Bu kadar
 * gürültü gerçek kusurları görünmez ediyor.
 */
final class CanonicalConsolidationIsNotAnIssueTest extends TestCase
{
    use RefreshDatabase;

    private function tarif(string $slug, string $durum = 'published'): Recipe
    {
        $tarif = Recipe::query()->create([
            'title' => 'Tarif '.$slug,
            'slug' => $slug,
            'excerpt' => 'Özet '.$slug,
            'description' => 'Bu tarifin açıklaması testte kullanılmak üzere yazıldı ve yeterli uzunluktadır.',
            'status' => $durum,
            'verification_status' => 'verified',
            'published_at' => $durum === 'published' ? now()->subDay() : null,
            'servings' => 4,
            'prep_minutes' => 10,
            'cook_minutes' => 20,
        ]);

        if ($durum === 'published') {
            $this->makeRecipePubliclyEligible($tarif);
        }

        return $tarif->refresh();
    }

    private function kanonikKoy(Recipe $tarif, string $adres): void
    {
        $tarif->seoMetadata()->updateOrCreate([], ['canonical_override' => $adres]);
    }

    /** @return list<string> */
    private function kanonikUyarilari(): array
    {
        $sonuc = app(SeoAuditService::class)->run();

        return array_values(array_map(
            static fn (array $k): string => (string) $k['message'],
            array_filter($sonuc['issues'], static fn (array $k): bool => str_contains((string) $k['message'], 'Canonical override'))
        ));
    }

    public function test_acik_bir_ese_baglanan_kopya_uyari_uretmez(): void
    {
        $gercek = $this->tarif('kibris-sef-tali-kebabi');
        $kopya = $this->tarif('kibris-sef-tali-kebabi-KEFH3');
        $this->kanonikKoy($kopya, route('recipes.show', $gercek));

        $this->assertSame([], $this->kanonikUyarilari());
    }

    public function test_kapali_bir_adresi_gosteren_kanonik_uyari_uretir(): void
    {
        $kapali = $this->tarif('yayinda-olmayan-es', 'draft');
        $kopya = $this->tarif('kopya-tarif');
        $this->kanonikKoy($kopya, route('recipes.show', $kapali));

        $this->assertStringContainsString('yayında değil', implode(' ', $this->kanonikUyarilari()));
    }

    public function test_karsiligi_olmayan_adres_uyari_uretir(): void
    {
        $kopya = $this->tarif('kopya-tarif');
        $this->kanonikKoy($kopya, rtrim((string) config('app.url'), '/').'/tarifler/hic-olmayan-tarif');

        $this->assertStringContainsString('kayıt yok', implode(' ', $this->kanonikUyarilari()));
    }

    public function test_site_disi_kanonik_uyari_uretir(): void
    {
        $kopya = $this->tarif('kopya-tarif');
        $this->kanonikKoy($kopya, 'https://baska-site.example/tarifler/kopya-tarif');

        $this->assertStringContainsString('site dışı', implode(' ', $this->kanonikUyarilari()));
    }
}
