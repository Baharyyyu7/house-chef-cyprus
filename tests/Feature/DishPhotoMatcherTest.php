<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Services\Media\RecipeDisplayMediaService;
use Tests\TestCase;

/**
 * Yemek fotoğrafı eşleşmesi parça duyarlıdır.
 *
 * Alt-dize araması "pilavuna" böreğine pilav fotoğrafı basıyordu: kullanıcı
 * "resim yanlış" diye şikayet ediyordu. Tek kelimelik anahtar yalnız tam
 * slug parçası veya tam başlık kelimesiyle eşleşir.
 */
final class DishPhotoMatcherTest extends TestCase
{
    public function test_pilavuna_does_not_match_pilav(): void
    {
        $this->assertNull(RecipeDisplayMediaService::matchDishPhoto(
            'kibris-usulu-pilavuna-ABCDE',
            'Kıbrıs Usulü Pilavuna'
        ));
    }

    public function test_pilav_matches_pilav(): void
    {
        $this->assertNotNull(RecipeDisplayMediaService::matchDishPhoto(
            'nohutlu-pilav-ABCDE',
            'Nohutlu Pilav'
        ));
    }

    public function test_hyphenated_keyword_still_matches(): void
    {
        $this->assertNotNull(RecipeDisplayMediaService::matchDishPhoto(
            'tavuk-sote-ABCDE',
            'Tavuk Sote'
        ));
    }

    public function test_unrelated_dish_returns_null(): void
    {
        $this->assertNull(RecipeDisplayMediaService::matchDishPhoto(
            'kibris-usulu-molehiya-ABCDE',
            'Kıbrıs Usulü Molehiya'
        ));
    }

    public function test_turkish_inflection_still_matches(): void
    {
        // Çekim eki kökü değiştirmez: mantısı, dolması, sütlacı.
        $this->assertNotNull(RecipeDisplayMediaService::matchDishPhoto(
            'kayseri-mantisi-ABCDE',
            'Kayseri Mantısı'
        ));
        $this->assertNotNull(RecipeDisplayMediaService::matchDishPhoto(
            'etli-kabak-dolmasi-ABCDE',
            'Etli Kabak Dolması'
        ));
    }

    public function test_title_word_match_counts(): void
    {
        // Slug jenerik ama başlık yemeği söylüyor.
        $this->assertNotNull(RecipeDisplayMediaService::matchDishPhoto(
            'firinda-lezzet-ABCDE',
            'Fırında Hellim'
        ));
    }
}
