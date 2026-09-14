<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Artisan;
use Tests\TestCase;

final class ProductExperienceAuditTest extends TestCase
{
    use RefreshDatabase;

    public function test_public_layout_has_an_accessible_skip_link_and_a_modal_mobile_navigation_contract(): void
    {
        $this->get(route('home'))
            ->assertOk()
            ->assertSee('İçeriğe geç')
            ->assertSee('id="main-content"', false)
            ->assertSee('aria-modal="true"', false)
            ->assertSee('data-public-drawer-close', false);
    }

    public function test_product_experience_command_outputs_a_safe_stable_json_contract(): void
    {
        $this->assertSame(0, Artisan::call('ux:audit', ['--json' => true]));

        $report = json_decode(trim(Artisan::output()), true, flags: JSON_THROW_ON_ERROR);

        $this->assertSame('2026-08-01', $report['rule_version']);
        $this->assertSame('needs_browser_qa', $report['status']);
        $this->assertSame(['public', 'member', 'admin', 'overall'], array_keys($report['scores']));
        $this->assertNotContains(resource_path('views'), $report['surface_files_checked']);
        $this->assertArrayHasKey('manual_qa_required', $report);

        $this->artisan('ux:responsive-audit', ['--json' => true])->assertSuccessful();
        $this->artisan('ux:accessibility-audit', ['--json' => true])->assertSuccessful();
        $this->artisan('ux:theme-audit', ['--json' => true])->assertSuccessful();
        $this->artisan('ux:state-audit', ['--json' => true])->assertSuccessful();
        $this->artisan('ux:performance-audit', ['--json' => true])->assertSuccessful();
        $this->artisan('ux:health', ['--json' => true])->assertSuccessful();
    }

    public function test_product_experience_admin_command_center_is_admin_only_and_honest_about_manual_qa(): void
    {
        $admin = User::factory()->create(['role' => 'admin', 'email_verified_at' => now()]);
        $member = User::factory()->create(['role' => 'member', 'email_verified_at' => now()]);

        $this->actingAs($admin)->get(route('admin.product-experience.index'))
            ->assertOk()
            ->assertSee('Arayüz Kontrolü')
            ->assertSee('Tarayıcı QA bekliyor')
            ->assertSee('Gerçek tarayıcı');
        $this->actingAs($member)->get(route('admin.product-experience.index'))->assertForbidden();
    }
}
