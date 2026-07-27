<?php

declare(strict_types=1);

namespace App\Services;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use RuntimeException;

final class BackupService
{
    /** @return array<string,mixed> */
    public function create(): array
    {
        if (config('database.default') !== 'sqlite') {
            throw new RuntimeException('BACKUP_DRIVER_UNSUPPORTED');
        }
        $id = (string) Str::ulid();
        $dir = 'backups/'.$id;
        $disk = Storage::disk('local');
        $source = (string) config('database.connections.sqlite.database');
        if ($source === ':memory:') {
            $temporary = tempnam(sys_get_temp_dir(), 'housechef-snapshot-');
            if ($temporary === false) {
                throw new RuntimeException('BACKUP_COPY_FAILED');
            }
            @unlink($temporary);
            DB::unprepared("VACUUM INTO '".str_replace("'", "''", $temporary)."'");
            $database = file_get_contents($temporary);
            @unlink($temporary);
        } else {
            if (! is_file($source)) {
                throw new RuntimeException('BACKUP_DATABASE_MISSING');
            }
            $database = file_get_contents($source);
        }
        if ($database === false) {
            throw new RuntimeException('BACKUP_COPY_FAILED');
        }
        $disk->put($dir.'/database.sqlite', $database);
        $checksum = hash('sha256', $database);
        $manifest = ['backup_id' => $id, 'created_at' => now()->toIso8601String(), 'app_version' => trim((string) @shell_exec('git describe --tags --always 2>/dev/null')) ?: 'local', 'database_driver' => 'sqlite', 'database_size' => strlen($database), 'media_count' => 0, 'checksum' => $checksum, 'status' => 'created', 'verified_at' => null, 'source_environment' => app()->environment()];
        $disk->put($dir.'/manifest.json', json_encode($manifest, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES));

        return $manifest;
    }

    /** @return array<string,mixed> */
    public function verify(string $id): array
    {
        $disk = Storage::disk('local');
        $base = 'backups/'.$id;
        if (! preg_match('/^[0-9A-HJKMNP-TV-Z]{26}$/', $id) || ! $disk->exists($base.'/manifest.json') || ! $disk->exists($base.'/database.sqlite')) {
            throw new RuntimeException('BACKUP_NOT_FOUND');
        }
        $manifest = json_decode((string) $disk->get($base.'/manifest.json'), true);
        $database = $disk->get($base.'/database.sqlite');
        if (! is_array($manifest) || ! hash_equals((string) ($manifest['checksum'] ?? ''), hash('sha256', $database))) {
            throw new RuntimeException('BACKUP_CHECKSUM_INVALID');
        }
        $tmp = tempnam(sys_get_temp_dir(), 'housechef-backup-');
        file_put_contents($tmp, $database);
        $pdo = new \PDO('sqlite:'.$tmp);
        $tables = $pdo->query("select name from sqlite_master where type='table'")->fetchAll(\PDO::FETCH_COLUMN);
        @unlink($tmp);
        if (! in_array('migrations', $tables, true) || ! in_array('recipes', $tables, true)) {
            throw new RuntimeException('BACKUP_DATABASE_INVALID');
        }
        $manifest['verified_at'] = now()->toIso8601String();
        $manifest['status'] = 'verified';
        $disk->put($base.'/manifest.json', json_encode($manifest, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES));

        return $manifest;
    }

    /** @return array<int,array<string,mixed>> */
    public function list(): array
    {
        return collect(Storage::disk('local')->directories('backups'))->map(fn (string $path) => json_decode((string) Storage::disk('local')->get($path.'/manifest.json'), true))->filter(fn (mixed $manifest): bool => is_array($manifest))->sortByDesc('created_at')->values()->all();
    }
}
