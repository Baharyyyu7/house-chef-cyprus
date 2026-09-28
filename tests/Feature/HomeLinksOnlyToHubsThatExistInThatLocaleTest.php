<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Http\Controllers\CategoryHubController;
use App\Models\Category;
use App\Models\Recipe;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Ana sayfa yalnız o dilde var olan hub'a bağlantı verir.
 *
 * 27 Eylül 2026'da canlıda tarandı: /en/, /de/ ve /ru/ ana sayfaları
 * /en/kategoriler/corba ve /en/mutfaklar/akdeniz-mutfagi adreslerine
 * bağlantı veriyordu, ikisi de 404'tü. Hub yalnız o dile çevrilmiş en az
 * dört tarifle açılıyor; ana sayfa ise sabit bağlantı ve Türkçe sayım
 * kullanıyordu.
 */
final class HomeLinksOnlyToHubsThatExistInThatLocaleTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        config()->set('locales.supported.en.enabled', true);
    }

    private function corbalar(): void
    {
        $corba = Category::query()->create(['name' => 'Çorba', 'slug' => 'corba']);

        foreach (range(1, 4) as $i) {
            Recipe::query()->create([
                'title' => 'Çorba '.$i,
                'slug' => 'corba-'.$i,
                'excerpt' => 'Özet.',
                'description' => 'Açıklama.',
                'status' => 'published',
                'verification_status' => 'verified',
                'published_at' => now()->subDay(),
                'category_id' => $corba->id,
                'prep_minutes' => 10,
                'cook_minutes' => 10,
                'servings' => 2,
            ]);
        }
    }

    public function test_the_turkish_home_links_to_a_hub_with_enough_recipes(): void
    {
        $this->corbalar();

        $this->assertSame(['corba' => 4], CategoryHubController::ceviriSayilari());
        $this->get('/')->assertOk()->assertSee(route('categories.show', 'corba'), false);
    }

    public function test_a_foreign_home_does_not_link_to_a_hub_that_is_404_in_that_locale(): void
    {
        $this->corbalar();

        $icerik = (string) $this->get('/en/')->assertOk()->getContent();

        $this->assertStringNotContainsString('/en/kategoriler/corba', $icerik);
        $this->get('/en/kategoriler/corba')->assertNotFound();
    }
}
