<?php

declare(strict_types=1);

namespace App\Console\Commands;

final class PersonalizationSafetyAudit extends PersonalizationScopedAudit
{
    protected $signature = 'personalization:safety-audit {--json} {--dry-run} {--limit=100}';

    protected $description = 'Audit personalization safety constraints.';

    protected function scope(): string
    {
        return 'safety';
    }
}
