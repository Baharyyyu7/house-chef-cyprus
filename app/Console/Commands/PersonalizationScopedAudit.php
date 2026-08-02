<?php

declare(strict_types=1);

namespace App\Console\Commands;

use App\Services\Personalization\PersonalizationOperationsService;
use Illuminate\Console\Command;

abstract class PersonalizationScopedAudit extends Command
{
    abstract protected function scope(): string;

    protected function isMorningBrief(): bool
    {
        return false;
    }

    public function handle(PersonalizationOperationsService $operations): int
    {
        $data = $operations->audit($this->scope());
        if ($this->isMorningBrief()) {
            $data['brief'] = $operations->report();
        }
        $data['dry_run'] = (bool) $this->option('dry-run');
        $data['batch_limit'] = min(1000, max(1, (int) $this->option('limit')));
        if ($this->option('json')) {
            $this->line((string) json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
        } else {
            $this->table(['Kontrol', 'Durum'], collect($data['checks'])->map(fn ($value, $key): array => [$key, is_bool($value) ? ($value ? 'evet' : 'hayır') : (string) $value])->all());
            $this->info($data['ok'] ? 'Kişiselleştirme denetimi geçti.' : 'Kişiselleştirme denetimi inceleme gerektiriyor.');
        }

        return $data['ok'] ? self::SUCCESS : self::FAILURE;
    }
}
