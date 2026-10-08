<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Mail\OperationsDigestMail;
use App\Models\SystemEvent;
use App\Services\Scheduler\OperationsDigestService;
use App\Services\SchedulerHealthService;
use Illuminate\Console\Scheduling\Event;
use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Mail;
use RuntimeException;
use Tests\TestCase;

final class ScheduleConsolidationTest extends TestCase
{
    use RefreshDatabase;

    protected function tearDown(): void
    {
        Carbon::setTestNow();

        parent::tearDown();
    }

    /** @return array<string, Event> */
    private function gorevler(): array
    {
        return collect(app(Schedule::class)->events())->keyBy(fn (Event $e): string => (string) $e->description)->all();
    }

    public function test_overlap_locks_expire_quickly(): void
    {
        // Süresiz withoutOverlapping() 1440 dakikalık kilit bırakıyordu:
        // yarıda ölen bir koşu görevi bir gün boyunca kilitliyordu.
        foreach ($this->gorevler() as $ad => $gorev) {
            if (! $gorev->withoutOverlapping) {
                continue;
            }

            $beklenen = str_starts_with($ad, 'backup_') ? 120 : 30;
            $this->assertSame($beklenen, $gorev->expiresAt, $ad);
        }
    }

    public function test_removed_tasks_are_not_scheduled(): void
    {
        $adlar = array_keys($this->gorevler());

        foreach ([
            'media_morning_brief', 'ai_kitchen_morning_brief', 'personalization_morning_brief', 'seo_morning_brief',
            'content_factory_fact_check_morning_brief', 'content_factory_morning_brief',
            'content_factory_recipe_drafts', 'content_factory_blog_drafts',
            'editorial_source_queue_refresh', 'editorial_recipe_pack_refresh', 'editorial_blog_pack_refresh',
            'editorial_media_readiness_refresh', 'editorial_workload_refresh',
        ] as $ad) {
            $this->assertNotContains($ad, $adlar);
        }

        $this->assertContains('editorial_operations_plan', $adlar);
        $this->assertSame('30 7 * * *', $this->gorevler()['operations_digest']->expression);
    }

    public function test_backups_have_an_hourly_catch_up(): void
    {
        $gorevler = $this->gorevler();

        $this->assertSame('50 * * * *', $gorevler['backup_daily_catchup']->expression);
        $this->assertSame('55 * * * *', $gorevler['backup_verify_catchup']->expression);
    }

    public function test_catch_up_runs_only_without_a_successful_run_today(): void
    {
        $saglik = app(SchedulerHealthService::class);
        Carbon::setTestNow('2026-10-08 09:50:00');

        $this->assertTrue($saglik->needsCatchUp('backup_daily'), 'Hiç koşmamış yedek telafi edilmeli.');

        $saglik->run('backup_daily', static fn () => 0);
        $this->assertFalse($saglik->needsCatchUp('backup_daily'));

        Carbon::setTestNow('2026-10-09 01:50:00');
        $this->assertTrue($saglik->needsCatchUp('backup_daily'), '00:20 kaçtıysa ertesi saat telafi etmeli.');
    }

    public function test_the_digest_reports_failures_and_errors_once_a_day(): void
    {
        Mail::fake();
        config()->set('mail.registration_admin_recipient', 'ops@example.test');
        config()->set('observability.backup_scheduled', false);
        $saglik = app(SchedulerHealthService::class);

        foreach (['blog_publish_scheduled', 'queue_drain'] as $ad) {
            $saglik->run($ad, static fn () => 0);
        }

        $this->assertFalse(app(OperationsDigestService::class)->send(), 'Sorun yokken e-posta gitmemeli.');
        Mail::assertNothingSent();

        try {
            $saglik->run('seo_audit', static fn () => throw new RuntimeException('zaman aşımı'));
        } catch (RuntimeException) {
        }
        SystemEvent::create(['event_type' => 'application_error', 'severity' => 'error', 'message' => 'Uygulama hatası', 'metadata' => ['fingerprint' => 'abc', 'occurrences' => 4, 'summary' => 'QueryException']]);

        $this->assertTrue(app(OperationsDigestService::class)->send());

        Mail::assertSent(OperationsDigestMail::class, function (OperationsDigestMail $mail): bool {
            $metin = $mail->render();

            return $mail->hasTo('ops@example.test')
                && str_contains($metin, 'seo_audit: ')
                && str_contains($metin, 'zaman aşımı')
                && str_contains($metin, '4x QueryException');
        });
    }
}
