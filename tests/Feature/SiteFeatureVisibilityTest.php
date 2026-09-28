<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Models\SiteFeature;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

final class SiteFeatureVisibilityTest extends TestCase
{
    use RefreshDatabase;

    public function test_disabled_developments_are_hidden_and_not_directly_accessible(): void
    {
        foreach (['explore', 'services', 'member_kitchen', 'premium', 'shop'] as $key) {
            SiteFeature::query()->create(['key' => $key, 'enabled' => false]);
        }

        $home = (string) $this->get(route('home'))->assertOk()->getContent();
        $this->assertStringNotContainsString('href="'.route('ingredients.explore').'"', $home);
        $this->assertStringNotContainsString('href="'.route('private-chef.index').'"', $home);
        $this->assertStringNotContainsString('href="'.route('pricing').'"', $home);
        $this->assertStringNotContainsString('href="'.route('shop.index').'"', $home);

        $this->get(route('ingredients.explore'))->assertNotFound();
        $this->get(route('private-chef.index'))->assertNotFound();
        $this->get(route('pricing'))->assertNotFound();
        $this->get(route('shop.index'))->assertNotFound();
    }

    public function test_admin_can_enable_a_development(): void
    {
        $admin = User::factory()->create(['role' => 'admin', 'email_verified_at' => now()]);

        $this->actingAs($admin)->put(route('admin.site-features.update'), ['enabled' => ['explore']])
            ->assertRedirect(route('admin.site-features.index'));

        $this->assertDatabaseHas('site_features', ['key' => 'explore', 'enabled' => true]);
        $this->get(route('ingredients.explore'))->assertOk();
    }
}
