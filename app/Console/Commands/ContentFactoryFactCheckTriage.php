<?php

declare(strict_types=1);

namespace App\Console\Commands;

use App\Services\ContentFactoryFactCheckService;
use Illuminate\Console\Command;

final class ContentFactoryFactCheckTriage extends Command
{
    protected $signature = 'content:fact-check-triage {--json : Machine-readable output}';

    protected $description = 'Classify fact-check issues without approving, verifying, or publishing content.';

    public function handle(ContentFactoryFactCheckService $factChecks): int
    {
        $result = $factChecks->triage();
        if ($this->option('json')) {
            $this->line((string) json_encode($result, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES));

            return self::SUCCESS;
        }
        foreach ($result as $key => $value) {
            $this->line($key.': '.$value);
        }

        return self::SUCCESS;
    }
}
