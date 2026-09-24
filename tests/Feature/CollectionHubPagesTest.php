<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Models\Recipe;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

final class CollectionHubPagesTest extends TestCase
{
    use RefreshDatabase;

    public function test_the_parent_page_of_a_working_child_page_is_not_a_dead_end(): void
    {
        // /sure/30-dakika yayındaydı ama /sure 404 dönüyordu. Adresi kısaltan
        // ziyaretçi ve yolu yukarı yürüyen tarayıcı duvara çarpıyordu; diğer
        // üç hub (/mutfaklar, /kategoriler, /malzemeler) index + show
        // çiftiyle duruyordu.
        $this->tarifler(6, 15, 'easy');

        $this->get('/sure')->assertOk()->assertSee('30 Dakikada Hazır Tarifler');
        $this->get('/zorluk')->assertOk()->assertSee('Kolay Tarifler');
    }

    public function test_the_parent_links_to_the_children(): void
    {
        $this->tarifler(6, 15, 'easy');

        $this->get('/sure')->assertSee(route('collections.sure', '30-dakika'), false);
    }

    public function test_an_empty_parent_is_not_published(): void
    {
        // Boş bir üst sayfa yayınlamak yeni bir boş sayfa üretmek olur; bu
        // site zaten ölçekli zayıf içerik yüzünden ceza yedi.
        $this->get('/sure')->assertNotFound();
        $this->get('/zorluk')->assertNotFound();
    }

    public function test_the_parent_declares_its_children_not_an_empty_list(): void
    {
        // Boş itemListElement yanıltıcı sinyaldir.
        $this->tarifler(6, 15, 'easy');

        $icerik = (string) $this->get('/sure')->assertOk()->getContent();

        $this->assertStringContainsString('"@type":"ItemList"', $icerik);
        $this->assertStringNotContainsString('"numberOfItems":0', $icerik);
    }

    public function test_the_parent_pages_are_in_the_sitemap(): void
    {
        $this->tarifler(6, 15, 'easy');

        $this->get(route('sitemap.pages'))
            ->assertOk()
            ->assertSee(route('collections.sure.index'), false)
            ->assertSee(route('collections.zorluk.index'), false);
    }

    public function test_an_unpublished_parent_stays_out_of_the_sitemap(): void
    {
        // Harita 404 donen bir adres bildirmemeli.
        $this->get(route('sitemap.pages'))
            ->assertOk()
            ->assertDontSee(route('collections.sure.index'), false);
    }

    private function tarifler(int $adet, int $dakika, string $zorluk): void
    {
        for ($i = 0; $i < $adet; $i++) {
            Recipe::query()->create([
                'title' => 'Hızlı Tarif '.$i,
                'slug' => 'hizli-tarif-'.$i,
                'excerpt' => 'Özet.',
                'description' => 'Açıklama.',
                'status' => 'published',
                'verification_status' => 'verified',
                'prep_minutes' => $dakika,
                'cook_minutes' => 0,
                'rest_minutes' => 0,
                'difficulty' => $zorluk,
                'published_at' => now()->subDay(),
            ]);
        }
    }
}
