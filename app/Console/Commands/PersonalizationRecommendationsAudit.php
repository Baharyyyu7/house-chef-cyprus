<?php

declare(strict_types=1);

namespace App\Console\Commands;

final class PersonalizationRecommendationsAudit extends PersonalizationScopedAudit
{
    protected $signature = 'personalization:recommendations-audit {--json} {--dry-run} {--limit=100}';

    protected $description = 'Audit personalized recommendation safety and explanations.';

    protected function scope(): string
    {
        return 'recommendations';
    }
}
