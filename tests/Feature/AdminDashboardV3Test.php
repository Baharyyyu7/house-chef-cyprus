<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Models\AuditLog;
use App\Models\Ingredient;
use App\Models\Recipe;
use App\Models\User;
use App\Services\AdminDashboardService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Cache;
use Tests\TestCase;

final class AdminDashboardV3Test extends TestCase
{
    use RefreshDatabase;

    public function test_admin_dashboard_renders_the_v3_shell_period_controls_and_real_metric_sections(): void
    {
        $admin = $this->admin();

        $this->actingAs($admin)->get(route('admin.dashboard', ['period' => 30]))
            ->assertOk()
            ->assertSee('YÖNETİM / GENEL BAKIŞ')
            ->assertSee('İçerik ve operasyon özeti')
            ->assertSee('Toplam tarif')
            ->assertSee('Son 30 gün')
            ->assertSee('Hızlı işlemler')
            ->assertSee('Sistem durumu')
            ->assertSee('adminSidebarCollapsed', false)
            ->assertSee(route('admin.recipes.create'), false)
            ->assertSee(route('admin.backups.index'), false);
    }

    public function test_member_cannot_access_the_admin_dashboard(): void
    {
        $member = User::factory()->create(['role' => 'member', 'email_verified_at' => now()]);

        $this->actingAs($member)->get(route('admin.dashboard'))->assertForbidden();
    }

    public function test_dashboard_uses_real_recipe_user_and_ingredient_totals(): void
    {
        Recipe::create(['title' => 'Birinci tarif', 'slug' => 'birinci-tarif', 'servings' => 2, 'difficulty' => 'Kolay', 'status' => 'published', 'verification_status' => 'verified', 'published_at' => now()]);
        Recipe::create(['title' => 'İkinci tarif', 'slug' => 'ikinci-tarif', 'servings' => 2, 'difficulty' => 'Orta', 'status' => 'draft', 'verification_status' => 'needs_review']);
        Ingredient::create(['name' => 'Domates', 'canonical_name' => 'Domates', 'slug' => 'domates', 'is_active' => true]);
        User::factory()->create(['email_verified_at' => now()]);
        Cache::flush();

        $dashboard = app(AdminDashboardService::class)->dashboard(7);
        $byKey = collect($dashboard['cards'])->keyBy('key');

        $this->assertSame(2, $byKey['toplam_tarif']['current_value']);
        $this->assertSame(1, $byKey['yayinda']['current_value']);
        $this->assertSame(1, $byKey['dogrulanmis']['current_value']);
        $this->assertSame(1, $byKey['inceleme']['current_value']);
        $this->assertSame(1, $byKey['malzemeler']['current_value']);
        $this->assertSame(7, $dashboard['period']);
    }

    public function test_dashboard_exposes_accessible_real_chart_data_and_empty_states_without_fake_values(): void
    {
        Cache::flush();
        $empty = app(AdminDashboardService::class)->dashboard(1);

        $this->assertSame('empty', $empty['charts'][0]['data_status']);
        $this->assertSame('Veri henüz oluşmadı.', $empty['charts'][0]['summary']);

        Recipe::create(['title' => 'Grafik tarifi', 'slug' => 'grafik-tarifi', 'servings' => 2, 'difficulty' => 'Kolay', 'status' => 'published', 'verification_status' => 'verified']);
        Cache::flush();
        $available = app(AdminDashboardService::class)->dashboard(7);

        $this->assertSame('available', $available['charts'][1]['data_status']);
        $this->assertNotEmpty($available['charts'][1]['labels']);
        $this->assertNotEmpty($available['charts'][1]['summary']);
    }

    public function test_dashboard_analytics_are_cached_per_period_and_refresh_after_invalidation(): void
    {
        Recipe::create(['title' => 'Önbellek tarifi', 'slug' => 'onbellek-tarifi', 'servings' => 2, 'difficulty' => 'Kolay', 'status' => 'published', 'verification_status' => 'verified']);
        Cache::flush();
        $service = app(AdminDashboardService::class);
        $first = collect($service->dashboard(7)['cards'])->keyBy('key')['toplam_tarif']['current_value'];

        Recipe::create(['title' => 'İkinci önbellek tarifi', 'slug' => 'ikinci-onbellek-tarifi', 'servings' => 2, 'difficulty' => 'Kolay', 'status' => 'published', 'verification_status' => 'verified']);
        $cached = collect($service->dashboard(7)['cards'])->keyBy('key')['toplam_tarif']['current_value'];
        Cache::forget('admin.dashboard.v3.7');
        $refreshed = collect($service->dashboard(7)['cards'])->keyBy('key')['toplam_tarif']['current_value'];

        $this->assertSame(1, $first);
        $this->assertSame(1, $cached);
        $this->assertSame(2, $refreshed);
    }

    public function test_dashboard_activity_excludes_sensitive_metadata_and_internal_identifiers(): void
    {
        $admin = $this->admin();
        AuditLog::create([
            'user_id' => $admin->id,
            'action' => 'recipe.updated',
            'auditable_type' => Recipe::class,
            'auditable_id' => 987,
            'metadata' => ['authorization' => 'hidden-value', 'internal_id' => 987, 'recipe_public_id' => '01JSAFEACTIVITY000000000000'],
        ]);
        Cache::flush();

        $this->actingAs($admin)->get(route('admin.dashboard'))
            ->assertOk()
            ->assertSee('Recipe Updated')
            ->assertSee('01JSAFEACTIVITY000000000000')
            ->assertDontSee('hidden-value')
            ->assertDontSee('internal_id')
            ->assertDontSee('987');
    }

    private function admin(): User
    {
        return User::factory()->create(['role' => 'admin', 'email_verified_at' => now()]);
    }
}
