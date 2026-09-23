<?php

declare(strict_types=1);

namespace Tests\Unit;

use App\Support\RouteAudit\RouteSurfaceClassifier;
use Illuminate\Routing\Route;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

final class RouteSurfaceClassifierTest extends TestCase
{
    #[DataProvider('routes')]
    public function test_it_classifies_routes(string $uri, array $middleware, string $expected): void
    {
        $route = new Route(['GET'], $uri, fn () => null);
        $route->middleware($middleware);

        self::assertSame($expected, (new RouteSurfaceClassifier)->classify($route));
    }

    public static function routes(): array
    {
        return [
            'public' => ['tarifler', ['web'], 'public'],
            'authenticated' => ['favoriler', ['web', 'auth'], 'authenticated'],
            'admin' => ['yonetim/tarifler', ['web', 'auth', 'admin'], 'admin'],
            'api' => ['api/v1/recipes', ['api'], 'api'],
            'machine' => ['sitemap.xml', ['web'], 'machine'],
        ];
    }

    public function test_it_refuses_to_probe_mutating_or_dynamic_routes(): void
    {
        $classifier = new RouteSurfaceClassifier;

        self::assertFalse($classifier->isSafelyProbeable(new Route(['POST'], 'iletisim', fn () => null)));
        self::assertFalse($classifier->isSafelyProbeable(new Route(['GET'], 'tarifler/{recipe}', fn () => null)));
        self::assertTrue($classifier->isSafelyProbeable(new Route(['GET'], 'tarifler', fn () => null)));
    }
}
