<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Routing\Route;
use Illuminate\Support\Facades\Route as RouteFacade;
use Tests\TestCase;

final class AdminShellContractTest extends TestCase
{
    use RefreshDatabase;

    public function test_every_static_admin_screen_uses_the_shared_shell_and_navigation_contract(): void
    {
        $admin = User::factory()->create(['role' => 'admin', 'email_verified_at' => now()]);

        foreach ($this->staticAdminRoutes() as $route) {
            $this->actingAs($admin)->get(route($route->getName()))
                ->assertOk()
                ->assertSee('admin-shell', false)
                ->assertSee('Yönetim menüsünde ara')
                ->assertSee('Siteye dön');
        }
    }

    public function test_metric_cards_have_distinct_label_and_value_elements_in_operations_screens(): void
    {
        $admin = User::factory()->create(['role' => 'admin', 'email_verified_at' => now()]);

        foreach (['admin.ai-kitchen.index', 'admin.personalization.index'] as $name) {
            $this->actingAs($admin)->get(route($name))
                ->assertOk()
                ->assertSee('hc-admin-metric-card', false)
                ->assertSee('<span>', false)
                ->assertSee('<strong>', false);
        }
    }

    public function test_member_is_denied_the_representative_admin_shell_routes(): void
    {
        $member = User::factory()->create(['role' => 'member', 'email_verified_at' => now()]);

        foreach (['admin.dashboard', 'admin.ai-kitchen.index', 'admin.personalization.index', 'admin.recipes.index', 'admin.data-quality.index', 'admin.system-events.index'] as $name) {
            $this->actingAs($member)->get(route($name))->assertForbidden();
        }
    }

    public function test_admin_shell_styles_define_the_no_overlap_and_metric_card_contract(): void
    {
        $css = file_get_contents(resource_path('css/app.css'));

        self::assertIsString($css);
        self::assertStringContainsString('.admin-shell__content { min-width:0;', $css);
        self::assertStringContainsString('.admin-shell .hc-page { width:100%;', $css);
        self::assertStringContainsString('.hc-admin-stat, .hc-admin-metric-card { display:flex;', $css);
        // Yatay taşma artık her genişlikte kesiliyor (8e4aa76); clip, hidden'ın
        // aksine yapışkan başlığı bozmaz.
        self::assertStringContainsString('html, body, .admin-shell { overflow-x:clip;', $css);
    }

    /** @return list<Route> */
    private function staticAdminRoutes(): array
    {
        return collect(RouteFacade::getRoutes()->getRoutes())
            ->filter(fn (Route $route): bool => str_starts_with($route->uri(), 'yonetim')
                && in_array('GET', $route->methods(), true)
                && ! str_contains($route->uri(), '{')
                && filled($route->getName()))
            ->values()
            ->all();
    }
}
