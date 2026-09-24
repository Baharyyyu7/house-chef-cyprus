<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Models\DigitalProduct;
use App\Models\Ingredient;
use App\Models\Recipe;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

final class ShopSellsOnlyRealFilesTest extends TestCase
{
    use RefreshDatabase;

    public function test_a_product_without_a_file_is_not_shown_in_the_shop(): void
    {
        // Mağazada "120 sayfa" yazan, arkasında hiçbir dosya olmayan kayıtlar
        // duruyordu. Satın alma engelliydi ama vitrin satılabilir gösteriyordu.
        DigitalProduct::query()->create([
            'title' => 'Olmayan Kitap',
            'slug' => 'olmayan-kitap',
            'description' => 'Dosyası yok.',
            'price' => 99.00,
            'currency' => 'TRY',
            'page_count' => 120,
            'is_active' => true,
        ]);

        $this->get(route('shop.index'))
            ->assertOk()
            ->assertDontSee('Olmayan Kitap');
    }

    public function test_a_product_with_a_real_file_is_shown_with_the_turkish_lira_symbol(): void
    {
        Storage::disk('local')->put('products/gercek.pdf', '%PDF-1.7 test');

        DigitalProduct::query()->create([
            'title' => 'Gerçek Kitap',
            'slug' => 'gercek-kitap',
            'description' => 'Dosyası var.',
            'price' => 149.00,
            'currency' => 'TRY',
            'file_path' => 'products/gercek.pdf',
            'page_count' => 10,
            'is_active' => true,
        ]);

        $this->get(route('shop.index'))
            ->assertOk()
            ->assertSee('Gerçek Kitap')
            // Fiyat euro simgesiyle sabit basılıyordu; ürünün kendi para
            // birimi ne olursa olsun ekranda € görünüyordu.
            ->assertSee('₺149,00');
    }

    public function test_the_ebook_command_produces_a_real_pdf_and_a_sellable_product(): void
    {
        $this->seedPublishedRecipe();

        $this->artisan('shop:build-ebook', ['--limit' => 1, '--slug' => 'test-kitap'])
            ->assertExitCode(0);

        $urun = DigitalProduct::query()->where('slug', 'test-kitap')->sole();

        $this->assertSame('TRY', $urun->currency);
        $this->assertTrue(Storage::disk('local')->exists((string) $urun->file_path));
        // Dosya web kökünden değil, yalnız satın alma jetonuyla erişilebilir
        // olmalı: 'local' diski public değildir.
        $this->assertStringStartsWith('products/', (string) $urun->file_path);
        $this->assertStringStartsWith('%PDF-', (string) Storage::disk('local')->get((string) $urun->file_path));
    }

    public function test_the_ebook_command_refuses_when_there_is_nothing_to_publish(): void
    {
        // Boş bir kitap satmaktansa hiç üretmemek doğrudur.
        $this->artisan('shop:build-ebook', ['--limit' => 1, '--slug' => 'bos-kitap'])
            ->assertExitCode(1);

        $this->assertDatabaseMissing('digital_products', ['slug' => 'bos-kitap']);
    }

    private function seedPublishedRecipe(): void
    {
        $tarif = Recipe::query()->create([
            'title' => 'Fırında Hellim',
            'slug' => 'firinda-hellim-'.uniqid(),
            'status' => 'published',
            'verification_status' => 'verified',
            'servings' => 4,
            'published_at' => now(),
        ]);

        $malzeme = Ingredient::query()->create([
            'name' => 'Hellim',
            'slug' => 'hellim-'.uniqid(),
        ]);

        $tarif->recipeIngredients()->create([
            'ingredient_id' => $malzeme->id,
            'quantity' => 2,
            'unit' => 'adet',
            'sort_order' => 1,
        ]);

        $tarif->steps()->create([
            'position' => 1,
            'title' => 'Fırınlama',
            'instruction' => 'Hellimi dilimleyip fırında kızartın.',
        ]);
    }
}
