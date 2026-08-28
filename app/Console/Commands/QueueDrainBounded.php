<?php

declare(strict_types=1);

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Queue\WorkerOptions;
use Illuminate\Support\Facades\DB;

/**
 * Kuyruğu sınırlı sayıda iş ve sınırlı süre içinde boşaltır.
 *
 * Neden ayrı bir komut: zamanlayıcı Hostinger'ın ölü cron'u yüzünden
 * /_zamanlayici ucundan, yani bir HTTP isteğinin içinden tetikleniyor.
 * `queue:work` bu bağlamda çalışmıyor - CLI'da aynı görev 3 saniye sürüp
 * işleri bitirirken HTTP yolunda hata vermeden, iş işlemeden anında
 * dönüyor. Daemon döngüsü, sinyal işleyicileri ve çıkış davranışı web
 * SAPI'sinde güvenilir değil.
 *
 * Bu komut Worker'ın tek iş çalıştıran API'sini doğrudan kullanır: döngü
 * bizde, dolayısıyla davranış iki bağlamda da aynı.
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

    protected $description = 'Kuyruğu sınırlı sayıda iş ve süre içinde işler; HTTP bağlamında da çalışır.';

    public function handle(): int
    {
        // Worker'ın kurucusu container'dan çözülemiyor; framework onu
        // 'queue.worker' adıyla kendisi bağlıyor.
        $worker = $this->laravel->make('queue.worker');

        $kuyruklar = array_values(array_filter(array_map('trim', explode(',', (string) $this->option('queue')))));
        $enFazlaIs = max(1, (int) $this->option('max-jobs'));
        $sonAn = microtime(true) + max(1, (int) $this->option('max-seconds'));

        // sleep: 0 şart. Varsayılan 3 saniyeyle Worker boş her kuyrukta
        // uyuyor; dört kuyruk için tetikleme başına 12 saniye demek.
        $secenekler = new WorkerOptions(
            sleep: 0,
            maxTries: max(1, (int) $this->option('tries')),
            timeout: max(1, (int) $this->option('job-timeout')),
        );

        $baglanti = (string) config('queue.default');
        $islenen = 0;

        while ($islenen < $enFazlaIs && microtime(true) < $sonAn) {
            $oncekiSayac = $islenen;

            foreach ($kuyruklar as $kuyruk) {
                if ($islenen >= $enFazlaIs || microtime(true) >= $sonAn) {
                    break;
                }

                // runNextJob iş yoksa sessizce döner; boş kuyruk hata değil.
                // Gerçekten iş işlendi mi, bekleyen sayısındaki değişimden
                // anlaşılır - aksi halde boş kuyruk da sayılır ve döngü
                // kendini sonlandıramaz.
                $oncesi = $this->bekleyenToplam();
                $worker->runNextJob($baglanti, $kuyruk, $secenekler);

                if ($this->bekleyenToplam() !== $oncesi) {
                    $islenen++;
                }
            }

            // Hiçbir kuyrukta iş kalmadıysa beklemeye gerek yok.
            if ($islenen === $oncekiSayac) {
                break;
            }
        }

        $sonuc = ['processed' => $islenen, 'queues' => $kuyruklar, 'remaining' => $this->bekleyenToplam()];

        $this->line($this->option('json') ? (string) json_encode($sonuc, JSON_UNESCAPED_UNICODE) : "İşlenen: {$islenen}, kalan: {$sonuc['remaining']}");

        return self::SUCCESS;
    }

    private function bekleyenToplam(): int
    {
        return (int) DB::table('jobs')->count();
    }
}
