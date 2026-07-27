<?php

declare(strict_types=1);

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Route;

final class ApiOpenApiCheck extends Command
{
    protected $signature = 'api:openapi-check {--json : Makine okunabilir çıktı verir}';

    protected $description = 'Aktif API v1 endpointlerinin OpenAPI sözleşmesinde yer aldığını doğrular.';

    public function handle(): int
    {
        $specification = (string) file_get_contents(base_path('docs/openapi.yaml'));
        $routes = collect(Route::getRoutes())
            ->map(fn ($route) => $route->uri())
            ->filter(fn (string $uri) => str_starts_with($uri, 'api/v1/'))
            ->map(fn (string $uri) => preg_replace('/\{[^}]+\}/', '{public_id}', $uri))
            ->unique()
            ->values();
        $missingPaths = [];

        foreach ($routes as $uri) {
            if (! str_contains($specification, '/'.$uri)) {
                $missingPaths[] = $uri;
            }
        }

        $result = ['valid' => count($missingPaths) === 0, 'path_count' => $routes->count(), 'missing_paths' => $missingPaths];

        if ($this->option('json')) {
            $this->line((string) json_encode($result, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES));
        } elseif ($result['valid']) {
            $this->info("OpenAPI sözleşmesi doğrulandı ({$result['path_count']} endpoint).");
        } else {
            $this->error('OpenAPI sözleşmesinde eksik endpoint var: '.implode(', ', $result['missing_paths']));
        }

        return $result['valid'] ? self::SUCCESS : self::FAILURE;
    }
}
