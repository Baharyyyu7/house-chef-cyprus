<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Cuisine;
use App\Models\Recipe;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use Tests\TestCase;

/**
 * Koleksiyon sayfaları çıkmaz sokaktı.
 *
 * `/mutfaklar/kibris-mutfagi` sayfasında başka hiçbir mutfağa bağlantı
 * yoktu; ziyaretçi yalnız yol çizgisinden geri dönebiliyordu. Aynısı
 * kategori ve süre/zorluk sayfalarında da geçerliydi - ölçüldü, üçünde de
 * kardeş bağlantı sayısı sıfırdı.
 *
 * Bu hem ziyaretçi için çıkmaz sokak hem de tarama grafiğinde yatay bağın
 * hiç kurulmaması demek.
 */
final class CollectionPagesAreNotDeadEndsTest extends TestCase
{
    use RefreshDatabase;

    public function test_a_cuisine_page_links_to_its_siblings(): void
    {
        $kibris = $this->mutfak('Kıbrıs Mutfağı', 'kibris-mutfagi');
        $turk = $this->mutfak('Türk Mutfağı', 'turk-mutfagi');

        $this->get(route('cuisines.show', $kibris->slug))
            ->assertOk()
            ->assertSee(route('cuisines.show', $turk->slug), false);
    }

    public function test_a_page_does_not_link_to_itself(): void
    {
        // Kendine bağlantı, listeyi gürültüye çevirir.
        $kibris = $this->mutfak('Kıbrıs Mutfağı', 'kibris-mutfagi');
        $this->mutfak('Türk Mutfağı', 'turk-mutfagi');

        $icerik = (string) $this->get(route('cuisines.show', $kibris->slug))->assertOk()->getContent();
        $bolum = substr($icerik, (int) strpos($icerik, 'hc-kardes'));

        $this->assertStringNotContainsString(route('cuisines.show', $kibris->slug), $bolum);
    }

    public function test_a_thin_sibling_is_not_offered(): void
    {
        // Eşiği geçmeyen mutfağın sayfası 404; oraya bağlanmak ziyaretçiyi
        // hataya götürürdü.
        $kibris = $this->mutfak('Kıbrıs Mutfağı', 'kibris-mutfagi');
        $ince = Cuisine::query()->create(['name' => 'Yunan Mutfağı', 'slug' => 'yunan-mutfagi']);
        $this->tarif('Musakka', ['cuisine_id' => $ince->id]);

        $this->get(route('cuisines.show', $kibris->slug))
            ->assertOk()
            ->assertDontSee(route('cuisines.show', $ince->slug), false);
    }

    public function test_the_only_collection_shows_no_sibling_strip(): void
    {
        // Tek koleksiyon varsa boş bir başlık basmak anlamsız.
        $kibris = $this->mutfak('Kıbrıs Mutfağı', 'kibris-mutfagi');

        $this->get(route('cuisines.show', $kibris->slug))
            ->assertOk()
            ->assertDontSee('Diğer mutfaklar', false);
    }

    public function test_a_category_page_links_to_its_siblings(): void
    {
        $corba = $this->kategori('Çorba', 'corba');
        $tatli = $this->kategori('Tatlı', 'tatli');

        $this->get(route('categories.show', $corba->slug))
            ->assertOk()
            ->assertSee(route('categories.show', $tatli->slug), false);
    }

    private function mutfak(string $ad, string $slug): Cuisine
    {
        $mutfak = Cuisine::query()->create(['name' => $ad, 'slug' => $slug]);
        for ($i = 0; $i < 4; $i++) {
            $this->tarif($ad.' Tarifi '.$i, ['cuisine_id' => $mutfak->id]);
        }

        return $mutfak;
    }

    private function kategori(string $ad, string $slug): Category
    {
        $kategori = Category::query()->create(['name' => $ad, 'slug' => $slug]);
        for ($i = 0; $i < 4; $i++) {
            $this->tarif($ad.' Tarifi '.$i, ['category_id' => $kategori->id]);
        }

        return $kategori;
    }

    /** @param array<string, mixed> $alanlar */
    private function tarif(string $baslik, array $alanlar = []): Recipe
    {
        return Recipe::query()->create($alanlar + [
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
