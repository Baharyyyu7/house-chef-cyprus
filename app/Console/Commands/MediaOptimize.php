<?php

declare(strict_types=1);

namespace App\Console\Commands;

use App\Services\Media\MediaOptimizationService;
use Illuminate\Console\Command;

final class MediaOptimize extends Command
{
    protected $signature = 'media:optimize {--limit=10 : Shared hosting batch limiti} {--dry-run : Dosya yazma} {--json : Makine okunabilir çıktı}';

    protected $description = 'Yalnız onaylı public local medya için idempotent responsive varyasyon üretir';

    public function handle(MediaOptimizationService $optimizer): int
    {
        $result = $this->option('dry-run') ? ['processed' => 0, 'skipped' => 0, 'failed' => 0, 'dry_run' => true] : $optimizer->optimize(max(1, min(25, (int) $this->option('limit'))));
        $this->option('json') ? $this->line((string) json_encode($result, JSON_UNESCAPED_UNICODE)) : $this->info('Media optimize · işlenen: '.$result['processed'].' · atlanan: '.$result['skipped'].' · başarısız: '.$result['failed']);

        return self::SUCCESS;
    }
}
