<?php

declare(strict_types=1);

namespace Tests\Feature;

use Illuminate\Console\Application as ArtisanApplication;
use Illuminate\Console\Events\ScheduledTaskFailed;
use Illuminate\Console\Events\ScheduledTaskFinished;
use Illuminate\Console\Scheduling\ScheduleRunCommand;
use Illuminate\Contracts\Console\Kernel as ConsoleKernel;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Event;
use Tests\TestCase;

/**
 * Üretimde `/_zamanlayici` ucundan yapılan `Artisan::call('<özel komut>')`
 * CommandNotFoundException atıyordu; zamanlanmış görevlerin neredeyse hepsi
 * bu yoldan koştuğu için yedek, doğrulama ve blog yayını sessizce
 * koşmayabiliyordu. Burada o durum yeniden kuruluyor: Artisan örneği yalnız
 * çerçevenin schedule:run komutunu tanıyor, uygulama komutlarının hiçbiri
 * kayıtlı değil.
 */
final class SchedulerTriggerRunsCustomCommandsTest extends TestCase
{
    use RefreshDatabase;

    private const TOKEN = 'test-belirteci-1234567890abcdef';

    protected function setUp(): void
    {
        parent::setUp();

        config()->set('app.scheduler_trigger_token', self::TOKEN);
        // Yalnız dakikalık görevler koşsun: günlük ve saatlik görevler
        // 00:00 ve :00'a bağlı.
        Carbon::setTestNow('2026-10-08 10:07:00');
    }

    protected function tearDown(): void
    {
        Carbon::setTestNow();

        parent::tearDown();
    }

    public function test_the_http_trigger_runs_a_custom_command_even_when_artisan_does_not_know_it(): void
    {
        $this->artisanYalnizScheduleRunTaniyor();

        $bitenler = [];
        $dusenler = [];
        Event::listen(ScheduledTaskFinished::class, function (ScheduledTaskFinished $olay) use (&$bitenler): void {
            $bitenler[] = $olay->task->description;
        });
        Event::listen(ScheduledTaskFailed::class, function (ScheduledTaskFailed $olay) use (&$dusenler): void {
            $dusenler[$olay->task->description] = $olay->exception::class.': '.$olay->exception->getMessage();
        });

        $this->get('/_zamanlayici/'.self::TOKEN)->assertNoContent();

        // blog_publish_scheduled görevi içinde Artisan::call('blog:publish-scheduled')
        // yapıyor; komut bulunamasaydı görev ScheduledTaskFailed ile düşerdi.
        $this->assertSame([], $dusenler);
        $this->assertContains('blog_publish_scheduled', $bitenler);
    }

    public function test_without_registration_the_same_setup_cannot_find_the_command(): void
    {
        // Kurulumun gerçekten arızayı ürettiğinin kanıtı: kayıt adımı
        // olmadan aynı çağrı bulunamıyor.
        $this->artisanYalnizScheduleRunTaniyor();

        $this->expectException(\Symfony\Component\Console\Exception\CommandNotFoundException::class);

        app(ConsoleKernel::class)->call('blog:publish-scheduled');
    }

    private function artisanYalnizScheduleRunTaniyor(): void
    {
        $kernel = app(ConsoleKernel::class);
        $kernel->bootstrap();

        ArtisanApplication::forgetBootstrappers();
        $artisan = new ArtisanApplication(app(), app('events'), app()->version());
        $artisan->resolve(ScheduleRunCommand::class);
        $artisan->setContainerCommandLoader();
        $kernel->setArtisan($artisan);
    }
}
