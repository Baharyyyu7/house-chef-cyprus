<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Models\ContentFactoryIssue;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Gate;
use Tests\TestCase;

final class ContentFactoryAuthorizationTest extends TestCase
{
    use RefreshDatabase;

    public function test_fact_check_role_matrix_protects_high_risk_actions(): void
    {
        $this->seed();
        $issue = ContentFactoryIssue::query()->where('risk_level', 'critical')->firstOrFail();
        $admin = User::factory()->create(['role' => 'admin', 'email_verified_at' => now()]);
        $reviewer = User::factory()->create(['role' => 'fact_check_reviewer', 'email_verified_at' => now()]);
        $editor = User::factory()->create(['role' => 'content_editor', 'email_verified_at' => now()]);
        $seo = User::factory()->create(['role' => 'seo_reviewer', 'email_verified_at' => now()]);
        $member = User::factory()->create(['role' => 'member', 'email_verified_at' => now()]);

        $this->assertTrue(Gate::forUser($admin)->allows('review-fact-check', [$issue, 'approve']));
        $this->assertTrue(Gate::forUser($reviewer)->allows('review-fact-check', [$issue, 'approve']));
        $this->assertFalse(Gate::forUser($editor)->allows('review-fact-check', [$issue, 'approve']));
        $this->assertFalse(Gate::forUser($seo)->allows('review-fact-check', [$issue, 'approve']));
        $this->assertFalse(Gate::forUser($member)->allows('access-content-factory'));
    }

    public function test_system_and_self_reviewers_are_rejected_without_mutating_issue(): void
    {
        $this->seed();
        $issue = ContentFactoryIssue::query()->where('risk_level', 'critical')->firstOrFail();
        $system = User::factory()->create(['role' => 'fact_check_reviewer', 'email' => 'system@housechef.local', 'email_verified_at' => now()]);
        $issue->update(['reviewed_by_user_id' => $system->id]);

        $this->assertFalse(Gate::forUser($system)->allows('review-fact-check', [$issue, 'approve']));
        $this->assertSame('pending', $issue->fresh()->status);
    }

    public function test_only_admin_can_apply_revisions_or_bulk_changes(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $reviewer = User::factory()->create(['role' => 'fact_check_reviewer']);
        $editor = User::factory()->create(['role' => 'content_editor']);

        $this->assertTrue(Gate::forUser($admin)->allows('apply-content-revision'));
        $this->assertTrue(Gate::forUser($admin)->allows('run-duplicate-bulk'));
        $this->assertFalse(Gate::forUser($reviewer)->allows('apply-content-revision'));
        $this->assertFalse(Gate::forUser($editor)->allows('run-duplicate-bulk'));
    }
}
