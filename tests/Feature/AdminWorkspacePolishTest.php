<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Models\Media;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Blade;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

final class AdminWorkspacePolishTest extends TestCase
{
    use RefreshDatabase;

    public function test_empty_map_keeps_country_geometry_without_inventing_activity(): void
    {
        $html = $this->renderMap('member_profile_aggregate', false);

        $this->assertStringContainsString('id="ulke-TR"', $html);
        $this->assertStringContainsString('Konum özeti için henüz veri yok', $html);
        $this->assertStringNotContainsString('class="hc-world-map__ulke-aktif"', $html);
        $this->assertStringContainsString('etkin üye bulunmuyor', $html);
    }

    public function test_populated_map_has_accurate_units_and_keyboard_tooltip_position(): void
    {
        foreach (['visitor_country_aggregate' => 'ziyaret', 'member_profile_aggregate' => 'etkin üye'] as $source => $unit) {
            $html = $this->renderMap($source, true);
            $this->assertStringContainsString('aria-label="Türkiye: 12 '.$unit.'"', $html);
            $this->assertStringContainsString('konumla($event)', $html);
            $this->assertStringContainsString('@blur="ulke = null"', $html);
        }
    }

    public function test_media_preview_never_displays_placeholder_art_or_missing_local_file(): void
    {
        Storage::fake('public');
        foreach ([['fallbacks/sample.svg', true, 'Yer tutucu'], ['media/missing.jpg', false, 'Görsel dosyası bulunamadı']] as [$path, $placeholder, $label]) {
            $item = new Media(['path' => $path, 'disk' => 'public', 'visibility' => 'public', 'is_placeholder' => $placeholder]);
            $html = Blade::render('<x-admin.media-preview :item="$item" />', compact('item'));
            $this->assertStringContainsString($label, $html);
            $this->assertStringNotContainsString('<img ', $html);
        }
    }

    public function test_real_media_preview_has_an_explicit_network_failure_state(): void
    {
        Storage::fake('public');
        Storage::disk('public')->put('media/real.jpg', 'image-fixture');
        $item = new Media(['path' => 'media/real.jpg', 'disk' => 'public', 'visibility' => 'public', 'is_placeholder' => false]);
        $html = Blade::render('<x-admin.media-preview :item="$item" />', compact('item'));
        $this->assertStringContainsString('/storage/media/real.jpg', $html);
        $this->assertStringContainsString('x-on:error="failed = true"', $html);
        $this->assertStringContainsString('Görsel yüklenemedi', $html);
    }

    public function test_admin_shell_has_unique_search_labels_and_mobile_focus_handling(): void
    {
        $this->actingAs(User::factory()->create(['role' => 'admin', 'email_verified_at' => now()]))
            ->get(route('admin.dashboard'))
            ->assertOk()
            ->assertSee('id="admin-sidebar-search"', false)
            ->assertSee('id="admin-sidebar-search-mobile"', false)
            ->assertSee('id="admin-main-content"', false)
            ->assertSee('getClientRects().length', false)
            ->assertSee('Eşleşen bölüm bulunamadı.')
            ->assertSee('title="Çıkış yap"', false);
    }

    private function renderMap(string $source, bool $available): string
    {
        $map = [
            'source' => $source, 'data_status' => $available ? 'available' : 'empty',
            'summary' => 'Ülke toplamları', 'total_members' => $available ? 12 : 0,
            'countries' => $available ? [['country_code' => 'TR', 'label' => 'Türkiye', 'members' => 12, 'x' => 541, 'y' => 133]] : [],
        ];

        return Blade::render('<x-admin.world-activity-map :map="$map" period-label="Son 7 gün" />', compact('map'));
    }
}
