<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Models\Recipe;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

final class HeadingOutlineTest extends TestCase
{
    use RefreshDatabase;

    public function test_a_related_recipe_is_not_a_section_of_the_recipe_you_are_reading(): void
    {
        // Benzer tarif kartlarının başlığı h2'ydi: "Humus" sayfanın kendi
        // "Malzemeler" ve "Yapım adımları" başlıklarıyla aynı düzeyde
        // duruyordu. Başlıklarla gezinen biri onu bu tarifin bir bölümü
        // sanıyordu.
        $tarif = $this->tarif('cig-kofte');
        $this->tarif('humus');

        $icerik = (string) $this->get(route('recipes.show', $tarif))->assertOk()->getContent();

        $konum = mb_strpos($icerik, 'Benzer tarifler');

        $this->assertNotFalse($konum, 'Benzer tarifler bölümü render olmadı.');

        $kuyruk = mb_substr($icerik, $konum);

        // Kart gerçekten basıldı mı: aksi halde bu test boşluğa geçerdi.
        $this->assertStringContainsString('Humus', $kuyruk);
        $this->assertStringContainsString('<h3', $kuyruk);
        $this->assertStringNotContainsString('<h2', $kuyruk);
    }

    public function test_the_archive_keeps_its_recipe_names_at_h2(): void
    {
        // Arşivde kartlar h1'in hemen altında; orada h2 doğru. h3'e
        // indirmek başlık atlaması üretirdi.
        $this->tarif('cig-kofte');

        $this->get(route('recipes.index'))->assertOk()->assertSee('<h2', false);
    }

    public function test_a_single_ingredient_group_does_not_repeat_the_section_heading(): void
    {
        // Çiğ Köfte'de grup adı "Malzemeler"di ve bölümün kendi başlığını
        // tekrar ediyordu: anahatta iki kez "Malzemeler" görünüyordu.
        $tarif = $this->tarif('cig-kofte');

        $icerik = (string) $this->get(route('recipes.show', $tarif))->assertOk()->getContent();

        $this->assertSame(1, mb_substr_count($icerik, '>Malzemeler</h2>') + mb_substr_count($icerik, '>Malzemeler</h3>'));
    }

    private function tarif(string $slug): Recipe
    {
        return Recipe::query()->create([
            'title' => ucfirst($slug),
            'slug' => $slug,
            'excerpt' => 'Özet.',
            'description' => 'Açıklama.',
            'status' => 'published',
            'verification_status' => 'verified',
            'published_at' => now()->subDay(),
        ]);
    }
}
