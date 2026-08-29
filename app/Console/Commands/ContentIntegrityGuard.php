<?php

declare(strict_types=1);

namespace App\Console\Commands;

use App\Services\Integrity\PublishedContentGuard;
use Illuminate\Console\Command;

/**
 * Yayındaki içeriği bilinen bozulma desenlerine karşı tarar.
 *
 * Varsayılan davranış yalnız rapor. --enforce verildiğinde açıkça bozuk
 * tarifler yayından alınır (silinmez); blog tarafı editoryal karar
 * olduğu için hiçbir zaman otomatik alınmaz.
 */
final class ContentIntegrityGuard extends Command
{
    protected $signature = 'content:integrity-guard {--enforce : Açıkça bozuk tarifleri yayından al} {--json : Makine okunabilir çıktı}';

    protected $description = 'Yayındaki tarif ve blog içeriğini bilinen bozulma desenlerine karşı tarar.';

    public function handle(PublishedContentGuard $guard): int
    {
        $rapor = $guard->scan((bool) $this->option('enforce'));

        if ($this->option('json')) {
            $this->line((string) json_encode($rapor, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES));

            return self::SUCCESS;
        }

        if ($rapor['status'] === 'clean') {
            $this->info("Yayındaki {$rapor['published_recipes']} tarif temiz.");

            return self::SUCCESS;
        }

        $this->warn("Toplam {$rapor['total']} bulgu:");

        foreach ($rapor['findings'] as $tur => $slugler) {
            if ($slugler !== []) {
                $this->line('  '.$tur.': '.count($slugler));
            }
        }

        if ($rapor['withdrawn'] !== []) {
            $this->line('Yayından alınan: '.count($rapor['withdrawn']));
        }

        return self::SUCCESS;
    }
}
