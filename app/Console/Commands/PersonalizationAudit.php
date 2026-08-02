<?php

declare(strict_types=1);

namespace App\Console\Commands;

final class PersonalizationAudit extends PersonalizationScopedAudit
{
    protected $signature = 'personalization:health {--json} {--dry-run} {--limit=100}';

    protected $description = 'Run a privacy-safe personalization operational audit.';

    protected function scope(): string
    {
        return 'health';
    }
}
