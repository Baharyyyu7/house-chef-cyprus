<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Services\Integrity\BackupCompletenessProbe;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

/**
 * Yedek gerçekten geri yüklenebilir bir şey içeriyor mu?
 *
 * `backup:verify` checksum'ı ve dosyanın açılabildiğini doğruluyor, içeriğin
 * eksiksiz olduğunu değil. Bir migration yeni tablo eklediğinde ya da döküm
 * bir tabloyu dışladığında checksum yine tutar, gzip yine açılır ve yedek
 * "doğrulanmış" görünür. Eksik olduğu ancak geri yükleme anında anlaşılır.
 *
 * Restore testi üretimde bilinçli olarak yasak - ayrı bir veritabanı ister.
 * Sunucu gerektirmeden kanıtlanabilecek en güçlü şey tablo kümesi.
 */
final class BackupCompletenessTest extends TestCase
{
    use RefreshDatabase;

    public function test_a_dump_missing_a_live_table_is_reported(): void
    {
        // Asıl yakalanmak istenen kusur: sessiz veri kaybı.
        $rapor = $this->probe($this->dokum(['users']));

        $this->assertSame('incomplete', $rapor['status']);
        $this->assertGreaterThan(0, $rapor['missing_tables']);
        $this->assertNotContains('users', $rapor['missing']);
    }

    public function test_a_dump_covering_every_live_table_is_complete(): void
    {
        $rapor = $this->probe($this->dokum($this->canliTablolar()));

        $this->assertSame('complete', $rapor['status']);
        $this->assertSame(0, $rapor['missing_tables']);
    }

    public function test_no_backup_is_not_reported_as_healthy(): void
    {
        // Hiç yedek yokken "eksik tablo yok" demek en tehlikeli yanlış olurdu.
        Storage::fake('local');

        $this->assertSame('no_backup', app(BackupCompletenessProbe::class)->run()['status']);
    }

    public function test_a_manifest_without_its_dump_is_not_reported_as_healthy(): void
    {
        Storage::fake('local');
        Storage::disk('local')->put('backups/01TESTYEDEK00000000000000/manifest.json', '{}');

        $this->assertSame('dump_missing', app(BackupCompletenessProbe::class)->run('01TESTYEDEK00000000000000')['status']);
    }

    /** @param list<string> $tablolar */
    private function dokum(array $tablolar): string
    {
        $sql = '';
        foreach ($tablolar as $tablo) {
            $sql .= "CREATE TABLE `{$tablo}` (\n  `id` int\n);\n";
        }

        return (string) gzencode($sql);
    }

    /** @return array<string, mixed> */
    private function probe(string $gzip): array
    {
        Storage::fake('local');
        Storage::disk('local')->put('backups/01TESTYEDEK00000000000000/manifest.json', '{}');
        Storage::disk('local')->put('backups/01TESTYEDEK00000000000000/database.sql.gz', $gzip);

        return app(BackupCompletenessProbe::class)->run('01TESTYEDEK00000000000000');
    }

    /** @return list<string> */
    private function canliTablolar(): array
    {
        $tablolar = array_map(
            fn (string $ad): string => str_contains($ad, '.') ? substr($ad, strrpos($ad, '.') + 1) : $ad,
            \Illuminate\Support\Facades\Schema::getTableListing()
        );

        sort($tablolar);

        return $tablolar;
    }
}
