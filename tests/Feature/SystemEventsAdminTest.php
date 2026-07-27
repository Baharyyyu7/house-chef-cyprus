<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Models\AuditLog;
use App\Models\SystemEvent;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

final class SystemEventsAdminTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_can_view_filter_resolve_and_reopen_safe_event(): void
    {
        $admin = User::factory()->create(['role' => 'admin', 'email_verified_at' => now()]);
        $event = SystemEvent::create(['event_type' => 'test', 'severity' => 'error', 'message' => 'Güvenli mesaj', 'correlation_id' => 'REQ-1', 'metadata' => ['token' => 'secret', 'safe' => 'ok', 'trace' => 'x']]);
        $this->actingAs($admin)->get(route('admin.system-events.index', ['request_id' => 'REQ-1']))->assertOk()->assertSee('REQ-1');
        $this->get(route('admin.system-events.show', $event))->assertOk()->assertDontSee('secret')->assertDontSee('trace');
        $this->post(route('admin.system-events.resolve', $event))->assertRedirect();
        $this->post(route('admin.system-events.resolve', $event))->assertRedirect();
        $this->assertNotNull($event->fresh()->resolved_at);
        $this->post(route('admin.system-events.reopen', $event))->assertRedirect();
        $this->post(route('admin.system-events.reopen', $event))->assertRedirect();
        $this->assertNull($event->fresh()->resolved_at);
        $this->assertSame(2, AuditLog::whereIn('action', ['system_event.resolve', 'system_event.reopen'])->count());
    }

    public function test_member_is_forbidden_from_all_event_actions(): void
    {
        $member = User::factory()->create(['role' => 'member', 'email_verified_at' => now()]);
        $event = SystemEvent::create(['event_type' => 'test', 'severity' => 'error', 'message' => 'Güvenli']);
        $this->actingAs($member)->get(route('admin.system-events.index'))->assertForbidden();
        $this->get(route('admin.system-events.show', $event))->assertForbidden();
        $this->post(route('admin.system-events.resolve', $event))->assertForbidden();
        $this->post(route('admin.system-events.reopen', $event))->assertForbidden();
    }
}
