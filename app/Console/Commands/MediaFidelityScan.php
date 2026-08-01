<?php

declare(strict_types=1);

namespace App\Console\Commands;

use App\Services\Media\MediaExcellenceService;
use Illuminate\Console\Command;

final class MediaFidelityScan extends Command
{
    protected $signature = 'media:fidelity-scan {--limit=25 : Güvenli batch sınırı} {--dry-run : Kayıt yazma} {--json : Makine okunabilir çıktı}';

    protected $description = 'Adayları yalnız karar desteği için fidelity kurallarıyla tarar; public etmez';

    public function handle(MediaExcellenceService $media): int
    {
        $limit = max(1, min((int) config('media_excellence.batch_limit'), (int) $this->option('limit')));
        $result = $this->option('dry-run') ? ['scanned' => 0, 'updated' => 0, 'blocked' => 0, 'dry_run' => true] : $media->scanFidelity($limit);
        if ($this->option('json')) {
            $this->line((string) json_encode($result, JSON_UNESCAPED_UNICODE));
        } else {
            $this->info('Fidelity scan · taranan: '.$result['scanned'].' · güncellenen: '.$result['updated'].' · insan incelemesi gereken: '.$result['blocked']);
        }

        return self::SUCCESS;
    }
}
