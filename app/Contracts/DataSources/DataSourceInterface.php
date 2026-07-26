<?php

declare(strict_types=1);

namespace App\Contracts\DataSources;

interface DataSourceInterface
{
    public function name(): string;

    /** @return iterable<array<string, mixed>> */
    public function fetch(): iterable;
}
