<?php

declare(strict_types=1);

namespace App\Console\Commands;

use App\Services\Coni\ClaudeConi;
use Illuminate\Console\Command;

final class ConiGeriAl extends Command
{
    protected $signature = 'coni:geri-al {id : Defterdeki iş numarası}';

    protected $description = 'Claude Coni: bir değişikliği eski haline döndürür ve defterde geri alındı diye işaretler.';

    public function handle(ClaudeConi $coni): int
    {
        $is = $coni->geriAl((int) $this->argument('id'));
        $this->components->info(sprintf('#%d %s %s geri alındı.', $is->id, strtoupper($is->locale), $is->page));

        return self::SUCCESS;
    }
}
