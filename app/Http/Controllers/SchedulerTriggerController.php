<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Cache;

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
        } finally {
            $kilit->release();
        }

        return response('', 204);
    }
}
