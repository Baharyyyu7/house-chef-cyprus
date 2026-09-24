<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

final class MediaIntakeButtonTest extends TestCase
{
    use RefreshDatabase;

    public function test_the_media_page_offers_the_intake_button(): void
    {
        // Aynı iş SSH ile çalıştırılıyordu; onayı verecek kişi terminale
        // girmek zorunda kalıyordu.
        $this->actingAs($this->yonetici())
            ->get(route('admin.media.index'))
            ->assertOk()
            ->assertSee('Teslim edilenleri içe aktar');
    }

    public function test_the_button_runs_the_intake_and_reports_back(): void
    {
        $this->actingAs($this->yonetici())
            ->post(route('admin.media.intake'))
            ->assertRedirect()
            ->assertSessionHas('status');
    }

    public function test_a_member_cannot_trigger_the_intake(): void
    {
        // İçe aktarma medya operasyonu; üye tetikleyemez.
        $this->actingAs(User::factory()->create(['role' => 'user', 'email_verified_at' => now()]))
            ->post(route('admin.media.intake'))
            ->assertForbidden();
    }

    public function test_a_guest_cannot_trigger_the_intake(): void
    {
        $this->post(route('admin.media.intake'))->assertRedirect(route('login'));
    }

    public function test_the_route_rejects_a_get_request(): void
    {
        // Durum değiştiren bir işlem bağlantıyla tetiklenmemeli: tarayıcı ön
        // getirmesi ya da bir arama motoru onu çalıştırabilirdi.
        $this->actingAs($this->yonetici())
            ->get('/yonetim/medya/ice-aktar')
            // Laravel eşleşmeyen yöntemi 404 olarak da döndürebiliyor;
            // önemli olan GET'in bu işi çalıştırmaması.
            ->assertNotFound();
    }

    private function yonetici(): User
    {
        return User::factory()->create(['role' => 'admin', 'email_verified_at' => now()]);
    }
}
