<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Services\BackupService;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use RuntimeException;
use Tests\TestCase;

/**
 * Yedek doğrulayıcı, `migrations` tablosunu dökümün neresinde olursa bulur.
 *
 * mysqldump tabloları alfabetik döker; `migrations` veritabanı büyüdükçe
 * ileri kayar. Doğrulayıcı dökümün yalnız ilk 32 MiB'ını okuyordu. 14 Eylül
 * 2026'da tablo 35,9 MB'a düştü: sağlam ve eksiksiz yedek "geçersiz"
 * sayıldı, deploy güvenlik kuralı gereği durdu ve hiçbir değişiklik yayına
 * çıkmadı. Bir gün önceki yedekte tablo 31 MB'taydı; sınıra hiç yaklaşılmadığı
 * sanılıyordu.
 */
final class BackupVerifyReadsPastLargeTablesTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        Storage::fake('local');
        config([
            'observability.backup_disk' => 'local',
            'observability.backup_path' => 'backups',
        ]);
    }

    private function yedek(string $sql): string
    {
        $id = strtoupper((string) Str::ulid());
        $gecici = tempnam(sys_get_temp_dir(), 'hcgz');
        $gz = gzopen($gecici, 'wb1');
        gzwrite($gz, $sql);
        gzclose($gz);

        Storage::disk('local')->put("backups/{$id}/database.sql.gz", (string) file_get_contents($gecici));
        Storage::disk('local')->put("backups/{$id}/manifest.json", (string) json_encode([
            'backup_id' => $id,
            'database_driver' => 'mysql',
            'database_file' => 'database.sql.gz',
            'checksum' => hash_file('sha256', $gecici),
            'status' => 'created',
        ]));
        unlink($gecici);

        return $id;
    }

    public function test_migrations_tablosu_32_mibden_sonra_gelse_de_yedek_gecerlidir(): void
    {
        $dolgu = str_repeat("INSERT INTO `blog_posts` VALUES (1,'metin');\n", (int) ceil(36_000_000 / 45));
        $sql = "/*M!999999\\- enable the sandbox mode */\n".$dolgu
            ."CREATE TABLE `migrations` (`id` int unsigned NOT NULL);\n";

        $this->assertGreaterThan(33_554_432, strpos($sql, 'CREATE TABLE `migrations`'));

        $sonuc = app(BackupService::class)->verify($this->yedek($sql));

        $this->assertSame('verified', $sonuc['status']);
    }

    public function test_migrations_tablosu_hic_yoksa_yedek_hala_reddedilir(): void
    {
        // Sınırı genişletmek kapıyı açmak demek değil: şema içermeyen döküm
        // geri yüklenemez ve reddedilmeye devam etmeli.
        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('BACKUP_DATABASE_INVALID');

        app(BackupService::class)->verify($this->yedek("CREATE TABLE `recipes` (`id` int);\n"));
    }
}
