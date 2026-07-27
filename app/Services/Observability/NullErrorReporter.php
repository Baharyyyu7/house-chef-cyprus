<?php

declare(strict_types=1);

namespace App\Services\Observability;

use App\Contracts\Observability\ErrorReporterInterface;
use Throwable;

final class NullErrorReporter implements ErrorReporterInterface
{
    public function report(Throwable $exception, array $context = []): void {}
}
