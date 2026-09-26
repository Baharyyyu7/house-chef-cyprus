<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ThemeAndPremiumNavigationTest extends TestCase
{
    use RefreshDatabase;

    public function test_theme_switch_enables_a_bounded_smooth_transition(): void
    {
        $this->get('/')
            ->assertOk()
            ->assertSee('hc-theme-transitioning', false)
            ->assertSee('hcThemeTransitionTimer', false);
    }

    public function test_safe_premium_page_and_navigation_are_available(): void
    {
        $this->get('/')
            ->assertOk()
            ->assertSee(route('pricing'), false)
            ->assertSee('Premium');

        $this->get(route('pricing'))
            ->assertOk()
            ->assertSee('Henüz ödeme almıyoruz.')
            ->assertDontSee('Hemen Başlat');
    }

    public function test_desktop_header_groups_account_controls_under_one_visual_system(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);

        $this->actingAs($admin)->get('/')
            ->assertOk()
            ->assertSee('hc-nav-actions', false)
            ->assertSee('hc-nav-control', false);
    }

    public function test_desktop_header_renders_utility_controls_as_separate_peers(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);

        $this->actingAs($admin)->get('/')
            ->assertOk()
            ->assertSee('hc-nav-actions--separate', false);
    }

    public function test_desktop_utility_controls_share_one_labelled_motion_system(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);

        $this->actingAs($admin)->get('/')
            ->assertOk()
            ->assertSee('hc-nav-actions--unified', false)
            ->assertSee('hc-theme-switch__label', false)
            ->assertSee('Tema');
    }

    public function test_desktop_utility_controls_use_compact_content_widths(): void
    {
        $css = file_get_contents(resource_path('css/app.css'));

        $this->assertStringNotContainsString('--hc-nav-utility-width', $css);
        $this->assertStringContainsString('padding-inline: .75rem', $css);
    }
}
