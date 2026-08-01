<?php

declare(strict_types=1);

namespace App\Console\Commands;

use App\Services\Media\MediaExcellenceService;
use Illuminate\Console\Command;

final class MediaHealth extends Command
{
    protected $signature = 'media:health {--json : Makine okunabilir çıktı}';

    protected $description = 'Gerçek medya kapsama ve güven zinciri sağlık raporu';

    public function handle(MediaExcellenceService $media): int
    {
        $report = $media->health();
        $this->option('json') ? $this->line((string) json_encode($report, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES)) : $this->info('Media health: '.$report['score'].'/100 · onaylı gerçek medya: '.$report['approved_real_media'].' · private leakage: '.$report['private_leakage']);

        return self::SUCCESS;
    }
}
