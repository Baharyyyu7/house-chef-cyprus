<?php

declare(strict_types=1);

namespace App\Services\Integrity;

use App\Services\BackupService;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Storage;

/**
 * Yedek gerçekten geri yüklenebilir bir şey içeriyor mu?
 *
 * `backup:verify` checksum'ı ve dosyanın açılabildiğini doğruluyor - ama
 * içeriğin eksiksiz olduğunu değil. Bir migration yeni tablo eklediğinde ya
 * da döküm komutu bir tabloyu dışladığında checksum yine tutar, gzip yine
 * açılır ve yedek "doğrulanmış" görünür. Eksik olduğu ancak geri yükleme
 * anında anlaşılır - yani en kötü anda.
 *
 * Restore testi üretimde bilinçli olarak yasak (ayrı bir veritabanı ister).
 * Sunucu gerektirmeden kanıtlanabilecek en güçlü şey bu: dökümdeki tablo
 * kümesi canlı şemayla örtüşüyor mu.
 *
 * 2026-09-01'de elle ölçüldü: 103 tablo, canlı şemayla birebir, satır
 * sayıları da tutuyordu. Bu kontrol o ölçümü tekrarlanabilir kılıyor.
 */
final class BackupCompletenessProbe
{
    public function __construct(private readonly BackupService $backups) {}

    /** @return array<string, mixed> */
    public function run(?string $backupId = null): array
    {
        $id = $backupId ?? ($this->backups->list()[0]['backup_id'] ?? null);

        if (! is_string($id) || $id === '') {
            // Hiç yedek yoksa "eksik tablo yok" demek yanıltıcı olur.
            return $this->rapor('no_backup', null, [], []);
        }

        $yol = trim((string) config('observability.backup_path', 'backups'), '/').'/'.$id.'/database.sql.gz';
        $disk = Storage::disk((string) config('observability.backup_disk', 'local'));

        if (! $disk->exists($yol)) {
            return $this->rapor('dump_missing', $id, [], []);
        }

        $dokumdekiler = $this->dokumdekiTablolar($disk->path($yol));
        $canlidakiler = $this->canliTablolar();

        return $this->rapor(
            'ok',
            $id,
            array_values(array_diff($canlidakiler, $dokumdekiler)),
            array_values(array_diff($dokumdekiler, $canlidakiler)),
        );
    }

    /**
     * Dökümdeki tablo adları. Dosya 25 MB civarı; satır satır okunuyor,
     * belleğe alınmıyor.
     *
     * @return list<string>
     */
    private function dokumdekiTablolar(string $mutlakYol): array
    {
        $tablolar = [];
        $akis = gzopen($mutlakYol, 'rb');

        if ($akis === false) {
            return [];
        }

        try {
            while (($satir = gzgets($akis)) !== false) {
                if (preg_match('/^CREATE TABLE `([^`]+)`/', $satir, $eslesme) === 1) {
                    $tablolar[] = $eslesme[1];
                }
            }
        } finally {
            gzclose($akis);
        }

        sort($tablolar);

        return $tablolar;
    }

    /**
     * Canlı şemadaki tablolar.
     *
     * `SHOW TABLES` kullanılmıyor: MySQL'e özgü ve testler SQLite üzerinde
     * koşuyor. Şema oluşturucu iki sürücüde de çalışır. SQLite şema adını
     * önek olarak veriyor ("main.users"), o yüzden kırpılıyor.
     *
     * @return list<string>
     */
    private function canliTablolar(): array
    {
        $tablolar = array_map(
            fn (string $ad): string => str_contains($ad, '.') ? substr($ad, strrpos($ad, '.') + 1) : $ad,
            Schema::getTableListing()
        );

        sort($tablolar);

        return $tablolar;
    }

    /**
     * @param  list<string>  $eksik
     * @param  list<string>  $fazla
     * @return array<string, mixed>
     */
    private function rapor(string $durum, ?string $id, array $eksik, array $fazla): array
    {
        $kusurlu = count($eksik) + count($fazla);

        return [
            'status' => $durum !== 'ok' ? $durum : ($kusurlu === 0 ? 'complete' : 'incomplete'),
            'checked_at' => now()->toIso8601String(),
            'backup_id' => $id,
            // Manşet sayı: yedekte olmayan canlı tablo sayısı. Sıfır olmalı.
            'missing_tables' => count($eksik),
            'unexpected_tables' => count($fazla),
            'missing' => $eksik,
            'unexpected' => $fazla,
        ];
    }
}
