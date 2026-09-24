<?php

declare(strict_types=1);

namespace App\Console\Commands;

use App\Services\Coni\ClaudeConi;
use Illuminate\Console\Command;

/**
 * Claude Coni'nin defteri: aktif işler, sonuçlananlar, karne ve son
 * koşunun dil dil raporu. Claude oturumu güne bununla başlar.
 */
final class ConiDurum extends Command
{
    protected $signature = 'coni:durum {--json}';

    protected $description = 'Claude Coni: aktif işler, sonuçlar ve son koşunun dil dil raporu.';

    public function handle(ClaudeConi $coni): int
    {
        $durum = $coni->durum();

        if ($this->option('json')) {
            $this->line((string) json_encode($durum, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT));

            return self::SUCCESS;
        }

        $this->components->info('Son koşu: '.($durum['rapor_zamani'] ?? 'hiç koşmadı'));
        $this->line('  Karne: '.json_encode($durum['karne'], JSON_UNESCAPED_UNICODE));

        foreach ($durum['aktif'] as $is) {
            $this->line(sprintf('  #%d %s %-4s %s (ölçüm %s)', $is['id'], strtoupper($is['locale']), $is['kind'], $is['page'], $is['degerlendirme']));
        }

        foreach ($durum['sonuclanan'] as $is) {
            $this->line(sprintf('  ✓ #%d %s %-4s %s → %s', $is['id'], strtoupper($is['locale']), $is['kind'], $is['page'], $is['status']));
        }

        return self::SUCCESS;
    }
}
