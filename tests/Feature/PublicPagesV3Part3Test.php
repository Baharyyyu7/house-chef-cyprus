<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Models\CookingSession;
use App\Models\Ingredient;
use App\Models\PantryItem;
use App\Models\Recipe;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

final class PublicPagesV3Part3Test extends TestCase
{
    use RefreshDatabase;

    public function test_auth_surfaces_use_turkish_v3_copy_and_accessible_password_controls(): void
    {
        $this->get('/login')->assertOk()->assertSee('Giriş yapın')->assertSee('Göster')->assertSee('Koyu temaya geç');
        $this->get('/register')->assertOk()->assertSee('Üye olun')->assertSee('Şifre tekrarı')->assertSee('Göster');
        $this->get('/forgot-password')->assertOk()->assertSee('Şifrenizi yenileyin')->assertSee('Sıfırlama bağlantısı gönder');
        $this->get('/reset-password/test-token')->assertOk()->assertSee('Parolanızı güncelleyin')->assertSee('Yeni parolayı onaylayın');

        $user = User::factory()->unverified()->create();
        $this->actingAs($user)->get('/verify-email')->assertOk()->assertSee('E-posta adresinizi doğrulayın')->assertSee('Bağlantıyı yeniden gönder');
        $this->actingAs($user)->get('/confirm-password')->assertOk()->assertSee('Parolanızı doğrulayın')->assertSee('Devam et');
    }

    public function test_profile_shows_real_user_specific_summary_counts(): void
    {
        $user = User::factory()->create();
        $recipe = Recipe::create([
            'title' => 'Profil Özeti Tarifi',
            'slug' => 'profil-ozeti-tarifi',
            'servings' => 2,
            'prep_minutes' => 5,
            'cook_minutes' => 10,
            'rest_minutes' => 0,
            'difficulty' => 'Kolay',
            'status' => 'published',
            'verification_status' => 'verified',
        ]);

        $user->favoriteRecipes()->attach($recipe);
        CookingSession::create(['user_id' => $user->id, 'recipe_id' => $recipe->id, 'servings_snapshot' => 2, 'status' => 'active', 'started_at' => now(), 'last_activity_at' => now()]);
        $ingredient = Ingredient::create(['name' => 'Profil Malzemesi', 'canonical_name' => 'Profil Malzemesi', 'slug' => 'profil-malzemesi', 'default_unit' => 'adet']);
        PantryItem::create(['user_id' => $user->id, 'ingredient_id' => $ingredient->id, 'quantity' => 1, 'unit' => 'adet', 'is_available' => true]);

        $this->actingAs($user)->get('/profile')
            ->assertOk()
            ->assertSee('favori tarif')
            ->assertSee('dolap ürünü')
            ->assertSee('pişirme oturumu')
            ->assertSee('>1<', false);
    }
}
