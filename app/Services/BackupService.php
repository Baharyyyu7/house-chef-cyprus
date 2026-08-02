<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\AuditLog;
use Illuminate\Filesystem\FilesystemAdapter;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use RuntimeException;
use Symfony\Component\Process\Process;

final class BackupService
{
    /** @return array<string, mixed> */
    public function create(string $type = 'manual'): array
    {
        return match (config('database.default')) {
            'sqlite' => $this->createSqlite($type),
            'mysql' => $this->createMysql($type),
            default => throw new RuntimeException('BACKUP_DRIVER_UNSUPPORTED'),
        };
    }

    /** @return array<string, mixed> */
    public function verify(string $id): array
    {
        $disk = $this->disk();
        $base = $this->basePath().'/'.$id;
        if (! $this->validId($id) || ! $disk->exists($base.'/manifest.json')) {
            throw new RuntimeException('BACKUP_NOT_FOUND');
        }

        $manifest = json_decode((string) $disk->get($base.'/manifest.json'), true);
        if (! is_array($manifest)) {
            throw new RuntimeException('BACKUP_CHECKSUM_INVALID');
        }

        $driver = (string) ($manifest['database_driver'] ?? '');
        $file = $this->databaseFile($driver);
        if ($file === null || ! $disk->exists($base.'/'.$file)) {
            throw new RuntimeException('BACKUP_FILE_MISSING');
        }

        $temporary = $this->copyFromDisk($base.'/'.$file);
        try {
            $checksum = hash_file('sha256', $temporary);
            if ($checksum === false) {
                throw new RuntimeException('BACKUP_CHECKSUM_FAILED');
            }
            if (! hash_equals((string) ($manifest['checksum'] ?? ''), $checksum)) {
                throw new RuntimeException('BACKUP_CHECKSUM_INVALID');
            }

            match ($driver) {
                'sqlite' => $this->verifySqlite($temporary),
                'mysql' => $this->verifyMysqlDump($temporary),
                default => throw new RuntimeException('BACKUP_DATABASE_INVALID'),
            };
        } finally {
            $this->secureDelete($temporary);
        }

        $manifest['verified_at'] = now()->toIso8601String();
        $manifest['status'] = 'verified';
        $this->putManifest($base.'/manifest.json', $manifest);

        return $manifest;
    }

    /** @return array<string, mixed> */
    public function markRestoreTest(string $id): array
    {
        $manifest = $this->verify($id);
        $manifest['restore_test_status'] = 'verified_only';
        $manifest['restore_tested_at'] = now()->toIso8601String();
        $this->putManifest($this->basePath().'/'.$id.'/manifest.json', $manifest);

        return $manifest;
    }

    /**
     * Restores a MySQL backup only into the separately configured testing connection.
     * This is intentionally unavailable in production and never targets the default connection.
     *
     * @return array<string, mixed>
     */
    public function restoreTest(string $id, string $target = 'testing'): array
    {
        if (app()->environment('production') || $target !== 'testing') {
            throw new RuntimeException('RESTORE_TARGET_FORBIDDEN');
        }

        $manifest = $this->verify($id);
        if (($manifest['database_driver'] ?? null) !== 'mysql') {
            return $this->markRestoreTest($id);
        }

        $connection = config('database.connections.testing');
        $default = config('database.connections.'.config('database.default'));
        if (! is_array($connection) || ! array_key_exists('database', $connection) || ($connection['driver'] ?? null) !== 'mysql' || ! filled($connection['database'])
            || $connection['database'] === ($default['database'] ?? null)) {
            throw new RuntimeException('RESTORE_TEST_DATABASE_REQUIRED');
        }

        $backup = $this->copyFromDisk($this->basePath().'/'.$id.'/database.sql.gz');
        $defaults = $this->writeMysqlDefaults($connection);
        try {
            $this->restoreMysqlDump($backup, $this->mysqlBinary(), $defaults, (string) $connection['database']);
        } finally {
            $this->secureDelete($backup);
            $this->secureDelete($defaults);
        }

        $manifest['restore_test_status'] = 'restored_testing_database';
        $manifest['restore_tested_at'] = now()->toIso8601String();
        $this->putManifest($this->basePath().'/'.$id.'/manifest.json', $manifest);

        return $manifest;
    }

