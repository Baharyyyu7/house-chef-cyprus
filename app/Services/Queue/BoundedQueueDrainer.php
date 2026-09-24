<?php

declare(strict_types=1);

namespace App\Services\Queue;

use Illuminate\Queue\WorkerOptions;
use Illuminate\Support\Facades\DB;

/**
 * Kuyruğu sınırlı sayıda iş ve sınırlı süre içinde boşaltır.
 *
 * Neden konsol komutu değil de servis: zamanlayıcı Hostinger'ın ölü cron'u
 * yüzünden /_zamanlayici ucundan, yani bir HTTP isteğinin içinden
 * tetikleniyor. O bağlamda Artisan komutları güvenilir biçimde kayıtlı
 * değil - üretimde `Artisan::call('queue:drain-bounded')` çağrısı
 * CommandNotFoundException atıyor, aynı komut CLI'da sorunsuz listeleniyor
 * ve çalışıyor.
 *
 * Servis olarak çağrıldığında konsol çekirdeği hiç devreye girmiyor,
 * dolayısıyla iki bağlamda da aynı kod koşuyor. Konsol komutu bu servisin
 * ince bir sarmalayıcısı olarak duruyor.
 */
final class BoundedQueueDrainer
{
    /**
     * @param  list<string>  $kuyruklar
     * @return array{processed:int,queues:list<string>,remaining:int}
     */
    public function drain(
        array $kuyruklar = ['default'],
        int $enFazlaIs = 20,
        int $enFazlaSaniye = 20,
        int $deneme = 3,
        int $isSuresi = 15,
    ): array {
        $kuyruklar = array_values(array_filter(array_map('trim', $kuyruklar)));
        $enFazlaIs = max(1, $enFazlaIs);
        $sonAn = microtime(true) + max(1, $enFazlaSaniye);

        // sleep: 0 şart. Varsayılan 3 saniyeyle Worker boş her kuyrukta
        // uyuyor; dört kuyruk için çağrı başına 12 saniye demek.
        $secenekler = new WorkerOptions(
            sleep: 0,
            maxTries: max(1, $deneme),
            timeout: max(1, $isSuresi),
        );

        $worker = app('queue.worker');
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
                $oncesi = $this->bekleyen();
                $worker->runNextJob($baglanti, $kuyruk, $secenekler);

                if ($this->bekleyen() !== $oncesi) {
                    $islenen++;
                }
            }

            if ($islenen === $oncekiSayac) {
                break;
            }
        }

        return ['processed' => $islenen, 'queues' => $kuyruklar, 'remaining' => $this->bekleyen()];
    }

    private function bekleyen(): int
    {
        return (int) DB::table('jobs')->count();
    }
}
