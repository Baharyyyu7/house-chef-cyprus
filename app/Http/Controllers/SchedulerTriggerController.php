<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;

/**
 * Zamanlayıcıyı dışarıdan tetikleme ucu.
 *
 * Neden var: Hostinger bu hesapta 2 Ağustos 2026'dan beri hiçbir cron
 * çalıştırmıyor. Panelde iki kayıt duruyor, ikisinin de log dosyası o
 * tarihte donmuş; komutlar elle çalıştırıldığında kusursuz işliyor. Yani
 * sorun uygulamada değil, hosting'in cron servisinde ve panelden
 * düzeltilemiyor.
 *
 * Bu uç, cron yerine dışarıdan bir zamanlayıcının (Cloudflare Worker cron
 * trigger) uygulamayı dürtmesini sağlar. Sunucu tarafı cron düzeldiğinde
 * bu uç kapatılabilir; token kaldırılırsa kendiliğinden 404 olur.
 *
 * Güvenlik:
 * - Token yoksa ya da eşleşmiyorsa 404 döner. 403 dönmek ucun var olduğunu
 *   doğrulardı; 404 hiçbir şey söylemiyor.
 * - Karşılaştırma hash_equals ile yapılır; uzunluk farkından zaman sızmasın.
 * - Kilit, üst üste binen çalıştırmaları engeller: uç dışarıya açık ve
 *   art arda çağrılırsa zamanlayıcı paralel koşardı.
 */
final class SchedulerTriggerController extends Controller
{
    public function __invoke(Request $request, string $token): Response
    {
        $beklenen = (string) config('app.scheduler_trigger_token', '');

        // Token tanımlı değilse uç kapalıdır. Boş token'la eşleşme olmaz.
        abort_if($beklenen === '' || ! hash_equals($beklenen, $token), 404);

        $kilit = Cache::lock('scheduler-trigger', 55);

        if (! $kilit->get()) {
            // Zaten koşuyor. Bu bir hata değil; çağıran taraf sıklığı
            // ayarlarken bunu görebilsin diye ayrı bir kod dönüyor.
            return response('', 429);
        }

        try {
            Artisan::call('schedule:run', ['--no-interaction' => true]);

            // Kuyruk ayrıca ve doğrudan boşaltılıyor.
            //
            // Zamanlamada bir queue_drain görevi var ve CLI'da çalışıyor, ama
            // bu uçtan çağrıldığında schedule:run onu çalıştırmıyor: aynı
            // istekte blog_publish_scheduled koşuyor (işaret güncelleniyor),
            // queue_drain koşmuyor ve schedule:run hiç çıktı üretmiyor. Aynı
            // kod yerelde `artisan serve` altında sorunsuz çalışıyor, yani
            // sebep Hostinger'ın web ortamına özgü ve makul bir maliyetle
            // bulunamadı.
            //
            // Doğrudan çağrı o davranışa bağımlı değil. Sunucu tarafı cron
            // düzelirse zamanlanmış görev de işini görür; ikisi aynı anda
            // koşarsa ikincisi boş kuyruk görüp hemen döner.
            Artisan::call('queue:drain-bounded', [
                '--queue' => 'default,media,mail,nutrition',
                '--max-jobs' => 20,
                '--max-seconds' => 20,
                '--tries' => 3,
                '--job-timeout' => 15,
            ]);

            // Eski cron komutu çıktısını shared/logs/scheduler.log'a
            // yönlendiriyordu; bu uç Artisan::call kullandığı için o kayıt
            // kayboldu ve "hangi görev koştu" sorusu cevapsız kaldı. Çıktı
            // yalnız bir şey olduğunda yazılıyor: boş dakikalar log şişirmesin.
            $cikti = trim(Artisan::output());

            if ($cikti !== '') {
                Log::channel(config('logging.default'))->info('zamanlayici', ['cikti' => $cikti]);
            }
        } finally {
            $kilit->release();
        }

        return response('', 204);
    }
}