    /** @return array<int, array<string, mixed>> */
    public function list(): array
    {
        return collect($this->disk()->directories($this->basePath()))
            ->map(fn (string $path) => $this->disk()->exists($path.'/manifest.json') ? json_decode((string) $this->disk()->get($path.'/manifest.json'), true) : null)
            ->filter(fn (mixed $manifest): bool => is_array($manifest))
            ->sortByDesc('created_at')
            ->values()
            ->all();
    }

    /** @return array{dry_run:bool,candidates:int,deleted:int,ids:array<int,string>} */
    public function cleanup(bool $delete = false): array
    {
        $backups = collect($this->list())->sortByDesc('created_at')->values();
        $minimum = max(1, (int) config('observability.backup_minimum_keep'));
        $threshold = now()->subDays((int) config('observability.backup_retention_days'))->toIso8601String();
        $verified = $backups->firstWhere('status', 'verified');
        $ids = $backups->filter(fn (array $item, int $index): bool => $index >= $minimum && ($item['created_at'] ?? '') < $threshold && ($item['backup_id'] ?? null) !== ($verified['backup_id'] ?? null))
            ->pluck('backup_id')
            ->filter(fn (mixed $id): bool => is_string($id) && $this->validId($id))
            ->values()
            ->all();
        $deleted = 0;
        if ($delete) {
            foreach ($ids as $id) {
                $this->disk()->deleteDirectory($this->basePath().'/'.$id);
                $deleted++;
            }
            AuditLog::create(['action' => 'backup.cleanup', 'metadata' => ['deleted' => $deleted]]);
        }

        return ['dry_run' => ! $delete, 'candidates' => count($ids), 'deleted' => $deleted, 'ids' => $ids];
    }

    /** @return array<string, mixed> */
    private function createSqlite(string $type): array
    {
        $source = (string) config('database.connections.sqlite.database');
        if ($source === ':memory:') {
            $temporary = tempnam(sys_get_temp_dir(), 'housechef-snapshot-');
            if ($temporary === false) {
                throw new RuntimeException('BACKUP_COPY_FAILED');
            }
            $this->secureDelete($temporary);
            DB::unprepared("VACUUM INTO '".str_replace("'", "''", $temporary)."'");
            $database = file_get_contents($temporary);
            $this->secureDelete($temporary);
        } else {
            if (! is_file($source)) {
                throw new RuntimeException('BACKUP_DATABASE_MISSING');
            }
            $database = file_get_contents($source);
        }
        if ($database === false) {
            throw new RuntimeException('BACKUP_COPY_FAILED');
        }

        return $this->storeBackup($type, 'sqlite', 'database.sqlite', $database, strlen($database));
    }

    /** @return array<string, mixed> */
    private function createMysql(string $type): array
    {
        $connection = config('database.connections.'.config('database.default'));
        if (! is_array($connection) || ! filled($connection['database'] ?? null)) {
            throw new RuntimeException('BACKUP_DUMP_FAILED');
        }

        $defaults = $this->writeMysqlDefaults($connection);
        $compressed = $this->temporaryFile('housechef-mysql-backup-', '.sql.gz');
        if ($compressed === null) {
            $this->secureDelete($defaults);
            throw new RuntimeException('BACKUP_GZIP_STREAM_FAILED');
        }

        try {
            $databaseSize = $this->dumpMysql($this->mysqlDumpBinary(), $defaults, (string) $connection['database'], $compressed);
            $contents = fopen($compressed, 'rb');
            if ($contents === false) {
                throw new RuntimeException('BACKUP_GZIP_STREAM_FAILED');
            }
            try {
                $checksum = hash_file('sha256', $compressed);
                if ($checksum === false) {
                    throw new RuntimeException('BACKUP_CHECKSUM_FAILED');
                }

                return $this->storeBackupStream($type, 'mysql', 'database.sql.gz', $contents, $checksum, $databaseSize);
            } finally {
                fclose($contents);
            }
        } finally {
            $this->secureDelete($compressed);
            $this->secureDelete($defaults);
        }
    }

