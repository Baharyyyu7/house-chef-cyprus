<?php

declare(strict_types=1);

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Bu bölüm dokuz uydurma kişiyi dokuz uydurma alıntıyla gösteriyordu ve bu
 * test onların ekranda olduğunu doğruluyordu. Altındaki "temsili içeriktir"
 * notu iyi niyetliydi ama yeterli değil: uydurma isim ve uydurma alıntı,
 * altına ne yazılırsa yazılsın sayfada sosyal kanıt gibi okunur.
 *
 * Bölümün tasarımı ve hareketi korundu; içeriği sitede doğrulanabilir
 * taahhütlerle değiştirildi. Test artık bunu koruyor.
 */
final class HomeTestimonialsTest extends TestCase
{
    use RefreshDatabase;

    public function test_the_section_renders_with_its_animation_controls(): void
    {
        $this->get(route('home'))
            ->assertOk()
            ->assertSee('Neye güvenebilirsiniz?')
            ->assertSee('Onaysız görsel yayınlanmaz')
            ->assertSee('Yorum akışını duraklat', false)
            ->assertDontSee('data-testimonial-avatar', false);
    }

    public function test_the_disclosure_says_these_are_commitments_not_reviews(): void
    {
        $aciklama = (string) config('testimonials.disclosure');

        $this->assertNotEmpty($aciklama);
        $this->assertStringContainsString('kullanıcı yorumu değil', $aciklama);

        $this->get(route('home'))->assertOk()->assertSee('kullanıcı yorumu değil');
    }

    public function test_every_item_is_a_checkable_claim_and_never_a_person(): void
    {
        $maddeler = collect(config('testimonials.home'));

        $this->assertNotEmpty($maddeler);

        $maddeler->each(function (array $madde): void {
            $this->assertNotEmpty($madde['name']);
            $this->assertNotEmpty($madde['role']);
            $this->assertNotEmpty($madde['text']);
            // Yüz yok: fotoğraflı sahte kişi hiç oluşmasın.
            $this->assertNull($madde['image']);
            // Başlık bir iddia olmalı, bir insan adı değil. Ayırt edici:
            // "Elif Karaca" gibi adlarda iki kelime de büyük harfle başlar;
            // "Elinizdekilerle eşleştirme" gibi bir iddiada başlamaz.
            $this->assertDoesNotMatchRegularExpression(
                '/^\p{Lu}\p{L}+\s+\p{Lu}\p{L}+$/u',
                $madde['name'],
                'Bölüme insan adına benzeyen bir başlık girmiş: '.$madde['name']
            );
        });
    }
}
