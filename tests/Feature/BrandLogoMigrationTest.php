<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

final class BrandLogoMigrationTest extends TestCase
{
    use RefreshDatabase;

    public function test_official_brand_assets_and_web_manifest_are_present(): void
    {
        foreach ([
            'house-chef-cyprus-logo-on-light.svg',
            'house-chef-cyprus-logo-on-dark.svg',
            'house-chef-cyprus-mark-on-light.svg',
            'house-chef-cyprus-mark-on-dark.svg',
            'favicon.svg',
            'favicon-16x16.png',
            'favicon-32x32.png',
            'apple-touch-icon.png',
            'android-chrome-192x192.png',
            'android-chrome-512x512.png',
            'og-default.jpg',
            'site.webmanifest',
        ] as $asset) {
            self::assertFileExists(public_path('brand/'.$asset));
        }
    }

    public function test_public_layout_uses_centralized_official_brand_assets_for_logo_icons_and_schema(): void
    {
        $this->get(route('home'))
            ->assertOk()
            ->assertSee('brand/house-chef-cyprus-logo-on-light.svg', false)
            ->assertSee('brand/house-chef-cyprus-logo-on-dark.svg', false)
            ->assertSee('brand/favicon.svg', false)
            ->assertSee('brand/apple-touch-icon.png', false)
            ->assertSee('brand/site.webmanifest', false)
            ->assertSee('brand/og-default.jpg', false)
            ->assertSee('brand/house-chef-cyprus-mark-on-light.svg', false);
    }

    public function test_auth_and_admin_shell_use_the_official_brand_component(): void
    {
        $this->get(route('login'))
            ->assertOk()
            ->assertSee('brand/house-chef-cyprus-logo-on-light.svg', false)
            ->assertSee('brand/house-chef-cyprus-logo-on-dark.svg', false)
            ->assertSee('brand/favicon.svg', false);

        $admin = User::factory()->create(['role' => 'admin', 'email_verified_at' => now()]);

        $this->actingAs($admin)->get(route('admin.dashboard'))
            ->assertOk()
            ->assertSee('brand/house-chef-cyprus-logo-on-dark.svg', false)
            ->assertSee('brand/house-chef-cyprus-mark-on-dark.svg', false);
    }

    public function test_transactional_mail_header_uses_the_approved_png_at_an_absolute_url(): void
    {
        $html = view('vendor.mail.html.header', ['url' => 'https://housechefcyprus.com'])->render();

        self::assertStringContainsString('brand/house-chef-cyprus-logo-on-light.png', $html);
        self::assertStringContainsString('alt="House Chef Cyprus"', $html);
        self::assertStringContainsString('width="220"', $html);
    }
}
