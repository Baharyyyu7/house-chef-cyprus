<?php

declare(strict_types=1);

namespace App\Console\Commands;

use App\Services\Queue\BoundedQueueDrainer;
use Illuminate\Console\Command;

/**
 * BoundedQueueDrainer'ın ince konsol sarmalayıcısı.
 *
 * Asıl mantık serviste duruyor çünkü tetikleme ucu bir HTTP isteği ve o
 * bağlamda Artisan komutları güvenilir biçimde kayıtlı değil.
 */
final class QueueDrainBounded extends Command
{
    protected $signature = 'queue:drain-bounded
        {--queue=default : Virgülle ayrılmış kuyruk adları}
        {--max-jobs=20 : En fazla işlenecek iş sayısı}
        {--max-seconds=20 : En fazla çalışma süresi}
        {--tries=3 : Bir işin deneme sayısı}
        {--job-timeout=15 : Tek bir işin süre sınırı}
        {--json : Makine okunabilir çıktı}';

    protected $description = 'Kuyruğu sınırlı sayıda iş ve süre içinde işler.';

    public function handle(BoundedQueueDrainer $drainer): int
    {
        $sonuc = $drainer->drain(
            explode(',', (string) $this->option('queue')),
            (int) $this->option('max-jobs'),
            (int) $this->option('max-seconds'),
            (int) $this->option('tries'),
            (int) $this->option('job-timeout'),
        );

        $this->line($this->option('json')
            ? (string) json_encode($sonuc, JSON_UNESCAPED_UNICODE)
            : "İşlenen: {$sonuc['processed']}, kalan: {$sonuc['remaining']}");

        return self::SUCCESS;
    }
}
