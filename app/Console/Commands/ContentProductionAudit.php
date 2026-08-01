<?php

declare(strict_types=1);

namespace App\Console\Commands;

use App\Services\ProductionContentReadinessService;
use Illuminate\Console\Command;

final class ContentProductionAudit extends Command
{
    protected $signature = 'content:production-audit {--json : Makine tarafından okunabilir sonuç üret}';

    protected $description = 'Tarif ve blog kalite profillerini gerçek production kurallarıyla yeniden hesaplar.';

    public function handle(ProductionContentReadinessService $readiness): int
    {
        $result = $readiness->scan();
        $result['generated_at'] = now()->toIso8601String();
        $result['auto_publish'] = false;
        $result['human_approval_required'] = true;

        if ($this->option('json')) {
            $this->line((string) json_encode($result, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES));
        } else {
            $this->table(['Tarif', 'Blog', 'Bloklu', 'İnsan onayı bekleyen'], [[
                $result['recipes'], $result['blogs'], $result['blocked'], $result['ready_for_human_review'],
            ]]);
            $this->info('Otomatik yayın yapılmadı; sonuçlar yalnız kalite profillerine kaydedildi.');
        }

        return self::SUCCESS;
    }
}
