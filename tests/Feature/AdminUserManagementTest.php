<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Models\AuditLog;
use App\Models\User;
use App\Services\UserActivityLogger;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

final class AdminUserManagementTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_can_view_privacy_safe_user_directory(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $member = User::factory()->create(['name' => 'Güvenli Üye', 'email' => 'uye@example.test']);

        app(UserActivityLogger::class)->record($member, 'auth.login', request()->merge([
            'token' => 'never-display',
            'safe_label' => 'Giriş',
        ]));

        $this->actingAs($admin)
            ->get(route('admin.users.index'))
            ->assertOk()
            ->assertSee('Güvenli Üye')
            ->assertDontSee('never-display');

        $this->actingAs($admin)
            ->get(route('admin.users.show', ['user' => $member->public_id]))
            ->assertOk()
            ->assertSee('auth.login')
            ->assertDontSee('never-display')
            ->assertDontSee($member->password);

        $this->assertNotNull($admin->public_id);
        $this->assertNotNull($member->public_id);
    }

    public function test_member_cannot_access_user_administration(): void
    {
        $member = User::factory()->create(['role' => 'member']);

        $this->actingAs($member)
            ->get(route('admin.users.index'))
            ->assertForbidden();
    }

    public function test_admin_can_suspend_and_reactivate_a_member_with_audit_trail(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $member = User::factory()->create(['role' => 'member']);

        $this->actingAs($admin)
            ->post(route('admin.users.suspend', ['user' => $member->public_id]))
            ->assertRedirect();

        $this->assertDatabaseHas('users', ['id' => $member->id, 'account_status' => 'suspended']);
        $this->assertDatabaseHas('audit_logs', ['user_id' => $admin->id, 'action' => 'user.account_suspended']);

        $this->actingAs($admin)
            ->post(route('admin.users.reactivate', ['user' => $member->public_id]))
            ->assertRedirect();

        $this->assertDatabaseHas('users', ['id' => $member->id, 'account_status' => 'active']);
        $this->assertDatabaseHas('audit_logs', ['user_id' => $admin->id, 'action' => 'user.account_reactivated']);
        $this->assertSame(2, AuditLog::query()->where('auditable_id', $member->id)->count());
    }
}
