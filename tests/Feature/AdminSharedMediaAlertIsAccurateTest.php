<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Models\Media;
use App\Models\Recipe;
use App\Services\AdminDashboardService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use Tests\TestCase;

/**
 * Yönetim panelindeki "aynı görsel birden fazla tarifte" uyarısı doğru
 * saymalı.
 *
 * Sorgu yalnız `path`'e göre gruplanıyordu, yani aynı tarifin tekrarlanan
 * medya kayıtları da "başka bir tarife ait fotoğraf" sayılıyordu. Üretimde
 * panel "19 tarif, başka bir tarife ait fotoğrafı gösteriyor" diyordu ama
 * dokuz grubun dokuzu da tek bir tarifin mükerrer kaydıydı: gerçekten
 * farklı tarifler arasında paylaşılan tek bir görsel yoktu.
 *
 * Panelin operatörü olmayan bir sorun için alarma geçirmesi, gerçek
 * uyarılara olan güveni de aşındırır.
 */
final class AdminSharedMediaAlertIsAccurateTest extends TestCase
{
    use RefreshDatabase;

    public function test_duplicate_rows_on_one_recipe_do_not_raise_the_cross_recipe_alert(): void
    {
        $tarif = $this->tarif('Avcı Kebabı');
        $this->medya($tarif, 'media/ortak/foto.jpg');
        $this->medya($tarif, 'media/ortak/foto.jpg');
        $this->medya($tarif, 'media/ortak/foto.jpg');

        $this->assertNull($this->uyari('Aynı görsel birden fazla tarifte'));
    }

    public function test_duplicate_rows_are_reported_as_housekeeping_instead(): void
    {
        // Sessizce yok saymak da doğru değil: fazladan kayıtlar temizlenmeli.
        $tarif = $this->tarif('Avcı Kebabı');
        $this->medya($tarif, 'media/ortak/foto.jpg');
        $this->medya($tarif, 'media/ortak/foto.jpg');

        $uyari = $this->uyari('Mükerrer medya kaydı');

        $this->assertNotNull($uyari);
        $this->assertSame('warning', $uyari['severity']);
    }

    public function test_a_photo_shared_across_recipes_still_raises_the_alert(): void
    {
        // Daralma gerçek sorunu kaçırmamalı: iki ayrı tarif aynı fotoğrafı
        // gösteriyorsa ziyaretçiye yanlış yemek gösteriliyor demektir.
        $this->medya($this->tarif('Mercimek Çorbası'), 'media/ortak/foto.jpg');
        $this->medya($this->tarif('Beyti'), 'media/ortak/foto.jpg');

        $uyari = $this->uyari('Aynı görsel birden fazla tarifte');

        $this->assertNotNull($uyari);
        $this->assertSame('danger', $uyari['severity']);
        $this->assertStringContainsString('2 tarif', $uyari['detail']);
    }

    /** @return array<string, mixed>|null */
    private function uyari(string $etiket): ?array
    {
        $panel = app(AdminDashboardService::class)->dashboard(7);

        foreach ((array) data_get($panel, 'operations.attention', data_get($panel, 'attention', [])) as $uyari) {
            if (($uyari['label'] ?? null) === $etiket) {
                return $uyari;
            }
        }

        return null;
    }

    private function medya(Recipe $tarif, string $yol): Media
    {
        return $tarif->media()->create([
            'kind' => 'recipe_synthetic',
            'disk' => 'public',
            'path' => $yol,
            'mime_type' => 'image/jpeg',
            'visibility' => 'public',
            'media_status' => 'approved_real_media',
        ]);
    }

    private function tarif(string $baslik): Recipe
    {
        return Recipe::query()->create([
            'title' => $baslik,
            'slug' => Str::slug($baslik).'-'.uniqid(),
            'excerpt' => 'Özet.',
            'description' => 'Açıklama.',
            'status' => 'published',
            'verification_status' => 'verified',
            'published_at' => now(),
        ]);
    }
}
