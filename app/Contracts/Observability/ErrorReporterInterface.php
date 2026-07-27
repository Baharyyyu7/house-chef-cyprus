<?php

declare(strict_types=1);

namespace App\Contracts\Observability;

use Throwable;

interface ErrorReporterInterface
{
    /** @param array<string, mixed> $context */
    public function report(Throwable $exception, array $context = []): void;
}
