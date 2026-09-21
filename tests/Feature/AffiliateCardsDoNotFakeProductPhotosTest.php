<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Models\Recipe;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

/**
 * Stok fotoğraf ürünün kendi fotoğrafı değildir.
 *
 * 20 Eylül 2026'da ölçüldü: 35 gelir ortaklığı ürününün 35'i, başlığında
 * marka ve model yazarken yanında Unsplash'ten alınmış rastgele bir mutfak
 * fotoğrafı gösteriyordu. Okur o görseli ürünün fotoğrafı sanır. Ayrıca
 * kutu "Denenmiş ve Önerilen" diyordu; bu ürünler denenmedi.
 */
final class AffiliateCardsDoNotFakeProductPhotosTest extends TestCase
{
    use RefreshDatabase;

    private function urun(string $gorsel): void
    {
        DB::table('affiliate_links')->insert([
            'title' => 'Tefal Clipso Düdüklü Tencere 6L',
            'slug' => 'tefal-clipso-6l',
            'category' => 'equipment',
            'description' => 'Basınçlı pişirme için düdüklü tencere.',
            'image_url' => $gorsel,
            'target_url' => 'https://www.amazon.com.tr/s?k=tefal+duduklu+tencere',
            'provider' => 'amazon',
            'price' => 2450.00,
            'currency' => 'TRY',
            'is_active' => true,
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }

    private function govde(string $gorsel): string
    {
        $this->urun($gorsel);

        $urunler = DB::table('affiliate_links')->get();

        return (string) \Illuminate\Support\Facades\Blade::render(
            '<x-recipe-equipment-affiliate :products="$products" />',
            ['products' => $urunler]
        );
    }

    public function test_stok_fotograf_urun_gorseli_diye_basilmaz(): void
    {
        $icerik = $this->govde('https://images.unsplash.com/photo-1584992236310-6edddc08acff?w=400');

        $this->assertStringNotContainsString('images.unsplash.com/photo-1584992236310', $icerik);
    }

    public function test_gercek_urun_fotografi_basilir(): void
    {
        $icerik = $this->govde('https://m.media-amazon.com/images/I/71abc.jpg');

        $this->assertStringContainsString('m.media-amazon.com/images/I/71abc.jpg', $icerik);
    }

    public function test_denenmis_iddiasi_ve_dogrulanmamis_fiyat_gosterilmez(): void
    {
        $icerik = $this->govde('https://m.media-amazon.com/images/I/71abc.jpg');

        $this->assertStringNotContainsString('Denenmiş ve Önerilen', $icerik);
        $this->assertStringNotContainsString('2.450', $icerik);
        $this->assertStringContainsString('Gelir ortaklığı', $icerik);
    }
}