    /** @return array<string, mixed> */
    private function storeBackup(string $type, string $driver, string $file, string $contents, int $databaseSize): array
    {
        $id = (string) Str::ulid();
        $base = $this->basePath().'/'.$id;
        try {
            $written = $this->disk()->put($base.'/'.$file, $contents);
        } catch (\Throwable) {
            throw new RuntimeException('BACKUP_STORAGE_WRITE_FAILED');
        }
        if (! $written) {
            throw new RuntimeException('BACKUP_STORAGE_WRITE_FAILED');
        }

        return $this->writeManifest($id, $type, $driver, $file, hash('sha256', $contents), $databaseSize);
    }

    /** @param resource $stream @return array<string, mixed> */
    private function storeBackupStream(string $type, string $driver, string $file, $stream, string $checksum, int $databaseSize): array
    {
        $id = (string) Str::ulid();
        $base = $this->basePath().'/'.$id;
        try {
            $written = $this->disk()->put($base.'/'.$file, $stream);
        } catch (\Throwable) {
            throw new RuntimeException('BACKUP_STORAGE_WRITE_FAILED');
        }
        if (! $written) {
            throw new RuntimeException('BACKUP_STORAGE_WRITE_FAILED');
        }

        return $this->writeManifest($id, $type, $driver, $file, $checksum, $databaseSize);
    }

    /** @return array<string, mixed> */
    private function writeManifest(string $id, string $type, string $driver, string $file, string $checksum, int $databaseSize): array
    {
        $manifest = [
            'backup_id' => $id,
            'type' => $this->safeType($type),
            'created_at' => now()->toIso8601String(),
            'app_version' => $this->releaseCommitSha() ? substr((string) $this->releaseCommitSha(), 0, 12) : 'unknown',
            'commit_sha' => $this->releaseCommitSha(),
            'database_driver' => $driver,
            'database_file' => $file,
            'database_size' => $databaseSize,
            'media_count' => 0,
            'checksum' => $checksum,
            'encryption_status' => 'storage-policy-dependent',
            'storage_location_type' => 'private_'.config('observability.backup_disk', 'local'),
            'status' => 'created',
            'verified_at' => null,
            'restore_test_status' => 'not_tested',
            'retention_date' => now()->addDays((int) config('observability.backup_retention_days'))->toDateString(),
            'source_environment' => app()->environment(),
        ];
        $this->putManifest($this->basePath().'/'.$id.'/manifest.json', $manifest);

        return $manifest;
    }

    private function verifySqlite(string $database): void
    {
        $pdo = new \PDO('sqlite:'.$database);
        $tables = $pdo->query("select name from sqlite_master where type='table'")->fetchAll(\PDO::FETCH_COLUMN);
        if (! in_array('migrations', $tables, true) || ! in_array('recipes', $tables, true)) {
            throw new RuntimeException('BACKUP_DATABASE_INVALID');
        }
    }

    private function verifyMysqlDump(string $compressed): void
    {
        $gzip = @gzopen($compressed, 'rb');
        if ($gzip === false) {
            throw new RuntimeException('BACKUP_GZIP_STREAM_FAILED');
        }

        $limit = max(1048576, (int) config('observability.backup_verify_max_sql_bytes', 33554432));
        $read = 0;
        $sample = '';
        try {
            while (! gzeof($gzip) && $read < $limit) {
                $chunk = gzread($gzip, min(8192, $limit - $read));
                if ($chunk === false) {
                    throw new RuntimeException('BACKUP_GZIP_STREAM_FAILED');
                }
                $read += strlen($chunk);
                $sample = substr($sample.$chunk, -32768);
                if (str_contains($sample, '`migrations`') && (str_contains($sample, 'CREATE TABLE') || str_contains($sample, '-- MySQL dump'))) {
                    return;
                }
            }
        } finally {
            gzclose($gzip);
        }

        throw new RuntimeException('BACKUP_DATABASE_INVALID');
    }

