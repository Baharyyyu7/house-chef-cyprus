<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Contracts\Search\RecipeSearchInterface;
use App\Models\Cuisine;
use App\Models\Recipe;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use Tests\TestCase;

final class StructuredSearchQueriesFindRecipesTest extends TestCase
{
    use RefreshDatabase;

    public function test_searching_a_cuisine_by_name_finds_its_recipes(): void
    {
        // Sorgu doğru biçimde cuisine'e çözülüyordu ama arama ayrıca ham
        // metnin başlıkta geçmesini şart koşuyordu. Hiçbir tarifin başlığında
        // "kıbrıs mutfağı" ifadesi yok, o yüzden 21 arama sıfır sonuç aldı -
        // oysa sitede o adla 22 tarifli bir mutfak var.
        $mutfak = Cuisine::query()->create(['name' => 'Kıbrıs Mutfağı', 'slug' => 'kibris-mutfagi']);
        $this->tarif('Hellimli Gözleme', ['cuisine_id' => $mutfak->id]);

        $this->assertSame(1, $this->ara('kıbrıs mutfağı'));
    }

    public function test_searching_for_easy_recipes_finds_them(): void
    {
        $this->tarif('Basit Bir Yemek', ['difficulty' => 'easy']);
        $this->tarif('Zor Bir Yemek', ['difficulty' => 'hard']);

        $this->assertSame(1, $this->ara('kolay tarifler'));
    }

    public function test_searching_by_duration_finds_quick_recipes(): void
    {
        $this->tarif('Hızlı Yemek', ['prep_minutes' => 5, 'cook_minutes' => 10]);
        $this->tarif('Uzun Yemek', ['prep_minutes' => 30, 'cook_minutes' => 90]);

        $this->assertSame(1, $this->ara('20 dakikada'));
    }

    public function test_a_meaningless_query_still_returns_nothing(): void
    {
        // Daralma aramayı her şeyi döndüren bir şeye çevirmemeli; sıfır
        // sonuçlu arama kaydı da bu sayede anlamlı kalıyor.
        $this->tarif('Herhangi Bir Tarif');

        $this->assertSame(0, $this->ara('asdfqwer'));
    }

    public function test_an_ingredient_query_still_matches_by_ingredient(): void
    {
        // Metin/malzeme yolu korunmalı: başlıkta geçmeyen ama malzemesi
        // eşleşen tarif bulunmaya devam etmeli.
        $mercimekli = $this->tarif('Anavatan Usulü Sıcak Kase');
        $malzeme = \App\Models\Ingredient::query()->create(['name' => 'Mercimek', 'slug' => 'mercimek-'.uniqid()]);
        $mercimekli->recipeIngredients()->create([
            'ingredient_id' => $malzeme->id,
            'display_quantity' => 1,
            'display_measure_code' => 'g',
            'sort_order' => 1,
        ]);

        $this->tarif('Alakasız Tarif');

        $this->assertSame(1, $this->ara('mercimek'));
    }

    private function ara(string $sorgu): int
    {
        return app(RecipeSearchInterface::class)->search(['q' => $sorgu])->total();
    }

    /** @param array<string, mixed> $alanlar */
    private function tarif(string $baslik, array $alanlar = []): Recipe
    {
        return Recipe::query()->create($alanlar + [
            'title' => $baslik,
            'slug' => Str::slug($baslik).'-'.uniqid(),
            'excerpt' => 'Özet.',
            'description' => 'Açıklama.',
            'servings' => 4,
            'status' => 'published',
            'verification_status' => 'verified',
            'published_at' => now(),
        ]);
    }
}
