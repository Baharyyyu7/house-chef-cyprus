<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Tests\TestCase;

final class AdminBackupPaginationTest extends TestCase
{
    use RefreshDatabase;

    public function test_backup_history_is_paginated_without_losing_summary_counts(): void
    {
        Storage::fake('local');
        config()->set('observability.backup_disk', 'local');
        config()->set('observability.backup_path', 'backups');

        foreach (range(1, 30) as $day) {
            $id = (string) Str::ulid();
            Storage::disk('local')->put("backups/{$id}/manifest.json", (string) json_encode([
                'backup_id' => $id,
                'created_at' => now()->subMinutes($day)->toIso8601String(),
                'status' => $day <= 20 ? 'verified' : 'created',
                'database_size' => 1024,
                'source_environment' => 'testing',
                'app_version' => 'test',
            ]));
        }

        $admin = User::factory()->create(['role' => 'admin', 'email_verified_at' => now()]);

        $response = $this->actingAs($admin)->get(route('admin.backups.index'));

        $response->assertOk()
            ->assertSee('toplam 30 yedek, 20 tanesi doğrulanmış')
            ->assertSee('page=2', false);
        $this->assertSame(25, substr_count((string) $response->getContent(), 'data-backup-row'));
    }
}
