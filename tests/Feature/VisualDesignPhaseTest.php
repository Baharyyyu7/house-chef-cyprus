<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Models\Allergen;
use App\Models\Category;
use App\Models\DietaryTag;
use App\Models\Ingredient;
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
        // Metin değil davranış doğrulanıyor. Kahraman bölümündeki çağrı
        // metinleri tasarım turlarında yeniden yazılıyor ve birebir dizeye
        // bağlı bir test, kasıtlı bir metin değişikliğinde kırılıp gerçek
        // bir hata varmış gibi görünüyordu.
        $icerik = (string) $this->get(route('home'))
            ->assertOk()
            ->assertSee('house-chef-hero-v2.png', false)
            // Arama kutusunun kendisi aranıyor, üstündeki başlık değil:
            // başlık metni tasarım turlarında yeniden yazılıyor.
            ->assertSee('hero-search', false)
            ->assertDontSee('∞ ilham')
            ->getContent();

        // Arama gerçekten tarif listesine gönderiyor mu.
        $this->assertMatchesRegularExpression(
            '/<form[^>]*action="[^"]*tarifler[^"]*"/',
            $icerik,
            'Ana sayfadaki arama tarif listesine gitmeli.'
        );

        // Birincil çağrı: tarif listesine götüren bir düğme.
        $this->assertMatchesRegularExpression(
            '/<a[^>]*hc-btn-primary[^>]*href="[^"]*(#kesfet|tarifler)[^"]*"/',
            $icerik,
            'Kahraman bölümünde tariflere götüren birincil çağrı olmalı.'
        );

        // İkincil çağrı: malzemeden tarife giden yol. Ziyaretçi girişliyse
        // malzeme keşfine, değilse kayda gider.
        $this->assertMatchesRegularExpression(
            '/<a[^>]*hc-hero-secondary[^>]*href="[^"]*(malzeme|register|ingredient)[^"]*"/i',
            $icerik,
            'Kahraman bölümünde malzemeden başlatan ikincil çağrı olmalı.'
        );
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
            ->assertSee('hc-heading-1', false);
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
            ->assertSee('Pişirme rehberi')
            ->assertSee('fetchpriority="high"', false);
    }

    public function test_recipe_servings_controls_use_a_separate_increment_contract_without_overwriting_the_typed_value(): void
    {
        $this->seed();
        $recipe = Recipe::query()->where('status', 'published')->where('verification_status', 'verified')->firstOrFail();

        // route()'un üçüncü argümanı $absolute'tur; sorgu parametreleri parametre
        // dizisinin içinde verilmezse URL'e hiç eklenmez ve test sessizce anlamsızlaşır.
        $this->get(route('recipes.show', ['recipe' => $recipe, 'servings' => 4]))
            ->assertOk()
            ->assertSee('name="servings" value="4"', false)
            ->assertSee('name="servings_step" value="-1"', false)
            ->assertSee('name="servings_step" value="1"', false);

        $this->get(route('recipes.show', ['recipe' => $recipe, 'servings' => 4, 'servings_step' => 1]))
            ->assertOk()
            ->assertSee('name="servings" value="5"', false);
    }

    public function test_recipe_archive_keeps_advanced_filter_values_in_the_public_url_and_exposes_removable_filter_controls(): void
    {
        $this->seed();
        $category = Category::query()->whereNotNull('public_id')->firstOrFail();
        $dietaryTag = DietaryTag::query()->firstOrFail();
        $allergen = Allergen::query()->firstOrFail();

        $this->get(route('recipes.index', [
            'category' => $category->slug,
            'dietary_tag' => $dietaryTag->code,
            'exclude_allergens' => [$allergen->public_id],
            'sort' => 'time',
        ]))
            ->assertOk()
            ->assertSee('Gelişmiş filtreler')
            ->assertSee('Kesin alerjenleri hariç tut')
            ->assertSee('Tümünü temizle')
            ->assertSee('sort=time', false)
            ->assertSee('exclude_allergens', false);
    }

    public function test_recipe_detail_shows_related_verified_recipes_without_exposing_internal_editorial_data(): void
    {
        $this->seed();
        $recipe = Recipe::query()->where('status', 'published')->where('verification_status', 'verified')->firstOrFail();

        $this->get(route('recipes.show', $recipe))
            ->assertOk()
            ->assertSee('Benzer tarifler')
            ->assertSee('yeniden editoryal ve medya incelemesi sürüyor')
            ->assertDontSee('editorial_reviewed_by_user_id')
            ->assertDontSee('seed_demo');
    }

    public function test_matching_v3_exposes_pantry_import_filter_controls_and_explainable_results(): void
    {
        $this->seed();
        $member = User::factory()->create(['email_verified_at' => now()]);
        $ingredient = Ingredient::query()->where('is_active', true)->firstOrFail();
        $member->pantryItems()->create(['ingredient_id' => $ingredient->id, 'quantity' => '2', 'unit' => 'adet', 'is_available' => true]);

        $this->actingAs($member)->get(route('ingredients.explore', ['use_pantry' => 1]))
            ->assertOk()
            ->assertSee('Dolabımdaki 1 kullanılabilir ürünü seç')
            ->assertSee('Bu tercihlerle sonuç bulunamadı.')
            ->assertSee('exclude_allergens[]', false)
            ->assertDontSee('ingredient_id');
    }

    public function test_pantry_v3_exposes_expiry_and_canonical_measurement_language_without_internal_fields(): void
    {
        $member = User::factory()->create(['email_verified_at' => now()]);
        $ingredient = Ingredient::create(['name' => 'Kabak', 'canonical_name' => 'Kabak', 'slug' => 'kabak', 'is_active' => true]);
        $member->pantryItems()->create(['ingredient_id' => $ingredient->id, 'quantity' => '1', 'unit' => 'demet', 'expires_at' => today()->subDay(), 'is_available' => true]);

        $this->actingAs($member)->get(route('pantry.index'))
            ->assertOk()
            ->assertSee('Süresi dolmuş')
            ->assertSee('miktar doğrulanamadı')
            ->assertSee('Dolabımla eşleştir')
            ->assertDontSee('canonical_quantity');
    }

    public function test_favorites_v3_only_shows_verified_published_recipes_and_keeps_filter_contract(): void
    {
        $member = User::factory()->create(['email_verified_at' => now()]);
        $visible = Recipe::create(['title' => 'Sevilen tarif', 'slug' => 'sevilen-tarif', 'servings' => 2, 'difficulty' => 'Kolay', 'status' => 'published', 'verification_status' => 'verified']);
        $hidden = Recipe::create(['title' => 'Kontrol bekleyen tarif', 'slug' => 'kontrol-bekleyen-tarif', 'servings' => 2, 'difficulty' => 'Kolay', 'status' => 'published', 'verification_status' => 'pending']);
        $member->favoriteRecipes()->attach([$visible->id, $hidden->id]);

        $this->actingAs($member)->get(route('favorites.index', ['q' => 'Sevilen', 'sort' => 'time']))
            ->assertOk()
            ->assertSee('Sevilen tarif')
            ->assertDontSee('Kontrol bekleyen tarif')
            ->assertSee('Favorilerde ara')
            ->assertSee('Tarifleri keşfet');
    }
}
