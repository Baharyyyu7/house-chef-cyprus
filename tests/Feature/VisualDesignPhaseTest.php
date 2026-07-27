<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Models\Recipe;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

final class VisualDesignPhaseTest extends TestCase
{
    use RefreshDatabase;

    public function test_public_navigation_and_mobile_navigation_are_available(): void
    {
        $this->get(route('home'))
            ->assertOk()
            ->assertSee('Ana Sayfa')
            ->assertSee('Tarifler')
            ->assertSee('mobile-nav', false)
            ->assertSee('House Chef');
    }

    public function test_public_theme_controls_preserve_a_client_side_theme_preference_contract(): void
    {
        $this->get(route('home'))
            ->assertOk()
            ->assertSee('localStorage.theme', false)
            ->assertSee('Açık temaya geç')
            ->assertSee('Koyu temaya geç')
            ->assertSee('mobile-nav', false);
    }

    public function test_v2_homepage_uses_the_real_food_hero_and_keeps_search_and_primary_calls_to_action(): void
    {
        $this->get(route('home'))
            ->assertOk()
            ->assertSee('house-chef-hero-v2.png', false)
            ->assertSee('Tarifleri keşfet')
            ->assertSee('Elindekilerle tarif bul')
            ->assertSee('Akıllı arama')
            ->assertSee('hero-search', false)
            ->assertDontSee('∞ ilham');
    }

    public function test_homepage_renders_an_explicit_featured_recipe_empty_state_when_no_recipes_exist(): void
    {
        $this->get(route('home'))
            ->assertOk()
            ->assertSee('Seçilmiş tarifler hazırlanıyor.')
            ->assertSee('Tarif arşivine git');
    }

    public function test_verified_member_sees_combined_matching_form(): void
    {
        $member = User::factory()->create(['email_verified_at' => now()]);

        $this->actingAs($member)->get(route('ingredients.explore'))
            ->assertOk()
            ->assertSee('Beslenme ve güvenlik')
            ->assertSee('exclude_allergens[]', false)
            ->assertSee('min_protein', false)
            ->assertSee('max_calories', false);
    }

    public function test_admin_sees_branded_admin_navigation_while_member_is_forbidden(): void
    {
        $admin = User::factory()->create(['role' => 'admin', 'email_verified_at' => now()]);
        $member = User::factory()->create(['role' => 'member', 'email_verified_at' => now()]);

        $this->actingAs($admin)->get(route('admin.dashboard'))
            ->assertOk()
            ->assertSee('Yönetim alanı')
            ->assertSee('Veri Kalitesi')
            ->assertSee(route('admin.recipes.index'), false);

        $this->actingAs($member)->get(route('admin.dashboard'))->assertForbidden();
    }

    public function test_v3_component_primitives_render_on_the_public_member_and_admin_pilots(): void
    {
        $member = User::factory()->create(['email_verified_at' => now()]);
        $admin = User::factory()->create(['role' => 'admin', 'email_verified_at' => now()]);

        $this->actingAs($member)->get(route('favorites.index'))
            ->assertOk()
            ->assertSee('hc-v3-empty', false)
            ->assertSee('Henüz favori tarifiniz yok.');

        $this->actingAs($member)->get(route('profile'))
            ->assertOk()
            ->assertSee('hc-v3-card', false)
            ->assertSee('Hesap bilgileri')
            ->assertSee('Parolayı güncelle')
            ->assertSee('Hesabı sil');

        $this->actingAs($admin)->get(route('admin.dashboard'))
            ->assertOk()
            ->assertSee('hc-v3-card', false)
            ->assertSee('hc-display-lg', false);
    }

    public function test_profile_pilot_uses_turkish_labels_and_does_not_render_the_old_english_headings(): void
    {
        $user = User::factory()->create(['email_verified_at' => now()]);

        $this->actingAs($user)->get(route('profile'))
            ->assertOk()
            ->assertSee('Ad soyad')
            ->assertSee('Mevcut parola')
            ->assertDontSee('Profile Information')
            ->assertDontSee('Update Password')
            ->assertDontSee('Delete Account');
    }

    public function test_recipe_archive_uses_the_v3_verified_catalogue_card_and_filter_contract(): void
    {
        $this->seed();

        $this->get(route('recipes.index'))
            ->assertOk()
            ->assertSee('Doğrulanmış tarif koleksiyonu')
            ->assertSee('Tarifleri keşfedin.')
            ->assertSee('hc-recipe-archive', false)
            ->assertSee('hc-v3-card--interactive', false)
            ->assertSee('Türkçe editoryal içerik');
    }

    public function test_recipe_detail_uses_recipe_media_household_measurements_and_visible_trust_information(): void
    {
        $this->seed();
        $recipe = Recipe::query()->where('status', 'published')->where('verification_status', 'verified')->firstOrFail();

        $this->get(route('recipes.show', $recipe))
            ->assertOk()
            ->assertSee('hc-recipe-detail-hero', false)
            ->assertSee('Ev ölçüleri')
            ->assertSee('Kaynak ve güncellik')
            ->assertSee('AI Şef')
            ->assertSee('fetchpriority="high"', false);
    }
}
