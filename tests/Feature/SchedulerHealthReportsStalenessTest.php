<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Services\SchedulerHealthService;
use Illuminate\Support\Facades\Cache;
use Tests\TestCase;

final class SchedulerHealthReportsStalenessTest extends TestCase
{
    public function test_a_scheduler_that_stopped_an_hour_ago_is_not_healthy(): void
    {
        // Eşik kodda sabit 26 saatti ama rapor `max_age_minutes: 30`
        // diyordu. Alan bir söz veriyor, durum başka bir şeye bakıyordu:
        // zamanlayıcı bütün bir gün ölü kalsa da "healthy" görünüyordu.
        // Hostinger'ın cron servisi 2026-08-02'de durdu ve bunu yakalayacak
        // sinyal tam da buydu.
        $this->isaret(now()->subHour());

        $this->assertSame('delayed', app(SchedulerHealthService::class)->snapshot()['status']);
    }

    public function test_a_scheduler_that_ran_a_minute_ago_is_healthy(): void
    {
        $this->isaret(now()->subMinute());

        $this->assertSame('healthy', app(SchedulerHealthService::class)->snapshot()['status']);
    }

    public function test_the_threshold_follows_the_configured_value(): void
    {
        // Raporlanan eşikle kullanılan eşik aynı olmalı; ayrışırlarsa
        // rapor tekrar yanıltıcı hale gelir.
        config()->set('production.scheduler_max_age_minutes', 120);

        $this->isaret(now()->subHour());

        $this->assertSame('healthy', app(SchedulerHealthService::class)->snapshot()['status']);
    }

    public function test_a_scheduler_that_never_ran_is_reported_as_such(): void
    {
        Cache::forget('scheduler.health');

        $this->assertSame('never_run', app(SchedulerHealthService::class)->snapshot()['status']);
    }

    public function test_a_failed_run_is_not_relabelled_as_delayed(): void
    {
        // Bayatlık kontrolü yalnız "healthy" durumunu daraltmalı; hata
        // bilgisini üzerine yazarsa arıza sebebi kaybolur.
        Cache::forever('scheduler.health', [
            'last_started_at' => now()->subDay()->toIso8601String(),
            'last_failed_at' => now()->subDay()->toIso8601String(),
            'last_error_code' => 'SCHEDULER_TASK_FAILED',
            'status' => 'failed',
        ]);

        $this->assertSame('failed', app(SchedulerHealthService::class)->snapshot()['status']);
    }

    private function isaret(\Illuminate\Support\Carbon $an): void
    {
        Cache::forever('scheduler.health', [
            'last_started_at' => $an->toIso8601String(),
            'last_completed_at' => $an->toIso8601String(),
            'last_task_name' => 'blog_publish_scheduled',
            'status' => 'healthy',
        ]);
    }
}
