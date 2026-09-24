<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

final class AdminDashboardHasNoDeadSpaceTest extends TestCase
{
    use RefreshDatabase;

    /**
     * İki sütunlu ızgarada satır yüksekliği en uzun sütuna göre ölçülüyordu
     * ve kısa olan sağ rayı da o boya geriyordu: solda 3.173 karakter varken
     * sağda 470 karakter vardı ama ikisi de 2.829 piksel yüksekliğindeydi.
     * Aradaki fark ekranda boş beyaz alan olarak duruyordu ve belge 15.871
     * piksele çıkıyordu. Canlıda ölçtüm: items-start ile 4.322 piksele indi.
     */
    public function test_the_two_column_grid_does_not_stretch_its_short_rail(): void
    {
        $icerik = $this->panel();

        $this->assertStringContainsString(
            'grid items-start gap-6 xl:grid-cols-[minmax(0,1fr)_20rem]',
            $icerik,
            'Izgara kısa sütunu en uzun sütunun boyuna geriyor.'
        );
    }

    public function test_no_two_column_admin_grid_is_left_stretching(): void
    {
        // Aynı desenin başka bir örneği eklenirse burada yakalansın.
        $sablon = (string) file_get_contents(resource_path('views/admin/dashboard.blade.php'));

        preg_match_all('/class="[^"]*\bgrid\b[^"]*grid-cols-\[[^"]*"/', $sablon, $eslesmeler);

        foreach ($eslesmeler[0] as $sinif) {
            $this->assertStringContainsString(
                'items-start',
                $sinif,
                'Sütunları farklı uzunlukta olan ızgara items-start almalı: '.$sinif
            );
        }
    }

    private function panel(): string
    {
        return (string) $this->actingAs(User::factory()->create(['role' => 'admin']))
            ->get('/yonetim')->assertOk()->getContent();
    }
}
