<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Services\BackupService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

final class ObservabilityFoundationTest extends TestCase
{
    use RefreshDatabase;

    public function test_health_endpoints_are_safe_and_requests_receive_a_correlation_id(): void
    {
        $this->get('/health')->assertOk()->assertHeader('X-Request-ID')->assertJsonStructure(['status', 'checked_at']);
        $this->getJson('/api/v1/health')->assertOk()->assertHeader('X-Request-ID')->assertJsonStructure(['data' => ['status', 'checked_at'], 'request_id'])->assertJsonMissing(['components']);
        $this->getJson('/api/v1/version')->assertOk()->assertJsonPath('data.locale', 'tr');
    }

    public function test_backup_create_list_verify_and_safe_restore_contract(): void
    {
        if (config('database.connections.sqlite.database') === ':memory:') {
            $this->markTestSkipped('SQLite bellek bağlantısı dosya tabanlı backup doğrulamasını desteklemez.');
        }
        $created = app(BackupService::class)->create();
        $this->assertArrayHasKey('checksum', $created);
        $this->artisan('backup:list', ['--json' => true])->assertSuccessful();
        $this->artisan('backup:verify', ['backup_id' => $created['backup_id'], '--json' => true])->assertSuccessful();
        $this->artisan('backup:restore', ['backup_id' => $created['backup_id'], '--target' => 'testing', '--verify-only' => true])->assertSuccessful();
        $this->artisan('backup:restore', ['backup_id' => $created['backup_id'], '--target' => 'live'])->assertFailed();
    }
}
