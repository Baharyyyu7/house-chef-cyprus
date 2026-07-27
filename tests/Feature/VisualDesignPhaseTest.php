<?php

declare(strict_types=1);

namespace Tests\Feature;

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
}
