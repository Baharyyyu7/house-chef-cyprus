<?php

declare(strict_types=1);

namespace App\Console\Commands;

final class PersonalizationCacheAudit extends PersonalizationScopedAudit
{
    protected $signature = 'personalization:cache-audit {--json} {--dry-run} {--limit=100}';

    protected $description = 'Audit personalization cache health.';

    protected function scope(): string
    {
        return 'cache';
    }
}
