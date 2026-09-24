<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Models\Recipe;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

final class RecipeDifficultyIsCanonicalTest extends TestCase
{
    use RefreshDatabase;

    public function test_both_spellings_are_stored_the_same_way(): void
    {
        // Alan iki dilde birden saklanıyordu: üretimde 176 yayındaki tarifin
        // 112'si "Kolay/Orta/Zor", 64'ü "easy/medium/hard".
        $turkce = $this->tarif('Kolay');
        $ingilizce = $this->tarif('easy');

        $this->assertSame('easy', $turkce->fresh()->difficulty);
        $this->assertSame('easy', $ingilizce->fresh()->difficulty);
    }

    public function test_the_public_filter_finds_recipes_saved_in_either_spelling(): void
    {
        // Asıl bedel buydu: herkese açık filtre Türkçe değerleri sorguluyor,
        // kişiselleştirme profili İngilizce değerleri. Kesişimleri boştu ve
        // katalogun %36'sı zorluk filtresiyle bulunamıyordu.
        $this->tarif('Kolay', 'Türkçe Kayıtlı Tarif');
        $this->tarif('easy', 'İngilizce Kayıtlı Tarif');

        $icerik = (string) $this->get(route('recipes.index', ['difficulty' => 'easy']))->assertOk()->getContent();

        $this->assertStringContainsString('Türkçe Kayıtlı Tarif', $icerik);
        $this->assertStringContainsString('İngilizce Kayıtlı Tarif', $icerik);
    }

    public function test_the_turkish_filter_value_still_works(): void
    {
        // Eski bağlantılar ve yer imleri "?difficulty=Kolay" taşıyor.
        $this->tarif('easy', 'Kolay Bir Tarif');

        $this->get(route('recipes.index', ['difficulty' => 'Kolay']))
            ->assertOk()
            ->assertSee('Kolay Bir Tarif');
    }

    public function test_the_recipe_page_shows_a_turkish_label(): void
    {
        // ucfirst($recipe->difficulty) kullanılıyordu; İngilizce kayıtlı 64
        // tarifin sayfasında Türkçe sitede "Easy" yazıyordu.
        $tarif = $this->tarif('easy');

        $this->get(route('recipes.show', $tarif))
            ->assertOk()
            ->assertSee('Kolay')
            ->assertDontSee('>Easy<', false);
    }

    public function test_the_recipe_listing_shows_turkish_labels_too(): void
    {
        // Depolama kanonik İngilizceye çevrilince kartlar ham değeri
        // basmaya devam etti: listede "Hard", "Easy", "Medium" görünüyordu.
        // Detay sayfası düzeltilmiş ama kart bileşeni atlanmıştı.
        $this->tarif('easy', 'Listede Kolay Görünen');

        $this->get(route('recipes.index'))
            ->assertOk()
            ->assertSee('Kolay')
            ->assertDontSee('>Easy<', false);
    }

    public function test_an_unknown_value_is_left_untouched(): void
    {
        // Tanımadığını sessizce silmek veriyi kaybettirirdi.
        $tarif = $this->tarif('bilinmeyen-seviye');

        $this->assertSame('bilinmeyen-seviye', $tarif->fresh()->difficulty);
        $this->assertNull($tarif->zorlukEtiketi());
    }

    public function test_the_backfill_command_normalises_existing_rows(): void
    {
        // Model artık yazarken normalleştiriyor ama geçmiş kayıtlar duruyor.
        $tarif = $this->tarif('easy');
        DB::table('recipes')->where('id', $tarif->id)->update(['difficulty' => 'Zor']);

        $this->artisan('recipes:normalize-difficulty --apply')->assertSuccessful();

        $this->assertSame('hard', $tarif->fresh()->difficulty);
    }

    public function test_the_backfill_is_a_dry_run_by_default(): void
    {
        $tarif = $this->tarif('easy');
        DB::table('recipes')->where('id', $tarif->id)->update(['difficulty' => 'Zor']);

        $this->artisan('recipes:normalize-difficulty')->assertSuccessful();

        $this->assertSame('Zor', DB::table('recipes')->where('id', $tarif->id)->value('difficulty'));
    }

    private function tarif(string $zorluk, string $baslik = 'Zorluk Tarifi'): Recipe
    {
        return Recipe::query()->create([
            'title' => $baslik,
            'slug' => \Illuminate\Support\Str::slug($baslik).'-'.uniqid(),
            'excerpt' => 'Özet.',
            'description' => 'Açıklama.',
            'difficulty' => $zorluk,
            'servings' => 4,
            'prep_minutes' => 10,
            'cook_minutes' => 20,
            'status' => 'published',
            'verification_status' => 'verified',
            'published_at' => now(),
        ]);
    }
}
