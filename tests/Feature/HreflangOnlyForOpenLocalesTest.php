<?php

declare(strict_types=1);

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

final class HreflangOnlyForOpenLocalesTest extends TestCase
{
    use RefreshDatabase;

    public function test_no_hreflang_is_emitted_while_only_one_language_is_open(): void
    {
        foreach (array_keys(config('locales.supported', [])) as $loc) {
            if ($loc !== 'tr') {
                config()->set("locales.supported.{$loc}.enabled", false);
            }
        }
        // Henüz yayına girmemiş bir dili hreflang ile bildirmek, olmayan bir
        // sayfayı vaat etmek olur: Google adresi tarar, 404 alır.
        $this->get(route('recipes.index'))
            ->assertOk()
            ->assertDontSee('hreflang', false);
    }

    public function test_opening_a_second_language_emits_hreflang_for_both(): void
    {
        config()->set('locales.supported.en.enabled', true);

        $yanit = $this->get(route('recipes.index'))->assertOk();

        $yanit->assertSee('hreflang="tr"', false)
            ->assertSee('hreflang="en"', false)
            ->assertSee('hreflang="x-default"', false)
            ->assertSee('og:locale:alternate', false);
    }

    public function test_a_closed_language_never_appears_in_hreflang(): void
    {
        config()->set('locales.supported.en.enabled', true);
        config()->set('locales.supported.pl.enabled', false);

        // Lehçe kapalı kaldığında hreflang kümesinde yer almaz.
        $this->get(route('recipes.index'))
            ->assertOk()
            ->assertDontSee('hreflang="pl"', false);
    }
}
