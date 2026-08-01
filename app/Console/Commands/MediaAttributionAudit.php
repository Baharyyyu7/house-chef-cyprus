<?php

declare(strict_types=1);

namespace App\Console\Commands;

use App\Services\Media\MediaExcellenceService;
use Illuminate\Console\Command;

final class MediaAttributionAudit extends Command
{
    protected $signature = 'media:attribution-audit {--limit=100 : Güvenli batch sınırı} {--dry-run : Kayıt yazma} {--json : Makine okunabilir çıktı}';

    protected $description = 'Attribution gereksinimlerini denetler; görünür attribution eklemez';

    public function handle(MediaExcellenceService $media): int
    {
        $result = $this->option('dry-run') ? ['scanned' => 0, 'missing' => 0, 'dry_run' => true] : $media->auditAttribution(max(1, min(100, (int) $this->option('limit'))));
        $this->option('json') ? $this->line((string) json_encode($result, JSON_UNESCAPED_UNICODE)) : $this->info('Attribution audit · taranan: '.$result['scanned'].' · eksik: '.$result['missing']);

        return self::SUCCESS;
    }
}
