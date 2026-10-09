<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Models\User;
use App\Services\Seo\MachinaAeoAutomationService;
use App\Services\Seo\PrePublishSafetyHook;
use Illuminate\Foundation\Testing\RefreshDatabase;
use RuntimeException;
use Tests\TestCase;

final class MachinaAeoAutomationTest extends TestCase
{
    use RefreshDatabase;

    private function adminUser(): User
    {
        return User::factory()->create(['role' => 'admin', 'email_verified_at' => now()]);
    }

    public function test_four_pass_inspection_evaluates_all_dimensions(): void
    {
        $service = app(MachinaAeoAutomationService::class);
        $audit = $service->runFourPassAudit('https://housechefcyprus.com/private-chef', 'kıbrıs özel şef');

        $this->assertGreaterThanOrEqual(80, $audit['overall_score']);
        $this->assertArrayHasKey('pass_1_access_speed', $audit['passes']);
        $this->assertArrayHasKey('pass_2_competition', $audit['passes']);
        $this->assertArrayHasKey('pass_3_answer_engines', $audit['passes']);
        $this->assertArrayHasKey('pass_4_conversion_path', $audit['passes']);

        // Her iddia kaynak URL ile desteklenmeli (X postu kuralı)
        foreach ($audit['passes']['pass_2_competition']['content_gaps'] as $gap) {
            $this->assertNotEmpty($gap['source_url']);
            $this->assertStringStartsWith('http', $gap['source_url']);
        }

        // Rule Zero denetimi
        $this->assertTrue($audit['passes']['pass_4_conversion_path']['rule_zero_compliant']);

        // Tek değişiklik kuralı
        $this->assertNotEmpty($audit['single_recommended_fix']['summary']);
        $this->assertSame('WAITING_HUMAN_APPROVAL', $audit['single_recommended_fix']['status']);
    }

    public function test_safety_hook_blocks_unauthorized_publishing(): void
    {
        $hook = app(PrePublishSafetyHook::class);

        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('[GÜVENLİK ENGELİ]');

        // İnsan onayı olmadan çalıştırmayı dene -> durdurulmalı
        $hook->assertCanExecute(
            PrePublishSafetyHook::ACTION_PUBLISH_PAGE,
            'https://housechefcyprus.com/test',
            false
        );
    }

    public function test_safety_hook_allows_human_approved_action(): void
    {
        $hook = app(PrePublishSafetyHook::class);

        // İnsan onayı verildiğinde istisna fırlatmaz
        $hook->assertCanExecute(
            PrePublishSafetyHook::ACTION_PUBLISH_PAGE,
            'https://housechefcyprus.com/test',
            true,
            'kagan@example.com'
        );

        $this->assertTrue(true);
    }

    public function test_artisan_command_executes_weekly_loop(): void
    {
        $this->artisan('seo:machina-loop', [
            '--url' => 'https://housechefcyprus.com/private-chef',
            '--query' => 'kıbrıs özel şef',
        ])->assertSuccessful()
          ->expectsOutputToContain('MACHINA OPUS 5.5 SEO & AEO HAFTALIK DÖNGÜ');
    }

    public function test_admin_can_access_machina_loop_workspace(): void
    {
        $admin = $this->adminUser();

        $response = $this->actingAs($admin)->get(route('admin.seo.machina-loop'));
        $response->assertOk();
        $response->assertSee('HAFTANIN TEK DEĞİŞİKLİĞİ');
        $response->assertSee('Erişim ve Hız (Access & Speed)');
        $response->assertSee('Rekabet Analizi (Competition)');
    }

    public function test_admin_can_run_audit_via_post(): void
    {
        $admin = $this->adminUser();

        $response = $this->actingAs($admin)->post(route('admin.seo.machina-loop.audit'), [
            'url' => 'https://housechefcyprus.com/tarif/kibris-usulu-kolokas',
            'query' => 'kolokas tarifi',
        ]);

        $response->assertRedirect();
        $response->assertSessionHas('status');
    }

    public function test_admin_can_approve_weekly_fix(): void
    {
        $admin = $this->adminUser();

        $response = $this->actingAs($admin)->post(route('admin.seo.machina-loop.approve'), [
            'action_type' => 'INTERNAL_LINK_AND_CTA_POSITION',
            'summary' => 'Kolokas sayfasından Özel Şef sayfasına CTA ekleme',
        ]);

        $response->assertRedirect();
        $response->assertSessionHas('status', 'Onay denetim kaydına yazıldı. Öneri uygulanmadı; uygulama ayrı bir adımda yapılır.');
        $this->assertDatabaseHas('audit_logs', [
            'user_id' => $admin->id,
            'action' => 'seo.machina.approve',
        ]);
    }
}
