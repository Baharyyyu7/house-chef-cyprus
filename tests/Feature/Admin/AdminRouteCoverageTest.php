<?php

declare(strict_types=1);

namespace Tests\Feature\Admin;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Routing\Route;
use Illuminate\Support\Facades\Route as RouteFacade;
use Tests\TestCase;

final class AdminRouteCoverageTest extends TestCase
{
    use RefreshDatabase;

    public function test_every_static_admin_get_route_is_renderable_for_admins_and_forbidden_for_members(): void
    {
        $admin = User::factory()->create(['role' => 'admin', 'email_verified_at' => now()]);
        $member = User::factory()->create(['role' => 'member', 'email_verified_at' => now()]);
        $routes = collect(RouteFacade::getRoutes()->getRoutes())
            ->filter(fn (Route $route): bool => in_array('GET', $route->methods(), true))
            ->filter(fn (Route $route): bool => str_starts_with($route->uri(), 'yonetim'))
            ->filter(fn (Route $route): bool => $route->parameterNames() === [])
            ->values();

        $this->assertGreaterThanOrEqual(40, $routes->count(), 'Static admin route coverage unexpectedly shrank.');

        foreach ($routes as $route) {
            $uri = '/'.$route->uri();
            $name = $route->getName() ?? $uri;

            $adminResponse = $this->actingAs($admin)->get($uri);
            $this->assertTrue(
                in_array($adminResponse->getStatusCode(), [200, 302, 404], true),
                "Admin route {$name} returned {$adminResponse->getStatusCode()}.",
            );

            $memberResponse = $this->actingAs($member)->get($uri);
            $this->assertSame(403, $memberResponse->getStatusCode(), "Member accessed admin route {$name}.");
        }
    }

    public function test_every_static_admin_mutation_is_forbidden_for_members_before_controller_execution(): void
    {
        $member = User::factory()->create(['role' => 'member', 'email_verified_at' => now()]);
        $routes = collect(RouteFacade::getRoutes()->getRoutes())
            ->filter(fn (Route $route): bool => ! in_array('GET', $route->methods(), true))
            ->filter(fn (Route $route): bool => str_starts_with($route->uri(), 'yonetim'))
            ->filter(fn (Route $route): bool => $route->parameterNames() === [])
            ->values();

        $this->assertGreaterThanOrEqual(25, $routes->count(), 'Static admin mutation coverage unexpectedly shrank.');

        foreach ($routes as $route) {
            $method = $route->methods()[0];
            $name = $route->getName() ?? $route->uri();
            $response = $this->actingAs($member)->call($method, '/'.$route->uri());

            $this->assertSame(403, $response->getStatusCode(), "Member mutation {$name} returned {$response->getStatusCode()}.");
        }
    }
}
