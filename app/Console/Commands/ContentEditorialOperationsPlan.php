<?php

declare(strict_types=1);

namespace App\Console\Commands;

use App\Services\EditorialOperationsService;
use Illuminate\Console\Command;

final class ContentEditorialOperationsPlan extends Command
{
    protected $signature = 'content:editorial-operations-plan {--json : Machine-readable output}';

    protected $description = 'Refresh draft-only editorial review batches, source queues and review packs.';

    public function handle(EditorialOperationsService $operations): int
    {
        $result = $operations->refresh() + ['auto_publish' => false, 'human_approval_required' => true, 'generated_at' => now()->toIso8601String()];
        if ($this->option('json')) {
            $this->line((string) json_encode($result, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES));

            return self::SUCCESS;
        }
        foreach ($result as $key => $value) {
            $this->line($key.': '.(is_bool($value) ? ($value ? 'true' : 'false') : $value));
        }

        return self::SUCCESS;
    }
}
