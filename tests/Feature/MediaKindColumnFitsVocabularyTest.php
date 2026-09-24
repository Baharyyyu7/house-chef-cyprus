<?php

declare(strict_types=1);

namespace Tests\Feature;

use Tests\TestCase;

/**
 * SQLite varchar uzunluğunu yok sayar, MySQL saymaz. Bu yüzden kod tabanına
 * kolona sığmayan bir "kind" değeri eklenebiliyor, testler yeşil kalıyor ve
 * hata ilk kez üretim deploy'unda 1406 "Data too long" olarak çıkıyor.
 * Bu test sözlüğü şema sınırına karşı statik olarak doğrular.
 */
final class MediaKindColumnFitsVocabularyTest extends TestCase
{
    private const COLUMN_LIMIT = 64;

    public function test_every_media_kind_literal_fits_the_column(): void
    {
        $offenders = [];

        foreach ($this->kindLiterals() as $value => $files) {
            if (mb_strlen($value) > self::COLUMN_LIMIT) {
                $offenders[] = sprintf('%s (%d karakter) — %s', $value, mb_strlen($value), implode(', ', $files));
            }
        }

        $this->assertSame([], $offenders, "media.kind kolonuna sığmayan değerler:\n".implode("\n", $offenders));
    }

    public function test_the_migration_declares_at_least_the_documented_limit(): void
    {
        $migration = file_get_contents(database_path('migrations/2026_08_16_090000_widen_media_kind_column.php'));

        $this->assertIsString($migration);
        $this->assertMatchesRegularExpression(
            "/string\('kind',\s*".self::COLUMN_LIMIT.'\)/',
            $migration,
            'Migration ile bu testteki sınır birbirinden ayrıştı.'
        );
    }

    /** @return array<string, list<string>> */
    private function kindLiterals(): array
    {
        $found = [];
        $roots = [app_path('Services/Media'), app_path('Models'), app_path('Console/Commands')];

        foreach ($roots as $root) {
            if (! is_dir($root)) {
                continue;
            }

            $files = new \RecursiveIteratorIterator(new \RecursiveDirectoryIterator($root));
            foreach ($files as $file) {
                if (! $file instanceof \SplFileInfo || $file->getExtension() !== 'php') {
                    continue;
                }

                $contents = (string) file_get_contents($file->getPathname());
                preg_match_all("/'kind'\s*=>\s*'([a-z_]+)'/", $contents, $matches);
                foreach ($matches[1] as $value) {
                    $found[$value][] = $file->getFilename();
                }
            }
        }

        return $found;
    }
}
