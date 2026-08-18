<?php

declare(strict_types=1);

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Gizlilik, çerez ve kullanım koşulları bir zamanlar 301 ile yasal merkeze
 * toplanmıştı; kullanıcı tekrar ayrılmalarını istedi.
 *
 * Ayırırken tek kural önemli: bölümler kopyalanmaz, taşınır. Aynı metin iki
 * adreste dururken ikisi de yinelenen içerik sayılır ve arama tarafında
 * birbirini zayıflatır. Bu dosyanın asıl işi o kuralı korumak.
 */
final class PolicyPagesStandOnTheirOwnTest extends TestCase
{
    use RefreshDatabase;

    /** @return list<array{0:string,1:string}> */
    public static function sayfalar(): array
    {
        return [
            ['/gizlilik', 'Gizlilik ve Kişisel Veriler'],
            ['/cerezler', 'Çerez Politikası'],
            ['/kullanim-kosullari', 'Kullanım Koşulları'],
        ];
    }

    #[\PHPUnit\Framework\Attributes\DataProvider('sayfalar')]
    public function test_each_policy_has_its_own_page_and_canonical(string $yol, string $baslik): void
    {
        $this->get($yol)
            ->assertOk()
            ->assertSee($baslik)
            ->assertSee('rel="canonical" href="'.url($yol).'"', false);
    }

    public function test_the_moved_text_is_not_repeated_in_the_legal_centre(): void
    {
        $merkez = $this->get(route('legal.center'))->assertOk();

        foreach (['gizlilik-ve-kisisel-veriler', 'cerez-politikasi', 'kullanim-kosullari'] as $bolum) {
            $merkez->assertDontSee('id="'.$bolum.'"', false);
        }
    }

    public function test_the_legal_centre_still_links_to_all_three(): void
    {
        $merkez = $this->get(route('legal.center'))->assertOk();

        foreach (['/gizlilik', '/cerezler', '/kullanim-kosullari'] as $yol) {
            $merkez->assertSee(url($yol), false);
        }
    }

    public function test_the_legal_centre_keeps_the_sections_that_did_not_move(): void
    {
        $this->get(route('legal.center'))
            ->assertOk()
            ->assertSee('id="tarif-ve-saglik-uyarilari"', false)
            ->assertSee('id="telif-haklari"', false);
    }

    public function test_the_old_cookie_address_still_resolves(): void
    {
        $this->get('/cerez-politikasi')->assertRedirect('/cerezler');
    }

    public function test_an_unknown_policy_key_is_not_invented(): void
    {
        $this->get('/gizlilik-olmayan-sayfa')->assertNotFound();
    }
}
