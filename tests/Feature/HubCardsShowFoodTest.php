<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Cuisine;
use App\Models\Recipe;
use App\Services\Media\HubCoverPicker;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Tests\TestCase;

/**
 * Hub kartları yemeği göstersin.
 *
 * Mutfak, kategori ve malzeme sayfalarının tamamı metin kartlarından
 * oluşuyordu: bir yemek sitesinde, ürünün kendisi olan fotoğraf hiçbir
 * koleksiyon sayfasında görünmüyordu. Yeni görsel üretilmiyor, var olan
 * onaylılardan seçiliyor.
 *
 * Testler gerçek kamusal medya kapısından geçiyor: kartta gösterilen
 * görsel, tarif sayfasında gösterilebilecek görselle aynı kurala tabi
 * olmalı ve bunu sahte bir kapıyla doğrulamak, asıl güvenceyi test dışı
 * bırakırdı.
 */
final class HubCardsShowFoodTest extends TestCase
{
    use RefreshDatabase;

    private ?\App\Models\User $yonetici = null;

    protected function setUp(): void
    {
        parent::setUp();
        Storage::fake('public');
    }

    public function test_a_cuisine_gets_a_cover_from_its_own_recipes(): void
    {
        $mutfak = Cuisine::query()->create(['name' => 'Kıbrıs Mutfağı', 'slug' => 'kibris-mutfagi']);
        $this->gorselliTarif('Hellimli Gözleme', ['cuisine_id' => $mutfak->id]);

        $this->assertArrayHasKey($mutfak->id, app(HubCoverPicker::class)->sutunaGore('cuisine_id', [$mutfak->id]));
    }

    public function test_a_cuisine_without_media_gets_no_cover(): void
    {
        // Yer tutucu basmak, boş bırakmaktan kötü: kart metin hâlinde kalır.
        $mutfak = Cuisine::query()->create(['name' => 'Yunan Mutfağı', 'slug' => 'yunan-mutfagi']);
        $this->tarif('Musakka', ['cuisine_id' => $mutfak->id]);

        $this->assertSame([], app(HubCoverPicker::class)->sutunaGore('cuisine_id', [$mutfak->id]));
    }

    public function test_media_the_public_gate_rejects_is_not_used(): void
    {
        // Asıl güvence: kartta gösterilen görsel, tarif sayfasında
        // gösterilebilecek görselle aynı kurala tabi.
        $mutfak = Cuisine::query()->create(['name' => 'Kıbrıs Mutfağı', 'slug' => 'kibris-kapisiz']);
        $tarif = $this->tarif('Kapıdan Geçmeyen', ['cuisine_id' => $mutfak->id]);

        // Doğrulanmamış görsel: kapı reddeder, kart metin hâlinde kalmalı.
        $tarif->media()->create([
            'kind' => 'recipe_synthetic', 'disk' => 'public', 'path' => 'media/reddedilen/source.jpg',
            'mime_type' => 'image/jpeg', 'visibility' => 'public',
            'media_status' => 'approved_real_media', 'verification_status' => 'pending',
        ]);

        $this->assertSame([], app(HubCoverPicker::class)->sutunaGore('cuisine_id', [$mutfak->id]));
    }

    public function test_a_draft_recipe_does_not_supply_a_cover(): void
    {
        $mutfak = Cuisine::query()->create(['name' => 'Taslak Mutfağı', 'slug' => 'taslak-mutfagi']);
        $this->gorselliTarif('Taslak Tarif', ['cuisine_id' => $mutfak->id, 'status' => 'draft']);

        $this->assertSame([], app(HubCoverPicker::class)->sutunaGore('cuisine_id', [$mutfak->id]));
    }

    public function test_each_owner_gets_its_own_cover(): void
    {
        $bir = Category::query()->create(['name' => 'Çorba', 'slug' => 'corba-k']);
        $iki = Category::query()->create(['name' => 'Tatlı', 'slug' => 'tatli-k']);
        $this->gorselliTarif('Mercimek Çorbası', ['category_id' => $bir->id]);
        $this->gorselliTarif('Revani', ['category_id' => $iki->id]);

        $kapaklar = app(HubCoverPicker::class)->sutunaGore('category_id', [$bir->id, $iki->id]);

        $this->assertCount(2, $kapaklar);
        $this->assertNotSame($kapaklar[$bir->id]->id, $kapaklar[$iki->id]->id);
    }

