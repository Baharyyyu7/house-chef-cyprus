<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Console\Commands\BackupCreate;
use App\Exceptions\BackupException;
use App\Services\BackupService;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use ReflectionMethod;
use RuntimeException;
use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Console\Output\BufferedOutput;
use Tests\TestCase;

final class MySqlBackupServiceTest extends TestCase
{
    private string $dumpBinary;

    private string $argumentsLog;

    protected function setUp(): void
    {
        parent::setUp();

        putenv('HOUSECHEF_DUMP_MODE=success');
        Storage::fake('local');
        $this->argumentsLog = tempnam(sys_get_temp_dir(), 'housechef-mysqldump-log-');
        $this->dumpBinary = tempnam(sys_get_temp_dir(), 'housechef-mysqldump-');
        file_put_contents($this->dumpBinary, "#!/usr/bin/env php\n<?php\n\$defaults = '';\nforeach (\$argv as \$argument) { if (str_starts_with(\$argument, '--defaults-extra-file=')) { \$defaults = \$argument; break; } }\n\$path = substr(\$defaults, strlen('--defaults-extra-file='));\n\$isDump = in_array('--single-transaction', \$argv, true);\n\$mode = getenv('HOUSECHEF_DUMP_MODE') ?: 'success';\nfile_put_contents(".var_export($this->argumentsLog, true).", json_encode(['arguments' => \$argv, 'mode' => \$isDump ? 'dump' : 'restore', 'defaults_exists' => is_file(\$path), 'defaults_mode' => is_file(\$path) ? (fileperms(\$path) & 0777) : null, 'defaults_has_password' => is_file(\$path) && str_contains((string) file_get_contents(\$path), 'not-a-real-password')]));\nif (! \$isDump) { stream_get_contents(STDIN); exit(0); }\nif (\$mode === 'stderr') { fwrite(STDERR, str_repeat('diagnostic line ', 12000)); }\nif (\$mode === 'failure') { fwrite(STDERR, 'Access denied for user backup_user using password not-a-real-password'); exit(7); }\nif (\$mode === 'timeout') { sleep(3); exit(0); }\necho \"-- MySQL dump\\nCREATE TABLE `migrations` (`id` bigint);\\nCREATE TABLE `recipes` (`id` bigint);\\n\";");
        chmod($this->dumpBinary, 0700);

        config([
            'database.default' => 'mysql',
            'database.connections.mysql' => [
                'driver' => 'mysql',
                'host' => '127.0.0.1',
                'port' => 3306,
                'database' => 'housechef_backup_test',
                'username' => 'backup_user',
                'password' => 'not-a-real-password',
            ],
            'observability.backup_disk' => 'local',
            'observability.backup_path' => 'backups',
            'observability.mysqldump_binary' => $this->dumpBinary,
            'production.commit_sha' => 'c32602d098097e16c5be1609f36e01ba6dc55331',
        ]);
    }

    protected function tearDown(): void
    {
        putenv('HOUSECHEF_DUMP_MODE');
        @unlink($this->dumpBinary);
        @unlink($this->argumentsLog);

        parent::tearDown();
    }

    public function test_mysql_backup_uses_a_private_defaults_file_and_writes_a_verified_gzip_dump(): void
    {
        $backup = app(BackupService::class);
        $manifest = $backup->create('manual');

        $this->assertSame('mysql', $manifest['database_driver']);
        $this->assertSame('database.sql.gz', $manifest['database_file']);
        $this->assertSame('c32602d098097e16c5be1609f36e01ba6dc55331', $manifest['commit_sha']);
        $this->assertNotEmpty($manifest['checksum']);
        Storage::disk('local')->assertExists('backups/'.$manifest['backup_id'].'/database.sql.gz');

        $execution = json_decode((string) file_get_contents($this->argumentsLog), true);
        $this->assertIsArray($execution);
        $this->assertTrue($execution['defaults_exists']);
        $this->assertSame(0600, $execution['defaults_mode']);
        $this->assertTrue($execution['defaults_has_password']);
        $this->assertStringNotContainsString('not-a-real-password', implode(' ', $execution['arguments']));
        $this->assertStringContainsString('--defaults-extra-file=', implode(' ', $execution['arguments']));

        $verified = $backup->verify($manifest['backup_id']);
        $this->assertSame('verified', $verified['status']);
        $this->assertNotNull($verified['verified_at']);
    }

    public function test_mysql_restore_test_requires_a_distinct_testing_connection_and_keeps_password_out_of_arguments(): void
    {
        $backup = app(BackupService::class);
        $manifest = $backup->create();
        config([
            'database.connections.testing' => [
                'driver' => 'mysql',
                'host' => '127.0.0.1',
                'port' => 3306,
                'database' => 'housechef_restore_test',
                'username' => 'backup_user',
                'password' => 'not-a-real-password',
            ],
            'observability.mysql_binary' => $this->dumpBinary,
        ]);

        $restored = $backup->restoreTest($manifest['backup_id']);

        $this->assertSame('restored_testing_database', $restored['restore_test_status']);
        $execution = json_decode((string) file_get_contents($this->argumentsLog), true);
        $this->assertSame('restore', $execution['mode']);
        $this->assertStringContainsString('--database=housechef_restore_test', implode(' ', $execution['arguments']));
        $this->assertStringNotContainsString('not-a-real-password', implode(' ', $execution['arguments']));
    }

