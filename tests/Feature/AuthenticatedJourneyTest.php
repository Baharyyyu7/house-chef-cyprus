<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Routing\Route;
use Illuminate\Support\Facades\Route as RouteFacade;
use Tests\TestCase;

final class AuthenticatedJourneyTest extends TestCase
{
    use RefreshDatabase;

    public function test_every_non_admin_web_account_route_requires_authentication(): void
    {
        $routes = $this->accountRoutes();

        $this->assertGreaterThanOrEqual(40, $routes->count(), 'Authenticated route coverage unexpectedly shrank.');

        foreach ($routes as $route) {
            $parameters = array_fill_keys($route->parameterNames(), 'missing');
            $uri = route((string) $route->getName(), $parameters, false);
            $method = $route->methods()[0];
            $response = $this->call($method, $uri);

            $response->assertRedirect(route('login'));
        }
    }

    public function test_verified_user_can_open_every_static_account_workspace(): void
    {
        $user = User::factory()->create(['email_verified_at' => now()]);
        $routes = $this->accountRoutes()
            ->filter(fn (Route $route): bool => in_array('GET', $route->methods(), true))
            ->filter(fn (Route $route): bool => $route->parameterNames() === []);

        $this->assertGreaterThanOrEqual(8, $routes->count(), 'Static account workspace coverage unexpectedly shrank.');

        foreach ($routes as $route) {
            $response = $this->actingAs($user)->get(route((string) $route->getName(), absolute: false));

            $this->assertSame(200, $response->getStatusCode(), "Account route {$route->getName()} returned {$response->getStatusCode()}.");
        }
    }

    /** @return \Illuminate\Support\Collection<int, Route> */
    private function accountRoutes(): \Illuminate\Support\Collection
    {
        return collect(RouteFacade::getRoutes()->getRoutes())
            ->filter(fn (Route $route): bool => in_array('auth', $route->middleware(), true))
            ->reject(fn (Route $route): bool => str_starts_with($route->uri(), 'yonetim') || str_starts_with($route->uri(), 'api/'))
            ->filter(fn (Route $route): bool => filled($route->getName()))
            ->values();
    }
}
