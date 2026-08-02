<?php

declare(strict_types=1);

namespace App\Console\Commands;

final class PersonalizationProfileAudit extends PersonalizationScopedAudit
{
    protected $signature = 'personalization:profile-audit {--json} {--dry-run} {--limit=100}';

    protected $description = 'Audit personalization profile operations.';

    protected function scope(): string
    {
        return 'health';
    }
}
