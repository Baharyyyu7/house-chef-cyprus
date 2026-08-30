<?php

declare(strict_types=1);

namespace Tests\Feature;

use Tests\TestCase;

/**
 * Karusel noktaları 6x6 pikseldi ve dokunma hedefi de o kadardı.
 *
 * WCAG 2.5.8 asgari 24x24 CSS pikseli istiyor; telefonda bu noktalara
 * basmak pratikte mümkün değildi. Görsel nokta aynı boyutta kalmalı -
 * büyümesi gereken düğmenin kendisi.
 */
final class CarouselDotsAreTappableTest extends TestCase
{
    public function test_the_button_meets_the_minimum_target_size(): void
    {
        $css = $this->css();

        $this->assertMatchesRegularExpression(
            '/\.hc-karusel__nokta \{[^}]*min-width:1\.5rem[^}]*height:1\.5rem/',
            $css,
            'Düğme en az 24x24 CSS pikseli olmalı.'
        );
    }

    public function test_the_visible_dot_stays_small(): void
    {
        // Hedefi büyütmek noktayı büyütmek değil: gösterge aynı kalmalı.
        $this->assertStringContainsString(
            '.hc-karusel__nokta::before { content:\'\'; display:block; height:.375rem; width:.375rem;',
            $this->css()
        );
    }

    public function test_the_active_state_still_widens_the_indicator(): void
    {
        $this->assertStringContainsString('.hc-karusel__nokta.is-etkin::before { width:1.4rem;', $this->css());
    }

    private function css(): string
    {
        return (string) file_get_contents(resource_path('css/app.css'));
    }
}
