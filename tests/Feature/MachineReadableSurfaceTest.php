<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Support\RouteAudit\RouteSurfaceClassifier;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Routing\Route;
use Illuminate\Support\Facades\Route as RouteFacade;
use Tests\TestCase;

final class MachineReadableSurfaceTest extends TestCase
{
    use RefreshDatabase;

    public function test_every_machine_readable_get_surface_returns_a_nonempty_success_response(): void
    {
        $classifier = app(RouteSurfaceClassifier::class);
        $routes = collect(RouteFacade::getRoutes()->getRoutes())
            ->filter(fn (Route $route): bool => in_array('GET', $route->methods(), true))
            ->filter(fn (Route $route): bool => $classifier->classify($route) === 'machine')
            ->values();

        $this->assertSame(25, $routes->count(), 'Machine-readable route inventory changed; review its contracts.');

        foreach ($routes as $route) {
            $uri = '/'.preg_replace('/\{locale}/', 'en', $route->uri());
            $response = $this->get($uri);

            $response->assertOk();
            $this->assertNotSame('', trim((string) $response->getContent()), "Machine surface {$uri} was empty.");
        }
    }

    public function test_machine_formats_expose_their_expected_content_types_and_roots(): void
    {
        $this->get('/sitemap.xml')->assertOk()->assertHeader('content-type', 'application/xml; charset=UTF-8')->assertSee('<sitemapindex', false);
        $this->get('/feed.xml')->assertOk()->assertSee('<rss', false)->assertSee('<channel>', false);
        $this->get('/opensearch.xml')->assertOk()->assertSee('<OpenSearchDescription', false);
        $this->get('/manifest.webmanifest')->assertOk()->assertJsonStructure(['name', 'short_name', 'icons']);
        $this->get('/robots.txt')->assertOk()->assertSee('Sitemap:', false);
        $this->get('/llms.txt')->assertOk()->assertSee('House Chef Cyprus', false);
    }
}
