<?php

declare(strict_types=1);

namespace App\Console\Commands;

use App\Services\Seo\GrowthSystem;
use Illuminate\Console\Command;

final class SeoSystemDispatch extends Command
{
    protected $signature = 'seo:system-dispatch';
    protected $description = 'Queues the application-owned growth workflow once per day.';

    public function handle(GrowthSystem $system): int
    {
        $this->line((string) json_encode(['queued' => $system->dispatch()]));
        return self::SUCCESS;
    }
}
