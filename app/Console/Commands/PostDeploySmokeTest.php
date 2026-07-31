<?php

declare(strict_types=1);

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Http;

final class PostDeploySmokeTest extends Command
{
    protected $signature = 'app:post-deploy-smoke
                            {--json : Makine okunabilir çıktı verir}
                            {--dry-run : HTTP isteği göndermeden kontrol planını doğrular}';

    protected $description = 'Canlıya alım sonrası güvenli public endpoint smoke kontrolünü yürütür.';

    /** @var array<string, int> */
    private array $endpoints = [
        '/' => 200,
        '/tarifler' => 200,
        '/blog' => 200,
        '/login' => 200,
        '/register' => 200,
        '/robots.txt' => 200,
        '/sitemap.xml' => 200,
        '/health' => 200,
        '/api/v1/health' => 200,
    ];

    public function handle(): int
    {
        if ($this->option('dry-run')) {
            return $this->respond([
                'valid' => true,
                'mode' => 'dry-run',
                'endpoints' => array_keys($this->endpoints),
                'message' => 'HTTP isteği gönderilmedi; post-deploy kontrol planı doğrulandı.',
            ]);
        }

        $baseUrl = rtrim((string) config('app.url'), '/');
        $results = [];
        foreach ($this->endpoints as $path => $expectedStatus) {
            try {
                $response = Http::acceptJson()->timeout(8)->get($baseUrl.$path);
                $results[$path] = ['expected' => $expectedStatus, 'actual' => $response->status(), 'valid' => $response->status() === $expectedStatus];
            } catch (\Throwable) {
                $results[$path] = ['expected' => $expectedStatus, 'actual' => null, 'valid' => false];
            }
        }

        return $this->respond([
            'valid' => ! collect($results)->contains('valid', false),
            'mode' => 'live',
            'base_url_configured' => $baseUrl !== '',
            'endpoints' => $results,
        ]);
    }

    /** @param array<string, mixed> $result */
    private function respond(array $result): int
    {
        if ($this->option('json')) {
            $this->line((string) json_encode($result, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES));
        } else {
            $this->info($result['valid'] ? 'Post-deploy smoke kontrolü başarılı.' : 'Post-deploy smoke kontrolü başarısız.');
        }

        return $result['valid'] ? self::SUCCESS : self::FAILURE;
    }
}