    private function dumpMysql(string $binary, string $defaults, string $database, string $compressed): int
    {
        $gzip = @gzopen($compressed, 'wb9');
        if ($gzip === false) {
            throw new RuntimeException('BACKUP_GZIP_STREAM_FAILED');
        }

        $bytes = 0;
        try {
            try {
                $process = new Process([$binary, '--defaults-extra-file='.$defaults, '--single-transaction', '--quick', '--skip-comments', '--routines', '--events', '--databases', $database]);
                $process->setTimeout(900);
                $process->run(function (string $type, string $buffer) use ($gzip, &$bytes): void {
                    if ($type === Process::OUT) {
                        $bytes += strlen($buffer);
                        $this->writeGzip($gzip, $buffer);
                    }
                });
            } catch (RuntimeException $exception) {
                throw $exception;
            } catch (\Throwable) {
                throw new RuntimeException('BACKUP_DUMP_STREAM_FAILED');
            }
            if (! $process->isSuccessful() || $bytes === 0) {
                throw new RuntimeException('BACKUP_DUMP_STREAM_FAILED');
            }
        } finally {
            if (gzclose($gzip) === false) {
                throw new RuntimeException('BACKUP_GZIP_STREAM_FAILED');
            }
        }

        return $bytes;
    }

    /** @param array<string, mixed> $connection */
    private function writeMysqlDefaults(array $connection): string
    {
        $path = $this->temporaryFile('housechef-mysql-client-', '.cnf');
        if ($path === null) {
            throw new RuntimeException('BACKUP_DUMP_STREAM_FAILED');
        }

        $values = [
            'host' => (string) ($connection['host'] ?? '127.0.0.1'),
            'port' => (string) ($connection['port'] ?? 3306),
            'user' => (string) ($connection['username'] ?? ''),
            'password' => (string) ($connection['password'] ?? ''),
        ];
        foreach ($values as $value) {
            if (str_contains($value, "\n") || str_contains($value, "\r") || str_contains($value, "\0")) {
                $this->secureDelete($path);
                throw new RuntimeException('BACKUP_DUMP_STREAM_FAILED');
            }
        }
        $contents = "[client]\n".collect($values)->map(fn (string $value, string $key): string => $key.'=\"'.$this->mysqlOptionValue($value).'\"')->implode("\n")."\n";
        if (file_put_contents($path, $contents) === false || ! @chmod($path, 0600)) {
            $this->secureDelete($path);
            throw new RuntimeException('BACKUP_DUMP_STREAM_FAILED');
        }

        return $path;
    }

    private function restoreMysqlDump(string $compressed, string $binary, string $defaults, string $database): void
    {
        $gzip = @gzopen($compressed, 'rb');
        if ($gzip === false) {
            throw new RuntimeException('BACKUP_GZIP_FAILED');
        }
        $pipes = [];
        $process = proc_open([$binary, '--defaults-extra-file='.$defaults, '--database='.$database], [0 => ['pipe', 'r'], 1 => ['pipe', 'w'], 2 => ['pipe', 'w']], $pipes, null, null, ['bypass_shell' => true]);
        if (! is_resource($process)) {
            gzclose($gzip);
            throw new RuntimeException('RESTORE_TEST_FAILED');
        }
        try {
            while (! gzeof($gzip)) {
                $chunk = gzread($gzip, 8192);
                if ($chunk === false || fwrite($pipes[0], $chunk) === false) {
                    throw new RuntimeException('RESTORE_TEST_FAILED');
                }
            }
            fclose($pipes[0]);
            stream_get_contents($pipes[1]);
            stream_get_contents($pipes[2]);
            if (proc_close($process) !== 0) {
                throw new RuntimeException('RESTORE_TEST_FAILED');
            }
        } finally {
            gzclose($gzip);
            foreach ([0, 1, 2] as $index) {
                if (isset($pipes[$index]) && is_resource($pipes[$index])) {
                    fclose($pipes[$index]);
                }
            }
        }
    }

    private function mysqlDumpBinary(): string
    {
        return $this->binary((string) config('observability.mysqldump_binary'), 'mysqldump', 'BACKUP_MYSQLDUMP_MISSING');
    }

    private function mysqlBinary(): string
    {
        return $this->binary((string) config('observability.mysql_binary'), 'mysql', 'BACKUP_MYSQLDUMP_MISSING');
    }

