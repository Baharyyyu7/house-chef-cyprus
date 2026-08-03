<?php

declare(strict_types=1);

namespace App\Console\Commands;

use App\Services\Seo\SitemapAuditService;
use Illuminate\Console\Command;

final class SeoSitemapBuild extends Command
{
    protected $signature = 'seo:sitemap-build {--json : Makine okunabilir çıktı verir}';

    protected $description = 'Dinamik sitemap kaynaklarını doğrular; URL listesi HTTP isteğinde güncel üretilir.';

    public function handle(SitemapAuditService $service): int
    {
        $result = $service->audit() + ['storage' => 'dynamic_http_sitemap'];
        $this->option('json') ? $this->line((string) json_encode($result, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES)) : $this->info('Sitemap kaynakları doğrulandı.');

        return $result['valid'] ? self::SUCCESS : self::FAILURE;
    }
}
