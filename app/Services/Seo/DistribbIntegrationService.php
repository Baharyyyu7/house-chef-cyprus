<?php

declare(strict_types=1);

namespace App\Services\Seo;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class DistribbIntegrationService
{
    private const API_BASE = 'https://api.distribb.io/v1';

    private string $apiKey;

    private string $baseUrl;

    public function __construct(string $apiKey, string $baseUrl = self::API_BASE)
    {
        $this->apiKey = $apiKey;
        $this->baseUrl = $baseUrl;
    }

    public static function isConfigured(): bool
    {
        return filled(config('seo.distribb.api_key')) && config('seo.distribb.enabled');
    }

    public function get(string $endpoint, array $params = []): array|false
    {
        if (! self::isConfigured()) {
            return false;
        }

        try {
            $response = Http::withToken($this->apiKey)
                ->baseUrl($this->baseUrl)
                ->timeout(15)
                ->get($endpoint, $params);

            return $response->successful() ? $response->json() : false;
        } catch (\Exception $e) {
            Log::warning('Distribb API error', ['endpoint' => $endpoint, 'error' => $e->getMessage()]);

            return false;
        }
    }

    public function post(string $endpoint, array $data = []): array|false
    {
        if (! self::isConfigured()) {
            return false;
        }

        try {
            $response = Http::withToken($this->apiKey)
                ->baseUrl($this->baseUrl)
                ->timeout(30)
                ->post($endpoint, $data);

            return $response->successful() ? $response->json() : false;
        } catch (\Exception $e) {
            Log::warning('Distribb API error', ['endpoint' => $endpoint, 'error' => $e->getMessage()]);

            return false;
        }
    }

    public function getProjects(): array|false
    {
        return $this->get('/projects');
    }

    public function getBusinessContext(): array|false
    {
        return $this->get('/business-context');
    }

    public function getIntegrations(): array|false
    {
        return $this->get('/integrations');
    }

    public function getSearchConsole(): array|false
    {
        return $this->get('/search-console');
    }

    public function runGscAudit(string $domain): array|false
    {
        return $this->post('/gsc-audit', ['domain' => $domain]);
    }

    public function keywordResearch(string $seed): array|false
    {
        return $this->get('/keywords/search', ['seed' => $seed]);
    }

    public function writeArticle(string $keyword, int $projectId): array|false
    {
        return $this->post('/articles/create', ['keyword' => $keyword, 'project_id' => $projectId]);
    }

    public function getBacklinks(): array|false
    {
        return $this->get('/backlinks');
    }

    public function getContentCalendar(): array|false
    {
        return $this->get('/content-calendar');
    }

    public function optimize(): array|false
    {
        return $this->post('/optimize');
    }

    public function getAiVisibility(): array|false
    {
        return $this->get('/ai-visibility');
    }

    public function getStatus(): array
    {
        $gsc = $this->getSearchConsole();
        $integrations = $this->getIntegrations();

        return [
            'api_configured' => self::isConfigured(),
            'enabled' => config('seo.distribb.enabled'),
            'projects' => $this->getProjects(),
            'gsc_connected' => $gsc ? ($gsc['connected'] ?? null) : null,
            'cms_connected' => $integrations ? $integrations->isNotEmpty() : null,
        ];
    }
}
