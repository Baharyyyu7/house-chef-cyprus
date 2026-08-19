<?php

declare(strict_types=1);

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

final class NoRawBladeLeaksIntoThePageTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Çerez bandında "Ölçüm@if($reklamSorulacak) ve reklam@endif çerezleri"
     * yazıyordu: Blade, bir kelimeye bitişik yazılan direktifi derlemiyor ve
     * kod düz metin olarak ekrana çıkıyor. Sayfa kaynağında derlenmemiş bir
     * direktif kalırsa ziyaretçi onu okur.
     */
    public function test_no_uncompiled_blade_directive_reaches_the_visitor(): void
    {
        config()->set('integrations.google.adsense.enabled', true);
        config()->set('integrations.google.analytics.enabled', true);

        foreach (['home', 'recipes.index', 'blog.index', 'shop.index', 'pricing'] as $rota) {
            $icerik = (string) $this->get(route($rota))->assertOk()->getContent();

            $this->assertDoesNotMatchRegularExpression(
                '/@(if|endif|foreach|endforeach|else|elseif|isset|endisset|unless|endunless)\b/',
                $icerik,
                $rota.' sayfasında derlenmemiş Blade direktifi var.'
            );

            $this->assertStringNotContainsString('{{', $icerik, $rota.' sayfasında işlenmemiş Blade ifadesi var.');
        }
    }
}
