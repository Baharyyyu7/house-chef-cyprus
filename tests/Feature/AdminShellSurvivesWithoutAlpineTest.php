<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

final class AdminShellSurvivesWithoutAlpineTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Yönetim panelinin ızgara sütunları ve kenar çubuğu genişliği yalnız
     * Alpine bağlamasından geliyordu. Alpine çalışmadığı anda sütun tanımı
     * hiç oluşmuyor, sabit konumlu kenar çubuğu içeriğin üstüne biniyor ve
     * panel okunamaz hale geliyordu. Livewire'ı kaldırdığımda tam olarak bu
     * oldu.
     */
    public function test_the_grid_columns_come_from_a_static_class(): void
    {
        $icerik = $this->panel();

        $this->assertStringContainsString('lg:grid-cols-[17.5rem_minmax(0,1fr)]', $icerik);
    }

    public function test_the_sidebar_has_a_width_without_alpine(): void
    {
        $icerik = $this->panel();

        $this->assertMatchesRegularExpression(
            '/admin-shell__sidebar[^"]*\bw-\[17\.5rem\]/',
            $icerik,
            'Kenar çubuğu genişliği yalnız Alpine bağlamasından geliyor.'
        );
    }

    public function test_alpine_only_adds_the_collapsed_variant(): void
    {
        // Alpine'in işi durumu değiştirmek; taban düzeni kurmak değil.
        $icerik = $this->panel();

        $this->assertStringContainsString("adminSidebarCollapsed && 'lg:!grid-cols-[5.25rem_minmax(0,1fr)]'", $icerik);
    }

    private function panel(): string
    {
        $yonetici = User::factory()->create(['role' => 'admin']);

        return (string) $this->actingAs($yonetici)->get('/yonetim')->assertOk()->getContent();
    }
}
