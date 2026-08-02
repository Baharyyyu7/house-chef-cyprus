<?php

declare(strict_types=1);

namespace App\Console\Commands;

use App\Services\Personalization\PersonalizationOperationsService;
use Illuminate\Console\Command;

final class PersonalizationAudit extends Command
{
    protected $signature = 'personalization:health {--json} {--dry-run} {--limit=100}';

    protected $description = 'Run a privacy-safe personalization operational audit.';

    /** @var list<string> */
    protected $aliases = [
        'personalization:profile-audit',
        'personalization:recommendations-audit',
        'personalization:safety-audit',
        'personalization:cache-audit',
        'personalization:privacy-audit',
        'personalization:morning-brief',
    ];

    public function handle(PersonalizationOperationsService $operations): int
    {
        $scope = str_replace('personalization:', '', (string) $this->input->getFirstArgument());
        $map = ['profile-audit' => 'health', 'recommendations-audit' => 'recommendations', 'safety-audit' => 'safety', 'cache-audit' => 'cache', 'privacy-audit' => 'privacy', 'health' => 'health', 'morning-brief' => 'health'];
        $scope = array_key_exists($scope, $map) ? $scope : 'health';
        $data = $operations->audit($map[$scope]);
        if ($scope === 'morning-brief') {
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
