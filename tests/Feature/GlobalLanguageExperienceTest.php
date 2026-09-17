<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class GlobalLanguageExperienceTest extends TestCase
{
    use RefreshDatabase;

    public function test_english_login_and_recovery_keep_the_selected_language(): void
    {
        config(['locales.supported.en.enabled' => true]);

        $this->get('/en/login')->assertOk()
            ->assertSee('lang="en"', false)
            ->assertSee('Welcome back')
            ->assertSee('Email address')
            ->assertSee('href="'.url('/en/forgot-password').'"', false)
            ->assertDontSee('Tekrar hoş geldiniz');

        $this->get('/en/forgot-password')->assertOk()
            ->assertSee('Reset your password')
            ->assertSee('href="'.url('/en/login').'"', false)
            ->assertDontSee('Şifrenizi yenileyin');

        $this->get('/login')->assertOk()->assertSee('lang="tr"', false)
            ->assertSee('Tekrar hoş geldiniz')
            ->assertSee('href="'.url('/forgot-password').'"', false);
    }

    public function test_switch_confirmation_uses_the_destination_language(): void
    {
        config(['locales.supported.en.enabled' => true]);
        $this->post('/dil', ['locale' => 'en', 'return_to' => '/login'])
            ->assertRedirect(url('/en/login'))
            ->assertSessionHas('status', 'Your language preference has been updated to English.');
        $this->post('/dil', ['locale' => 'tr', 'return_to' => '/en/login'])
            ->assertRedirect(url('/login'))
            ->assertSessionHas('status', 'Dil tercihiniz Türkçe olarak güncellendi.');
    }

    public function test_english_public_navigation_has_localized_accessibility_labels(): void
    {
        config(['locales.supported.en.enabled' => true]);
        $this->get('/en/recipes')->assertOk()
            ->assertSee('aria-label="Main menu"', false)
            ->assertSee('aria-label="Open menu"', false)
            ->assertSee('aria-label="Close menu"', false)
            ->assertDontSee('aria-label="Ana menü"', false);
    }

    public function test_error_templates_translate_their_recovery_actions(): void
    {
        app()->setLocale('en');
        foreach ([403, 404, 419, 500] as $status) {
            $this->view('errors.'.$status)
                ->assertSee(__('ux.'.$status.'_heading', [], 'en'))
                ->assertSee('Back to home')
                ->assertDontSee('Ana sayfaya dön');
        }
    }
}
