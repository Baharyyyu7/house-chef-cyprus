<?php

declare(strict_types=1);

namespace App\Console\Commands;

final class PersonalizationPrivacyAudit extends PersonalizationScopedAudit
{
    protected $signature = 'personalization:privacy-audit {--json} {--dry-run} {--limit=100}';

    protected $description = 'Audit personalization privacy controls.';

    protected function scope(): string
    {
        return 'privacy';
    }
}
