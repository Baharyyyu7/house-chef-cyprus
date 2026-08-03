<?php

declare(strict_types=1);

namespace App\Console\Commands;

use App\Services\CatalogueReviewReadinessService;
use Illuminate\Console\Command;

final class CatalogueReviewReadiness extends Command
{
    protected $signature = 'content:catalogue-review-readiness {--json : Makine okunabilir çıktı}';

    protected $description = 'Tarif kataloğundaki private inceleme, kimlik blokajı ve medya gerçekliği durumunu salt-okunur raporlar.';

    public function handle(CatalogueReviewReadinessService $readiness): int
    {
        $report = $readiness->report();

        if ($this->option('json')) {
            $this->line((string) json_encode($report, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES));

            return self::SUCCESS;
        }

        $this->table(['Kapsam', 'Toplam', 'Private kimlik incelemesi', 'Açık konu', 'High risk'], [
            ['Yayınlanan', data_get($report, 'published.total'), data_get($report, 'published.private_identity_reviews'), data_get($report, 'published.open_issues'), data_get($report, 'published.high_risk_issues')],
            ['Taslak', data_get($report, 'draft.total'), data_get($report, 'draft.private_identity_reviews'), data_get($report, 'draft.open_issues'), data_get($report, 'draft.high_risk_issues')],
        ]);
        $this->warn('Rapor salt-okunurdur; hiçbir tarif, görsel, yayın veya doğrulama durumu değiştirilmedi.');

        return self::SUCCESS;
    }
}
