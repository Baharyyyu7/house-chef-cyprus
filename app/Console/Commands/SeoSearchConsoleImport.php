<?php

declare(strict_types=1);

namespace App\Console\Commands;

use App\Services\Seo\SearchConsoleMeasurementService;
use Illuminate\Console\Command;

final class SeoSearchConsoleImport extends Command
{
    protected $signature = 'seo:search-console-import {--file= : Search Console arayüzünden okunmuş JSON raporu} {--json}';

    protected $description = 'Doğrulanmış 28 günlük Search Console ölçümünü JSON veya yetkili Google API üzerinden alır.';

    public function handle(SearchConsoleMeasurementService $service): int
    {
        if ($file = $this->option('file')) {
            if (! is_file($file) || ! is_readable($file) || filesize($file) > 1048576 || strtolower(pathinfo($file, PATHINFO_EXTENSION)) !== 'json') {
                $this->error('Okunabilir, en fazla 1 MB JSON raporu gerekli.');

                return self::FAILURE;
            }
            try {
                $payload = $service->import(json_decode(file_get_contents($file), true, 32, JSON_THROW_ON_ERROR));
            } catch (\Throwable) {
                $this->error('Ölçüm doğrulanamadı; mülk, 28 günlük dönem ve toplamları kontrol edin.');

                return self::FAILURE;
            }
        } else {
            $payload = $service->importApi();
        }
        $this->line((string) json_encode($payload, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES));

        return $payload['status'] === 'failed' ? self::FAILURE : self::SUCCESS;
    }
}
