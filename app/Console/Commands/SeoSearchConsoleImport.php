<?php

declare(strict_types=1);

namespace App\Console\Commands;

use Illuminate\Console\Command;

final class SeoSearchConsoleImport extends Command
{
    protected $signature = 'seo:search-console-import {--json}';

    protected $description = 'Search Console entegrasyon durumunu kontrol eder; yetkilendirme yoksa dış çağrı yapmaz.';

    public function handle(): int
    {
        $enabled = (bool) config('seo.search_console.enabled');
        $payload = ['provider' => 'search_console', 'enabled' => $enabled, 'imported' => 0, 'reason' => $enabled ? 'oauth_authorization_required' : 'provider_disabled'];
        $this->option('json') ? $this->line((string) json_encode($payload, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES)) : $this->warn('Search Console içe aktarma kapalı; hiçbir dış çağrı yapılmadı.');

        return self::SUCCESS;
    }
}
