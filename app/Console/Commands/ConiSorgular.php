<?php

declare(strict_types=1);

namespace App\Console\Commands;

use App\Services\Seo\SearchConsoleMeasurementService;
use Illuminate\Console\Command;
use Throwable;

/**
 * Bir sayfanın göründüğü aramalar. Arama sonucu metni yazılmadan önce
 * okunur: başlık, sayfanın gerçekten çıktığı soruya cevap vermeli.
 */
final class ConiSorgular extends Command
{
    protected $signature = 'coni:sorgular {adres : Sayfanın tam adresi} {--gun=28} {--json}';

    protected $description = 'Claude Coni: bir sayfanın Search Console\'da göründüğü sorgular.';

    public function handle(SearchConsoleMeasurementService $gsc): int
    {
        try {
            $satirlar = $gsc->pageQueries((string) $this->argument('adres'), max(1, (int) $this->option('gun')));
        } catch (Throwable) {
            $this->components->error('Search Console verisi alınamadı.');

            return self::FAILURE;
        }

        if ($this->option('json')) {
            $this->line((string) json_encode($satirlar, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES));

            return self::SUCCESS;
        }

        foreach ($satirlar as $s) {
            $this->line(sprintf('  %5d gös  %3d tık  sıra %5.1f  %s', $s['impressions'], $s['clicks'], $s['position'], $s['query']));
        }

        return self::SUCCESS;
    }
}
