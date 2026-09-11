<?php

declare(strict_types=1);

namespace App\Services\Seo;

use App\Models\BlogPost;
use App\Models\Recipe;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

final class IndexNowService
{
    public const DEFAULT_KEY = 'c032a1e8e8214371b658ef9c7e0b57e1';
    public const ENDPOINT = 'https://api.indexnow.org/indexnow';

    public function getKey(): string
    {
        return (string) config('services.indexnow.key', self::DEFAULT_KEY);
    }

    /**
     * @param array<int, string>|null $urls
     * @return array{success: bool, status: int, count: int, urls: array<int, string>}
     */
    public function submit(?array $urls = null): array
    {
        if ($urls === null || empty($urls)) {
            $urls = $this->collectRecentUrls();
        }

        if (empty($urls)) {
            return [
                'success' => true,
                'status' => 200,
                'count' => 0,
                'urls' => [],
            ];
        }

        $key = $this->getKey();
        $host = (string) parse_url(config('app.url', 'https://housechefcyprus.com'), PHP_URL_HOST);
        if ($host === '') {
            $host = 'housechefcyprus.com';
        }

        $payload = [
            'host' => $host,
            'key' => $key,
            'keyLocation' => "https://{$host}/{$key}.txt",
            'urlList' => array_values(array_unique($urls)),
        ];

        try {
            $response = Http::timeout(10)
                ->withHeaders(['Content-Type' => 'application/json; charset=utf-8'])
                ->post(self::ENDPOINT, $payload);

            $status = $response->status();
            $success = $response->successful();

            Log::info('IndexNow submission completed', [
                'status' => $status,
                'count' => count($urls),
            ]);

            return [
                'success' => $success,
                'status' => $status,
                'count' => count($urls),
                'urls' => $urls,
            ];
        } catch (\Throwable $e) {
            Log::warning('IndexNow submission failed: ' . $e->getMessage(), [
                'count' => count($urls),
            ]);

            return [
                'success' => false,
                'status' => 500,
                'count' => count($urls),
                'urls' => $urls,
            ];
        }
    }

    /**
     * @return array<int, string>
     */
    public function collectRecentUrls(): array
    {
        $recipes = Recipe::query()
            ->where('status', 'published')
            ->where('verification_status', 'verified')
            ->latest('updated_at')
            ->limit(50)
            ->get();

        $blogs = BlogPost::query()
            ->publiclyVisible()
            ->latest('updated_at')
            ->limit(20)
            ->get();

        $urls = [
            route('home'),
            route('recipes.index'),
            route('blog.index'),
        ];

        foreach ($recipes as $recipe) {
            $urls[] = route('recipes.show', $recipe);
        }

        foreach ($blogs as $blog) {
            $urls[] = route('blog.show', $blog);
        }

        return $urls;
    }
}
