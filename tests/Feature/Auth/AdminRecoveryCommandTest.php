<?php

namespace Tests\Feature\Auth;

use App\Models\AuditLog;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class AdminRecoveryCommandTest extends TestCase
{
    use RefreshDatabase;

    public function test_existing_active_user_can_be_recovered_interactively_without_exposing_password(): void
    {
        $user = User::factory()->create([
            'role' => 'member',
            'email_verified_at' => now(),
            'account_status' => 'active',
        ]);
        DB::table('sessions')->insert([
            'id' => 'recovery-session',
            'user_id' => $user->id,
            'ip_address' => '127.0.0.1',
            'user_agent' => 'test',
            'payload' => 'payload',
            'last_activity' => now()->timestamp,
        ]);
        DB::table('password_reset_tokens')->insert([
            'email' => $user->email,
            'token' => 'hashed-token',
            'created_at' => now(),
        ]);

        $this->artisan('admin:recover-access')
            ->expectsQuestion('Hedef e-posta adresi', $user->email)
            ->expectsQuestion('Yeni güçlü parola', 'YeniGucluParola123!')
            ->expectsQuestion('Yeni güçlü parolayı tekrar girin', 'YeniGucluParola123!')
            ->expectsOutput('Erişim kurtarma tamamlandı: admin rolü atandı, oturumlar kapatıldı ve sıfırlama tokenları temizlendi.')
            ->assertExitCode(0);

        $user->refresh();
        $this->assertSame('admin', $user->role);
        $this->assertTrue(Hash::check('YeniGucluParola123!', $user->password));
        $this->assertDatabaseMissing('sessions', ['user_id' => $user->id]);
        $this->assertDatabaseMissing('password_reset_tokens', ['email' => $user->email]);
        $this->assertDatabaseHas('audit_logs', [
            'auditable_type' => User::class,
            'auditable_id' => $user->id,
            'action' => 'user.admin_access_recovered',
        ]);
        $this->assertStringNotContainsString('YeniGucluParola123!', (string) json_encode(AuditLog::query()->latest()->value('metadata')));
    }

    public function test_missing_user_does_not_create_an_account(): void
    {
        $this->artisan('admin:recover-access')
            ->expectsQuestion('Hedef e-posta adresi', 'missing@example.test')
            ->expectsOutput('Hedef kullanıcı bulunamadı; hiçbir kullanıcı oluşturulmadı.')
            ->assertExitCode(1);

        $this->assertDatabaseMissing('users', ['email' => 'missing@example.test']);
    }
}
