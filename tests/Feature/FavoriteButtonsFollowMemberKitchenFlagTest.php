<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Models\Recipe;
use App\Models\SiteFeature;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use Tests\TestCase;

/**
 * Favori ve koleksiyon düğmeleri "Mutfağım" (member_kitchen) özelliğine aittir.
 * Bayrak kapalıyken rotaları 404 veriyordu ama düğmeler giriş yapmış üyeye
 * basılmaya devam ediyordu: tıklanınca 404.
 */
final class FavoriteButtonsFollowMemberKitchenFlagTest extends TestCase
{
    use RefreshDatabase;

    private function tarif(): Recipe
    {
        return Recipe::create(['title' => 'Hellim tava', 'slug' => 'hellim-tava-'.Str::lower(Str::random(5)), 'excerpt' => 'Özet.', 'servings' => 2, 'prep_minutes' => 5, 'cook_minutes' => 5, 'rest_minutes' => 0, 'difficulty' => 'Kolay', 'status' => 'published', 'verification_status' => 'verified', 'published_at' => now()->subDay()]);
    }

    public function test_bayrak_kapaliyken_uyeye_favori_ve_koleksiyon_dugmesi_basilmaz(): void
    {
        SiteFeature::query()->create(['key' => 'member_kitchen', 'enabled' => false]);
        $tarif = $this->tarif();
        $uye = User::factory()->create(['email_verified_at' => now()]);

        $this->actingAs($uye)->get(route('recipes.show', $tarif))->assertOk()
            ->assertDontSee(route('favorites.toggle', $tarif), false)
            ->assertDontSee(route('collections.index'), false);
    }

    public function test_bayrak_acikken_uye_favori_dugmesini_gorur_misafir_gormez(): void
    {
        $tarif = $this->tarif();

        $this->get(route('recipes.show', $tarif))->assertOk()->assertDontSee(route('favorites.toggle', $tarif), false);

        $uye = User::factory()->create(['email_verified_at' => now()]);
        $this->actingAs($uye)->get(route('recipes.show', $tarif))->assertOk()
            ->assertSee(route('favorites.toggle', $tarif), false);
    }
}