    public function test_mysql_defaults_use_an_unquoted_mariadb_numeric_port(): void
    {
        $path = $this->mysqlDefaults(['port' => '3306']);
        try {
            $contents = (string) file_get_contents($path);
            $this->assertStringContainsString("port=3306\n", $contents);
            $this->assertStringNotContainsString('port="3306"', $contents);
            $this->assertStringContainsString('user="backup_user"', $contents);
            $this->assertStringContainsString('password="not-a-real-password"', $contents);
        } finally {
            @unlink($path);
        }
    }

    public function test_mysql_defaults_support_a_custom_numeric_port(): void
    {
        $path = $this->mysqlDefaults(['port' => 3310]);
        try {
            $this->assertStringContainsString("port=3310\n", (string) file_get_contents($path));
        } finally {
            @unlink($path);
        }
    }

    public function test_mysql_defaults_fall_back_to_the_standard_port_for_invalid_values(): void
    {
        $path = $this->mysqlDefaults(['port' => '3306"']);
        try {
            $contents = (string) file_get_contents($path);
            $this->assertStringContainsString("port=3306\n", $contents);
            $this->assertStringNotContainsString('3306"', $contents);
        } finally {
            @unlink($path);
        }
    }

    public function test_mysql_backup_fails_safely_when_mysqldump_is_unavailable(): void
    {
        config(['observability.mysqldump_binary' => '/not/a/real/mysqldump']);

        try {
            app(BackupService::class)->create();
            $this->fail('Expected a safe mysqldump error.');
        } catch (RuntimeException $exception) {
            $this->assertSame('BACKUP_MYSQLDUMP_MISSING', $exception->getMessage());
        }
    }

    public function test_backup_commands_report_safe_json_codes_without_exception_details(): void
    {
        config(['observability.mysqldump_binary' => '/not/a/real/mysqldump']);

        $this->artisan('backup:create', ['--json' => true])
            ->expectsOutputToContain('BACKUP_MYSQLDUMP_MISSING')
            ->doesntExpectOutputToContain('not-a-real-password')
            ->assertFailed();
    }

    public function test_mysql_verify_reports_checksum_failure_after_tampering(): void
    {
        $manifest = app(BackupService::class)->create();
        Storage::disk('local')->put('backups/'.$manifest['backup_id'].'/database.sql.gz', 'tampered');

        $this->artisan('backup:verify', ['backup_id' => $manifest['backup_id'], '--json' => true])
            ->expectsOutputToContain('BACKUP_CHECKSUM_INVALID')
            ->assertFailed();
    }

    public function test_mysql_backup_drains_stderr_while_streaming_a_gzip_dump(): void
    {
        putenv('HOUSECHEF_DUMP_MODE=stderr');

        $manifest = app(BackupService::class)->create();

        Storage::disk('local')->assertExists('backups/'.$manifest['backup_id'].'/database.sql.gz');
        $this->assertSame('verified', app(BackupService::class)->verify($manifest['backup_id'])['status']);
    }

    public function test_mysql_backup_classifies_a_non_zero_dump_exit_without_leaking_credentials(): void
    {
        putenv('HOUSECHEF_DUMP_MODE=failure');

        try {
            app(BackupService::class)->create();
            $this->fail('Expected a classified dump failure.');
        } catch (RuntimeException $exception) {
            $this->assertSame('BACKUP_DUMP_EXIT_FAILED', $exception->getMessage());
            $this->assertInstanceOf(BackupException::class, $exception);
            $this->assertSame('mysql_access_denied', $exception->safeDiagnostic);
            $this->assertSame('Access denied user=[redacted] using password=[redacted]', $exception->safeStderrSummary);
        }

        Log::spy();
        $output = new BufferedOutput;
        $command = app(BackupCreate::class);
        $command->setLaravel($this->app);
        $status = $command->run(new ArrayInput(['--json' => true]), $output);
        $json = $output->fetch();
        $this->assertSame(1, $status);
        $this->assertStringContainsString('BACKUP_DUMP_EXIT_FAILED', $json);
        $this->assertStringContainsString('mysql_access_denied', $json);
        $this->assertStringContainsString('stderr_summary', $json);
        $this->assertStringNotContainsString('not-a-real-password', $json);
        Log::shouldHaveReceived('error')
            ->once()
            ->with('Backup creation failed.', \Mockery::on(fn (array $context): bool => ($context['stderr_summary'] ?? null) === 'Access denied user=[redacted] using password=[redacted]'));
    }

    public function test_mysql_backup_times_out_and_does_not_store_a_partial_gzip_file(): void
    {
        putenv('HOUSECHEF_DUMP_MODE=timeout');
        config(['observability.backup_dump_timeout_seconds' => 1]);

        try {
            app(BackupService::class)->create();
            $this->fail('Expected the dump to time out.');
        } catch (RuntimeException $exception) {
            $this->assertSame('BACKUP_DUMP_TIMEOUT', $exception->getMessage());
        }

        $this->assertSame([], Storage::disk('local')->allFiles('backups'));
    }

    /** @param array<string, mixed> $overrides */
    private function mysqlDefaults(array $overrides): string
    {
        $connection = array_replace((array) config('database.connections.mysql'), $overrides);
        $method = new ReflectionMethod(BackupService::class, 'writeMysqlDefaults');
        $path = $method->invoke(app(BackupService::class), $connection);
        if (! is_string($path)) {
            throw new RuntimeException('Defaults file path was not returned.');
        }

        return $path;
    }
}
