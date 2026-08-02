<?php

declare(strict_types=1);

namespace App\Console\Commands;

final class PersonalizationMorningBrief extends PersonalizationScopedAudit
{
    protected $signature = 'personalization:morning-brief {--json} {--dry-run} {--limit=100}';

    protected $description = 'Generate the privacy-safe personalization morning brief.';

    protected function scope(): string
    {
        return 'health';
    }

    protected function isMorningBrief(): bool
    {
        return true;
    }
}
