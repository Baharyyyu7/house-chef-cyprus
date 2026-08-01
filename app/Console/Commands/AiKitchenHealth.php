<?php

declare(strict_types=1);

namespace App\Console\Commands;

use App\Services\AI\AiKitchenService;
use Illuminate\Console\Command;

final class AiKitchenHealth extends Command
{
    protected $signature = 'ai-kitchen:health {--json : Makine okunabilir çıktı}';

    protected $description = 'AI Kitchen sağlayıcı sağlığını secret göstermeden raporlar';

    public function handle(AiKitchenService $kitchen): int
    {
        $result = $kitchen->stats();
        if ($this->option('json')) {
            $this->line((string) json_encode(['providers' => $result['providers'], 'requests' => $result['requests'], 'failures' => $result['failures']], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES));

            return self::SUCCESS;
        }
        $this->table(['Provider', 'Durum', 'Model', 'Faturalandırma'], collect($result['providers'])->map(fn (array $row) => [$row['provider'], $row['status'], $row['model'] ?: '—', $row['billable'] ? 'olası' : 'hayır'])->all());

        return self::SUCCESS;
    }
}
