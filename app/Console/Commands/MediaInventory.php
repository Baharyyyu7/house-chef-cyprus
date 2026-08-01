<?php

declare(strict_types=1);

namespace App\Console\Commands;

use App\Services\Media\MediaExcellenceService;
use Illuminate\Console\Command;

final class MediaInventory extends Command
{
    protected $signature = 'media:inventory {--limit=0 : Her içerik türü için en fazla kayıt} {--json : Makine okunabilir çıktı}';

    protected $description = 'Tarif ve blog medya envanterini gerçek kayıtlarla raporlar';

    public function handle(MediaExcellenceService $media): int
    {
        $report = $media->inventory(max(0, (int) $this->option('limit')));
        if ($this->option('json')) {
            $this->line((string) json_encode($report, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES));

            return self::SUCCESS;
        }
        $this->info('Media inventory · tarif: '.count($report['recipes']).' · blog: '.count($report['blogs']));
        $this->table(['Tür', 'Durum', 'Adet'], collect($report['summary'])->flatMap(fn (array $statuses, string $type) => collect($statuses)->map(fn (int $count, string $status) => [$type, $status, $count]))->values()->all());

        return self::SUCCESS;
    }
}
