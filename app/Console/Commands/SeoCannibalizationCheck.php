<?php

declare(strict_types=1);

namespace App\Console\Commands;

use App\Services\Seo\CannibalizationCheckService;
use Illuminate\Console\Command;

final class SeoCannibalizationCheck extends Command
{
    protected $signature = 'seo:cannibalization-check {--json}';

    protected $description = 'Aynı arama niyeti için yarışan sayfaları tespit eder (salt-okunur, editoryal onay bekler).';

    public function handle(CannibalizationCheckService $service): int
    {
        $result = $service->detect();
        $payload = $result + ['auto_publish' => false];

        if ($this->option('json')) {
            $this->line((string) json_encode($payload, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES));

            return self::SUCCESS;
        }

        if ($result['detected'] === 0) {
            $this->info('Yarışan sayfa bulunamadı.');

            return self::SUCCESS;
        }

        $this->info(sprintf('Kannibalizasyon adayı: %d', $result['detected']));

        foreach ($result['candidates'] as $candidate) {
            $this->newLine();
            $this->warn(sprintf('[%s] %s (%d sayfa)', strtoupper($candidate['severity']), $candidate['keyword'], $candidate['page_count']));
            foreach ($candidate['pages'] as $page) {
                $this->line(sprintf('  - %s [%s] %s', $page['type'], $page['status'], $page['url']));
            }
        }

        return self::SUCCESS;
    }
}
