<?php

declare(strict_types=1);

namespace App\Exceptions;

use RuntimeException;

final class BackupException extends RuntimeException
{
    public function __construct(string $safeCode, public readonly ?string $safeDiagnostic = null)
    {
        parent::__construct($safeCode);
    }
}
