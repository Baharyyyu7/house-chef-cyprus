<?php

declare(strict_types=1);

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Route;

final class SeoDeploymentGuard extends Command
{
    protected $signature = 'seo:deployment-guard
                            {--environment= : Doğrulanacak ortam profili}
                            {--json : Makine okunabilir çıktı verir}';

    protected $description = 'Kritik production SEO yapılandırmasını gizli değerler göstermeden doğrular.';

    public function handle(): int
    {
        $target = (string) ($this->option('environment') ?: app()->environment());
        $productionLike = in_array($target, ['production', 'staging'], true);
        $appUrl = rtrim((string) config('app.url'), '/');
        $appHost = (string) parse_url($appUrl, PHP_URL_HOST);
        $canonicalHost = (string) config('production.canonical_host');
        $robots = (string) view('robots')->render();
        $requiredRoutes = ['home', 'recipes.index', 'blog.index', 'sitemap', 'sitemap.recipes', 'sitemap.blog'];

        $checks = [
            'app_url_valid' => filter_var($appUrl, FILTER_VALIDATE_URL) !== false,
            'sitemap_routes_available' => collect($requiredRoutes)->every(fn (string $name): bool => Route::has($name)),
            'robots_has_sitemap' => str_contains($robots, 'Sitemap:'),
            'robots_does_not_block_all' => preg_match('/^Disallow:\\s*\\/\\s*$/mi', $robots) !== 1,
            'indexing_configuration_present' => config('seo.indexing_enabled') !== null,
        ];

        if ($productionLike) {
            $checks += [
                'https_url' => str_starts_with($appUrl, 'https://'),
                'public_host' => $appHost !== '' && ! in_array($appHost, ['localhost', '127.0.0.1', '::1'], true),
                'canonical_host_matches_app_url' => $canonicalHost !== '' && hash_equals($canonicalHost, $appHost),
                'canonical_scheme_https' => config('production.canonical_scheme') === 'https',
                'production_indexing_enabled' => (bool) config('seo.indexing_enabled'),
                'public_build_manifest_exists' => is_file(public_path('build/manifest.json')),
            ];
        }

        $result = [
            'valid' => ! in_array(false, $checks, true),
            'target_environment' => $target,
            'checks' => $checks,
        ];

        if ($this->option('json')) {
            $this->line((string) json_encode($result, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES));
        } else {
            $this->table(['Kontrol', 'Durum'], collect($checks)->map(fn (bool $valid, string $name): array => [$name, $valid ? 'OK' : 'HATA'])->all());
        }

        return $result['valid'] ? self::SUCCESS : self::FAILURE;
    }
}
