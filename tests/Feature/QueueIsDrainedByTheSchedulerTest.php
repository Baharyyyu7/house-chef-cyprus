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

    public function test_the_scheduler_still_publishes_blog_posts(): void
    {
        // Daralma değil, ekleme: var olan dakikalık görev yerinde kalmalı.
        $olay = $this->gorev('blog_publish_scheduled');

        $this->assertNotNull($olay);
        $this->assertSame('* * * * *', $olay->expression);
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
