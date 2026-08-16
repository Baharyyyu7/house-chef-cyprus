<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Services\Media\PhotographicContentAnalyzer;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

/**
 * Denetim yalnız kenar enerjisine bakıyordu ve altı üretilmiş grafik bu
 * kontrolü geçip onay ızgarasına düştü -- "Sütlaç" diye pembe zemin, beyaz
 * elips ve gri dikdörtgenden ibaret bir görsel 23.86 enerji ölçtü, eşik ise
 * 6.0'dı. Birkaç keskin kenar, yüzeyin tamamı düz olsa bile yeterli enerji
 * üretiyor.
 *
 * Fixture'lar üretilmiş örnek değil: ikisi de canlıdan indirilen gerçek
 * dosyalar, biri o sahte "sütlaç" grafiği, diğeri Adana Kebabı fotoğrafı.
 */
final class PhotographicAnalyzerRejectsFlatArtworkTest extends TestCase
{
    public function test_a_flat_generated_graphic_is_not_accepted_as_a_photograph(): void
    {
        $sonuc = $this->analyze('uretilmis-grafik.jpg');

        $this->assertTrue($sonuc['analyzed']);
        $this->assertFalse($sonuc['photographic']);
        // Kaçıran sinyal buydu: enerji eşiği rahatça geçiliyor.
        $this->assertGreaterThan(PhotographicContentAnalyzer::ENERGY_THRESHOLD, $sonuc['energy']);
        $this->assertLessThan(PhotographicContentAnalyzer::COLOUR_THRESHOLD, $sonuc['unique_colors']);
    }

    public function test_a_real_food_photograph_is_still_accepted(): void
    {
        $sonuc = $this->analyze('gercek-fotograf.jpg');

        $this->assertTrue($sonuc['analyzed']);
        $this->assertTrue($sonuc['photographic']);
        $this->assertGreaterThanOrEqual(PhotographicContentAnalyzer::COLOUR_THRESHOLD, $sonuc['unique_colors']);
    }

    public function test_the_two_are_separated_by_a_wide_margin(): void
    {
        // Eşiğin kıl payı değil, geniş bir boşluğun ortasında durduğunu
        // sabitler; aksi hâlde sıradan bir fotoğraf yanlışlıkla elenebilir.
        $sahte = $this->analyze('uretilmis-grafik.jpg')['unique_colors'];
        $gercek = $this->analyze('gercek-fotograf.jpg')['unique_colors'];

        $this->assertGreaterThan($sahte * 3, $gercek);
    }

    /** @return array{analyzed:bool,photographic:bool|null,energy:float|null,unique_colors:int|null,reason:string|null} */
    private function analyze(string $dosya): array
    {
        Storage::fake('public');
        Storage::disk('public')->put('media/test/'.$dosya, (string) file_get_contents(base_path('tests/Fixtures/media/'.$dosya)));

        return app(PhotographicContentAnalyzer::class)->analyze('public', 'media/test/'.$dosya);
    }
}
