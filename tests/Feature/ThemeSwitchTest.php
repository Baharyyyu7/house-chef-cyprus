<?php

declare(strict_types=1);

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

final class ThemeSwitchTest extends TestCase
{
    use RefreshDatabase;

    public function test_public_layout_uses_accessible_theme_switches(): void
    {
        $this->get(route('home'))
            ->assertOk()
            ->assertSee('data-theme-switch', false)
            ->assertSee('role="switch"', false)
            ->assertSee('Koyu temaya geç')
            ->assertSee('Açık temaya geç');
    }

    public function test_guest_layout_uses_the_same_theme_switch(): void
    {
        $this->get(route('login'))
            ->assertOk()
            ->assertSee('data-theme-switch', false)
            ->assertSee('role="switch"', false);
    }

    public function test_theme_switch_labels_follow_the_requested_language(): void
    {
        config(['locales.supported.en.enabled' => true]);

        $this->get('/en/login')
            ->assertOk()
            ->assertSee('Switch to dark mode')
            ->assertSee('Switch to light mode')
            ->assertDontSee('Koyu temaya geç')
            ->assertDontSee('Açık temaya geç');
    }
}
