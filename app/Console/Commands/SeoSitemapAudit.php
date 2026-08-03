<?php

declare(strict_types=1);

namespace App\Console\Commands;

use App\Services\Seo\SitemapAuditService;
use Illuminate\Console\Command;

final class SeoSitemapAudit extends Command
{
    protected $signature = 'seo:sitemap-audit {--json : Makine okunabilir çıktı verir}';

    protected $description = 'Sitemap kaynaklarının taslak, zamanlanmış veya onaysız içeriği güvenle dışladığını denetler.';

    public function handle(SitemapAuditService $service): int
    {
        $result = $service->audit();
        $this->option('json') ? $this->line((string) json_encode($result, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES)) : $this->info($result['valid'] ? 'Sitemap denetimi başarılı.' : 'Sitemap denetiminde engelleyici kayıt bulundu.');

        return $result['valid'] ? self::SUCCESS : self::FAILURE;
    }
}
