<?php

declare(strict_types=1);

namespace App\Console\Commands;

use App\Services\Seo\SeoProviderHealthService;
use Illuminate\Console\Command;

final class SeoProviderHealth extends Command
{
    protected $signature = 'seo:provider-health {--json}';

    protected $description = 'SEO sağlayıcılarını secret göstermeden raporlar.';

    public function handle(SeoProviderHealthService $service): int
    {
        $result = $service->snapshot();
        $this->option('json') ? $this->line((string) json_encode($result, JSON_UNESCAPED_UNICODE)) : collect($result)->each(fn (array $provider, string $name) => $this->line("{$name}: {$provider['safe_message']}"));

        return self::SUCCESS;
    }
}
