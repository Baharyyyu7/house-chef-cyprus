<?php

declare(strict_types=1);

namespace App\Console\Commands;

use App\Services\Media\MediaExcellenceService;
use Illuminate\Console\Command;

final class MediaDuplicateScan extends Command
{
    protected $signature = 'media:duplicate-scan {--limit=100 : Güvenli batch sınırı} {--dry-run : Kayıt yazma} {--json : Makine okunabilir çıktı}';

    protected $description = 'Checksum tabanlı duplicate medya gruplarını denetler';

    public function handle(MediaExcellenceService $media): int
    {
        $limit = max(1, min((int) config('media_excellence.batch_limit'), (int) $this->option('limit')));
        $result = $this->option('dry-run') ? ['scanned' => 0, 'groups' => $media->duplicateGroups()->count(), 'dry_run' => true] : $media->scanDuplicates($limit);
        $this->option('json') ? $this->line((string) json_encode($result, JSON_UNESCAPED_UNICODE)) : $this->info('Duplicate scan · grup: '.$result['groups']);

        return self::SUCCESS;
    }
}
