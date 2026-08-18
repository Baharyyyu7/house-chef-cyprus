<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

final class ProfilePageLayoutTest extends TestCase
{
    use RefreshDatabase;

    public function test_profile_page_groups_each_section_under_its_own_heading(): void
    {
        $yanit = $this->actingAs($this->uye())->get(route('profile'))->assertOk();

        // Üç bölüm de başlıksız kartlar hâlindeydi; hangisinin ne olduğu
        // ancak içine bakınca anlaşılıyordu.
        $yanit->assertSee('Hesap bilgileri')
            ->assertSee('Parola')
            ->assertSee('Hesabı sil');
    }

    public function test_profile_page_keeps_its_content_inside_the_page_container(): void
    {
        // Başlık kapsayıcının içindeydi, kartlar dışındaydı: kartlar ekranın
        // kenarına yapışıyor ve başlıkla hizalanmıyordu.
        $icerik = $this->actingAs($this->uye())->get(route('profile'))->assertOk()->getContent();

        $this->assertSame(1, substr_count((string) $icerik, 'hc-container hc-page'));
    }

    public function test_account_deletion_warns_that_it_cannot_be_undone(): void
    {
        $this->actingAs($this->uye())
            ->get(route('profile'))
            ->assertOk()
            ->assertSee('Bu işlem geri alınamaz.');
    }

    public function test_admin_area_is_linked_once_not_twice_for_an_admin(): void
    {
        // Başlık çubuğunda kendi "Yönetim" düğmesi dururken hesap menüsünde
        // de aynı yere giden "Yönetim alanı" satırı vardı. Masaüstü çubuğu ile
        // mobil çekmece ayrı görünümler, o ikisi kopya sayılmaz.
        $this->actingAs($this->uye('admin'))
            ->get(route('profile'))
            ->assertOk()
            ->assertSee('Yönetim')
            ->assertDontSee('Yönetim alanı');
    }

    private function uye(string $role = 'user'): User
    {
        return User::factory()->create(['role' => $role, 'email_verified_at' => now()]);
    }
}
