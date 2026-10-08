<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Services\SchedulerHealthService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Cache;
use RuntimeException;
use Tests\TestCase;

final class SchedulerPerTaskHealthTest extends TestCase
{
    use RefreshDatabase;

    protected function tearDown(): void
    {
        Carbon::setTestNow();

        parent::tearDown();
    }

    public function test_a_later_success_does_not_erase_an_earlier_failure(): void
    {
        // Eskiden tek anahtar vardı: 00:20'de yedek "failed" yazıyor,
        // 00:21'de blog görevi "healthy" yazıp hatayı siliyordu.
        $saglik = app(SchedulerHealthService::class);

        try {
            $saglik->run('backup_daily', static fn () => throw new RuntimeException('disk dolu'));
        } catch (RuntimeException) {
        }
        $saglik->run('blog_publish_scheduled', static fn () => 0);

        $this->assertSame('failed', $saglik->task('backup_daily')['status']);
        $this->assertSame('disk dolu', $saglik->task('backup_daily')['last_error']);
        $this->assertSame('healthy', $saglik->task('blog_publish_scheduled')['status']);
        $this->assertSame('failed', $saglik->snapshot()['status']);
        $this->assertContains('backup_daily', $saglik->snapshot()['failed_tasks']);
    }

    public function test_a_non_zero_exit_code_is_a_failure(): void
    {
        // Artisan::call çıkış kodunu döndürür; 1 dönen komut "healthy"
        // sayılıyordu.
        $saglik = app(SchedulerHealthService::class);

        $this->expectException(RuntimeException::class);

        try {
            $saglik->run('data_quality_scan', static fn () => 1);
        } finally {
            $this->assertSame('failed', $saglik->task('data_quality_scan')['status']);
        }
    }

    public function test_task_count_is_the_real_number_of_tasks(): void
    {
        $saglik = app(SchedulerHealthService::class);
        config()->set('observability.backup_scheduled', false);

        foreach (['a', 'b', 'c'] as $ad) {
            $saglik->run($ad, static fn () => 0);
        }

        // 3 görev + kayıt yokken de izlenen iki dakikalık kritik görev.
        $this->assertSame(5, $saglik->snapshot()['task_count']);
    }

    public function test_a_success_older_than_twice_the_interval_is_stale(): void
    {
        $saglik = app(SchedulerHealthService::class);
        Carbon::setTestNow('2026-10-08 00:30:00');
        $saglik->run('backup_daily', static fn () => 0);

        Carbon::setTestNow('2026-10-09 23:00:00');
        $this->assertFalse($saglik->task('backup_daily')['stale']);

        Carbon::setTestNow('2026-10-10 01:00:00');
        $this->assertTrue($saglik->task('backup_daily')['stale']);
    }

    public function test_the_expected_interval_comes_from_the_cron_expression(): void
    {
        $this->assertSame(1, SchedulerHealthService::intervalMinutes('* * * * *'));
        $this->assertSame(60, SchedulerHealthService::intervalMinutes('0 * * * *'));
        $this->assertSame(1440, SchedulerHealthService::intervalMinutes('20 0 * * *'));
        $this->assertSame(10080, SchedulerHealthService::intervalMinutes('30 8 * * 1'));
    }

    public function test_health_is_degraded_when_a_critical_task_is_stale_and_reports_the_build_commit(): void
    {
        config()->set('observability.backup_scheduled', true);
        config()->set('production.commit_sha', 'abc1234def');
        $saglik = app(SchedulerHealthService::class);
        Carbon::setTestNow('2026-10-08 10:00:00');

        foreach (array_keys(SchedulerHealthService::CRITICAL_TASKS) as $ad) {
            $saglik->run($ad, static fn () => 0);
        }

        Cache::forget('health.snapshot.v1');
        $this->get('/health')->assertOk()->assertJsonPath('commit', 'abc1234def');
        $this->assertSame([], $saglik->staleCriticalTasks());

        // Dakikalık görevler sürüyor ama gece yedeği iki gündür başarılı değil.
        Carbon::setTestNow('2026-10-10 11:00:00');
        foreach (['blog_publish_scheduled', 'queue_drain', 'backup_verify'] as $ad) {
            $saglik->run($ad, static fn () => 0);
        }

        Cache::forget('health.snapshot.v1');
        $this->get('/health')->assertOk()->assertJsonPath('status', 'degraded')->assertJsonMissing(['components']);
        $this->assertSame(['backup_daily'], $saglik->staleCriticalTasks());
    }

    public function test_the_public_health_payload_does_not_leak_task_errors(): void
    {
        $saglik = app(SchedulerHealthService::class);

        try {
            $saglik->run('queue_drain', static fn () => throw new RuntimeException('SQLSTATE parola=gizli'));
        } catch (RuntimeException) {
        }

        Cache::forget('health.snapshot.v1');
        $this->get('/health')->assertOk()->assertDontSee('SQLSTATE')->assertDontSee('queue_drain');
    }

    public function test_errors_are_masked_before_they_are_stored(): void
    {
        $this->assertSame('GET /_zamanlayici/*** token=***', SchedulerHealthService::safeError('GET /_zamanlayici/'.str_repeat('a', 40).' token=abc'));
    }
}
