<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Models\Recipe;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Testing\TestResponse;
use Tests\TestCase;

/**
 * AdSense'te oluşturulan her birim kendi biçimindedir ve sayfada da o
 * özniteliklerle basılmak zorundadır. Yanlış öznitelikle basılan birim ya hiç
 * dolmaz ya da bozuk görünür — ikisi de sessizdir, hata vermez, sadece para
 * gelmez. Yuva tek bir biçim basıyordu; dördü de ayrı.
 */
final class EachAdUnitRendersItsOwnFormatTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        config()->set('integrations.google.adsense.enabled', true);
        config()->set('integrations.google.adsense.certified_cmp_enabled', true);
        config()->set('integrations.google.adsense.cmp_provider', 'google');
        config()->set('integrations.google.adsense.client_id', 'ca-pub-1614195335767159');
    }

    public function test_the_in_article_unit_carries_the_in_article_layout(): void
    {
        config()->set('monetization.slots.in_article', ['id' => '6007812595', 'format' => 'in_article']);

        $this->tarifSayfasi()
            ->assertSee('data-ad-layout="in-article"', false)
            ->assertSee('data-ad-slot="6007812595"', false);
    }

    public function test_the_multiplex_unit_asks_for_a_relaxed_grid(): void
    {
        config()->set('monetization.slots.multiplex', ['id' => '4694730924', 'format' => 'multiplex']);

        $this->tarifSayfasi()
            ->assertSee('data-ad-format="autorelaxed"', false)
            ->assertSee('data-ad-slot="4694730924"', false);
    }

    public function test_the_in_feed_unit_carries_its_layout_key(): void
    {
        // Anahtar olmadan feed birimi biçimini kaybeder.
        config()->set('monetization.slots.in_feed', [
            'id' => '4500802572',
            'format' => 'in_feed',
            'layout_key' => '-gw-3+1f-3d+2z',
        ]);
        config()->set('monetization.slots.in_article', ['id' => '4500802572', 'format' => 'in_feed', 'layout_key' => '-gw-3+1f-3d+2z']);

        $this->tarifSayfasi()
            ->assertSee('data-ad-layout-key="-gw-3+1f-3d+2z"', false);
    }

    public function test_the_display_unit_stays_responsive(): void
    {
        config()->set('monetization.slots.in_article', ['id' => '7904132011', 'format' => 'display']);

        $this->tarifSayfasi()
            ->assertSee('data-full-width-responsive="true"', false)
            ->assertSee('data-ad-format="auto"', false);
    }

    public function test_a_placeholder_id_still_blocks_every_format(): void
    {
        foreach (['in_article', 'multiplex', 'display'] as $bicim) {
            config()->set('monetization.slots.in_article', ['id' => '1234567890', 'format' => $bicim]);

            $this->tarifSayfasi()->assertDontSee('data-ad-slot', false);
        }
    }

    private function tarifSayfasi(): TestResponse
    {
        $recipe = Recipe::query()->create([
            'title' => 'Adana Kebabı',
            'slug' => 'adana-kebabi-'.uniqid(),
            'status' => 'published',
            'verification_status' => 'verified',
            'published_at' => now(),
        ]);

        return $this->get(route('recipes.show', $recipe))->assertOk();
    }
}
