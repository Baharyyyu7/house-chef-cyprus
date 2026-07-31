<?php

declare(strict_types=1);

namespace App\Console\Commands;

use App\Services\Seo\SeoAuditService;
use Illuminate\Console\Command;

final class SeoAudit extends Command
{
    protected $signature = 'seo:audit {--scope=all : all|technical|content|schema} {--json : Makine okunabilir çıktı}';

    protected $description = 'Teknik SEO, içerik ve schema kurallarını güvenli biçimde tarar.';

    public function handle(SeoAuditService $service): int
    {
        $scope = (string) $this->option('scope');
        if (! in_array($scope, ['all', 'technical', 'content', 'schema'], true)) {
            $this->error('Geçersiz scope.');

            return self::INVALID;
        }
        $result = $service->run($scope);
        if ($this->option('json')) {
            $this->line((string) json_encode($result, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES));
        } else {
            $this->info("SEO skor {$result['score']} | yeni {$result['created']} | güncellenen {$result['updated']} | çözülen {$result['resolved']} | açık {$result['open']}");
        }

        return self::SUCCESS;
    }
}
