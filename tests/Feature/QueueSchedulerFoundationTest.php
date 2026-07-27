<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Jobs\RecalculateRecipeNutritionJob;
use App\Models\AuditLog;
use App\Models\User;
use App\Services\QueueHealthService;
use App\Services\SchedulerHealthService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use RuntimeException;
use Tests\TestCase;

final class QueueSchedulerFoundationTest extends TestCase
{
    use RefreshDatabase;

    public function test_queue_health_reports_pending_reserved_failed_and_stuck_jobs(): void
    {
        DB::table('jobs')->insert([
            ['queue' => 'default', 'payload' => '{}', 'attempts' => 0, 'reserved_at' => null, 'available_at' => now()->timestamp, 'created_at' => now()->timestamp],
            ['queue' => 'nutrition', 'payload' => '{}', 'attempts' => 1, 'reserved_at' => now()->subMinutes(10)->timestamp, 'available_at' => now()->timestamp, 'created_at' => now()->timestamp],
        ]);
        $this->failedJob();

        $health = app(QueueHealthService::class)->snapshot();

        $this->assertSame(1, $health['pending_jobs']);
        $this->assertSame(1, $health['reserved_jobs']);
        $this->assertSame(1, $health['stuck_jobs']);
        $this->assertSame(1, $health['failed_jobs']);
        $this->assertSame('degraded', $health['overall_status']);
    }

    public function test_queue_health_json_command_is_machine_readable(): void
    {
        Artisan::call('queue:health', ['--json' => true]);
        $payload = json_decode(Artisan::output(), true);

        $this->assertIsArray($payload);
        $this->assertSame(config('queue.default'), $payload['connection']);
    }

    public function test_worker_health_becomes_unavailable_when_heartbeat_is_stale(): void
    {
        Cache::forever('queue.worker_heartbeat', now()->subMinutes(5)->toIso8601String());

        $this->assertSame('unavailable', app(QueueHealthService::class)->snapshot()['worker_status']);
    }

    public function test_admin_can_view_retry_and_forget_failed_jobs_with_audit_trail(): void
    {
        $admin = User::factory()->create(['role' => 'admin', 'email_verified_at' => now()]);
        $uuid = $this->failedJob();

        $this->actingAs($admin)->get(route('admin.failed-jobs.index'))->assertOk()->assertSee('Başarısız İşler');
        $this->actingAs($admin)->get(route('admin.failed-jobs.show', $uuid))->assertOk()->assertDontSee('test-token');
        $this->actingAs($admin)->post(route('admin.failed-jobs.retry', $uuid))->assertRedirect();
        $this->assertDatabaseMissing('failed_jobs', ['uuid' => $uuid]);
        $this->assertDatabaseHas('audit_logs', ['action' => 'queue.failed_job.retry']);

        $forgetUuid = $this->failedJob();
        $this->actingAs($admin)->delete(route('admin.failed-jobs.forget', $forgetUuid))->assertRedirect();
        $this->assertDatabaseMissing('failed_jobs', ['uuid' => $forgetUuid]);
        $this->assertDatabaseHas('audit_logs', ['action' => 'queue.failed_job.forget']);
    }

    public function test_member_cannot_access_failed_job_management(): void
    {
        $member = User::factory()->create(['role' => 'member', 'email_verified_at' => now()]);
        $uuid = $this->failedJob();

        $this->actingAs($member)->get(route('admin.failed-jobs.index'))->assertForbidden();
        $this->actingAs($member)->post(route('admin.failed-jobs.retry', $uuid))->assertForbidden();
    }

    public function test_admin_can_retry_multiple_failed_jobs(): void
    {
        $admin = User::factory()->create(['role' => 'admin', 'email_verified_at' => now()]);
        $first = $this->failedJob();
        $second = $this->failedJob();

        $this->actingAs($admin)->post(route('admin.failed-jobs.retry-many'), ['uuids' => [$first, $second, $first]])->assertRedirect();

        $this->assertDatabaseMissing('failed_jobs', ['uuid' => $first]);
        $this->assertDatabaseMissing('failed_jobs', ['uuid' => $second]);
        $this->assertSame(2, AuditLog::query()->where('action', 'queue.failed_job.retry')->count());
    }

    public function test_scheduler_records_success_and_failure_lifecycle(): void
    {
        $service = app(SchedulerHealthService::class);
        $service->run('test_success', static function (): void {});
        $success = $service->snapshot();
        $this->assertSame('healthy', $success['status']);
        $this->assertSame('test_success', $success['last_task_name']);
        $this->assertArrayHasKey('last_completed_at', $success);

        try {
            $service->run('test_failure', static function (): void {
                throw new RuntimeException('secret failure');
            });
        } catch (RuntimeException) {
            // The scheduler must receive the failure after the safe health state is recorded.
        }

        $failure = $service->snapshot();
        $this->assertSame('failed', $failure['status']);
        $this->assertSame('SCHEDULER_TASK_FAILED', $failure['last_error_code']);
        $this->assertArrayHasKey('last_failed_at', $failure);
    }

    private function failedJob(): string
    {
        $uuid = (string) Str::uuid();
        $job = new RecalculateRecipeNutritionJob(999999);
        $payload = json_encode([
            'uuid' => (string) Str::uuid(),
            'displayName' => $job::class,
            'job' => 'Illuminate\\Queue\\CallQueuedHandler@call',
            'maxTries' => 3,
            'data' => ['commandName' => $job::class, 'command' => serialize($job)],
        ], JSON_THROW_ON_ERROR);
        DB::table('failed_jobs')->insert([
            'uuid' => $uuid,
            'connection' => 'database',
            'queue' => 'nutrition',
            'payload' => $payload,
            'exception' => 'RuntimeException: test-token must never be displayed',
            'failed_at' => now(),
        ]);

        return $uuid;
    }
}
