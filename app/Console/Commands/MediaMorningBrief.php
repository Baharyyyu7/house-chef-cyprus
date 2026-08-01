<?php

declare(strict_types=1);

namespace App\Console\Commands;

use App\Services\Media\MediaExcellenceService;
use Illuminate\Console\Command;

final class MediaMorningBrief extends Command
{
    protected $signature = 'media:morning-brief {--json : Makine okunabilir çıktı}';

    protected $description = 'Medya operasyonları için gerçek, güvenli sabah özeti';

    public function handle(MediaExcellenceService $media): int
    {
        $report = $media->morningBrief();
        $this->option('json') ? $this->line((string) json_encode($report, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES)) : $this->info('Morning media brief · skor: '.$report['score'].'/100 · öncelik: '.count($report['top_priority']));

        return self::SUCCESS;
    }
}
