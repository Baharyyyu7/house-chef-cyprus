<?php

declare(strict_types=1);

namespace Tests\Feature;

use Illuminate\Console\Scheduling\Schedule;
use Tests\TestCase;

final class QueueIsDrainedByTheSchedulerTest extends TestCase
{
    public function test_the_queue_is_drained_every_minute(): void
    {
        // Kuyruk işçisi ayrı bir cron kaydıyla koşuyordu; Hostinger'ın cron
        // servisi 2026-08-02'de durunca o da durdu. Zamanlayıcı dışarıdan
        // tetiklenmeye başladıktan sonra bile kuyruk beklemeye devam etti:
        // canlıda iki iş sırada duruyordu ve worker_count sıfırdı.
        $olay = $this->gorev('queue_drain');

        $this->assertNotNull($olay, 'Kuyruk zamanlamaya bağlı olmalı.');
        $this->assertSame('* * * * *', $olay->expression);
    }

    public function test_the_queue_task_cannot_outlive_the_trigger_lock(): void
    {
        // Tetikleme ucu 55 saniyelik bir kilit alıyor ve çağrı bir HTTP
        // isteği. Kuyruk işi bundan uzun sürerse istek asılır.
        $olay = $this->gorev('queue_drain');

        $this->assertNotNull($olay);
        $this->assertTrue($olay->withoutOverlapping, 'Üst üste binen çalıştırma kuyruğu paralel işletirdi.');
    }

    public function test_every_queue_the_app_dispatches_to_is_drained(): void
    {
        // queue:work varsayılan olarak yalnız "default" kuyruğunu işler.
        // Canlıda bekleyen iki iş "media" kuyruğundaydı; görev koşuyordu ama
        // o işlere hiç dokunmuyordu. Uygulamanın kullandığı her kuyruk
        // burada sayılmalı.
        // Kuyruk adı yalnız app/Jobs içinde geçmiyor; iş gönderen her yer
        // taranmalı, yoksa test eksik kalır.
        $kullanilan = [];
        $gezgin = new \RecursiveIteratorIterator(new \RecursiveDirectoryIterator(app_path()));

        foreach ($gezgin as $dosya) {
            if (! $dosya->isFile() || $dosya->getExtension() !== 'php') {
                continue;
            }
            if (preg_match_all("/onQueue\('([a-z_-]+)'\)/", (string) file_get_contents($dosya->getPathname()), $eslesme)) {
                $kullanilan = [...$kullanilan, ...$eslesme[1]];
            }
        }

        $this->assertNotEmpty($kullanilan, 'Tarama hiçbir kuyruk bulamadıysa test boş geçer.');

        $tanimli = explode(',', $this->kuyrukSecenegi());

        foreach (array_unique($kullanilan) as $kuyruk) {
            $this->assertContains($kuyruk, $tanimli, "'$kuyruk' kuyruğu boşaltılmıyor.");
        }

        $this->assertContains('default', $tanimli);
    }

    public function test_the_scheduler_still_publishes_blog_posts(): void
    {
        // Daralma değil, ekleme: var olan dakikalık görev yerinde kalmalı.
        $olay = $this->gorev('blog_publish_scheduled');

        $this->assertNotNull($olay);
        $this->assertSame('* * * * *', $olay->expression);
    }

    /** Zamanlanmış görevin kuyruk boşaltma çağrısındaki --queue değeri. */
    private function kuyrukSecenegi(): string
    {
        $kaynak = (string) file_get_contents(base_path('routes/console.php'));

        // Komut adı da doğrulanır: queue:work bu bağlamda çalışmadığı için
        // geri dönülmemeli.
        $this->assertStringContainsString('queue:drain-bounded', $kaynak);
        $this->assertMatchesRegularExpression("/'--queue' => '([a-z,_-]+)'/", $kaynak);
        preg_match("/'--queue' => '([a-z,_-]+)'/", $kaynak, $eslesme);

        return $eslesme[1];
    }

    private function gorev(string $ad): ?\Illuminate\Console\Scheduling\Event
    {
        foreach (app(Schedule::class)->events() as $olay) {
            if ($olay->description === $ad) {
                return $olay;
            }
        }

        return null;
    }
}
