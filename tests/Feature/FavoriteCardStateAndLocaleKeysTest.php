<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Models\Recipe;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\File;
use Tests\TestCase;

final class FavoriteCardStateAndLocaleKeysTest extends TestCase
{
    use RefreshDatabase;

    public function test_listing_card_shows_whether_the_recipe_is_already_a_favorite(): void
    {
        // 9 Ekim denetimi: kart her zaman "Favorilere ekle" gösteriyordu; favoride
        // olan tarifte tıklamak kaydı siliyordu.
        $uye = User::factory()->create(['email_verified_at' => now()]);
        $favori = $this->tarif('Favori kartı tarifi', 'favori-karti-tarifi');
        $diger = $this->tarif('Diğer kart tarifi', 'diger-kart-tarifi');
        $uye->favoriteRecipes()->attach($favori);

        $this->actingAs($uye)->get(route('recipes.index'))
            ->assertOk()
            ->assertSee('aria-pressed="true"', false)
            ->assertSee(__('card.remove_from_favorites_aria', ['title' => 'Favori kartı tarifi']), false)
            ->assertSee(__('card.add_to_favorites_aria', ['title' => 'Diğer kart tarifi']), false);
    }

    public function test_new_favorite_labels_exist_in_every_supported_locale(): void
    {
        // Eksik anahtar ekranda ham anahtar olarak basılır; her dilde karşılığı olmalı.
        foreach (['tr', 'en', 'de', 'ru', 'pl', 'pt', 'fa'] as $dil) {
            $sozluk = json_decode(File::get(lang_path($dil.'.json')), true, 512, JSON_THROW_ON_ERROR);
            $this->assertArrayHasKey('card.remove_from_favorites', $sozluk, $dil);
            $this->assertArrayHasKey('card.remove_from_favorites_aria', $sozluk, $dil);
        }
    }

    private function tarif(string $baslik, string $slug): Recipe
    {
        return Recipe::query()->create([
            'title' => $baslik, 'slug' => $slug, 'excerpt' => 'Özet.', 'description' => 'Açıklama.', 'servings' => 4,
            'status' => 'published', 'verification_status' => 'verified', 'published_at' => now(),
        ]);
    }
}
