<?php

declare(strict_types=1);

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Ana sayfada dokuz uydurma kişi ve dokuz uydurma yorum vardı. Altında
 * "temsili içeriktir" notu duruyordu, ama uydurma isim ve uydurma alıntı,
 * altına ne yazılırsa yazılsın sayfada sosyal kanıt gibi okunur.
 *
 * Bölümün tasarımı korundu, içeriği doğrulanabilir taahhütlerle değiştirildi.
 * Bu test uydurma isimlerin geri gelmesini engeller.
 */
final class HomepageNeverShowsInventedPeopleTest extends TestCase
{
    use RefreshDatabase;

    public function test_the_invented_names_are_gone(): void
    {
        $yanit = $this->get(route('home'))->assertOk();

        foreach (['Elif Karaca', 'Mert Yalçın'] as $uydurma) {
            $yanit->assertDontSee($uydurma);
        }
    }

    public function test_nothing_is_presented_as_a_quote_from_a_person(): void
    {
        foreach (config('testimonials.home', []) as $madde) {
            $this->assertArrayNotHasKey('quote', $madde);
            $this->assertNotSame('Ev mutfağı kullanıcısı', $madde['role'] ?? null);
        }
    }

    public function test_the_section_still_renders(): void
    {
        $this->get(route('home'))
            ->assertOk()
            ->assertSee('Neye güvenebilirsiniz?')
            ->assertSee('Onaysız görsel yayınlanmaz');
    }
}
