<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * "Ne Pişirebilirim?" üyelik duvarının arkasındayken arama motorları sayfayı hiç
 * göremiyordu. Herkese açık kalması ve misafirde çökmemesi bu testlerle korunur.
 */
final class IngredientExplorerPublicAccessTest extends TestCase
{
    use RefreshDatabase;

    public function test_guests_can_open_the_explorer(): void
    {
        $this->get(route('ingredients.explore'))
            ->assertOk()
            ->assertSee('Ne Pişirebilirim?', false)
            ->assertDontSee('Cmd/Ctrl', false);
    }

    public function test_guest_pantry_shortcut_does_not_crash(): void
    {
        // use_pantry misafirde de gönderilebilir; kullanıcı yokken sessizce yok sayılmalı.
        $this->get(route('ingredients.explore', ['use_pantry' => 1]))->assertOk();
    }

    public function test_members_still_reach_their_pantry_shortcut(): void
    {
        $user = User::factory()->create(['email_verified_at' => now()]);

        $this->actingAs($user)
            ->get(route('ingredients.explore'))
            ->assertOk()
            ->assertSee('Dolabımdaki', false);
    }

    public function test_the_page_is_listed_in_the_sitemap(): void
    {
        $this->get('/sitemaps/pages.xml')
            ->assertOk()
            ->assertSee(route('ingredients.explore'), false);
    }
}
