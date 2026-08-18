<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Services\BackupService;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Tests\TestCase;

/**
 * Yedekleme sözleşmesi hiç sınanmıyordu.
 *
 * Test bellek içi SQLite'ta atlanıyordu ve gerekçe olarak "dosya tabanlı
 * doğrulama desteklenmiyor" yazıyordu. Gerçek sebep başkaymış:
 * BackupService bellek içi veritabanını zaten VACUUM INTO ile geçici bir
 * dosyaya alıyor, ama RefreshDatabase her testi bir işleme sarıyor ve
 * SQLite işlem içinde VACUUM yapamıyor. Yani engel yeteneğin yokluğu değil,
 * testin kendi kurulumuydu.
 *
 * Bu sınıf DatabaseMigrations kullanıyor: şema kuruluyor ama işlem sarmalı
 * yok, dolayısıyla VACUUM çalışabiliyor. Böylece her deploy öncesi çalışan
 * tek koruma gerçekten sınanıyor - yedek alınıyor, doğrulanıyor, ve canlıya
 * geri yükleme denemesinin reddedildiği görülüyor.
 */
final class BackupContractTest extends TestCase
{
    use DatabaseMigrations;

    public function test_backup_create_list_verify_and_safe_restore_contract(): void
    {
        $created = app(BackupService::class)->create();

        $this->assertArrayHasKey('checksum', $created);

        $this->artisan('backup:list', ['--json' => true])->assertSuccessful();
        $this->artisan('backup:verify', ['backup_id' => $created['backup_id'], '--json' => true])->assertSuccessful();
        $this->artisan('backup:restore', ['backup_id' => $created['backup_id'], '--target' => 'testing', '--verify-only' => true])->assertSuccessful();

        // Canlıya geri yükleme her zaman reddedilmeli.
        $this->artisan('backup:restore', ['backup_id' => $created['backup_id'], '--target' => 'live'])->assertFailed();
    }
}
