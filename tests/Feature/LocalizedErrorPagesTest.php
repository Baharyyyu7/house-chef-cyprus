<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class LocalizedErrorPagesTest extends TestCase
{
    use RefreshDatabase;

    public function test_member_receives_the_localized_safe_forbidden_page_for_admin_routes(): void
    {
        $member = User::factory()->create(['role' => 'member']);

        $this->actingAs($member)
            ->get('/yonetim')
            ->assertForbidden()
            ->assertSee('Bu alana erişim yetkiniz yok.')
            ->assertDontSee('This action is unauthorized.');
    }

    public function test_error_views_are_turkish_noindex_pages_without_sensitive_details(): void
    {
        // Metnin kendisi değil, sayfanın Türkçe olduğu ve teknik ayrıntı
        // sızdırmadığı korunmak isteniyor. Metni sabit yazmak, her kopya
        // düzenlemesinde testi kırar ve asıl güvenceyi gizler.
        foreach ([419, 500] as $status) {
            $response = $this->view("errors.{$status}");

            $response->assertSee(__("ux.{$status}_heading"))
                ->assertSee('noindex,nofollow')
                ->assertDontSee(base_path());
        }
    }
}
