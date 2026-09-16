<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Services\StepDurationParser;
use PHPUnit\Framework\Attributes\DataProvider;
use Tests\TestCase;

/**
 * Adım metnindeki süreler zamanlayıcı düğmesini besler.
 *
 * duration_seconds boş olan adımlarda düğme bu ayrıştırıcıya bağlıdır:
 * eşleşme yoksa düğme basılmaz, yanlış eşleşme (fırın derecesi) asla
 * süre sayılmaz.
 */
final class StepDurationParserTest extends TestCase
{
    #[DataProvider('sureler')]
    public function test_metinler_dakikaya_cevrilir(?string $metin, ?int $beklenen): void
    {
        $this->assertSame($beklenen, app(StepDurationParser::class)->minutes($metin));
    }

    /** @return array<string, array{?string, ?int}> */
    public static function sureler(): array
    {
        return [
            'dakika' => ['Fırında 15 dakika pişirin.', 15],
            'dk kısaltması' => ['Tavada 45 dk kavurun.', 45],
            'saat' => ['Kısık ateşte 1 saat pişirin.', 60],
            'aralıkta üst sınır' => ['10-12 dakika dinlendirin.', 12],
            'derece süre değildir' => ['180 derecede ısıtın.', null],
            'süre yok' => ['Tuz ekleyip karıştırın.', null],
            'boş metin' => [null, null],
            'kısa saniye yok sayılır' => ['30 saniye karıştırın.', null],
            'uzun saniye dakikaya çevrilir' => ['90 saniye kavurun.', 2],
        ];
    }
}
