<?php

declare(strict_types=1);

namespace App\Console\Commands;

use App\Services\Integrity\IntegrityReportLog;
use App\Services\Integrity\SchemaContractProbe;
use Illuminate\Console\Command;

final class DatabaseSchemaContractCheck extends Command
{
    protected $signature = 'db:schema-contract
        {--source=* : Taranacak uygulama kaynak dizini}
        {--migrations=* : Migration dizini}
        {--schema-json= : Karşılaştırılacak MySQL şema snapshot dosyası}
        {--json : Makine okunabilir çıktı}';

    protected $description = 'Dar veritabanı sütunlarının yazma noktalarını ve şema farklarını gözlemsel olarak raporlar.';

    public function handle(SchemaContractProbe $probe, IntegrityReportLog $log): int
    {
        $sources = $this->option('source') ?: [app_path()];
        $migrations = $this->option('migrations') ?: [database_path('migrations')];
        $snapshot = $this->snapshot((string) ($this->option('schema-json') ?? ''));
        $report = $probe->inspect($sources, $migrations, $snapshot);
        // Panel manşeti yalnız kanıtlanabilir sabit-değer taşmalarını sayar.
        // Şema sapması ayrı payload alanıdır; dinamik yazım ise bulgu değildir.
        $headline = (int) $report['finding_count'];

        $log->record('db:schema-contract', (string) $report['status'], $headline, $report);

        if ($this->option('json')) {
            $this->line((string) json_encode($report, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES));
        } else {
            $this->info("{$report['narrow_columns']} dar sütun, {$report['writer_count']} çözümlenmiş yazma noktası, {$headline} kanıtlanmış taşma, {$report['schema_drift_count']} şema sapması.");
        }

        // Gözlem komutu deploy kapısı değildir.
        return self::SUCCESS;
    }

    /** @return list<array{table:string,column:string,type:string,max_length:?int}> */
    private function snapshot(string $path): array
    {
        if ($path === '' || ! is_file($path)) {
            return [];
        }

        $decoded = json_decode((string) file_get_contents($path), true);

        return is_array($decoded) ? array_values($decoded) : [];
    }
}