    private function binary(string $configured, string $name, string $error): string
    {
        if ($configured !== '') {
            if (is_file($configured) && is_executable($configured)) {
                return $configured;
            }
            throw new RuntimeException($error);
        }
        foreach (explode(PATH_SEPARATOR, (string) getenv('PATH')) as $directory) {
            $candidate = rtrim($directory, '/').'/'.$name;
            if (is_file($candidate) && is_executable($candidate)) {
                return $candidate;
            }
        }

        throw new RuntimeException($error);
    }

    private function copyFromDisk(string $path): string
    {
        $stream = $this->disk()->readStream($path);
        $temporary = tempnam(sys_get_temp_dir(), 'housechef-backup-');
        if (! is_resource($stream) || $temporary === false) {
            throw new RuntimeException('BACKUP_FILE_MISSING');
        }
        $destination = fopen($temporary, 'wb');
        if ($destination === false) {
            fclose($stream);
            $this->secureDelete($temporary);
            throw new RuntimeException('BACKUP_FILE_MISSING');
        }
        stream_copy_to_stream($stream, $destination);
        fclose($stream);
        fclose($destination);

        return $temporary;
    }

    private function disk(): FilesystemAdapter
    {
        return Storage::disk((string) config('observability.backup_disk', 'local'));
    }

    private function basePath(): string
    {
        return trim((string) config('observability.backup_path', 'backups'), '/');
    }

    private function databaseFile(string $driver): ?string
    {
        return match ($driver) {
            'sqlite' => 'database.sqlite',
            'mysql' => 'database.sql.gz',
            default => null,
        };
    }

    private function validId(string $id): bool
    {
        return preg_match('/^[0-9A-HJKMNP-TV-Z]{26}$/', $id) === 1;
    }

    private function safeType(string $type): string
    {
        return in_array($type, ['manual', 'scheduled', 'pre-deploy', 'pre-migration', 'daily', 'weekly'], true) ? $type : 'manual';
    }

    /** @param array<string, mixed> $value */
    private function json(array $value): string
    {
        $json = json_encode($value, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES);
        if ($json === false) {
            throw new RuntimeException('BACKUP_MANIFEST_WRITE_FAILED');
        }

        return $json;
    }

    /** @param array<string, mixed> $manifest */
    private function putManifest(string $path, array $manifest): void
    {
        try {
            $written = $this->disk()->put($path, $this->json($manifest));
        } catch (\Throwable) {
            throw new RuntimeException('BACKUP_MANIFEST_WRITE_FAILED');
        }
        if (! $written) {
            throw new RuntimeException('BACKUP_MANIFEST_WRITE_FAILED');
        }
    }

    private function releaseCommitSha(): ?string
    {
        $metadata = base_path('.house-chef-build-commit');
        $candidates = [
            is_readable($metadata) ? trim((string) file_get_contents($metadata)) : null,
            config('production.commit_sha'),
        ];
        foreach ($candidates as $candidate) {
            if (is_string($candidate) && preg_match('/^[0-9a-f]{7,64}$/', $candidate) === 1) {
                return $candidate;
            }
        }

        return null;
    }

    private function temporaryFile(string $prefix, string $suffix): ?string
    {
        $temporary = tempnam(sys_get_temp_dir(), $prefix);
        if ($temporary === false) {
            return null;
        }
        $target = $temporary.$suffix;
        if (! @rename($temporary, $target)) {
            $this->secureDelete($temporary);

            return null;
        }

        return $target;
    }

    /** @param resource $gzip */
    private function writeGzip($gzip, string $buffer): void
    {
        $offset = 0;
        $length = strlen($buffer);
        while ($offset < $length) {
            $written = gzwrite($gzip, substr($buffer, $offset));
            if ($written === false || $written === 0) {
                throw new RuntimeException('BACKUP_GZIP_STREAM_FAILED');
            }
            $offset += $written;
        }
    }

    private function secureDelete(string $path): void
    {
        if (! is_file($path)) {
            return;
        }
        $handle = @fopen($path, 'r+b');
        if ($handle !== false) {
            $size = filesize($path) ?: 0;
            if ($size > 0) {
                fwrite($handle, str_repeat("\0", min($size, 1048576)));
                fflush($handle);
            }
            fclose($handle);
        }
        @unlink($path);
    }

    private function mysqlOptionValue(string $value): string
    {
        return str_replace(['\\', '"'], ['\\\\', '\\"'], $value);
    }
}
