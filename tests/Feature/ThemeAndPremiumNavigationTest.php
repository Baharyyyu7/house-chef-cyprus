<?php

namespace Tests\Feature;

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
}
