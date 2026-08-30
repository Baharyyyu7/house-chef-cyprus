<?php

declare(strict_types=1);

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * WCAG 2.5.8: dokunma hedefleri en az 24x24 CSS pikseli.
 *
 * Antigravity'nin bağımsız Chrome ölçümü altbilgi bağlantılarını 20px
 * yükseklikte buldu - dört sayfa da değil, her sayfada. Metin bağlantısı
 * olduğu için gözden kaçıyordu.
 *
 * Malzeme onay kutuları raporda 26x22 görünüyordu ama onlar tamamı
 * tıklanabilir bir <label> içinde: gerçek dokunma alanı satırın tamamı,
 * kutunun kendisi değil. O yüzden onlara dokunulmadı.
 */
final class TapTargetsMeetTheMinimumTest extends TestCase
{
    use RefreshDatabase;

    public function test_footer_links_carry_a_minimum_height(): void
    {
        $this->get(route('home'))
            ->assertOk()
            ->assertSee('[&>a]:min-h-[24px]', false);
    }

    public function test_the_utility_is_actually_generated(): void
    {
        // Keyfi Tailwind varyantı şablonda yazılıp derlemede üretilmezse
        // düzeltme yalnız kaynakta var olur, tarayıcıda değil.
        //
        // Derlenmiş varlıklar depoda tutulmuyor ve testler derlemeden önce
        // koşabiliyor; varlık yoksa test atlanıyor. Yanlış bir "geçti"
        // vermektense ölçmediğini söylemesi doğru.
        $dosyalar = glob(public_path('build/assets/*.css')) ?: [];

        if ($dosyalar === []) {
            $this->markTestSkipped('Derlenmiş CSS yok; bu kontrol yalnız derleme sonrası anlamlı.');
        }

        $css = collect($dosyalar)->map(fn (string $yol): string => (string) file_get_contents($yol))->implode('');

        $this->assertStringContainsString('min-height:24px', $css);
    }

    public function test_the_ingredient_checkbox_stays_inside_a_clickable_label(): void
    {
        // Kutunun kendisini büyütmek yerine etiketin tıklanabilirliğine
        // güveniyoruz; bu güvence kaybolursa hedef gerçekten küçülür.
        $this->assertStringContainsString(
            '<label class="flex cursor-pointer items-start gap-3"><input',
            (string) file_get_contents(resource_path('views/recipes/show.blade.php'))
        );
    }
}
