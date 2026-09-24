<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Models\Ingredient;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use Tests\TestCase;

/**
 * Dolap sayfasının işi "neyin acilen kullanılması gerektiğini" söylemek.
 * Sıralama son kullanma tarihi olmayanları başa alıyordu ve özet yoktu.
 */
final class PantryWorkspaceTest extends TestCase
{
    use RefreshDatabase;

    public function test_items_closest_to_expiry_are_listed_first(): void
    {
        $user = $this->member();
        $this->stock($user, 'Tarihsiz', null);
        $this->stock($user, 'Yarin Biten', now()->addDay());
        $this->stock($user, 'Haftaya Biten', now()->addWeek());

        $page = $this->actingAs($user)->get(route('pantry.index'))->assertOk()->getContent();

        $this->assertIsString($page);
        // Formdaki malzeme <select>'i tüm adları alfabetik listeliyor; ölçüm yalnız kart listesinde yapılmalı.
        $body = substr($page, (int) strpos($page, 'Dolaptakiler'));

        $this->assertLessThan(
            strpos($body, 'Haftaya Biten'),
            strpos($body, 'Yarin Biten'),
            'Önce bitecek ürün listede daha üstte olmalı.'
        );
        $this->assertLessThan(
            strpos($body, 'Tarihsiz'),
            strpos($body, 'Haftaya Biten'),
            'Son kullanma tarihi girilmemiş ürünler tarihli olanların önüne geçmemeli.'
        );
    }

    public function test_the_summary_counts_what_needs_attention(): void
    {
        $user = $this->member();
        $this->stock($user, 'Dun Bitti', now()->subDay());
        $this->stock($user, 'Yarin Biten', now()->addDay());
        $this->stock($user, 'Haftaya Biten', now()->addWeek());

        $this->actingAs($user)->get(route('pantry.index'))
            ->assertOk()
            ->assertSee('süresi dolmuş', false)
            ->assertSee('3 gün içinde bitiyor', false)
            ->assertSee('Bitmek üzere olanları değerlendir', false);
    }

    public function test_an_empty_pantry_opens_the_form_without_an_extra_click(): void
    {
        $this->actingAs($this->member())->get(route('pantry.index'))
            ->assertOk()
            ->assertSee('formOpen: true', false);
    }

    public function test_a_stocked_pantry_keeps_the_form_collapsed(): void
    {
        $user = $this->member();
        $this->stock($user, 'Un', null);

        $this->actingAs($user)->get(route('pantry.index'))
            ->assertOk()
            ->assertSee('formOpen: false', false);
    }

    private function member(): User
    {
        return User::factory()->create(['email_verified_at' => now()]);
    }

    private function stock(User $user, string $name, ?\DateTimeInterface $expiresAt): void
    {
        $ingredient = Ingredient::query()->create(['name' => $name, 'slug' => Str::slug($name), 'is_active' => true]);

        $user->pantryItems()->create([
            'ingredient_id' => $ingredient->id,
            'quantity' => 1,
            'unit' => 'adet',
            'expires_at' => $expiresAt,
            'is_available' => true,
        ]);
    }
}
