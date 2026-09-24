<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Models\Recipe;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Üyelikle açılan dolap, koleksiyon ve pişirme akışı kayıt sayfasında tek bir
 * cümlenin içinde kalıyordu; ziyaretçi neye kaydolduğunu bilmeden form
 * dolduruyordu. Gelir bu üründen gelecekse, önce ne olduğunun görünmesi gerekir.
 *
 * Kartlarda anlatılanların hepsi sitede çalışan özellikler, vaat değil; testler
 * bu yüzden hem metni hem de arkasındaki rotanın varlığını doğrular.
 */
final class PublicPagesShowWhatMembershipOpensTest extends TestCase
{
    use RefreshDatabase;

    public function test_the_signup_page_names_what_a_member_actually_gets(): void
    {
        $this->get(route('register'))
            ->assertOk()
            ->assertSee('Malzeme dolabı', false)
            ->assertSee('Favoriler ve koleksiyonlar', false)
            ->assertSee('Pişirme modu', false);
    }

    public function test_every_promised_feature_has_a_route_behind_it(): void
    {
        foreach (['pantry.index', 'favorites.index', 'collections.index', 'personalization.index'] as $rota) {
            $this->assertTrue(app('router')->has($rota), $rota.' rotası yok ama kayıt sayfasında vaat ediliyor.');
        }
    }

    public function test_recipe_cards_reveal_as_the_reader_scrolls(): void
    {
        Recipe::query()->create([
            'title' => 'Adana Kebabı',
            'slug' => 'adana-kebabi',
            'status' => 'published',
            'verification_status' => 'verified',
            'published_at' => now(),
        ]);

        // Başlangıç durumunu JavaScript ekler: betik çalışmazsa içerik gizlenmez.
        $this->get(route('recipes.index'))
            ->assertOk()
            ->assertSee('data-reveal-group', false);
    }

    public function test_the_reveal_is_skipped_when_the_reader_asked_for_less_motion(): void
    {
        $this->get(route('recipes.index'))
            ->assertOk()
            ->assertSee('prefers-reduced-motion: reduce', false);
    }
}
