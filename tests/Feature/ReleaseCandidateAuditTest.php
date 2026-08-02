<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Models\User;
use App\Services\ReleaseCandidateAuditService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Artisan;
use Tests\TestCase;

final class ReleaseCandidateAuditTest extends TestCase
{
    use RefreshDatabase;

    public function test_rc1_audit_preserves_the_public_and_draft_catalogue_contract(): void
    {
        $this->seed();
        $report = app(ReleaseCandidateAuditService::class)->audit();

        $this->assertSame(500, $report['catalogue']['recipes_total']);
        $this->assertSame(50, $report['catalogue']['recipes_published_verified']);
        $this->assertSame(450, $report['catalogue']['recipes_draft']);
        $this->assertSame(20, $report['catalogue']['blogs_public']);
        $this->assertSame(180, $report['catalogue']['blogs_draft']);
        $this->assertSame(0, $report['catalogue']['recipes_published_unverified']);
        $this->assertSame(0, $report['catalogue']['recipes_published_incomplete']);
        $this->assertSame(0, $report['catalogue']['blogs_draft_with_publication_date']);
    }

    public function test_rc1_audit_command_uses_a_safe_stable_json_contract(): void
    {
        $this->seed();
        $this->assertSame(0, Artisan::call('release-candidate:rc1-audit', ['--json' => true]));
        $report = json_decode(trim(Artisan::output()), true, flags: JSON_THROW_ON_ERROR);

        $this->assertSame('rc1-2026-08-01', $report['rule_version']);
        $this->assertArrayHasKey('route_inventory', $report);
        $this->assertArrayHasKey('manual_prelaunch_requirements', $report);
        $this->assertStringNotContainsString(base_path(), Artisan::output());
    }

    public function test_rc1_command_center_is_admin_only_and_does_not_claim_human_approval(): void
    {
        $admin = User::factory()->create(['role' => 'admin', 'email_verified_at' => now()]);
        $member = User::factory()->create(['role' => 'member', 'email_verified_at' => now()]);

        $this->actingAs($admin)->get(route('admin.release-candidate.index'))
            ->assertOk()->assertSee('Yayın öncesi kontrol merkezi')->assertSee('İnsan ve Production Kapanışı');
        $this->actingAs($member)->get(route('admin.release-candidate.index'))->assertForbidden();
    }
}
