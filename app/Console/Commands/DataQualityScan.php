<?php

namespace App\Console\Commands;

use App\Services\DataQualityService;
use Illuminate\Console\Command;

final class DataQualityScan extends Command
{
    protected $signature = 'data:quality-scan';

    protected $description = 'Veri kalite sorunlarını kaydeder';

    public function handle(DataQualityService $quality): int
    {
        $result = $quality->scan();
        $this->info("Skor {$result['score']} | yeni {$result['new']} | güncellenen {$result['updated']} | çözülen {$result['resolved']}");

        return self::SUCCESS;
    }
}
