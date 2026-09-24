<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

final class AdminDisclosureStaysClosedTest extends TestCase
{
    use RefreshDatabase;

    /**
     * .hc-v3-card display:flex veriyordu ve bu sınıf <details> öğelerinde de
     * kullanılıyordu. display:flex bir <details>'e uygulanınca Chrome'un
     * yerel aç/kapa davranışı bozuluyor: panel kapalıyken de gövdesi
     * yerleşiyor. Yönetim panelinde iki kapalı panelin gövdesi 507 ve 453
     * piksel yer kaplıyordu; yalnızca overflow:hidden sayesinde
     * görünmüyorlardı, ekran okuyucu için hâlâ oradaydılar.
     */
    public function test_the_card_rule_does_not_reach_details_elements(): void
    {
        $stil = (string) file_get_contents(resource_path('css/app.css'));

        $this->assertStringContainsString('.hc-v3-card:not(details)', $stil);
        $this->assertStringNotContainsString(
            ".hc-v3-card {\n  display: flex;",
            $stil,
            'Kural hâlâ her karta, details dahil, uygulanıyor.'
        );
    }

    public function test_the_admin_panels_are_details_elements_and_start_closed(): void
    {
        $icerik = (string) $this->actingAs(User::factory()->create(['role' => 'admin']))
            ->get('/yonetim')->assertOk()->getContent();

        $this->assertStringContainsString('<details id="system-status" class="hc-admin-disclosure hc-v3-card">', $icerik);
        $this->assertStringNotContainsString('<details id="system-status" class="hc-admin-disclosure hc-v3-card" open', $icerik);
    }
}
