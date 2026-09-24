<?php

declare(strict_types=1);

namespace App\Console\Commands;

use App\Services\Monetization\RevenueOperationsReport;
use Illuminate\Console\Command;

final class MonetizationRevenueReport extends Command
{
    protected $signature = 'monetization:revenue-report {--json : Makine okunabilir çıktı verir}';

    protected $description = 'Doğrulanmış gelir durumunu, MRR ve ödeme risklerini hiçbir veri değiştirmeden raporlar.';

    public function handle(RevenueOperationsReport $report): int
    {
        $result = $report->build();

        if ($this->option('json')) {
            $this->line((string) json_encode($result, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES));

            return self::SUCCESS;
        }

        $rows = collect($result['currencies'])->map(function (array $metrics, string $currency): array {
            return [
                $currency,
                $this->money($metrics['recognized_one_time_minor'], $currency),
                $this->money($metrics['active_subscription_mrr_minor'], $currency),
                $this->money($metrics['at_risk_one_time_minor'], $currency),
                $this->money($metrics['reversed_one_time_minor'], $currency),
            ];
        })->values()->all();

        $this->table(['Para', 'Tek seferlik net', 'Aktif MRR', 'Riskte', 'İade/kayıp'], $rows);
        $this->line((string) $result['accounting_note']);

        return self::SUCCESS;
    }

    private function money(int $minor, string $currency): string
    {
        return number_format($minor / 100, 2, ',', '.').' '.$currency;
    }
}
