<?php

declare(strict_types=1);

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Routing\Route;
use Illuminate\Support\Facades\Route as RouteFacade;
use Tests\TestCase;

final class ApiContractCoverageTest extends TestCase
{
    use RefreshDatabase;

    public function test_every_authenticated_api_route_rejects_anonymous_requests_with_json(): void
    {
        $routes = collect(RouteFacade::getRoutes()->getRoutes())
            ->filter(fn (Route $route): bool => str_starts_with($route->uri(), 'api/v1/'))
            ->filter(fn (Route $route): bool => in_array('auth', $route->middleware(), true))
            ->values();

        $this->assertGreaterThanOrEqual(15, $routes->count(), 'Authenticated API coverage unexpectedly shrank.');

        foreach ($routes as $route) {
            $uri = '/'.preg_replace('/\{[^}]+}/', 'missing', $route->uri());
            $method = $route->methods()[0];
            $response = $this->withHeader('Accept', 'application/json')->call($method, $uri);

            $this->assertSame(401, $response->getStatusCode(), "Anonymous API request {$method} {$route->uri()} returned {$response->getStatusCode()}.");
            $this->assertTrue(str_contains((string) $response->headers->get('content-type'), 'application/json'));
        }
    }

    public function test_static_public_v1_get_endpoints_share_the_success_envelope(): void
    {
        $routes = collect(RouteFacade::getRoutes()->getRoutes())
            ->filter(fn (Route $route): bool => str_starts_with($route->uri(), 'api/v1/'))
            ->filter(fn (Route $route): bool => in_array('GET', $route->methods(), true))
            ->reject(fn (Route $route): bool => in_array('auth', $route->middleware(), true))
            ->filter(fn (Route $route): bool => $route->parameterNames() === [])
            ->reject(fn (Route $route): bool => str_starts_with($route->uri(), 'api/v1/agent/'))
            ->values();

        $this->assertGreaterThanOrEqual(10, $routes->count(), 'Public API coverage unexpectedly shrank.');

        foreach ($routes as $route) {
            $response = $this->getJson('/'.$route->uri());

            $response->assertOk()->assertJsonStructure(['data', 'request_id'])->assertHeader('X-Request-ID');
        }
    }
}