    public function test_one_cover_per_owner_even_with_many_recipes(): void
    {
        $mutfak = Cuisine::query()->create(['name' => 'Çok Tarifli', 'slug' => 'cok-tarifli']);
        for ($i = 0; $i < 5; $i++) {
            $this->gorselliTarif('Tarif '.$i, ['cuisine_id' => $mutfak->id]);
        }

        $this->assertCount(1, app(HubCoverPicker::class)->sutunaGore('cuisine_id', [$mutfak->id]));
    }

    public function test_no_owners_means_no_queries_and_no_covers(): void
    {
        $this->assertSame([], app(HubCoverPicker::class)->sutunaGore('cuisine_id', []));
        $this->assertSame([], app(HubCoverPicker::class)->malzemeyeGore([]));
    }

    /** @param array<string, mixed> $alanlar */
    public function test_the_home_page_cuisine_cards_use_the_same_card_as_the_hub(): void
    {
        // Ayni icerik iki sayfada farkli goruniyordu: /mutfaklar kartlari
        // yemek fotografi tasirken anasayfadaki secki tamamen metindi.
        $mutfak = Cuisine::query()->create(['name' => 'Kıbrıs Mutfağı', 'slug' => 'kibris-mutfagi']);
        // Kullanıcı menüsünde bir mutfak ancak 10 görünür tarife ulaştığında
        // açılır; kart sözleşmesi de aynı gerçek eşikte sınanır.
        for ($i = 0; $i < 10; $i++) {
            $this->gorselliTarif('Kıbrıs Tarifi '.$i, ['cuisine_id' => $mutfak->id]);
        }

        $this->get(route('home'))
            ->assertOk()
            ->assertSee('hc-hub-card__gorsel', false)
            ->assertDontSee('hc-card-hover group flex items-center justify-between gap-4 rounded-3xl border p-5', false);
    }

    private function gorselliTarif(string $baslik, array $alanlar = []): Recipe
    {
        $tarif = $this->tarif($baslik, $alanlar);
        $kok = 'media/'.uniqid();
        Storage::disk('public')->put($kok.'/source.jpg', 'jpeg');

        // Varyantlar profil adiyla anahtarlanmis harita degil, her ogesinde
        // `name` alani olan liste; kapi bu bicimi okuyor ve dosyalarin
        // diskte gercekten bulunmasini istiyor.
        $varyantlar = [];
        foreach (['thumbnail', 'square', 'card', 'open_graph', 'hero'] as $profil) {
            Storage::disk('public')->put($kok.'/'.$profil.'.jpg', 'jpeg');
            Storage::disk('public')->put($kok.'/'.$profil.'.webp', 'webp');
            $varyantlar[] = ['name' => $profil, 'jpeg' => $kok.'/'.$profil.'.jpg', 'webp' => $kok.'/'.$profil.'.webp'];
        }

        $yonetici = $this->yonetici ??= \App\Models\User::factory()->create(['role' => 'admin']);

        $tarif->media()->create([
            'kind' => 'recipe_synthetic', 'disk' => 'public',
            'path' => $kok.'/source.jpg',
            'mime_type' => 'image/jpeg', 'visibility' => 'public',
            'media_status' => 'approved_real_media', 'verification_status' => 'verified',
            'fidelity_status' => 'strong_match', 'fidelity_confidence' => 100,
            'license_review_status' => 'approved', 'attribution_review_status' => 'approved',
            'is_placeholder' => false, 'responsive_variants' => $varyantlar,
            'media_reviewer_user_id' => $yonetici->id, 'media_reviewed_at' => now(),
            'approved_by_user_id' => $yonetici->id, 'approved_at' => now(),
        ]);

        return $tarif;
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
